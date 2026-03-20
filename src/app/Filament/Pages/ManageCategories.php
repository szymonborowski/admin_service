<?php

namespace App\Filament\Pages;

use App\Services\BlogApiService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Str;

class ManageCategories extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-tag';
    protected static ?string $navigationLabel = 'Categories';
    protected static ?string $title = 'Manage Categories';
    protected static string $view = 'filament.pages.manage-categories';
    protected static ?int $navigationSort = 2;

    public array $categories = [];

    // Modal
    public bool $showCategoryModal = false;
    public bool $isEditing = false;
    public ?int $editCategoryId = null;

    // Form fields
    public string $categoryName = '';
    public string $categorySlug = '';
    public string $categoryColor = '';
    public ?int $categoryParentId = null;

    public static array $availableColors = [
        ''        => 'Default',
        'slate'   => 'Slate',
        'red'     => 'Red',
        'orange'  => 'Orange',
        'amber'   => 'Amber',
        'yellow'  => 'Yellow',
        'lime'    => 'Lime',
        'emerald' => 'Emerald',
        'teal'    => 'Teal',
        'cyan'    => 'Cyan',
        'sky'     => 'Sky',
        'blue'    => 'Blue',
        'indigo'  => 'Indigo',
        'violet'  => 'Violet',
        'purple'  => 'Purple',
        'pink'    => 'Pink',
        'rose'    => 'Rose',
    ];

    public static function colorHex(string $color): string
    {
        return match($color) {
            'slate'   => '#64748b',
            'red'     => '#ef4444',
            'orange'  => '#f97316',
            'amber'   => '#f59e0b',
            'yellow'  => '#eab308',
            'lime'    => '#84cc16',
            'emerald' => '#10b981',
            'teal'    => '#14b8a6',
            'cyan'    => '#06b6d4',
            'sky'     => '#0ea5e9',
            'blue'    => '#3b82f6',
            'indigo'  => '#6366f1',
            'violet'  => '#8b5cf6',
            'purple'  => '#a855f7',
            'pink'    => '#ec4899',
            'rose'    => '#f43f5e',
            default   => '#9ca3af',
        };
    }

    public function mount(): void
    {
        $this->loadCategories();
    }

    public function loadCategories(): void
    {
        $this->categories = app(BlogApiService::class)->getCategories();
    }

    public function openCreateModal(): void
    {
        $this->resetCategoryForm();
        $this->isEditing = false;
        $this->showCategoryModal = true;
    }

    public function openEditModal(int $id): void
    {
        $category = collect($this->categories)->firstWhere('id', $id);
        if (!$category) {
            return;
        }

        $this->editCategoryId = $category['id'];
        $this->categoryName = $category['name'] ?? '';
        $this->categorySlug = $category['slug'] ?? '';
        $this->categoryColor = $category['color'] ?? '';
        $this->categoryParentId = $category['parent_id'] ?? null;

        $this->isEditing = true;
        $this->showCategoryModal = true;
    }

    public function updatedCategoryName(): void
    {
        if (!$this->isEditing) {
            $this->categorySlug = Str::slug($this->categoryName);
        }
    }

    public function saveCategory(): void
    {
        $this->validate([
            'categoryName' => 'required|string|max:100',
            'categorySlug' => 'required|string|max:100',
        ]);

        $data = [
            'name'      => $this->categoryName,
            'slug'      => $this->categorySlug,
            'color'     => $this->categoryColor ?: null,
            'parent_id' => $this->categoryParentId,
        ];

        $service = app(BlogApiService::class);

        if ($this->isEditing && $this->editCategoryId) {
            $result = $service->updateCategory($this->editCategoryId, $data);
            $message = 'Category updated successfully';
        } else {
            $result = $service->createCategory($data);
            $message = 'Category created successfully';
        }

        $success = is_array($result) ? ($result['success'] ?? $result !== null) : (bool) $result;

        if ($success) {
            Notification::make()->title($message)->success()->send();
            $this->dispatch('close-modal', id: 'category-modal');
            $this->resetCategoryForm();
            $this->loadCategories();
        } else {
            $errors = '';
            if (is_array($result)) {
                $errors = collect($result['body']['errors'] ?? [])->flatten()->implode(' ');
                $errors = $errors ?: ($result['body']['message'] ?? "HTTP {$result['status']}");
            }
            Notification::make()->title('Failed to save category')->body($errors ?: 'Unknown error')->danger()->send();
        }
    }

    public function deleteCategory(int $id): void
    {
        $service = app(BlogApiService::class);

        if ($service->deleteCategory($id)) {
            Notification::make()->title('Category deleted')->success()->send();
            $this->loadCategories();
        } else {
            Notification::make()->title('Failed to delete category')->danger()->send();
        }
    }

    private function resetCategoryForm(): void
    {
        $this->editCategoryId = null;
        $this->categoryName = '';
        $this->categorySlug = '';
        $this->categoryColor = '';
        $this->categoryParentId = null;
    }
}

<?php

namespace App\Filament\Pages;

use App\Services\BlogApiService;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ManageSlides extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-photo';
    protected static ?string $navigationLabel = 'Slides';
    protected static ?string $title = 'Manage Slides';
    protected static string $view = 'filament.pages.manage-slides';
    protected static ?int $navigationSort = 2;

    public array $slides = [];

    // Create/Edit modal properties
    public bool $showSlideModal = false;
    public bool $isEditing = false;
    public ?int $editSlideId = null;
    public string $slideTitle = '';
    public string $slideType = 'image';
    public string $slideImageUrl = '';
    public string $slideHtmlContent = '';
    public string $slideLinkUrl = '';
    public string $slideLinkText = '';
    public int $slidePosition = 0;
    public bool $slideIsActive = true;

    public function mount(): void
    {
        $this->loadData();
    }

    public function loadData(): void
    {
        $service = app(BlogApiService::class);
        $this->slides = $service->getSlides();
    }

    public function openCreateModal(): void
    {
        $this->resetSlideForm();
        $this->slidePosition = count($this->slides);
        $this->isEditing = false;
        $this->showSlideModal = true;
    }

    public function openEditModal(int $id): void
    {
        $slide = collect($this->slides)->firstWhere('id', $id);
        if (!$slide) {
            return;
        }

        $this->editSlideId = $slide['id'];
        $this->slideTitle = $slide['title'] ?? '';
        $this->slideType = $slide['type'] ?? 'image';
        $this->slideImageUrl = $slide['image_url'] ?? '';
        $this->slideHtmlContent = $slide['html_content'] ?? '';
        $this->slideLinkUrl = $slide['link_url'] ?? '';
        $this->slideLinkText = $slide['link_text'] ?? '';
        $this->slidePosition = $slide['position'] ?? 0;
        $this->slideIsActive = $slide['is_active'] ?? true;
        $this->isEditing = true;
        $this->showSlideModal = true;
    }

    public function saveSlide(): void
    {
        $data = [
            'title' => $this->slideTitle,
            'type' => $this->slideType,
            'image_url' => $this->slideType === 'image' ? $this->slideImageUrl : null,
            'html_content' => $this->slideType === 'html' ? $this->slideHtmlContent : null,
            'link_url' => $this->slideLinkUrl ?: null,
            'link_text' => $this->slideLinkText ?: null,
            'position' => $this->slidePosition,
            'is_active' => $this->slideIsActive,
        ];

        $service = app(BlogApiService::class);

        if ($this->isEditing && $this->editSlideId) {
            $result = $service->updateSlide($this->editSlideId, $data);
            $message = 'Slide updated successfully';
        } else {
            $result = $service->createSlide($data);
            $message = 'Slide created successfully';
        }

        if ($result) {
            Notification::make()->title($message)->success()->send();
            $this->dispatch('close-modal', id: 'slide-modal');
            $this->closeSlideModal();
            $this->loadData();
        } else {
            Notification::make()->title('Failed to save slide')->danger()->send();
        }
    }

    public function closeSlideModal(): void
    {
        $this->showSlideModal = false;
        $this->resetSlideForm();
    }

    public function toggleActive(int $id): void
    {
        $slide = collect($this->slides)->firstWhere('id', $id);
        if (!$slide) {
            return;
        }

        $service = app(BlogApiService::class);
        $result = $service->updateSlide($id, [
            'is_active' => !$slide['is_active'],
        ]);

        if ($result) {
            Notification::make()->title('Slide status updated')->success()->send();
            $this->loadData();
        } else {
            Notification::make()->title('Failed to update status')->danger()->send();
        }
    }

    public function moveUp(int $id): void
    {
        $this->swapPosition($id, -1);
    }

    public function moveDown(int $id): void
    {
        $this->swapPosition($id, 1);
    }

    public function deleteSlide(int $id): void
    {
        $service = app(BlogApiService::class);

        if ($service->deleteSlide($id)) {
            Notification::make()->title('Slide deleted successfully')->success()->send();
            $this->loadData();
        } else {
            Notification::make()->title('Failed to delete slide')->danger()->send();
        }
    }

    private function swapPosition(int $id, int $direction): void
    {
        $slides = collect($this->slides)->sortBy('position')->values();
        $index = $slides->search(fn($s) => $s['id'] === $id);

        if ($index === false) {
            return;
        }

        $targetIndex = $index + $direction;
        if ($targetIndex < 0 || $targetIndex >= $slides->count()) {
            return;
        }

        $reorder = [];
        $current = $slides[$index];
        $target = $slides[$targetIndex];

        $reorder[] = ['id' => $current['id'], 'position' => $target['position']];
        $reorder[] = ['id' => $target['id'], 'position' => $current['position']];

        $service = app(BlogApiService::class);
        if ($service->reorderSlides($reorder)) {
            $this->loadData();
        }
    }

    private function resetSlideForm(): void
    {
        $this->editSlideId = null;
        $this->slideTitle = '';
        $this->slideType = 'image';
        $this->slideImageUrl = '';
        $this->slideHtmlContent = '';
        $this->slideLinkUrl = '';
        $this->slideLinkText = '';
        $this->slidePosition = 0;
        $this->slideIsActive = true;
    }
}

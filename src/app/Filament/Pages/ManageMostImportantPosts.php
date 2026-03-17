<?php

namespace App\Filament\Pages;

use App\Services\BlogApiService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ManageMostImportantPosts extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-star';
    protected static ?string $navigationLabel = 'Start Here';
    protected static ?string $navigationGroup = 'CMS';
    protected static ?string $title = 'Start Here — Widget';
    protected static string $view = 'filament.pages.manage-most-important-posts';
    protected static ?int $navigationSort = 1;

    public array $featuredPosts = [];
    public array $allPosts = [];
    public int|string $selectedPostId = '';

    public function mount(): void
    {
        $this->loadData();
    }

    public function loadData(): void
    {
        $service = app(BlogApiService::class);
        $this->featuredPosts = $service->getFeaturedPosts();

        $posts = $service->getPosts(['status' => 'published', 'per_page' => 100]);
        $this->allPosts = $posts['data'] ?? [];
    }

    public function openAddModal(): void
    {
        $this->selectedPostId = '';
    }

    public function closeAddModal(): void
    {
        $this->selectedPostId = '';
    }

    public function addPost(): void
    {
        if (!$this->selectedPostId) {
            Notification::make()->title('Please select a post')->warning()->send();
            return;
        }

        $service = app(BlogApiService::class);
        $result = $service->addFeaturedPost((int) $this->selectedPostId);

        if ($result['success']) {
            Notification::make()->title('Post added to the list')->success()->send();
            $this->dispatch('close-modal', id: 'add-post-modal');
            $this->closeAddModal();
            $this->loadData();
        } else {
            $message = $result['data']['message'] ?? 'Unknown error';
            Notification::make()
                ->title("Error {$result['status']}: {$message}")
                ->danger()
                ->send();
        }
    }

    public function removePost(int $id): void
    {
        $service = app(BlogApiService::class);

        if ($service->removeFeaturedPost($id)) {
            Notification::make()->title('Post removed from the list')->success()->send();
            $this->loadData();
        } else {
            Notification::make()->title('Failed to remove post')->danger()->send();
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

    private function swapPosition(int $id, int $direction): void
    {
        $items = collect($this->featuredPosts)->sortBy('position')->values();
        $index = $items->search(fn($i) => $i['id'] === $id);

        if ($index === false) {
            return;
        }

        $targetIndex = $index + $direction;
        if ($targetIndex < 0 || $targetIndex >= $items->count()) {
            return;
        }

        $current = $items[$index];
        $target  = $items[$targetIndex];

        $service = app(BlogApiService::class);
        $service->reorderFeaturedPosts([
            ['id' => $current['id'], 'position' => $target['position']],
            ['id' => $target['id'],  'position' => $current['position']],
        ]);

        $this->loadData();
    }

    public function getAvailablePosts(): array
    {
        $featuredIds = collect($this->featuredPosts)
            ->pluck('post_id')
            ->toArray();

        return collect($this->allPosts)
            ->reject(fn($p) => \in_array($p['id'], $featuredIds))
            ->values()
            ->toArray();
    }
}

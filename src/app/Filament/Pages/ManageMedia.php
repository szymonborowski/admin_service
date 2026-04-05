<?php

namespace App\Filament\Pages;

use App\Services\BlogApiService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Livewire\WithFileUploads;

class ManageMedia extends Page
{
    use WithFileUploads;

    protected static ?string $navigationIcon = 'heroicon-o-photo';
    protected static ?string $navigationLabel = 'Media';
    protected static ?string $title = 'Manage Media';
    protected static string $view = 'filament.pages.manage-media';
    protected static ?int $navigationSort = 5;

    public array $mediaItems = [];
    public array $meta = [];
    public int $currentPage = 1;
    public string $search = '';

    // Upload modal
    public $mediaFile = null;
    public string $mediaAlt = '';

    // Edit alt modal
    public ?int $editMediaId = null;
    public string $editAlt = '';

    public function mount(): void
    {
        $this->loadMedia();
    }

    public function loadMedia(): void
    {
        $query = [
            'page' => $this->currentPage,
            'per_page' => 24,
        ];

        if ($this->search !== '') {
            $query['search'] = $this->search;
        }

        $result = app(BlogApiService::class)->getMedia($query);

        $this->mediaItems = $result['data'] ?? [];
        $this->meta = $result['meta'] ?? [];
    }

    public function updatedSearch(): void
    {
        $this->currentPage = 1;
        $this->loadMedia();
    }

    public function nextPage(): void
    {
        if ($this->currentPage < ($this->meta['last_page'] ?? 1)) {
            $this->currentPage++;
            $this->loadMedia();
        }
    }

    public function prevPage(): void
    {
        if ($this->currentPage > 1) {
            $this->currentPage--;
            $this->loadMedia();
        }
    }

    public function uploadMedia(): void
    {
        $this->validate([
            'mediaFile' => 'required|file|max:10240|mimes:jpg,jpeg,png,gif,webp,svg',
            'mediaAlt' => 'nullable|string|max:255',
        ]);

        $result = app(BlogApiService::class)->uploadMedia(
            $this->mediaFile,
            $this->mediaAlt ?: null,
        );

        if ($result['success']) {
            Notification::make()->title('Media uploaded successfully')->success()->send();
            $this->dispatch('close-modal', id: 'upload-modal');
            $this->resetUploadForm();
            $this->loadMedia();
        } else {
            $errors = '';
            if (is_array($result)) {
                $errors = collect($result['body']['errors'] ?? [])->flatten()->implode(' ');
                $errors = $errors ?: ($result['body']['message'] ?? "HTTP {$result['status']}");
            }
            Notification::make()->title('Upload failed')->body($errors ?: 'Unknown error')->danger()->send();
        }
    }

    public function openEditAltModal(int $id): void
    {
        $media = collect($this->mediaItems)->firstWhere('id', $id);
        if (!$media) {
            return;
        }

        $this->editMediaId = $id;
        $this->editAlt = $media['alt'] ?? '';
    }

    public function saveAlt(): void
    {
        $this->validate([
            'editAlt' => 'nullable|string|max:255',
        ]);

        $result = app(BlogApiService::class)->updateMedia($this->editMediaId, [
            'alt' => $this->editAlt ?: null,
        ]);

        if ($result['success']) {
            Notification::make()->title('Alt text updated')->success()->send();
            $this->dispatch('close-modal', id: 'edit-alt-modal');
            $this->editMediaId = null;
            $this->editAlt = '';
            $this->loadMedia();
        } else {
            Notification::make()->title('Failed to update alt text')->danger()->send();
        }
    }

    public function deleteMedia(int $id): void
    {
        if (app(BlogApiService::class)->deleteMedia($id)) {
            Notification::make()->title('Media deleted')->success()->send();
            $this->loadMedia();
        } else {
            Notification::make()->title('Failed to delete media')->danger()->send();
        }
    }

    private function resetUploadForm(): void
    {
        $this->mediaFile = null;
        $this->mediaAlt = '';
    }

    private function formatFileSize(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 1) . ' MB';
        }

        return round($bytes / 1024, 1) . ' KB';
    }
}

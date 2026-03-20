<?php

namespace App\Filament\Pages;

use App\Services\BlogApiService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ManagePosts extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Posts';
    protected static ?string $title = 'Manage Posts';
    protected static string $view = 'filament.pages.manage-posts';
    protected static ?int $navigationSort = 1;

    public array $posts = [];
    public array $meta = [];
    public int $currentPage = 1;
    public string $search = '';
    public string $statusFilter = '';
    public string $localeFilter = '';

    // Modal
    public bool $showPostModal = false;
    public bool $isEditing = false;
    public ?int $editPostId = null;

    // Form fields
    public string $postTitle = '';
    public string $postSlug = '';
    public string $postExcerpt = '';
    public string $postContent = '';
    public string $postStatus = 'draft';
    public string $postLocale = 'pl';
    public string $postPublishedAt = '';
    public array $postCategoryIds = [];
    public array $postTagIds = [];

    // Dropdowns
    public array $categories = [];
    public array $tags = [];

    public function mount(): void
    {
        $this->loadDropdowns();
        $this->loadPosts();
    }

    public function loadDropdowns(): void
    {
        $service = app(BlogApiService::class);
        $this->categories = $service->getCategories();
        $this->tags = $service->getTags();
    }

    public function loadPosts(): void
    {
        $service = app(BlogApiService::class);

        $query = ['page' => $this->currentPage, 'per_page' => 15];

        if ($this->search !== '') {
            $query['search'] = $this->search;
        }

        if ($this->statusFilter !== '') {
            $query['status'] = $this->statusFilter;
        }

        if ($this->localeFilter !== '') {
            $query['locale'] = $this->localeFilter;
        }

        $result = $service->getPosts($query);
        $this->posts = $result['data'] ?? [];
        $this->meta = $result['meta'] ?? [];
    }

    public function updatedSearch(): void
    {
        $this->currentPage = 1;
        $this->loadPosts();
    }

    public function updatedStatusFilter(): void
    {
        $this->currentPage = 1;
        $this->loadPosts();
    }

    public function updatedLocaleFilter(): void
    {
        $this->currentPage = 1;
        $this->loadPosts();
    }

    public function nextPage(): void
    {
        $lastPage = $this->meta['last_page'] ?? 1;
        if ($this->currentPage < $lastPage) {
            $this->currentPage++;
            $this->loadPosts();
        }
    }

    public function prevPage(): void
    {
        if ($this->currentPage > 1) {
            $this->currentPage--;
            $this->loadPosts();
        }
    }

    public function openCreateModal(): void
    {
        $this->resetPostForm();
        $this->isEditing = false;
        $this->showPostModal = true;
    }

    public function openEditModal(int $id): void
    {
        $post = collect($this->posts)->firstWhere('id', $id);
        if (!$post) {
            return;
        }

        $this->editPostId = $post['id'];
        $this->postTitle = $post['title'] ?? '';
        $this->postSlug = $post['slug'] ?? '';
        $this->postExcerpt = $post['excerpt'] ?? '';
        $this->postContent = $post['content'] ?? '';
        $this->postStatus = $post['status'] ?? 'draft';
        $this->postPublishedAt = isset($post['published_at'])
            ? \Carbon\Carbon::parse($post['published_at'])->format('Y-m-d\TH:i')
            : '';
        $this->postLocale = $post['locale'] ?? 'pl';
        $this->postCategoryIds = collect($post['categories'] ?? [])->pluck('id')->map(fn($id) => (string) $id)->toArray();
        $this->postTagIds = collect($post['tags'] ?? [])->pluck('id')->map(fn($id) => (string) $id)->toArray();

        $this->isEditing = true;
        $this->showPostModal = true;
    }

    public function updatedPostTitle(): void
    {
        if (!$this->isEditing) {
            $this->postSlug = \Illuminate\Support\Str::slug($this->postTitle);
        }
    }

    public function savePost(): void
    {
        $this->validate([
            'postTitle'   => 'required|string|max:255',
            'postSlug'    => 'required|string|max:255',
            'postContent' => 'required|string',
            'postStatus'  => 'required|in:draft,published,archived',
        ]);

        $data = [
            'title'        => $this->postTitle,
            'slug'         => $this->postSlug,
            'excerpt'      => $this->postExcerpt ?: null,
            'content'      => $this->postContent,
            'status'       => $this->postStatus,
            'locale'       => $this->postLocale,
            'published_at' => $this->postPublishedAt ?: null,
            'category_ids' => array_map('intval', $this->postCategoryIds),
            'tag_ids'      => array_map('intval', $this->postTagIds),
        ];

        $service = app(BlogApiService::class);

        if ($this->isEditing && $this->editPostId) {
            $result = $service->updatePost($this->editPostId, $data);
            $message = 'Post updated successfully';
        } else {
            $result = $service->createPost($data);
            $message = 'Post created successfully';
        }

        if ($result['success']) {
            Notification::make()->title($message)->success()->send();
            $this->dispatch('close-modal', id: 'post-modal');
            $this->resetPostForm();
            $this->loadPosts();
        } else {
            $errors = collect($result['body']['errors'] ?? [])->flatten()->implode(' ');
            $body = $errors ?: ($result['body']['message'] ?? "HTTP {$result['status']}");
            Notification::make()->title('Failed to save post')->body($body)->danger()->send();
        }
    }

    public function deletePost(int $id): void
    {
        $service = app(BlogApiService::class);

        if ($service->deletePost($id)) {
            Notification::make()->title('Post deleted')->success()->send();
            $this->loadPosts();
        } else {
            Notification::make()->title('Failed to delete post')->danger()->send();
        }
    }

    private function resetPostForm(): void
    {
        $this->editPostId = null;
        $this->postTitle = '';
        $this->postSlug = '';
        $this->postExcerpt = '';
        $this->postContent = '';
        $this->postStatus = 'draft';
        $this->postLocale = 'pl';
        $this->postPublishedAt = '';
        $this->postCategoryIds = [];
        $this->postTagIds = [];
    }
}

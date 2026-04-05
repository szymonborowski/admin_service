<x-filament-panels::page>
    <div class="space-y-6">

        {{-- Toolbar --}}
        <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-center justify-between">
            <div class="flex gap-3 w-full sm:w-auto">
                <x-filament::input.wrapper class="w-64">
                    <x-filament::input
                        type="text"
                        wire:model.live.debounce.400ms="search"
                        placeholder="Search posts..."
                    />
                </x-filament::input.wrapper>

                <select
                    wire:model.live="statusFilter"
                    class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white text-sm"
                >
                    <option value="">All statuses</option>
                    <option value="draft">Draft</option>
                    <option value="published">Published</option>
                    <option value="archived">Archived</option>
                </select>

                <select
                    wire:model.live="localeFilter"
                    class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white text-sm"
                >
                    <option value="">All locales</option>
                    <option value="pl">PL</option>
                    <option value="en">EN</option>
                </select>
            </div>

            <x-filament::button
                x-on:click="$dispatch('open-modal', { id: 'post-modal' }); $wire.openCreateModal()"
                icon="heroicon-o-plus"
            >
                New Post
            </x-filament::button>
        </div>

        {{-- Posts Table --}}
        <x-filament::section>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs uppercase bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-4 py-3">Title</th>
                            <th class="px-4 py-3 w-32 hidden lg:table-cell">Author</th>
                            <th class="px-4 py-3 w-28">Status</th>
                            <th class="px-4 py-3 w-16 hidden sm:table-cell">Locale</th>
                            <th class="px-4 py-3 w-16 hidden sm:table-cell">Ver.</th>
                            <th class="px-4 py-3 w-36 hidden md:table-cell">Categories</th>
                            <th class="px-4 py-3 w-36 hidden lg:table-cell">Published</th>
                            <th class="px-4 py-3 w-24">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($posts as $post)
                            <tr class="border-b dark:border-gray-700 dark:hover:bg-white/5">
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-900 dark:text-white">{{ $post['title'] }}</div>
                                    <div class="text-xs text-gray-400 mt-0.5">{{ $post['slug'] }}</div>
                                </td>
                                <td class="px-4 py-3 hidden lg:table-cell text-gray-500 dark:text-gray-400 text-xs">
                                    {{ $post['author']['name'] ?? '—' }}
                                </td>
                                <td class="px-4 py-3 hidden sm:table-cell">
                                    <span class="inline-flex items-center px-2 py-0.5 text-xs font-semibold rounded bg-indigo-50 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300 uppercase">
                                        {{ $post['locale'] ?? 'pl' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 hidden sm:table-cell text-gray-500 text-xs text-center">
                                    v{{ $post['version'] ?? 1 }}
                                </td>
                                <td class="px-4 py-3">
                                    @php
                                        $statusColors = [
                                            'published' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                                            'draft'     => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                                            'archived'  => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300',
                                        ];
                                    @endphp
                                    <span class="inline-flex items-center px-2 py-0.5 text-xs font-medium rounded-full {{ $statusColors[$post['status']] ?? '' }}">
                                        {{ ucfirst($post['status']) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 hidden md:table-cell">
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($post['categories'] ?? [] as $cat)
                                            <span class="inline-flex items-center px-1.5 py-0.5 text-xs rounded bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">
                                                {{ $cat['name'] }}
                                            </span>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="px-4 py-3 hidden lg:table-cell text-gray-500 text-xs">
                                    {{ isset($post['published_at']) ? \Carbon\Carbon::parse($post['published_at'])->format('d M Y') : '—' }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <x-filament::icon-button
                                            x-on:click="$dispatch('open-modal', { id: 'post-modal' }); $wire.openEditModal({{ $post['id'] }})"
                                            icon="heroicon-o-pencil-square"
                                            color="warning"
                                            size="sm"
                                            label="Edit"
                                        />
                                        <x-filament::icon-button
                                            wire:click="deletePost({{ $post['id'] }})"
                                            wire:confirm="Are you sure you want to delete '{{ addslashes($post['title']) }}'?"
                                            icon="heroicon-o-trash"
                                            color="danger"
                                            size="sm"
                                            label="Delete"
                                        />
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-10 text-center text-gray-400">
                                    No posts found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if(($meta['last_page'] ?? 1) > 1)
                <div class="mt-4 flex items-center justify-between text-sm text-gray-500">
                    <span>Page {{ $meta['current_page'] ?? 1 }} of {{ $meta['last_page'] ?? 1 }} ({{ $meta['total'] ?? 0 }} posts)</span>
                    <div class="flex gap-2">
                        <x-filament::button wire:click="prevPage" color="gray" size="sm" :disabled="$currentPage <= 1">
                            Previous
                        </x-filament::button>
                        <x-filament::button wire:click="nextPage" color="gray" size="sm" :disabled="$currentPage >= ($meta['last_page'] ?? 1)">
                            Next
                        </x-filament::button>
                    </div>
                </div>
            @endif
        </x-filament::section>
    </div>

    {{-- Create/Edit Post Modal --}}
    <x-filament::modal id="post-modal" width="4xl" :close-by-clicking-away="false">
            <x-slot name="heading">
                {{ $isEditing ? 'Edit Post' : 'New Post' }}
            </x-slot>

            <div class="space-y-4">
                {{-- Title + Slug --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Title <span class="text-red-500">*</span></label>
                        <x-filament::input.wrapper>
                            <x-filament::input type="text" wire:model.live="postTitle" placeholder="Post title" />
                        </x-filament::input.wrapper>
                        @error('postTitle') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Slug <span class="text-red-500">*</span></label>
                        <x-filament::input.wrapper>
                            <x-filament::input type="text" wire:model.live="postSlug" placeholder="post-slug" />
                        </x-filament::input.wrapper>
                        @error('postSlug') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Excerpt --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Excerpt</label>
                    <textarea
                        wire:model.live="postExcerpt"
                        rows="2"
                        class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white text-sm"
                        placeholder="Short description..."
                    ></textarea>
                </div>

                {{-- Content --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Content <span class="text-red-500">*</span></label>
                    <div wire:ignore>
                        <div
                            x-data="{ editor: null }"
                            x-init="
                                editor = new EasyMDE({
                                    element: $refs.mde,
                                    initialValue: $wire.postContent ?? '',
                                    autoDownloadFontAwesome: false,
                                    spellChecker: false,
                                    toolbar: [
                                        'bold','italic','heading-1','heading-2','|',
                                        'quote','code','|',
                                        'unordered-list','ordered-list','|',
                                        'link',
                                        {
                                            name: 'insert-image',
                                            action: () => {
                                                $wire.openMediaPicker();
                                                $dispatch('open-modal', { id: 'media-picker-modal' });
                                            },
                                            className: 'fa fa-image',
                                            title: 'Insert Image',
                                        },
                                        '|','preview','guide'
                                    ],
                                    minHeight: '320px',
                                    placeholder: 'Post content (Markdown)...',
                                });
                                editor.codemirror.on('change', () => {
                                    $wire.set('postContent', editor.value(), false);
                                });
                                $wire.$watch('postContent', value => {
                                    if (editor.value() !== value) {
                                        editor.value(value ?? '');
                                    }
                                });
                                Livewire.on('insert-markdown-image', ({ alt, url }) => {
                                    const cm = editor.codemirror;
                                    const cursor = cm.getCursor();
                                    cm.replaceRange('![' + alt + '](' + url + ')\n', cursor);
                                    cm.focus();
                                });
                            "
                        >
                            <textarea x-ref="mde"></textarea>
                        </div>
                    </div>
                    @error('postContent') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                {{-- Status + Published At + Locale --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Status <span class="text-red-500">*</span></label>
                        <select
                            wire:model.live="postStatus"
                            class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white text-sm"
                        >
                            <option value="draft">Draft</option>
                            <option value="published">Published</option>
                            <option value="archived">Archived</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Language</label>
                        <select
                            wire:model.live="postLocale"
                            class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white text-sm"
                        >
                            <option value="pl">Polish (PL)</option>
                            <option value="en">English (EN)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Publish date</label>
                        <x-filament::input.wrapper>
                            <x-filament::input type="datetime-local" wire:model.live="postPublishedAt" />
                        </x-filament::input.wrapper>
                    </div>
                </div>

                {{-- Categories + Tags --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Categories</label>
                        <div class="rounded-lg border border-gray-300 dark:border-gray-700 divide-y divide-gray-100 dark:divide-gray-700 max-h-40 overflow-y-auto">
                            @foreach($categories as $category)
                                <label class="flex items-center gap-3 px-3 py-2 cursor-pointer hover:bg-gray-50 dark:hover:bg-white/5">
                                    <input
                                        type="checkbox"
                                        wire:model.live="postCategoryIds"
                                        value="{{ $category['id'] }}"
                                        class="rounded border-gray-300 dark:border-gray-600 text-primary-600"
                                    >
                                    <span class="text-sm text-gray-700 dark:text-gray-200">{{ $category['name'] }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Tags</label>
                        <div class="rounded-lg border border-gray-300 dark:border-gray-700 divide-y divide-gray-100 dark:divide-gray-700 max-h-40 overflow-y-auto">
                            @foreach($tags as $tag)
                                <label class="flex items-center gap-3 px-3 py-2 cursor-pointer hover:bg-gray-50 dark:hover:bg-white/5">
                                    <input
                                        type="checkbox"
                                        wire:model.live="postTagIds"
                                        value="{{ $tag['id'] }}"
                                        class="rounded border-gray-300 dark:border-gray-600 text-primary-600"
                                    >
                                    <span class="text-sm text-gray-700 dark:text-gray-200">{{ $tag['name'] }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <x-slot name="footerActions">
                <x-filament::button x-on:click="$dispatch('close-modal', { id: 'post-modal' })" color="gray">
                    Cancel
                </x-filament::button>
                <x-filament::button wire:click="savePost" color="primary">
                    {{ $isEditing ? 'Update Post' : 'Create Post' }}
                </x-filament::button>
            </x-slot>
        </x-filament::modal>

    {{-- Media Picker Modal --}}
    <x-filament::modal id="media-picker-modal" width="4xl">
        <x-slot name="heading">
            Insert Image
        </x-slot>

        <div class="space-y-4">
            <x-filament::input.wrapper class="max-w-xs">
                <x-filament::input
                    type="search"
                    wire:model.live.debounce.400ms="pickerSearch"
                    placeholder="Search media..."
                />
            </x-filament::input.wrapper>

            <div class="grid grid-cols-3 sm:grid-cols-4 gap-3">
                @forelse($pickerMedia as $media)
                    <button
                        wire:click="selectMedia({{ $media['id'] }})"
                        type="button"
                        class="relative aspect-square rounded-lg overflow-hidden border-2 border-transparent hover:border-primary-500 transition cursor-pointer"
                        style="background: rgb(17,24,39)"
                    >
                        @if(str_starts_with($media['mime_type'], 'image/'))
                            <img
                                src="{{ $media['variant_urls']['thumbnail'] ?? $media['url'] }}"
                                alt="{{ $media['alt'] ?? $media['filename'] }}"
                                class="w-full h-full object-cover"
                                loading="lazy"
                            >
                        @else
                            <div class="w-full h-full flex items-center justify-center">
                                <svg class="w-8 h-8 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                </svg>
                            </div>
                        @endif
                        <div class="absolute bottom-0 inset-x-0 px-1.5 py-1 text-xs text-white truncate" style="background:rgba(0,0,0,0.6)">
                            {{ $media['filename'] }}
                        </div>
                    </button>
                @empty
                    <div class="col-span-full py-8 text-center text-gray-400">
                        No media found.
                    </div>
                @endforelse
            </div>

            @if(($pickerMeta['last_page'] ?? 1) > 1)
                <div class="flex items-center justify-between text-sm text-gray-500">
                    <span>Page {{ $pickerMeta['current_page'] ?? 1 }} of {{ $pickerMeta['last_page'] ?? 1 }}</span>
                    <div class="flex gap-2">
                        <x-filament::button wire:click="pickerPrevPage" color="gray" size="sm" :disabled="$pickerPage <= 1">
                            Previous
                        </x-filament::button>
                        <x-filament::button wire:click="pickerNextPage" color="gray" size="sm" :disabled="$pickerPage >= ($pickerMeta['last_page'] ?? 1)">
                            Next
                        </x-filament::button>
                    </div>
                </div>
            @endif
        </div>

        <x-slot name="footerActions">
            <x-filament::button x-on:click="$dispatch('close-modal', { id: 'media-picker-modal' })" color="gray">
                Cancel
            </x-filament::button>
        </x-slot>
    </x-filament::modal>
</x-filament-panels::page>

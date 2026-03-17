<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex justify-between items-center">
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Posts displayed in the "Most Important Posts" widget on the homepage. Drag or use arrows to reorder.
            </p>
            <x-filament::button
                x-on:click="$dispatch('open-modal', { id: 'add-post-modal' }); $wire.openAddModal()"
                icon="heroicon-o-plus"
            >
                Add Post
            </x-filament::button>
        </div>

        {{-- Featured Posts Table --}}
        <x-filament::section>
            <x-slot name="heading">Current List</x-slot>

            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs uppercase bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-4 py-3 w-12">#</th>
                            <th class="px-4 py-3">Title</th>
                            <th class="px-4 py-3 w-32">Published</th>
                            <th class="px-4 py-3 w-24">Order</th>
                            <th class="px-4 py-3 w-24">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($featuredPosts as $item)
                            <tr class="border-b dark:border-gray-700">
                                <td class="px-4 py-3 text-gray-400">{{ $item['position'] + 1 }}</td>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-900 dark:text-white">
                                        {{ $item['post']['title'] ?? '—' }}
                                    </div>
                                    @if(!empty($item['post']['excerpt']))
                                        <div class="text-xs text-gray-400 line-clamp-1 mt-0.5">
                                            {{ $item['post']['excerpt'] }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-gray-500 text-xs">
                                    {{ isset($item['post']['published_at'])
                                        ? \Carbon\Carbon::parse($item['post']['published_at'])->format('d.m.Y')
                                        : '—' }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-1">
                                        <x-filament::icon-button
                                            wire:click="moveUp({{ $item['id'] }})"
                                            icon="heroicon-o-chevron-up"
                                            color="gray"
                                            size="sm"
                                            label="Move up"
                                        />
                                        <x-filament::icon-button
                                            wire:click="moveDown({{ $item['id'] }})"
                                            icon="heroicon-o-chevron-down"
                                            color="gray"
                                            size="sm"
                                            label="Move down"
                                        />
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <x-filament::icon-button
                                        wire:click="removePost({{ $item['id'] }})"
                                        wire:confirm="Remove this post from the list?"
                                        icon="heroicon-o-trash"
                                        color="danger"
                                        size="sm"
                                        label="Remove"
                                    />
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                                    No posts in the list yet. Add your first post.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                <x-filament::button wire:click="loadData" icon="heroicon-o-arrow-path" color="gray">
                    Refresh
                </x-filament::button>
            </div>
        </x-filament::section>
    </div>

    {{-- Add Post Modal --}}
    <x-filament::modal id="add-post-modal" :close-by-clicking-away="false" width="lg">
        <x-slot name="heading">Add Post to List</x-slot>

        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Select Post
                </label>
                <select
                    wire:model="selectedPostId"
                    class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white text-sm"
                >
                    <option value="">— choose a post —</option>
                    @foreach($this->getAvailablePosts() as $post)
                        <option value="{{ $post['id'] }}">{{ $post['title'] }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <x-slot name="footerActions">
            <x-filament::button
                x-on:click="$dispatch('close-modal', { id: 'add-post-modal' }); $wire.closeAddModal()"
                color="gray"
            >
                Cancel
            </x-filament::button>
            <x-filament::button wire:click="addPost" color="primary">
                Add to List
            </x-filament::button>
        </x-slot>
    </x-filament::modal>
</x-filament-panels::page>

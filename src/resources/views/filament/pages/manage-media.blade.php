<x-filament-panels::page>
    <div class="space-y-6">

        {{-- Toolbar --}}
        <div class="flex items-center justify-between gap-4">
            <div class="flex-1 max-w-xs">
                <x-filament::input.wrapper>
                    <x-filament::input
                        type="search"
                        wire:model.live.debounce.400ms="search"
                        placeholder="Search by filename..."
                    />
                </x-filament::input.wrapper>
            </div>
            <x-filament::button
                x-on:click="$dispatch('open-modal', { id: 'upload-modal' })"
                icon="heroicon-o-arrow-up-tray"
            >
                Upload
            </x-filament::button>
        </div>

        {{-- Media Grid --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
            @forelse($mediaItems as $media)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                    {{-- Thumbnail --}}
                    <div class="relative aspect-square bg-gray-100 dark:bg-gray-900 flex items-center justify-center overflow-hidden">
                        @if(str_starts_with($media['mime_type'], 'image/'))
                            <img
                                src="{{ $media['variant_urls']['thumbnail'] ?? $media['url'] }}"
                                alt="{{ $media['alt'] ?? $media['filename'] }}"
                                class="w-full h-full object-cover"
                                loading="lazy"
                            >
                        @else
                            <svg class="w-10 h-10 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                            </svg>
                        @endif

                        {{-- Actions --}}
                        <div style="position:absolute;top:8px;right:8px;z-index:10;display:flex;gap:6px">
                        {{-- Copy Markdown --}}
                        <button
                            x-data
                            x-on:click="
                                navigator.clipboard.writeText('![{{ addslashes($media['alt'] ?? $media['filename']) }}]({{ $media['variant_urls']['large'] ?? $media['url'] }})');
                                $tooltip('Copied!', { timeout: 1500 });
                            "
                            class="p-2 rounded-lg shadow-md text-white transition"
                            style="background: rgba(0,0,0,0.7)"
                            title="Copy Markdown"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                        </button>
                        {{-- Edit alt --}}
                        <button
                            x-on:click="$dispatch('open-modal', { id: 'edit-alt-modal' }); $wire.openEditAltModal({{ $media['id'] }})"
                            class="p-2 rounded-lg shadow-md text-white transition"
                            style="background: rgba(0,0,0,0.7)"
                            title="Edit alt text"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z" />
                            </svg>
                        </button>
                        {{-- Delete --}}
                        <button
                            wire:click="deleteMedia({{ $media['id'] }})"
                            wire:confirm="Delete '{{ addslashes($media['filename']) }}'?"
                            class="p-2 rounded-lg shadow-md text-white transition"
                            style="background: rgba(0,0,0,0.7)"
                            title="Delete"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                            </svg>
                        </button>
                        </div>
                    </div>

                    {{-- Info --}}
                    <div class="p-2">
                        <p class="text-xs font-medium text-gray-700 dark:text-gray-300 truncate" title="{{ $media['filename'] }}">
                            {{ $media['filename'] }}
                        </p>
                        <p class="text-xs text-gray-400 dark:text-gray-500">
                            @if($media['width'] && $media['height'])
                                {{ $media['width'] }}&times;{{ $media['height'] }} &middot;
                            @endif
                            @if($media['size'] >= 1048576)
                                {{ round($media['size'] / 1048576, 1) }} MB
                            @else
                                {{ round($media['size'] / 1024, 1) }} KB
                            @endif
                        </p>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-12 text-center text-gray-400 dark:text-gray-500">
                    No media found.
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if(($meta['last_page'] ?? 1) > 1)
            <div class="flex items-center justify-between text-sm text-gray-500">
                <span>Page {{ $meta['current_page'] ?? 1 }} of {{ $meta['last_page'] ?? 1 }} ({{ $meta['total'] ?? 0 }} items)</span>
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
    </div>

    {{-- Edit Alt Modal --}}
    <x-filament::modal id="edit-alt-modal" width="md">
        <x-slot name="heading">
            Edit Alt Text
        </x-slot>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Alt text</label>
            <x-filament::input.wrapper>
                <x-filament::input type="text" wire:model="editAlt" placeholder="Image description for accessibility" />
            </x-filament::input.wrapper>
            @error('editAlt') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>

        <x-slot name="footerActions">
            <x-filament::button x-on:click="$dispatch('close-modal', { id: 'edit-alt-modal' })" color="gray">
                Cancel
            </x-filament::button>
            <x-filament::button wire:click="saveAlt" color="primary">
                Save
            </x-filament::button>
        </x-slot>
    </x-filament::modal>

    {{-- Upload Modal --}}
    <x-filament::modal id="upload-modal" width="lg" :close-by-clicking-away="false">
        <x-slot name="heading">
            Upload Media
        </x-slot>

        <div class="space-y-4">
            {{-- File input --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">
                    File <span class="text-red-500">*</span>
                </label>
                <input
                    type="file"
                    wire:model="mediaFile"
                    accept="image/jpeg,image/png,image/gif,image/webp,image/svg+xml"
                    class="block w-full text-sm text-gray-500 dark:text-gray-400
                        file:mr-4 file:py-2 file:px-4
                        file:rounded file:border-0
                        file:text-sm file:font-medium
                        file:bg-primary-50 file:text-primary-700
                        dark:file:bg-primary-500/10 dark:file:text-primary-400
                        hover:file:bg-primary-100 dark:hover:file:bg-primary-500/20"
                />
                @error('mediaFile') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror

                <div wire:loading wire:target="mediaFile" class="mt-2 text-xs text-gray-500">
                    Uploading file...
                </div>
            </div>

            {{-- Alt text --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Alt text</label>
                <x-filament::input.wrapper>
                    <x-filament::input type="text" wire:model="mediaAlt" placeholder="Image description for accessibility" />
                </x-filament::input.wrapper>
                @error('mediaAlt') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>

            {{-- Preview --}}
            @if($mediaFile)
                <div class="rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-900 p-2">
                    <img src="{{ $mediaFile->temporaryUrl() }}" alt="Preview" class="max-h-48 mx-auto object-contain" />
                </div>
            @endif
        </div>

        <x-slot name="footerActions">
            <x-filament::button x-on:click="$dispatch('close-modal', { id: 'upload-modal' })" color="gray">
                Cancel
            </x-filament::button>
            <x-filament::button wire:click="uploadMedia" color="primary" :disabled="!$mediaFile">
                Upload
            </x-filament::button>
        </x-slot>
    </x-filament::modal>
</x-filament-panels::page>

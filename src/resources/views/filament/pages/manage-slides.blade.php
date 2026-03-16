<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Header with Create Button --}}
        <div class="flex justify-between items-center">
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Manage hero slider slides displayed on the homepage.
            </p>
            <x-filament::button
                x-on:click="$dispatch('open-modal', { id: 'slide-modal' }); $wire.openCreateModal()"
                icon="heroicon-o-plus"
            >
                New Slide
            </x-filament::button>
        </div>

        {{-- Slides Table --}}
        <x-filament::section>
            <x-slot name="heading">Slides</x-slot>

            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs uppercase bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-4 py-3 w-16">Pos</th>
                            <th class="px-4 py-3">Title</th>
                            <th class="px-4 py-3 w-20">Type</th>
                            <th class="px-4 py-3 w-24">Status</th>
                            <th class="px-4 py-3 w-24">Order</th>
                            <th class="px-4 py-3 w-32">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($slides as $index => $slide)
                            <tr class="border-b dark:border-gray-700">
                                <td class="px-4 py-3 text-gray-500">{{ $slide['position'] }}</td>
                                <td class="px-4 py-3">
                                    <div class="font-medium">{{ $slide['title'] }}</div>
                                    @if($slide['type'] === 'image' && $slide['image_url'])
                                        <div class="text-xs text-gray-400 truncate max-w-xs" title="{{ $slide['image_url'] }}">
                                            {{ $slide['image_url'] }}
                                        </div>
                                    @endif
                                    @if($slide['link_url'])
                                        <div class="text-xs text-blue-500 truncate max-w-xs">
                                            {{ $slide['link_text'] ?? $slide['link_url'] }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full {{ $slide['type'] === 'image' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200' }}">
                                        {{ ucfirst($slide['type']) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <button
                                        wire:click="toggleActive({{ $slide['id'] }})"
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full cursor-pointer {{ $slide['is_active'] ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' }}"
                                    >
                                        {{ $slide['is_active'] ? 'Active' : 'Inactive' }}
                                    </button>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-1">
                                        <x-filament::icon-button
                                            wire:click="moveUp({{ $slide['id'] }})"
                                            icon="heroicon-o-chevron-up"
                                            color="gray"
                                            size="sm"
                                            label="Move up"
                                        />
                                        <x-filament::icon-button
                                            wire:click="moveDown({{ $slide['id'] }})"
                                            icon="heroicon-o-chevron-down"
                                            color="gray"
                                            size="sm"
                                            label="Move down"
                                        />
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <x-filament::icon-button
                                            x-on:click="$dispatch('open-modal', { id: 'slide-modal' }); $wire.openEditModal({{ $slide['id'] }})"
                                            icon="heroicon-o-pencil-square"
                                            color="warning"
                                            size="sm"
                                            label="Edit"
                                        />
                                        <x-filament::icon-button
                                            wire:click="deleteSlide({{ $slide['id'] }})"
                                            wire:confirm="Are you sure you want to delete this slide?"
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
                                <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                    No slides yet. Create your first slide to get started.
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

    {{-- Create/Edit Slide Modal --}}
    <x-filament::modal id="slide-modal" width="lg">
        <x-slot name="heading">
            {{ $isEditing ? 'Edit Slide' : 'Create Slide' }}
        </x-slot>

        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Title</label>
                <x-filament::input.wrapper>
                    <x-filament::input type="text" wire:model.live="slideTitle" />
                </x-filament::input.wrapper>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Type</label>
                <select wire:model.live="slideType" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    <option value="image">Image</option>
                    <option value="html">HTML</option>
                </select>
            </div>

            @if($slideType === 'image')
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Image URL</label>
                    <x-filament::input.wrapper>
                        <x-filament::input type="url" wire:model.live="slideImageUrl" placeholder="https://example.com/image.jpg" />
                    </x-filament::input.wrapper>
                    @if($slideImageUrl)
                        <div class="mt-2 rounded-lg overflow-hidden border dark:border-gray-700">
                            <img src="{{ $slideImageUrl }}" alt="Preview" class="w-full h-40 object-cover" onerror="this.style.display='none'">
                        </div>
                    @endif
                </div>
            @else
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">HTML Content</label>
                    <textarea
                        wire:model.live="slideHtmlContent"
                        rows="8"
                        class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white font-mono text-sm"
                        placeholder="<div class='...'>Your HTML here</div>"
                    ></textarea>
                    <p class="mt-1 text-xs text-gray-400">Raw HTML that will be rendered as a slide.</p>
                </div>
            @endif

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Link URL (optional)</label>
                    <x-filament::input.wrapper>
                        <x-filament::input type="url" wire:model.live="slideLinkUrl" placeholder="https://..." />
                    </x-filament::input.wrapper>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Link Text (optional)</label>
                    <x-filament::input.wrapper>
                        <x-filament::input type="text" wire:model.live="slideLinkText" placeholder="Learn more" />
                    </x-filament::input.wrapper>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Position</label>
                    <x-filament::input.wrapper>
                        <x-filament::input type="number" wire:model.live="slidePosition" min="0" />
                    </x-filament::input.wrapper>
                </div>
                <div class="flex items-end">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" wire:model.live="slideIsActive" class="rounded border-gray-300 dark:border-gray-700 text-primary-600">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Active</span>
                    </label>
                </div>
            </div>
        </div>

        <x-slot name="footerActions">
            <x-filament::button x-on:click="$dispatch('close-modal', { id: 'slide-modal' })" color="gray">
                Cancel
            </x-filament::button>
            <x-filament::button wire:click="saveSlide" color="primary">
                {{ $isEditing ? 'Update Slide' : 'Create Slide' }}
            </x-filament::button>
        </x-slot>
    </x-filament::modal>
</x-filament-panels::page>

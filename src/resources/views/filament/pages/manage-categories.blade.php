<x-filament-panels::page>
    <div class="space-y-6">

        {{-- Toolbar --}}
        <div class="flex items-center justify-between">
            <h2 class="text-sm text-gray-500 dark:text-gray-400">
                {{ count($categories) }} {{ count($categories) === 1 ? 'category' : 'categories' }}
            </h2>
            <x-filament::button
                x-on:click="$dispatch('open-modal', { id: 'category-modal' }); $wire.openCreateModal()"
                icon="heroicon-o-plus"
            >
                New Category
            </x-filament::button>
        </div>

        {{-- Categories Table --}}
        <x-filament::section>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs uppercase bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-4 py-3">Name</th>
                            <th class="px-4 py-3 w-36">Slug</th>
                            <th class="px-4 py-3 w-28">Color</th>
                            <th class="px-4 py-3 w-28 hidden md:table-cell">Posts</th>
                            <th class="px-4 py-3 w-24">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $category)
                            <tr class="border-b dark:border-gray-700 dark:hover:bg-white/5">
                                <td class="px-4 py-3">
                                    <span class="font-medium text-gray-900 dark:text-white">{{ $category['name'] }}</span>
                                </td>
                                <td class="px-4 py-3 text-gray-500 dark:text-gray-400 text-xs">
                                    {{ $category['slug'] }}
                                </td>
                                <td class="px-4 py-3">
                                    @php
                                        $colorName = $category['color'] ?? '';
                                        $colorPreview = match($colorName) {
                                            'violet'  => 'bg-violet-500',
                                            'blue'    => 'bg-blue-500',
                                            'emerald' => 'bg-emerald-500',
                                            'amber'   => 'bg-amber-500',
                                            'rose'    => 'bg-rose-500',
                                            'cyan'    => 'bg-cyan-500',
                                            default   => 'bg-gray-400',
                                        };
                                    @endphp
                                    <div class="flex items-center gap-2">
                                        <span class="w-3 h-3 rounded-full {{ $colorPreview }}"></span>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ $colorName ?: 'gray' }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 hidden md:table-cell text-gray-500 text-xs">
                                    {{ $category['posts_count'] ?? '—' }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <x-filament::icon-button
                                            x-on:click="$dispatch('open-modal', { id: 'category-modal' }); $wire.openEditModal({{ $category['id'] }})"
                                            icon="heroicon-o-pencil-square"
                                            color="warning"
                                            size="sm"
                                            label="Edit"
                                        />
                                        <x-filament::icon-button
                                            wire:click="deleteCategory({{ $category['id'] }})"
                                            wire:confirm="Are you sure you want to delete '{{ addslashes($category['name']) }}'?"
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
                                <td colspan="5" class="px-4 py-10 text-center text-gray-400">
                                    No categories found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-filament::section>
    </div>

    {{-- Create/Edit Category Modal --}}
    <x-filament::modal id="category-modal" width="lg" :close-by-clicking-away="false">
        <x-slot name="heading">
            {{ $isEditing ? 'Edit Category' : 'New Category' }}
        </x-slot>

        <div class="space-y-4">
            {{-- Name --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Name <span class="text-red-500">*</span></label>
                <x-filament::input.wrapper>
                    <x-filament::input type="text" wire:model.live="categoryName" placeholder="Category name" />
                </x-filament::input.wrapper>
                @error('categoryName') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>

            {{-- Slug --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Slug <span class="text-red-500">*</span></label>
                <x-filament::input.wrapper>
                    <x-filament::input type="text" wire:model.live="categorySlug" placeholder="category-slug" />
                </x-filament::input.wrapper>
                @error('categorySlug') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>

            {{-- Color --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Color</label>
                <div class="flex flex-wrap gap-2">
                    @foreach(\App\Filament\Pages\ManageCategories::$availableColors as $value => $label)
                        @php
                            $dotColor = match($value) {
                                'violet'  => 'bg-violet-500',
                                'blue'    => 'bg-blue-500',
                                'emerald' => 'bg-emerald-500',
                                'amber'   => 'bg-amber-500',
                                'rose'    => 'bg-rose-500',
                                'cyan'    => 'bg-cyan-500',
                                default   => 'bg-gray-400',
                            };
                            $isSelected = $categoryColor === $value;
                        @endphp
                        <button
                            type="button"
                            wire:click="$set('categoryColor', '{{ $value }}')"
                            class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg border text-sm transition-colors
                                {{ $isSelected
                                    ? 'border-primary-500 bg-primary-50 dark:bg-primary-500/10 text-primary-700 dark:text-primary-300 ring-1 ring-primary-500'
                                    : 'border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-white/5' }}"
                        >
                            <span class="w-3 h-3 rounded-full {{ $dotColor }}"></span>
                            {{ $label }}
                        </button>
                    @endforeach
                </div>
            </div>
        </div>

        <x-slot name="footerActions">
            <x-filament::button x-on:click="$dispatch('close-modal', { id: 'category-modal' })" color="gray">
                Cancel
            </x-filament::button>
            <x-filament::button wire:click="saveCategory" color="primary">
                {{ $isEditing ? 'Update Category' : 'Create Category' }}
            </x-filament::button>
        </x-slot>
    </x-filament::modal>
</x-filament-panels::page>

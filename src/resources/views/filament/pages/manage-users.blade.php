<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Assign Role Form --}}
        <x-filament::section>
            <x-slot name="heading">Assign Role</x-slot>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">User</label>
                    <select wire:model="selectedUserId" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                        <option value="">Select user...</option>
                        @foreach($users as $user)
                            <option value="{{ $user['id'] }}">{{ $user['name'] }} ({{ $user['email'] }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Role</label>
                    <select wire:model="selectedRole" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                        <option value="">Select role...</option>
                        @foreach($roles as $role)
                            <option value="{{ $role['name'] }}">{{ ucfirst($role['name']) }} (Level: {{ $role['level'] }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-end">
                    <x-filament::button wire:click="assignRole" icon="heroicon-o-plus">
                        Assign Role
                    </x-filament::button>
                </div>
            </div>
        </x-filament::section>

        {{-- Users Table --}}
        <x-filament::section>
            <x-slot name="heading">Users from Microservice</x-slot>

            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs uppercase bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-4 py-3">ID</th>
                            <th class="px-4 py-3">Name</th>
                            <th class="px-4 py-3">Email</th>
                            <th class="px-4 py-3">Roles</th>
                            <th class="px-4 py-3">Created</th>
                            <th class="px-4 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr class="border-b dark:border-gray-700">
                                <td class="px-4 py-3">{{ $user['id'] }}</td>
                                <td class="px-4 py-3 font-medium">{{ $user['name'] }}</td>
                                <td class="px-4 py-3">{{ $user['email'] }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($user['roles'] ?? [] as $role)
                                            <span class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium rounded-full bg-primary-100 text-primary-800 dark:bg-primary-900 dark:text-primary-200">
                                                {{ ucfirst($role) }}
                                                <button
                                                    wire:click="removeUserRole({{ $user['id'] }}, '{{ $role }}')"
                                                    wire:confirm="Are you sure you want to remove this role?"
                                                    class="ml-1 text-primary-600 hover:text-primary-900 dark:hover:text-primary-100"
                                                >
                                                    <x-heroicon-o-x-mark class="w-3 h-3"/>
                                                </button>
                                            </span>
                                        @endforeach
                                        @if(empty($user['roles']))
                                            <span class="text-gray-400 text-xs">No roles</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-gray-500">
                                    {{ \Carbon\Carbon::parse($user['created_at'])->format('Y-m-d H:i') }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <x-filament::icon-button
                                            x-on:click="$dispatch('open-modal', { id: 'edit-user-modal' }); $wire.openEditModal({{ $user['id'] }}, '{{ addslashes($user['name']) }}', '{{ $user['email'] }}')"
                                            icon="heroicon-o-pencil-square"
                                            color="warning"
                                            size="sm"
                                            label="Edit"
                                        />
                                        <x-filament::icon-button
                                            wire:click="deleteUser({{ $user['id'] }})"
                                            wire:confirm="Are you sure you want to delete this user? This action cannot be undone."
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
                                    No users found. Make sure the Users service is running.
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

        {{-- Available Roles --}}
        <x-filament::section collapsible collapsed>
            <x-slot name="heading">Available Roles</x-slot>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach($roles as $role)
                    <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-800">
                        <h4 class="font-semibold text-lg">{{ ucfirst($role['name']) }}</h4>
                        <p class="text-sm text-gray-500">{{ $role['description'] ?? 'No description' }}</p>
                        <p class="text-xs text-gray-400 mt-1">Level: {{ $role['level'] }}</p>
                    </div>
                @endforeach
            </div>
        </x-filament::section>
    </div>

    {{-- Edit User Modal --}}
    <x-filament::modal id="edit-user-modal" width="md">
        <x-slot name="heading">
            Edit User
        </x-slot>

        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Name</label>
                <x-filament::input.wrapper>
                    <x-filament::input
                        type="text"
                        wire:model.live="editUserName"
                    />
                </x-filament::input.wrapper>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                <x-filament::input.wrapper>
                    <x-filament::input
                        type="email"
                        wire:model.live="editUserEmail"
                    />
                </x-filament::input.wrapper>
            </div>
        </div>

        <x-slot name="footerActions">
            <x-filament::button x-on:click="$dispatch('close-modal', { id: 'edit-user-modal' })" color="gray">
                Cancel
            </x-filament::button>
            <x-filament::button wire:click="saveUser" color="primary">
                Save Changes
            </x-filament::button>
        </x-slot>
    </x-filament::modal>
</x-filament-panels::page>

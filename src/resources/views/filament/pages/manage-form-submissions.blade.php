<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Filters --}}
        <x-filament::section>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Search</label>
                    <x-filament::input.wrapper>
                        <x-filament::input
                            type="text"
                            wire:model.live.debounce.300ms="search"
                            placeholder="Search in payload..."
                        />
                    </x-filament::input.wrapper>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Form Type</label>
                    <select wire:model.live="formType" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                        <option value="">All types</option>
                        <option value="contact">Contact</option>
                    </select>
                </div>

                <div class="flex items-end">
                    <x-filament::button wire:click="loadData" icon="heroicon-o-arrow-path" color="gray">
                        Refresh
                    </x-filament::button>
                </div>
            </div>
        </x-filament::section>

        {{-- Submissions Table --}}
        <x-filament::section>
            <x-slot name="heading">
                Submissions
                @if(($pagination['total'] ?? 0) > 0)
                    <span class="text-sm font-normal text-gray-500">({{ $pagination['total'] }} total)</span>
                @endif
            </x-slot>

            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs uppercase bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-4 py-3">ID</th>
                            <th class="px-4 py-3">Type</th>
                            <th class="px-4 py-3">Name</th>
                            <th class="px-4 py-3">Email</th>
                            <th class="px-4 py-3">Subject</th>
                            <th class="px-4 py-3">Sent</th>
                            <th class="px-4 py-3">Date</th>
                            <th class="px-4 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($submissions as $submission)
                            @php
                                $payload = $submission['payload'] ?? [];
                            @endphp
                            <tr class="border-b dark:border-gray-700">
                                <td class="px-4 py-3">{{ $submission['id'] }}</td>
                                <td class="px-4 py-3">
                                    <x-filament::badge>
                                        {{ $submission['form_type'] ?? '-' }}
                                    </x-filament::badge>
                                </td>
                                <td class="px-4 py-3 font-medium">{{ $payload['name'] ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $payload['email'] ?? '-' }}</td>
                                <td class="px-4 py-3 max-w-xs truncate">{{ $payload['subject'] ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    @if($submission['sent_at'])
                                        <x-filament::badge color="success" icon="heroicon-o-check">Yes</x-filament::badge>
                                    @else
                                        <x-filament::badge color="danger" icon="heroicon-o-x-mark">No</x-filament::badge>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-gray-500 whitespace-nowrap">
                                    {{ \Carbon\Carbon::parse($submission['created_at'])->format('Y-m-d H:i') }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <x-filament::icon-button
                                            x-on:click="$dispatch('open-modal', { id: 'submission-detail-modal' }); $wire.viewSubmission({{ $submission['id'] }})"
                                            icon="heroicon-o-eye"
                                            color="info"
                                            size="sm"
                                            label="View"
                                        />
                                        <x-filament::icon-button
                                            wire:click="deleteSubmission({{ $submission['id'] }})"
                                            wire:confirm="Are you sure you want to delete this submission?"
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
                                <td colspan="8" class="px-4 py-8 text-center text-gray-500">
                                    No submissions found. Make sure the Frontend service is running.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if(($pagination['last_page'] ?? 1) > 1)
                <div class="mt-4 flex items-center justify-between">
                    <p class="text-sm text-gray-500">
                        Page {{ $pagination['current_page'] ?? 1 }} of {{ $pagination['last_page'] ?? 1 }}
                    </p>
                    <div class="flex gap-2">
                        <x-filament::button
                            wire:click="previousPage"
                            color="gray"
                            size="sm"
                            :disabled="($pagination['current_page'] ?? 1) <= 1"
                        >
                            Previous
                        </x-filament::button>
                        <x-filament::button
                            wire:click="nextPage"
                            color="gray"
                            size="sm"
                            :disabled="($pagination['current_page'] ?? 1) >= ($pagination['last_page'] ?? 1)"
                        >
                            Next
                        </x-filament::button>
                    </div>
                </div>
            @endif
        </x-filament::section>
    </div>

    {{-- Detail Modal --}}
    <x-filament::modal id="submission-detail-modal" width="lg">
        <x-slot name="heading">
            Submission Details
        </x-slot>

        @if($selectedSubmission)
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <span class="block text-sm font-medium text-gray-500 dark:text-gray-400">ID</span>
                        <span class="text-sm">{{ $selectedSubmission['id'] }}</span>
                    </div>
                    <div>
                        <span class="block text-sm font-medium text-gray-500 dark:text-gray-400">Type</span>
                        <x-filament::badge>{{ $selectedSubmission['form_type'] }}</x-filament::badge>
                    </div>
                    <div>
                        <span class="block text-sm font-medium text-gray-500 dark:text-gray-400">URL</span>
                        <span class="text-sm">{{ $selectedSubmission['url'] ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="block text-sm font-medium text-gray-500 dark:text-gray-400">Sent at</span>
                        <span class="text-sm">{{ $selectedSubmission['sent_at'] ? \Carbon\Carbon::parse($selectedSubmission['sent_at'])->format('Y-m-d H:i:s') : 'Not sent' }}</span>
                    </div>
                    <div>
                        <span class="block text-sm font-medium text-gray-500 dark:text-gray-400">Created</span>
                        <span class="text-sm">{{ \Carbon\Carbon::parse($selectedSubmission['created_at'])->format('Y-m-d H:i:s') }}</span>
                    </div>
                </div>

                <hr class="dark:border-gray-700">

                <div>
                    <span class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Payload</span>
                    <div class="space-y-2">
                        @foreach(($selectedSubmission['payload'] ?? []) as $key => $value)
                            <div class="rounded-lg bg-gray-50 dark:bg-gray-800 p-3">
                                <span class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">{{ $key }}</span>
                                <span class="text-sm whitespace-pre-wrap">{{ is_array($value) ? json_encode($value, JSON_PRETTY_PRINT) : $value }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @else
            <div class="text-center text-gray-500 py-4">Loading...</div>
        @endif

        <x-slot name="footerActions">
            <x-filament::button x-on:click="$dispatch('close-modal', { id: 'submission-detail-modal' })" color="gray">
                Close
            </x-filament::button>
        </x-slot>
    </x-filament::modal>
</x-filament-panels::page>

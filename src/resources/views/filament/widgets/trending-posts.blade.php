<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">Trending Posts (last {{ $this->period }})</x-slot>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs uppercase bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-4 py-3 w-12">#</th>
                        <th class="px-4 py-3">Post UUID</th>
                        <th class="px-4 py-3 text-right">Views</th>
                        <th class="px-4 py-3 text-right">Unique</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($this->getPosts() as $index => $post)
                        <tr class="border-b dark:border-gray-700">
                            <td class="px-4 py-3 text-gray-500">{{ $index + 1 }}</td>
                            <td class="px-4 py-3 font-mono text-xs" title="{{ $post['post_uuid'] ?? '' }}">
                                {{ \Illuminate\Support\Str::limit($post['post_uuid'] ?? '', 36) }}
                            </td>
                            <td class="px-4 py-3 text-right">{{ number_format($post['views'] ?? 0) }}</td>
                            <td class="px-4 py-3 text-right">{{ number_format($post['unique_views'] ?? 0) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-gray-500">
                                No trending posts data available.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>

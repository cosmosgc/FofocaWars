<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ $war->name }} — {{ __('Analytics') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">{{ __('Rankings') }}</h3>
                    @if(empty($ranking))
                        <p class="text-gray-500 dark:text-gray-400">{{ __('No players yet.') }}</p>
                    @else
                        @php $rankColors = ['bg-yellow-400 text-black', 'bg-gray-300 text-black', 'bg-amber-600 text-white']; @endphp
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left">
                                <thead class="text-xs text-gray-700 dark:text-gray-400 uppercase bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-3 py-2">#</th>
                                        <th class="px-3 py-2">{{ __('Player') }}</th>
                                        <th class="px-3 py-2 text-right">{{ __('Cities') }}</th>
                                        <th class="px-3 py-2 text-right">{{ __('Bases') }}</th>
                                        <th class="px-3 py-2 text-right">{{ __('Tiles') }}</th>
                                        <th class="px-3 py-2 text-right">{{ __('Resources') }}</th>
                                        <th class="px-3 py-2 text-right">{{ __('Units') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($ranking as $i => $row)
                                        <tr class="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700">
                                            <td class="px-3 py-2">
                                                @if($i < 3)
                                                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full text-xs font-bold {{ $rankColors[$i] }}">{{ $i + 1 }}</span>
                                                @else
                                                    <span class="text-gray-500">{{ $i + 1 }}</span>
                                                @endif
                                            </td>
                                            <td class="px-3 py-2 font-medium text-gray-900 dark:text-gray-100">{{ $row['name'] }}</td>
                                            <td class="px-3 py-2 text-right text-gray-600 dark:text-gray-400">{{ $row['cities'] }}</td>
                                            <td class="px-3 py-2 text-right text-gray-600 dark:text-gray-400">{{ $row['bases'] }}</td>
                                            <td class="px-3 py-2 text-right text-gray-600 dark:text-gray-400">{{ $row['tiles'] }}</td>
                                            <td class="px-3 py-2 text-right text-gray-600 dark:text-gray-400">{{ number_format($row['total_resources']) }}</td>
                                            <td class="px-3 py-2 text-right text-gray-600 dark:text-gray-400">{{ number_format($row['total_units']) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">{{ __('Resources Over Time') }}</h3>
                        <canvas id="resourcesChart" height="200"></canvas>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">{{ __('Army Power Over Time') }}</h3>
                        <canvas id="armyChart" height="200"></canvas>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg md:col-span-2">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">{{ __('Territory Over Time') }}</h3>
                        <canvas id="territoryChart" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
    <script>
        const colors = {
            indigo: '#6366f1',
            green: '#22c55e',
            purple: '#a855f7',
            amber: '#f59e0b',
            gray: '#6b7280',
            red: '#ef4444',
            orange: '#f97316',
            blue: '#3b82f6',
            yellow: '#f5a623',
            slate: '#94a3b8',
        };

        function makeChart(id, data) {
            const ctx = document.getElementById(id)?.getContext('2d');
            if (!ctx) return;
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.labels,
                    datasets: data.datasets.map(d => ({ ...d, tension: 0.3, pointRadius: 2 })),
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { labels: { color: '#9ca3af', font: { size: 11 } } } },
                    scales: {
                        x: { ticks: { color: '#9ca3af', maxTicksLimit: 10 }, grid: { color: '#374151' } },
                        y: { ticks: { color: '#9ca3af' }, grid: { color: '#374151' } },
                    },
                },
            });
        }

        makeChart('resourcesChart', {
            labels: @json($charts['resources']['labels']),
            datasets: [
                { label: '{{ __('Total') }}', data: @json($charts['resources']['datasets'][0]['data']), borderColor: colors.indigo },
                { label: '{{ __('Wood') }}', data: @json($charts['resources']['datasets'][1]['data']), borderColor: colors.green },
                { label: '{{ __('Stone') }}', data: @json($charts['resources']['datasets'][2]['data']), borderColor: colors.purple },
                { label: '{{ __('Food') }}', data: @json($charts['resources']['datasets'][3]['data']), borderColor: colors.amber },
                { label: '{{ __('Metal') }}', data: @json($charts['resources']['datasets'][4]['data']), borderColor: colors.gray },
            ],
        });

        makeChart('armyChart', {
            labels: @json($charts['army']['labels']),
            datasets: [
                { label: '{{ __('Units') }}', data: @json($charts['army']['datasets'][0]['data']), borderColor: colors.red },
                { label: '{{ __('Attack Power') }}', data: @json($charts['army']['datasets'][1]['data']), borderColor: colors.orange },
                { label: '{{ __('Defense Power') }}', data: @json($charts['army']['datasets'][2]['data']), borderColor: colors.blue },
            ],
        });

        makeChart('territoryChart', {
            labels: @json($charts['territory']['labels']),
            datasets: [
                { label: '{{ __('Cities') }}', data: @json($charts['territory']['datasets'][0]['data']), borderColor: colors.yellow },
                { label: '{{ __('Bases') }}', data: @json($charts['territory']['datasets'][1]['data']), borderColor: colors.green },
                { label: '{{ __('Tiles') }}', data: @json($charts['territory']['datasets'][2]['data']), borderColor: colors.slate },
            ],
        });
    </script>
    @endpush
</x-app-layout>

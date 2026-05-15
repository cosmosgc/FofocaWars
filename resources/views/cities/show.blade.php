<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ $city->name }}
            </h2>
            <span class="text-sm text-gray-500 dark:text-gray-400">
                ({{ $city->tile_x }}, {{ $city->tile_y }})
            </span>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('Resources') }}</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                {{ __('Population') }}: {{ number_format($city->population) }}
                            </p>
                        </div>
                        <a href="{{ route('wars.map', $war) }}"
                           class="inline-flex items-center px-3 py-1.5 text-xs bg-blue-600 text-white rounded-md hover:bg-blue-500">
                            {{ __('Open Map') }}
                        </a>
                    </div>

                    @php $pct = app(App\Game\Economy\ResourceService::class)->getFillPercentages($city); @endphp
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="border dark:border-gray-700 rounded-lg p-4">
                            <div class="flex items-baseline justify-between">
                                <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ number_format($city->wood) }}</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">/ {{ number_format($city->max_wood) }}</div>
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">{{ __('Wood') }}</div>
                            <div class="mt-2 w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                <div class="bg-amber-600 h-2 rounded-full transition-all" style="width: {{ $pct['wood'] }}%"></div>
                            </div>
                            <div class="flex justify-between text-xs mt-1">
                                <span class="text-green-600 dark:text-green-400">+{{ $rates['wood'] }}{{ __('/min') }}</span>
                                <span class="text-gray-500 dark:text-gray-400">{{ $pct['wood'] }}%</span>
                            </div>
                        </div>
                        <div class="border dark:border-gray-700 rounded-lg p-4">
                            <div class="flex items-baseline justify-between">
                                <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ number_format($city->stone) }}</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">/ {{ number_format($city->max_stone) }}</div>
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">{{ __('Stone') }}</div>
                            <div class="mt-2 w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                <div class="bg-gray-400 h-2 rounded-full transition-all" style="width: {{ $pct['stone'] }}%"></div>
                            </div>
                            <div class="flex justify-between text-xs mt-1">
                                <span class="text-green-600 dark:text-green-400">+{{ $rates['stone'] }}{{ __('/min') }}</span>
                                <span class="text-gray-500 dark:text-gray-400">{{ $pct['stone'] }}%</span>
                            </div>
                        </div>
                        <div class="border dark:border-gray-700 rounded-lg p-4">
                            <div class="flex items-baseline justify-between">
                                <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ number_format($city->food) }}</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">/ {{ number_format($city->max_food) }}</div>
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">{{ __('Food') }}</div>
                            <div class="mt-2 w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                <div class="bg-green-500 h-2 rounded-full transition-all" style="width: {{ $pct['food'] }}%"></div>
                            </div>
                            <div class="flex justify-between text-xs mt-1">
                                <span class="text-green-600 dark:text-green-400">+{{ $rates['food'] }}{{ __('/min') }}</span>
                                <span class="text-gray-500 dark:text-gray-400">{{ $pct['food'] }}%</span>
                            </div>
                        </div>
                        <div class="border dark:border-gray-700 rounded-lg p-4">
                            <div class="flex items-baseline justify-between">
                                <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ number_format($city->metal) }}</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">/ {{ number_format($city->max_metal) }}</div>
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">{{ __('Metal') }}</div>
                            <div class="mt-2 w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                <div class="bg-cyan-600 h-2 rounded-full transition-all" style="width: {{ $pct['metal'] }}%"></div>
                            </div>
                            <div class="flex justify-between text-xs mt-1">
                                <span class="text-green-600 dark:text-green-400">+{{ $rates['metal'] }}{{ __('/min') }}</span>
                                <span class="text-gray-500 dark:text-gray-400">{{ $pct['metal'] }}%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">{{ __('Buildings') }}</h3>
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                        @php
                            $buildings = [
                                ['name' => __('Town Hall'), 'icon' => '🏛️', 'level' => 1, 'desc' => __('City management')],
                                ['name' => __('Lumber Mill'), 'icon' => '🪵', 'level' => 0, 'desc' => __('Wood production +5')],
                                ['name' => __('Quarry'), 'icon' => '🪨', 'level' => 0, 'desc' => __('Stone production +4')],
                                ['name' => __('Farm'), 'icon' => '🌾', 'level' => 0, 'desc' => __('Food production +8')],
                                ['name' => __('Smelter'), 'icon' => '⚙️', 'level' => 0, 'desc' => __('Metal production +3')],
                                ['name' => __('Barracks'), 'icon' => '⚔️', 'level' => 0, 'desc' => __('Train units')],
                                ['name' => __('Wall'), 'icon' => '🧱', 'level' => 0, 'desc' => __('City defense')],
                                ['name' => __('Market'), 'icon' => '🏪', 'level' => 0, 'desc' => __('Resource trading')],
                            ];
                        @endphp
                        @foreach($buildings as $building)
                            <div class="border dark:border-gray-700 rounded-lg p-3 text-center {{ $building['level'] > 0 ? '' : 'opacity-50' }}">
                                <div class="text-2xl mb-1">{{ $building['icon'] }}</div>
                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $building['name'] }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $building['desc'] }}</div>
                                @if($building['level'] > 0)
                                    <div class="text-xs text-blue-500 mt-1">{{ __('Lv.') }}{{ $building['level'] }}</div>
                                @else
                                    <div class="text-xs text-yellow-500 mt-1">{{ __('Locked') }}</div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

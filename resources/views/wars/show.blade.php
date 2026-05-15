<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ $war->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if($player)
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <div class="flex gap-4">
                            <a href="{{ route('wars.map', $war) }}"
                               class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                {{ __('Open Map') }}
                            </a>
                            <a href="{{ route('armies.index', $war) }}"
                               class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                {{ __('Armies') }}
                            </a>
                            @if($war->status === 'setup' && auth()->user()->isAdmin())
                                <form action="{{ route('wars.start', $war) }}" method="POST">
                                    @csrf
                                    <button type="submit"
                                       class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-500 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        Start War
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>

                 <div id="resources-panel"
                     x-data="resources()"
                     x-init="init('{{ route('api.wars.resources', $war) }}')"
                     class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">{{ __('Resources') }}</h3>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4" x-show="cities.length > 0">
                            <template x-for="city in cities" :key="city.id">
                                <a :href="city.url" class="block border dark:border-gray-700 rounded-lg p-3 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                    <h4 class="font-medium text-gray-900 dark:text-gray-100" x-text="city.name"></h4>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        👥 <span x-text="city.population"></span>
                                    </p>
                                    <div class="mt-2 text-sm space-y-1">
                                        <p>🪵 <span x-text="city.wood"></span> <span class="text-green-500" x-text="'+' + rates.wood + '/min'"></span></p>
                                        <p>🪨 <span x-text="city.stone"></span> <span class="text-green-500" x-text="'+' + rates.stone + '/min'"></span></p>
                                        <p>🍖 <span x-text="city.food"></span> <span class="text-green-500" x-text="'+' + rates.food + '/min'"></span></p>
                                        <p>⚙️ <span x-text="city.metal"></span> <span class="text-green-500" x-text="'+' + rates.metal + '/min'"></span></p>
                                    </div>
                                </a>
                            </template>
                        </div>
                        <p x-show="cities.length === 0" class="text-gray-500">{{ __('Loading resources...') }}</p>
                    </div>
                </div>
            @else
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <p>{{ __("You have not joined this war yet.") }}</p>
                        <form action="{{ route('wars.join', $war) }}" method="POST" class="mt-4">
                            @csrf
                            <button type="submit"
                               class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                {{ __('Join War') }}
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
    <script>
        function resources() {
            return {
                cities: [],
                rates: {},
                interval: null,
                url: '',
                async init(resourcesUrl) {
                    this.url = resourcesUrl;
                    await this.fetchResources();
                    this.interval = setInterval(() => this.fetchResources(), 10000);
                },
                async fetchResources() {
                    try {
                        const response = await fetch(this.url);
                        const data = await response.json();
                        this.cities = data.cities;
                        this.rates = data.rates;
                    } catch (e) {
                        console.error('Failed to fetch resources:', e);
                    }
                },
                destroy() {
                    if (this.interval) clearInterval(this.interval);
                }
            };
        }
    </script>
    @endpush
</x-app-layout>

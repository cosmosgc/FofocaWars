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
                <div class="p-6"
                     x-data="cityResources('{{ route('api.wars.resources', $war) }}', {{ $city->id }}, {{ $city->wood }}, {{ $city->stone }}, {{ $city->food }}, {{ $city->metal }}, {{ $city->max_wood }}, {{ $city->max_stone }}, {{ $city->max_food }}, {{ $city->max_metal }}, {{ $city->population }}, {{ $rates['wood'] }}, {{ $rates['stone'] }}, {{ $rates['food'] }}, {{ $rates['metal'] }})"
                     x-init="init()">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('Resources') }}</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                {{ __('Population') }}: <span x-text="format(population)" class="font-medium">0</span>
                            </p>
                        </div>
                        <div class="flex gap-2">
                            <a href="{{ route('wars.map', $war) }}"
                               class="inline-flex items-center px-3 py-1.5 text-xs bg-blue-600 text-white rounded-md hover:bg-blue-500">
                                {{ __('Open Map') }}
                            </a>
                            <a href="{{ route('armies.index', $war) }}"
                               class="inline-flex items-center px-3 py-1.5 text-xs bg-red-600 text-white rounded-md hover:bg-red-500">
                                {{ __('Armies') }}
                            </a>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <template x-for="r in ['wood','stone','food','metal']" :key="r">
                            <div class="border dark:border-gray-700 rounded-lg p-4">
                                <div class="flex items-baseline justify-between">
                                    <div class="text-2xl font-bold text-gray-900 dark:text-gray-100" x-text="format(display[r])">0</div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400" x-text="'/' + format(max[r])">/ 0</div>
                                </div>
                                <div class="text-sm text-gray-500 dark:text-gray-400" x-text="labels[r]"></div>
                                <div class="mt-2 w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                    <div class="h-2 rounded-full transition-all"
                                         :class="barColors[r]"
                                         :style="'width:' + Math.min(100, max[r] > 0 ? (display[r] / max[r] * 100) : 0) + '%'"></div>
                                </div>
                                <div class="flex justify-between text-xs mt-1">
                                    <span class="text-green-600 dark:text-green-400">+<span x-text="ratePerSec[r]"></span>/s</span>
                                    <span class="text-gray-500 dark:text-gray-400" x-text="Math.min(100, max[r] > 0 ? Math.round(display[r] / max[r] * 100) : 0) + '%'"></span>
                                </div>
                            </div>
                        </template>
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

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">{{ __('Train Units') }}</h3>

                    @if($cityUnits->isNotEmpty())
                        <div class="mb-4">
                            <h4 class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-2">{{ __('Garrison') }}</h4>
                            <div class="flex flex-wrap gap-2">
                                @foreach($cityUnits as $u)
                                    <span class="inline-flex items-center px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded text-xs font-medium text-gray-800 dark:text-gray-200">
                                        {{ $u->unitType->name }} x{{ $u->quantity }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div x-data="cityTrain({{ $city->id }}, '{{ route('api.wars.train', $war) }}', '{{ route('api.wars.training-queue', $war) }}')" x-init="init()">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Quantity') }}</label>
                            <input type="number" x-model="qty" min="1" value="1"
                                   class="w-32 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm text-sm">
                        </div>

                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
                            @foreach($unitTypes as $ut)
                                <div class="border dark:border-gray-700 rounded-lg p-3 text-center text-sm cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/50 transition"
                                     @click="doTrain({{ $ut->id }})"
                                     x-data="{ ut: {{ $ut->id }}, w: {{ $ut->wood_cost }}, s: {{ $ut->stone_cost }}, f: {{ $ut->food_cost }}, m: {{ $ut->metal_cost }}, tt: {{ $ut->training_time }} }"
                                     :class="busy === ut ? 'opacity-50 pointer-events-none' : ''">
                                    <div class="font-medium text-gray-900 dark:text-gray-100">{{ $ut->name }}</div>
                                    <div class="text-xs text-gray-500 mt-0.5">{{ $ut->attack }}⚔️ {{ $ut->defense }}🛡️</div>
                                    <div class="text-xs text-gray-500 mt-1">
                                        🪵<span x-text="w * qty"></span> 🪨<span x-text="s * qty"></span> 🍖<span x-text="f * qty"></span> ⚙️<span x-text="m * qty"></span>
                                    </div>
                                    <div class="text-xs text-gray-400 mt-0.5" x-text="(tt * qty) + 'min'"></div>
                                </div>
                            @endforeach
                        </div>

                        <div x-show="message" x-text="message" class="mt-3 text-sm" :class="error ? 'text-red-600' : 'text-green-600'"></div>

                        <div x-show="queue.length > 0" class="mt-4 border-t dark:border-gray-700 pt-4">
                            <h4 class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-2">{{ __('Training in Progress') }}</h4>
                            <template x-for="q in queue" :key="q.id">
                                <div class="flex items-center justify-between text-sm py-1">
                                    <span class="text-gray-900 dark:text-gray-100" x-text="q.quantity + 'x ' + q.unit"></span>
                                    <span class="text-gray-500" x-text="'Ready: ' + new Date(q.finishes_at).toLocaleString()"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function cityTrain(cityId, trainUrl, queueUrl) {
            return {
                qty: 1, message: '', error: false, busy: null, queue: [],
                init() { this.fetchQueue(); setInterval(() => this.fetchQueue(), 10000); },
                async fetchQueue() {
                    try { const r = await fetch(queueUrl); this.queue = await r.json(); }
                    catch(e) {}
                },
                async doTrain(unitTypeId) {
                    this.busy = unitTypeId;
                    this.message = ''; this.error = false;
                    try {
                        const res = await fetch(trainUrl, {
                            method: 'POST',
                            headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
                            body: JSON.stringify({city_id: cityId, unit_type_id: unitTypeId, quantity: this.qty})
                        });
                        const data = await res.json();
                        if (!res.ok) { this.error = true; this.message = data.error || 'Error'; }
                        else { this.message = data.message; this.fetchQueue(); }
                    } catch(e) { this.error = true; this.message = 'Request failed.'; }
                    this.busy = null;
                },
            };
        }

        function cityResources(url, cityId, w, s, f, m, mw, ms, mf, mm, pop, rw, rs, rf, rm) {
            return {
                base: { wood: w, stone: s, food: f, metal: m },
                display: { wood: w, stone: s, food: f, metal: m },
                max: { wood: mw, stone: ms, food: mf, metal: mm },
                population: pop,
                rates: { wood: rw, stone: rs, food: rf, metal: rm },
                ratePerSec: { wood: rw / 60, stone: rs / 60, food: rf / 60, metal: rm / 60 },
                lastFetch: Date.now(),
                labels: { wood: '{{ __("Wood") }}', stone: '{{ __("Stone") }}', food: '{{ __("Food") }}', metal: '{{ __("Metal") }}' },
                barColors: { wood: 'bg-amber-600', stone: 'bg-gray-400', food: 'bg-green-500', metal: 'bg-cyan-600' },
                interval: null,
                tickId: null,

                init() {
                    this.fetch();
                    this.interval = setInterval(() => this.fetch(), 15000);
                    this.tickId = setInterval(() => this.simulate(), 1000);
                },

                simulate() {
                    const now = Date.now();
                    const elapsed = (now - this.lastFetch) / 1000;
                    for (const r of ['wood', 'stone', 'food', 'metal']) {
                        const produced = this.ratePerSec[r] * elapsed;
                        this.display[r] = Math.min(this.max[r], this.base[r] + produced);
                    }
                },

                async fetch() {
                    try {
                        const res = await fetch(url);
                        const data = await res.json();
                        const city = data.cities.find(c => c.id === cityId);
                        if (city) {
                            this.base = {
                                wood: city.wood,
                                stone: city.stone,
                                food: city.food,
                                metal: city.metal,
                            };
                            this.max = {
                                wood: city.max_wood,
                                stone: city.max_stone,
                                food: city.max_food,
                                metal: city.max_metal,
                            };
                            this.population = city.population;
                            this.lastFetch = Date.now();
                        }
                        this.rates = data.rates;
                        this.ratePerSec = {
                            wood: data.rates.wood / 60,
                            stone: data.rates.stone / 60,
                            food: data.rates.food / 60,
                            metal: data.rates.metal / 60,
                        };
                    } catch (e) {
                        console.error('Failed to fetch resources:', e);
                    }
                },

                format(n) { return Math.floor(n).toLocaleString(); },
                destroy() {
                    if (this.interval) clearInterval(this.interval);
                    if (this.tickId) clearInterval(this.tickId);
                }
            };
        }
    </script>
    @endpush
</x-app-layout>

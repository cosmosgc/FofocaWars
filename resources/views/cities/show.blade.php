<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4" x-data="renameCity('{{ route('cities.rename', [$war, $city]) }}', '{{ $city->name }}')">
            <template x-if="!editing">
                <div class="flex items-center gap-2">
                    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight" x-text="name"></h2>
                    <button @click="editing = true" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 text-sm">✏️</button>
                </div>
            </template>
            <template x-if="editing">
                <form class="flex items-center gap-2" @submit.prevent="submit()">
                    <input type="text" x-model="name"
                           class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm text-lg font-semibold px-2 py-1">
                    <button type="submit" class="text-green-600 hover:text-green-500 text-sm font-medium">Save</button>
                    <button @click="cancel()" type="button" class="text-gray-400 hover:text-gray-600 text-sm">Cancel</button>
                    <span x-show="msg" x-text="msg" class="text-sm" :class="err ? 'text-red-600' : 'text-green-600'"></span>
                </form>
            </template>
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

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg"
                 x-data="cityPreview('{{ route('api.wars.cities.buildings', [$war, $city]) }}', '{{ $city->name }}', {{ $city->tile_x }}, {{ $city->tile_y }})"
                 x-init="init()">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('City Preview') }}</h3>
                    </div>

                    <template x-if="loading">
                        <p class="text-sm text-gray-400">{{ __('Loading...') }}</p>
                    </template>

                    <div class="relative w-full aspect-video bg-gray-900 rounded-lg overflow-hidden border border-gray-700"
                         x-show="!loading">
                        <div class="absolute inset-0 flex items-center justify-center" x-html="svg"></div>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg"
                 x-data="cityBuildings('{{ route('api.wars.cities.buildings', [$war, $city]) }}', '{{ route('api.wars.cities.build', [$war, $city]) }}')"
                 x-init="init()">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('Buildings') }}</h3>
                        <span class="text-sm text-gray-500 dark:text-gray-400" x-text="'{{ $war->construction_speed }}x speed'"></span>
                    </div>

                    <template x-if="loading">
                        <p class="text-sm text-gray-400">{{ __('Loading...') }}</p>
                    </template>

                    <template x-if="!loading && buildings.length === 0">
                        <p class="text-sm text-gray-400">{{ __('No buildings available.') }}</p>
                    </template>

                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4" x-show="!loading">
                        <template x-for="b in buildings" :key="b.type">
                            <div class="border dark:border-gray-700 rounded-lg p-3 text-center relative"
                                 :class="b.level > 0 ? '' : 'opacity-60'">
                                <div class="text-2xl mb-1" x-text="b.icon"></div>
                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100" x-text="b.name"></div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5" x-text="b.description"></div>
                                <div class="mt-2">
                                    <span class="inline-block px-2 py-0.5 rounded-full text-xs font-bold"
                                          :class="b.level === 0 ? 'bg-gray-600 text-gray-300' : 'bg-blue-600 text-white'">
                                        <span x-text="b.level > 0 ? 'Lv ' + b.level + '/' + b.max_level : '{{ __('Not Built') }}'"></span>
                                    </span>
                                </div>

                                <template x-if="b.is_under_construction">
                                    <div class="mt-2">
                                        <div class="text-xs text-yellow-500 font-medium">{{ __('Under Construction') }}</div>
                                        <div class="text-xs text-gray-400" x-text="'Ready: ' + new Date(b.finishes_at).toLocaleString()"></div>
                                    </div>
                                </template>

                                <template x-if="b.can_upgrade">
                                    <div class="mt-2">
                                        <button @click="doBuild(b.type)"
                                                :disabled="busy === b.type"
                                                class="w-full px-2 py-1 text-xs font-medium text-white bg-blue-600 rounded hover:bg-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                                                x-text="busy === b.type ? '{{ __('...') }}' : '{{ __('Upgrade') }}'">
                                        </button>
                                        <div class="text-xs text-gray-400 mt-0.5">
                                            <template x-for="(val, key) in b.next_costs" :key="key">
                                                <span x-show="val > 0" x-text="key + ':' + val + ' '"></span>
                                            </template>
                                            <span x-text="b.build_time + 'min'"></span>
                                        </div>
                                    </div>
                                </template>

                                <template x-if="!b.can_upgrade && !b.is_under_construction && b.level > 0">
                                    <div class="mt-2 text-xs text-green-500 font-medium">{{ __('Max Level') }}</div>
                                </template>
                            </div>
                        </template>
                    </div>

                    <div x-show="message" x-text="message" class="mt-3 text-sm" :class="error ? 'text-red-600' : 'text-green-600'"></div>
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
                                    <span class="inline-flex items-center px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded text-xs font-medium text-gray-800 dark:text-gray-200 gap-1">
                                        @if($u->unitType->image)
                                            <img src="{{ $u->unitType->image }}" alt="{{ $u->unitType->name }}" class="w-3 h-3 object-contain">
                                        @endif
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
                                    <div class="font-medium text-gray-900 dark:text-gray-100 flex items-center justify-center gap-1">
                                        @if($ut->image)
                                            <img src="{{ $ut->image }}" alt="{{ $ut->name }}" class="w-4 h-4 object-contain inline-block">
                                        @endif
                                        {{ $ut->name }}
                                    </div>
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
        function renameCity(url, originalName) {
            return {
                editing: false, name: originalName, original: originalName, msg: '', err: false,
                cancel() { this.name = this.original; this.editing = false; this.msg = ''; },
                async submit() {
                    if (!this.name.trim() || this.name === this.original) { this.editing = false; return; }
                    this.msg = ''; this.err = false;
                    try {
                        const res = await fetch(url, {
                            method: 'POST',
                            headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
                            body: JSON.stringify({name: this.name})
                        });
                        const data = await res.json();
                        if (!res.ok) { this.err = true; this.msg = data.error || 'Error'; }
                        else { this.original = this.name; this.msg = ''; this.editing = false; }
                    } catch(e) { this.err = true; this.msg = 'Request failed.'; }
                },
            };
        }

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

        function cityPreview(listUrl, cityName, tileX, tileY) {
            return {
                buildings: [], loading: true, svg: '',
                async init() { await this.fetch(); setInterval(() => this.fetch(), 15000); },
                async fetch() {
                    try {
                        const r = await fetch(listUrl);
                        const d = await r.json();
                        this.buildings = d.buildings;
                        this.renderSvg();
                        this.loading = false;
                    } catch(e) {}
                },
                renderSvg() {
                    const defs = {
                        town_hall: { color: '#8B4513', roofColor: '#A0522D', w: 28 },
                        lumber_mill: { color: '#6B8E23', roofColor: '#556B2F', w: 16 },
                        quarry: { color: '#808080', roofColor: '#696969', w: 16 },
                        farm: { color: '#228B22', roofColor: '#006400', w: 20 },
                        smelter: { color: '#B22222', roofColor: '#8B0000', w: 14 },
                        barracks: { color: '#4682B4', roofColor: '#36648B', w: 18 },
                        wall: { color: '#A0522D', roofColor: '#8B4513', w: 22 },
                        market: { color: '#DAA520', roofColor: '#B8860B', w: 16 },
                    };

                    const positions = [
                        { x: 100, y: 140, s: 1.2 },
                        { x: 180, y: 125, s: 1.0 },
                        { x: 260, y: 130, s: 1.0 },
                        { x: 140, y: 160, s: 0.9 },
                        { x: 220, y: 155, s: 0.9 },
                        { x: 300, y: 145, s: 0.8 },
                        { x: 330, y: 160, s: 0.8 },
                        { x: 70,  y: 155, s: 0.8 },
                    ];

                    const withLevel = this.buildings.filter(b => b.level > 0);
                    let buildingsHtml = '';

                    if (withLevel.length === 0) {
                        buildingsHtml += `<rect x="185" y="150" width="30" height="20" fill="#5a4a3a" rx="2"/>
                                          <polygon points="180,150 200,140 220,150" fill="#7a6a5a"/>`;
                    } else {
                        withLevel.forEach((b, i) => {
                            const d = defs[b.type] || { color: '#555', roofColor: '#444', w: 14 };
                            const p = positions[i] || positions[0];
                            const w = d.w + (b.level * 2);
                            const h = 18 + (b.level * 4);
                            const sc = (p.s + (b.level * 0.05)).toFixed(2);

                            buildingsHtml += `<g transform="translate(${p.x},${p.y}) scale(${sc})">`;
                            buildingsHtml += `<rect x="${-w/2}" y="${-h}" width="${w}" height="${h}" fill="${d.color}" rx="2" stroke="#ffd700" stroke-width="1.5" opacity="0.95"/>`;
                            buildingsHtml += `<polygon points="${-w/2-4},${-h} 0,${-h-10} ${w/2+4},${-h}" fill="${d.roofColor}"/>`;
                            buildingsHtml += `<text x="0" y="${-h/2 + 3}" text-anchor="middle" fill="white" font-size="8" font-weight="bold">Lv${b.level}</text>`;
                            buildingsHtml += `<rect x="-4" y="-8" width="8" height="8" fill="#4a3728" rx="1"/>`;
                            buildingsHtml += `</g>`;
                        });
                    }

                    this.svg = `<svg viewBox="0 0 400 250" class="w-full h-full">
                        <defs>
                            <linearGradient id="sky" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#1a1a2e"/>
                                <stop offset="100%" stop-color="#16213e"/>
                            </linearGradient>
                            <linearGradient id="ground" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#3a7d1e"/>
                                <stop offset="100%" stop-color="#2d5016"/>
                            </linearGradient>
                        </defs>
                        <rect x="0" y="0" width="400" height="180" fill="url(#sky)"/>
                        <rect x="0" y="180" width="400" height="70" fill="url(#ground)"/>
                        <circle cx="40" cy="30" r="1" fill="white" opacity="0.7"/>
                        <circle cx="120" cy="15" r="1" fill="white" opacity="0.6"/>
                        <circle cx="200" cy="25" r="1.5" fill="white" opacity="0.8"/>
                        <circle cx="300" cy="10" r="1" fill="white" opacity="0.5"/>
                        <circle cx="360" cy="35" r="1" fill="white" opacity="0.7"/>
                        <circle cx="80" cy="50" r="1" fill="white" opacity="0.4"/>
                        <circle cx="250" cy="40" r="1" fill="white" opacity="0.6"/>
                        <circle cx="340" cy="55" r="1" fill="white" opacity="0.5"/>
                        ${buildingsHtml}
                        <text x="200" y="225" text-anchor="middle" fill="#e0e0e0" font-size="14" font-weight="bold">${cityName}</text>
                        <text x="200" y="242" text-anchor="middle" fill="#a0a0a0" font-size="10">${tileX}, ${tileY}</text>
                    </svg>`;
                },
            };
        }

        function cityBuildings(listUrl, buildUrl) {
            return {
                buildings: [], loading: true, message: '', error: false, busy: null,
                async init() { await this.fetch(); setInterval(() => this.fetch(), 15000); },
                async fetch() {
                    try {
                        const r = await fetch(listUrl);
                        const d = await r.json();
                        this.buildings = d.buildings;
                        this.loading = false;
                    } catch(e) {}
                },
                async doBuild(type) {
                    this.busy = type; this.message = ''; this.error = false;
                    try {
                        const res = await fetch(buildUrl, {
                            method: 'POST',
                            headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
                            body: JSON.stringify({type: type})
                        });
                        const data = await res.json();
                        if (!res.ok) { this.error = true; this.message = data.error || 'Error'; }
                        else { this.message = data.message; this.fetch(); }
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

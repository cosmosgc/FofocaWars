<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ $war->name }} — {{ __('Armies') }}
            </h2>
            <span class="text-sm text-gray-500 dark:text-gray-400">{{ $cities->count() }} {{ __('Cities') }}</span>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if(session('success'))
                <div class="bg-green-100 dark:bg-green-900 border border-green-400 dark:border-green-700 text-green-700 dark:text-green-300 px-4 py-3 rounded">{{ session('success') }}</div>
            @endif

            @if($cities->isNotEmpty())
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6"
                 x-data="training({{ $cities->map(fn($c) => ['id' => $c->id, 'name' => $c->name])->toJson() }}, '{{ route('api.wars.train', $war) }}', '{{ route('api.wars.training-queue', $war) }}')"
                 x-init="init()">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">{{ __('Train Units') }}</h3>
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('City') }}</label>
                                <select x-model="cityId" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm text-sm">
                                    <option value="">{{ __('Select') }}</option>
                                    <template x-for="c in cities" :key="c.id">
                                        <option :value="c.id" x-text="c.name"></option>
                                    </template>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Quantity') }}</label>
                                <input type="number" x-model="qty" min="1" value="1"
                                       class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm text-sm">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Click a unit to train') }}</label>
                            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
                                @foreach($unitTypes as $ut)
                                    <div class="border dark:border-gray-700 rounded-lg p-3 text-center text-sm cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/50 transition"
                                         @click="cityId ? doTrain({{ $ut->id }}) : null"
                                         :class="!cityId ? 'opacity-50' : (busy === {{ $ut->id }} ? 'opacity-50 pointer-events-none' : '')"
                                         x-data="{ w: {{ $ut->wood_cost }}, s: {{ $ut->stone_cost }}, f: {{ $ut->food_cost }}, m: {{ $ut->metal_cost }}, tt: {{ $ut->training_time }} }">
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
                        </div>

                        <div x-show="message" x-text="message" class="text-sm" :class="error ? 'text-red-600' : 'text-green-600'"></div>

                        <div x-show="queue.length > 0" class="border-t dark:border-gray-700 pt-4">
                            <h4 class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-2">{{ __('Training in Progress') }}</h4>
                            <template x-for="q in queue" :key="q.id">
                                <div class="flex items-center justify-between text-sm py-1">
                                    <span class="text-gray-900 dark:text-gray-100" x-text="q.quantity + 'x ' + q.unit + ' (city ' + q.city_id + ')'"></span>
                                    <span class="text-gray-500" x-text="'Ready: ' + new Date(q.finishes_at).toLocaleString()"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">{{ __('Send Army') }}</h3>
                    <form action="{{ route('armies.send', $war) }}" method="POST" class="space-y-4">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="origin_city_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('From') }}</label>
                                <select name="origin_city_id" id="origin_city_id" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm text-sm">
                                    @foreach($cities as $city)
                                        <option value="{{ $city->id }}">{{ $city->name }} ({{ $city->tile_x }},{{ $city->tile_y }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="target_city_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('To') }}</label>
                                <select name="target_city_id" id="target_city_id" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm text-sm">
                                    @foreach($war->cities()->get() as $city)
                                        <option value="{{ $city->id }}" {{ $city->owner_id === $player->id ? 'data-own="1"' : '' }}>{{ $city->name }} ({{ $city->tile_x }},{{ $city->tile_y }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Mission') }}</label>
                            <div class="flex gap-4">
                                <label class="inline-flex items-center">
                                    <input type="radio" name="mission" value="attack" checked class="text-red-600 focus:ring-red-500">
                                    <span class="ml-2 text-sm text-gray-900 dark:text-gray-100">⚔️ {{ __('Attack') }}</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" name="mission" value="reinforce" class="text-blue-600 focus:ring-blue-500">
                                    <span class="ml-2 text-sm text-gray-900 dark:text-gray-100">🛡️ {{ __('Reinforce') }}</span>
                                </label>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Units to send') }}</label>
                            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-2">
                                @foreach($unitTypes as $ut)
                                    <div class="border dark:border-gray-700 rounded p-2 text-center">
                                        <div class="text-xs font-medium text-gray-900 dark:text-gray-100 flex items-center justify-center gap-1">
                                            @if($ut->image)
                                                <img src="{{ $ut->image }}" alt="{{ $ut->name }}" class="w-3 h-3 object-contain">
                                            @endif
                                            {{ $ut->name }}
                                        </div>
                                        <div class="text-xs text-gray-500">{{ $ut->attack }}/{{ $ut->defense }}</div>
                                        <input type="number" name="units[{{ $ut->id }}]" min="0" value="0"
                                               class="mt-1 w-full text-center text-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded">
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <button type="submit"
                           class="px-4 py-2 bg-red-600 text-white text-sm font-semibold rounded-md hover:bg-red-500">
                            {{ __('Send Army') }}
                        </button>
                    </form>
                </div>

                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6"
                     x-data="garrisons('{{ route('api.wars.garrisons', $war) }}')"
                     x-init="init()">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">{{ __('Garrisons') }}</h3>
                    <div x-show="list.length === 0" class="text-gray-500 text-sm">{{ __('No garrisons.') }}</div>
                    <template x-for="g in list" :key="g.id">
                        <div class="border dark:border-gray-700 rounded-lg p-3 mb-2 flex items-center justify-between">
                            <div class="text-sm">
                                <span class="font-medium text-gray-900 dark:text-gray-100" x-text="g.target_name"></span>
                                <span class="text-gray-500 ml-2" x-text="g.units.map(u => u.quantity + 'x ' + u.name).join(', ')"></span>
                            </div>
                            <form method="POST" :action="g.recall_url">
                                @csrf
                                <button type="submit" class="text-xs px-3 py-1.5 bg-yellow-600 text-white rounded hover:bg-yellow-500">{{ __('Recall') }}</button>
                            </form>
                        </div>
                    </template>
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6"
                 x-data="movements('{{ route('api.wars.armies.movements', $war) }}')"
                 x-init="init()">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">{{ __('Active Movements') }}</h3>
                <div x-show="armies.length === 0" class="text-gray-500 text-sm">{{ __('No active movements.') }}</div>
                <template x-for="a in armies" :key="a.id">
                    <div class="border dark:border-gray-700 rounded-lg p-3 mb-2 flex items-center justify-between">
                        <div>
                            <span class="text-sm font-medium text-gray-900 dark:text-gray-100" x-text="a.status"></span>
                            <span class="text-xs text-gray-500 ml-2" x-text="'(' + a.origin.x + ',' + a.origin.y + ' → ' + a.target.x + ',' + a.target.y + ')'"></span>
                        </div>
                        <div class="text-xs text-gray-500" x-text="'Arrives: ' + new Date(a.arrival_at).toLocaleString()"></div>
                    </div>
                </template>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6"
                 x-data="battles('{{ route('api.wars.battles.reports', $war) }}')"
                 x-init="init()">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">{{ __('Battle Reports') }}</h3>
                <div x-show="reports.length === 0" class="text-gray-500 text-sm">{{ __('No battle reports.') }}</div>
                <template x-for="r in reports" :key="r.id">
                    <div class="border dark:border-gray-700 rounded-lg p-3 mb-2">
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="text-sm font-medium" :class="r.winner === 'attacker' ? 'text-green-600' : 'text-red-600'" x-text="r.winner === 'attacker' ? 'Victory' : 'Defeat'"></span>
                                <span class="text-xs text-gray-500 ml-2" x-text="r.origin + ' → ' + r.city"></span>
                            </div>
                            <div class="text-xs text-gray-500" x-text="new Date(r.created_at).toLocaleString()"></div>
                        </div>
                        <div x-show="r.details" class="mt-1 text-xs text-gray-500">
                            <span x-text="'Power: ' + r.details.attack_power + ' vs ' + r.details.defense_power"></span>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function training(cities, trainUrl, queueUrl) {
            return {
                cities: cities, cityId: '', qty: 1,
                message: '', error: false, busy: null, queue: [],
                init() { this.fetchQueue(); setInterval(() => this.fetchQueue(), 10000); },
                async fetchQueue() {
                    try { const r = await fetch(queueUrl); this.queue = await r.json(); }
                    catch(e) {}
                },
                async doTrain(unitTypeId) {
                    if (!this.cityId) { this.message = 'Select a city first.'; this.error = true; return; }
                    this.busy = unitTypeId;
                    this.message = ''; this.error = false;
                    try {
                        const res = await fetch(trainUrl, {
                            method: 'POST',
                            headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
                            body: JSON.stringify({city_id: this.cityId, unit_type_id: unitTypeId, quantity: this.qty})
                        });
                        const data = await res.json();
                        if (!res.ok) { this.error = true; this.message = data.error || 'Error'; }
                        else { this.message = data.message; this.fetchQueue(); }
                    } catch(e) { this.error = true; this.message = 'Request failed.'; }
                    this.busy = null;
                },
            };
        }
        function garrisons(url) {
            return { list: [], interval: null,
                init() { this.fetch(); this.interval = setInterval(() => this.fetch(), 15000); },
                async fetch() {
                    try { const r = await fetch(url); this.list = await r.json(); }
                    catch(e) {}
                },
                destroy() { if (this.interval) clearInterval(this.interval); }
            };
        }
        function movements(url) {
            return { armies: [], interval: null,
                init() { this.fetch(); this.interval = setInterval(() => this.fetch(), 10000); },
                async fetch() {
                    try { const r = await fetch(url); this.armies = await r.json(); }
                    catch(e) {}
                },
                destroy() { if (this.interval) clearInterval(this.interval); }
            };
        }
        function battles(url) {
            return { reports: [], interval: null,
                init() { this.fetch(); this.interval = setInterval(() => this.fetch(), 15000); },
                async fetch() {
                    try { const r = await fetch(url); this.reports = await r.json(); }
                    catch(e) {}
                },
                destroy() { if (this.interval) clearInterval(this.interval); }
            };
        }
    </script>
    @endpush
</x-app-layout>

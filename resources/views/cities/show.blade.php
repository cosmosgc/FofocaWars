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
                 x-data="cityPreviewPixi('{{ route('api.wars.cities.buildings', [$war, $city]) }}', '{{ route('api.wars.cities.buildings.save-positions', [$war, $city]) }}', '{{ $city->name }}')"
                 x-init="init()">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('City Preview') }}</h3>
                        <button x-show="dirty" @click="savePositions()" :disabled="saving"
                                class="px-3 py-1 text-xs font-medium text-white bg-green-600 rounded hover:bg-green-500 disabled:opacity-50">
                            <span x-text="saving ? '{{ __('Saving...') }}' : '{{ __('Save Layout') }}'"></span>
                        </button>
                    </div>

                    <template x-if="loading">
                        <p class="text-sm text-gray-400">{{ __('Loading...') }}</p>
                    </template>

                    <div class="relative w-full aspect-video bg-gray-900 rounded-lg overflow-hidden border border-gray-700 pixi-preview"
                         x-show="!loading">
                        <div x-show="dirty" class="absolute top-2 left-2 bg-yellow-500 text-black text-xs px-2 py-0.5 rounded font-medium z-10 pointer-events-none">
                            {{ __('Unsaved') }}
                        </div>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pixi.js/7.3.2/pixi.min.js"></script>
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

        function cityPreviewPixi(listUrl, saveUrl, cityName) {
            return {
                buildings: [], loading: true, app: null, npcs: [], soldiers: [],
                animId: null, destroyed: false, dirty: false, saving: false,
                builtSprites: [], wallGfx: null,
                wallLevel: 0, wallRadius: 100, cx: 0, cy: 0,

                async init() {
                    await this.fetch();
                    this.$nextTick(() => this.initPixi());
                },

                destroy() {
                    this.destroyed = true;
                    if (this.animId) cancelAnimationFrame(this.animId);
                    if (this.app && !this.app.destroyed) {
                        this.app.destroy(true, { children: true, texture: true });
                    }
                },

                async fetch() {
                    try {
                        const r = await fetch(listUrl);
                        const d = await r.json();
                        this.buildings = d.buildings;
                        this.loading = false;
                    } catch(e) {}
                },

                initPixi() {
                    const el = this.$el.querySelector('.pixi-preview');
                    if (!el || typeof PIXI === 'undefined' || el.clientWidth === 0) return;

                    const w = el.clientWidth;
                    const h = el.clientHeight;

                    this.app = new PIXI.Application({
                        width: w, height: h,
                        backgroundColor: 0x2d5a1e,
                        antialias: true,
                        resolution: window.devicePixelRatio || 1,
                        autoDensity: true,
                    });

                    el.appendChild(this.app.view);
                    this.drawScene();
                },

                drawScene() {
                    const app = this.app;
                    const W = app.screen.width;
                    const H = app.screen.height;
                    this.cx = W / 2;
                    this.cy = H / 2 + 10;
                    const cx = this.cx, cy = this.cy;
                    const tileSize = Math.max(16, Math.floor(W / 20));

                    // --- Grid ---
                    const grid = new PIXI.Graphics();
                    for (let x = 0; x < W; x += tileSize) {
                        for (let y = 0; y < H; y += tileSize) {
                            const shade = ((x / tileSize + y / tileSize) % 2 === 0) ? 0x3a7d1e : 0x2d6b14;
                            grid.beginFill(shade);
                            grid.drawRect(x, y, tileSize, tileSize);
                            grid.endFill();
                        }
                    }
                    app.stage.addChild(grid);

                    const built = this.buildings.filter(b => b.level > 0);

                    // --- Grid layout for buildings ---
                    const bCellSize = 44;
                    const bGridCols = Math.ceil(Math.sqrt(built.length)) || 1;
                    const bGridRows = Math.ceil(built.length / bGridCols) || 1;

                    // --- Wall circle ---
                    const wallB = built.find(b => b.type === 'wall');
                    this.wallLevel = wallB ? wallB.level : 0;
                    this.wallRadius = Math.max(bGridCols, bGridRows) * bCellSize / 2 + 50;
                    this.wallGfx = new PIXI.Graphics();
                    this.drawWall();
                    app.stage.addChild(this.wallGfx);

                    // --- Forest patches ---
                    const drawForest = (fx, fy) => {
                        const f = new PIXI.Graphics();
                        f.beginFill(0x1a5c1a, 0.8); f.drawCircle(fx, fy, 26); f.endFill();
                        f.beginFill(0x145014, 0.6); f.drawCircle(fx + 16, fy - 8, 18); f.endFill();
                        f.beginFill(0x1a6a1a, 0.5); f.drawCircle(fx - 10, fy + 12, 14); f.endFill();
                        app.stage.addChild(f);
                    };
                    drawForest(cx - this.wallRadius - 30, cy - 10);
                    drawForest(cx + this.wallRadius + 30, cy);

                    // --- Rocky patches for quarry ---
                    const drawRocks = (rx, ry) => {
                        const r = new PIXI.Graphics();
                        r.beginFill(0x5a5a5a, 0.7); r.drawCircle(rx, ry, 12); r.endFill();
                        r.beginFill(0x4a4a4a, 0.5); r.drawCircle(rx + 10, ry + 5, 8); r.endFill();
                        app.stage.addChild(r);
                    };
                    drawRocks(cx - this.wallRadius - 20, cy + 15);
                    drawRocks(cx + this.wallRadius + 25, cy - 15);

                    // --- Field patches for farm ---
                    const drawField = (fx, fy) => {
                        const f = new PIXI.Graphics();
                        f.beginFill(0x8B8B00, 0.6); f.drawRect(fx - 12, fy - 8, 24, 16); f.endFill();
                        f.lineStyle(1, 0x6B6B00, 0.4);
                        for (let i = -8; i <= 8; i += 8) { f.moveTo(fx + i, fy - 8); f.lineTo(fx + i, fy + 8); }
                        app.stage.addChild(f);
                    };
                    drawField(cx - this.wallRadius - 25, cy - 20);
                    drawField(cx + this.wallRadius + 20, cy + 15);

                    // --- Building definitions ---
                    const defs = [
                        { type: 'town_hall',   color: 0x8B4513, label: 'Town Hall' },
                        { type: 'lumber_mill', color: 0x6B8E23, label: 'Lumber Mill' },
                        { type: 'farm',        color: 0x228B22, label: 'Farm' },
                        { type: 'quarry',      color: 0x808080, label: 'Quarry' },
                        { type: 'smelter',     color: 0xB22222, label: 'Smelter' },
                        { type: 'market',      color: 0xDAA520, label: 'Market' },
                        { type: 'barracks',    color: 0x4682B4, label: 'Barracks' },
                    ];

                    const bSprites = [];
                    const gridStartX = cx - (bGridCols - 1) * bCellSize / 2;
                    const gridStartY = cy - (bGridRows - 1) * bCellSize / 2;
                    const occupied = {};

                    built.forEach(b => {
                        const d = defs.find(x => x.type === b.type);
                        if (!d) return;

                        const size = 10 + b.level * 2;

                        let bx, by;
                        if (b.pos_x != null && b.pos_y != null) {
                            bx = b.pos_x;
                            by = b.pos_y;
                        } else {
                            let col = 0, row = 0, found = false;
                            for (let r = 0; r < bGridRows && !found; r++) {
                                for (let c = 0; c < bGridCols && !found; c++) {
                                    const key = c + ',' + r;
                                    if (!occupied[key]) {
                                        col = c; row = r;
                                        occupied[key] = true;
                                        found = true;
                                    }
                                }
                            }
                            bx = gridStartX + col * bCellSize;
                            by = gridStartY + row * bCellSize;
                        }

                        const container = new PIXI.Container();
                        container.x = bx;
                        container.y = by;

                        const gfx = new PIXI.Graphics();
                        gfx.beginFill(d.color, 0.9);
                        gfx.drawRoundedRect(-size / 2, -size / 2, size, size, 2);
                        gfx.endFill();
                        container.addChild(gfx);

                        const lt = new PIXI.Text(d.label, { fontSize: 9, fill: '#eee', fontFamily: 'monospace', align: 'center' });
                        lt.anchor.set(0.5, 0); lt.y = size / 2 + 2;
                        container.addChild(lt);

                        const lv = new PIXI.Text('Lv' + b.level, { fontSize: 8, fill: '#ffd700', fontFamily: 'monospace', align: 'center' });
                        lv.anchor.set(0.5, 1); lv.y = -size / 2 - 2;
                        container.addChild(lv);

                        app.stage.addChild(container);

                        bSprites.push({ type: b.type, container, level: b.level, bx, by, size });
                    });

                    this.builtSprites = bSprites;

                    // --- NPCs ---
                    this.npcs = [];

                    // Helper to create an NPC with movement path
                    const addNpc = (fromSprite, toPositions, color, drawFn) => {
                        if (!fromSprite) return;
                        toPositions.forEach(tp => {
                            const dot = new PIXI.Graphics();
                            drawFn(dot);
                            dot.x = fromSprite.container.x;
                            dot.y = fromSprite.container.y;
                            app.stage.addChild(dot);
                            this.npcs.push({
                                gfx: dot,
                                fromX: fromSprite.container.x, fromY: fromSprite.container.y,
                                toX: tp.x, toY: tp.y,
                                progress: Math.random(),
                                speed: 0.003 + Math.random() * 0.004,
                                paused: false, pauseTimer: 0,
                                wobble: Math.random() * Math.PI * 2,
                            });
                        });
                    };

                    const drawWoodcutter = (g) => {
                        g.beginFill(0x8B4513); g.drawCircle(0, 0, 3); g.endFill();
                        const ax = new PIXI.Graphics();
                        ax.lineStyle(1, 0x654321); ax.moveTo(-1, -2); ax.lineTo(1, 2); ax.moveTo(1, -2); ax.lineTo(-1, 2);
                        g.addChild(ax);
                    };
                    const drawFarmer = (g) => {
                        g.beginFill(0xDAA520); g.drawCircle(0, 0, 3); g.endFill();
                        const fork = new PIXI.Graphics();
                        fork.lineStyle(1, 0x8B7355); fork.moveTo(0, -3); fork.lineTo(0, 3);
                        fork.moveTo(-1, -3); fork.lineTo(1, -3);
                        g.addChild(fork);
                    };
                    const drawMiner = (g) => {
                        g.beginFill(0x888888); g.drawCircle(0, 0, 3); g.endFill();
                        const pick = new PIXI.Graphics();
                        pick.lineStyle(1, 0x444444); pick.moveTo(-2, 1); pick.lineTo(2, -1);
                        pick.moveTo(-1, 1); pick.lineTo(0, 3);
                        g.addChild(pick);
                    };
                    const drawSmelter = (g) => {
                        g.beginFill(0xCC5500); g.drawCircle(0, 0, 3); g.endFill();
                        const ham = new PIXI.Graphics();
                        ham.lineStyle(1, 0x444444); ham.moveTo(0, -2); ham.lineTo(0, 3);
                        ham.moveTo(-2, -2); ham.lineTo(2, -2);
                        g.addChild(ham);
                    };
                    const drawTrader = (g) => {
                        g.beginFill(0x4488CC); g.drawCircle(0, 0, 3); g.endFill();
                        const bag = new PIXI.Graphics();
                        bag.beginFill(0x8B4513); bag.drawCircle(0, 1, 1.5); bag.endFill();
                        g.addChild(bag);
                    };

                    const lm = bSprites.find(s => s.type === 'lumber_mill');
                    const farm = bSprites.find(s => s.type === 'farm');
                    const quarry = bSprites.find(s => s.type === 'quarry');
                    const smelter = bSprites.find(s => s.type === 'smelter');
                    const market = bSprites.find(s => s.type === 'market');
                    const br = bSprites.find(s => s.type === 'barracks');

                    addNpc(lm, [
                        { x: cx - this.wallRadius - 30, y: cy - 10 },
                        { x: cx + this.wallRadius + 30, y: cy },
                    ], 0x8B4513, drawWoodcutter);

                    addNpc(farm, [
                        { x: cx - this.wallRadius - 25, y: cy - 20 },
                    ], 0xDAA520, drawFarmer);

                    addNpc(quarry, [
                        { x: cx - this.wallRadius - 20, y: cy + 15 },
                        { x: cx + this.wallRadius + 25, y: cy - 15 },
                    ], 0x888888, drawMiner);

                    addNpc(smelter, [
                        { x: smelter.container.x + 20, y: smelter.container.y + 10 },
                    ], 0xCC5500, drawSmelter);

                    addNpc(market, [
                        { x: market ? market.container.x - 15 : cx, y: market ? market.container.y + 15 : cy },
                    ], 0x4488CC, drawTrader);

                    // --- Army formation ---
                    if (br) {
                        const offsetY = br.level >= 2 ? 22 : 16;
                        for (let r = 0; r < 3; r++) {
                            for (let c = 0; c < 3; c++) {
                                const s = new PIXI.Graphics();
                                s.beginFill(0xcc4444); s.drawRect(-2, -3, 4, 6); s.endFill();
                                s.beginFill(0x888888); s.drawCircle(0, 0, 1.5); s.endFill();
                                s.x = br.container.x + (c - 1) * 8;
                                s.y = br.container.y + offsetY + r * 8;
                                app.stage.addChild(s);
                                this.soldiers.push({ gfx: s, baseX: s.x, baseY: s.y });
                            }
                        }
                    }

                    // --- City name ---
                    const title = new PIXI.Text(cityName, { fontSize: 12, fill: '#fff', fontFamily: 'monospace', fontWeight: 'bold' });
                    title.anchor.set(0.5, 1); title.x = cx; title.y = H - 6;
                    app.stage.addChild(title);

                    this.startLoop();
                },

                drawWall() {
                    const g = this.wallGfx;
                    g.clear();
                    const cx = this.cx, cy = this.cy;
                    if (this.wallLevel > 0) {
                        const thickness = 2 + this.wallLevel * 3;
                        const t = Math.min(1, (this.wallLevel - 1) / 4);
                        const r = Math.round(139 * (1 - t) + 128 * t);
                        const g2 = Math.round(90 * (1 - t) + 128 * t);
                        const b = Math.round(43 * (1 - t) + 128 * t);
                        g.lineStyle(thickness, (r << 16) | (g2 << 8) | b, 0.85);
                    } else {
                        g.lineStyle(1, 0x7a5a3a, 0.25);
                    }
                    g.drawCircle(cx, cy, this.wallRadius);
                },

                async savePositions() {
                    this.saving = true;
                    const positions = this.builtSprites.map(s => ({
                        type: s.type,
                        pos_x: Math.round(s.container.x),
                        pos_y: Math.round(s.container.y),
                    }));
                    try {
                        const res = await fetch(saveUrl, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            body: JSON.stringify({ positions }),
                        });
                        const d = await res.json();
                        if (d.success) this.dirty = false;
                    } catch(e) {}
                    this.saving = false;
                },

                startLoop() {
                    const loop = () => {
                        if (this.destroyed) return;
                        this.npcs.forEach(w => {
                            if (w.paused) {
                                w.pauseTimer--;
                                if (w.pauseTimer <= 0) {
                                    w.paused = false;
                                    const tx = w.fromX; const ty = w.fromY;
                                    w.fromX = w.toX; w.fromY = w.toY;
                                    w.toX = tx; w.toY = ty;
                                    w.progress = 0;
                                }
                                return;
                            }
                            w.progress += w.speed;
                            if (w.progress >= 1) {
                                w.progress = 1;
                                w.paused = true;
                                w.pauseTimer = 40 + Math.random() * 80;
                            }
                            const t = w.progress * w.progress * (3 - 2 * w.progress);
                            w.gfx.x = w.fromX + (w.toX - w.fromX) * t;
                            w.gfx.y = w.fromY + (w.toY - w.fromY) * t + Math.sin(w.wobble + Date.now() * 0.002) * 0.5;
                        });
                        this.soldiers.forEach(s => {
                            s.gfx.x = s.baseX + Math.sin(Date.now() * 0.001 + s.baseY) * 0.3;
                        });
                        this.animId = requestAnimationFrame(loop);
                    };
                    this.animId = requestAnimationFrame(loop);
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

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ $base->name }}
            </h2>
            <span class="text-sm text-gray-500 dark:text-gray-400">
                ({{ $base->tile_x }}, {{ $base->tile_y }})
            </span>
            <span class="text-xs px-2 py-0.5 rounded-full text-white font-medium"
                  style="background: {{ $typeColors[$base->type] ?? '#6b7280' }}">
                {{ $typeNames[$base->type] ?? $base->type }}
            </span>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if(session('success'))
                <div class="bg-green-100 dark:bg-green-900 border border-green-400 dark:border-green-700 text-green-700 dark:text-green-300 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3">{{ __('Details') }}</h3>
                            <dl class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <dt class="text-gray-500 dark:text-gray-400">{{ __('Type') }}</dt>
                                    <dd class="text-gray-900 dark:text-gray-100 font-medium">{{ $typeNames[$base->type] ?? $base->type }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-500 dark:text-gray-400">{{ __('Level') }}</dt>
                                    <dd class="text-gray-900 dark:text-gray-100 font-medium">{{ $base->level }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-500 dark:text-gray-400">{{ __('Coordinates') }}</dt>
                                    <dd class="text-gray-900 dark:text-gray-100 font-mono">({{ $base->tile_x }}, {{ $base->tile_y }})</dd>
                                </div>
                            </dl>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3">{{ __('Description') }}</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                @switch($base->type)
                                    @case('resource')
                                        {{ __('A resource outpost that collects materials from the surrounding area.') }}
                                        @break
                                    @case('military')
                                        {{ __('A military camp where troops can be stationed and organized.') }}
                                        @break
                                    @case('trade')
                                        {{ __('A trading post where merchants gather to exchange goods.') }}
                                        @break
                                    @case('alliance')
                                        {{ __('An alliance base that serves as a meeting point for allied forces.') }}
                                        @break
                                    @default
                                        {{ __('A base of operations.') }}
                                @endswitch
                            </p>
                        </div>
                    </div>

                    <hr class="my-4 border-gray-300 dark:border-gray-600">

                    <form method="POST" action="{{ route('bases.rename', [$war, $base]) }}" class="flex items-end gap-4">
                        @csrf
                        <div class="grow">
                            <x-input-label for="rename" :value="__('Rename Base')" />
                            <x-text-input id="rename" name="name" type="text" class="mt-1 block w-full" value="{{ $base->name }}" required maxlength="100" />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>
                        <x-primary-button>{{ __('Rename') }}</x-primary-button>
                    </form>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg"
                 x-data="basePreviewPixi('{{ route('api.wars.bases.buildings', [$war, $base]) }}', '{{ route('api.wars.bases.buildings.save-positions', [$war, $base]) }}', '{{ $base->name }}')"
                 x-init="init()">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('Base Preview') }}</h3>
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
                 x-data="baseBuildings('{{ route('api.wars.bases.buildings', [$war, $base]) }}', '{{ route('api.wars.bases.build', [$war, $base]) }}')"
                 x-init="init()">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">{{ __('Constructions') }}</h3>

                    <template x-if="loading">
                        <p class="text-sm text-gray-400">{{ __('Loading...') }}</p>
                    </template>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4" x-show="!loading">
                        <template x-for="b in buildings" :key="b.type">
                            <div class="border dark:border-gray-700 rounded-lg p-4">
                                <div class="flex items-center gap-3">
                                    <div class="text-2xl" x-text="b.icon"></div>
                                    <div class="grow">
                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100" x-text="b.name"></div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400" x-text="b.description"></div>
                                        <div class="mt-1">
                                            <span class="inline-block px-2 py-0.5 rounded-full text-xs font-bold"
                                                  :class="b.level === 0 ? 'bg-gray-600 text-gray-300' : 'bg-emerald-600 text-white'">
                                                <span x-text="b.level > 0 ? 'Lv ' + b.level + '/' + b.max_level : '{{ __('Not Built') }}'"></span>
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <template x-if="b.is_under_construction">
                                    <div class="mt-2 flex items-center gap-2 text-sm">
                                        <span class="text-yellow-500 font-medium">{{ __('Under Construction') }}</span>
                                        <span class="text-gray-400 text-xs" x-text="'Ready: ' + new Date(b.finishes_at).toLocaleString()"></span>
                                    </div>
                                </template>

                                <template x-if="b.can_upgrade">
                                    <div class="mt-3">
                                        <div class="text-xs text-gray-400 mb-1">
                                            <template x-for="(val, key) in b.next_costs" :key="key">
                                                <span x-show="val > 0" x-text="key + ':' + val + ' '"></span>
                                            </template>
                                            <span x-text="b.build_time + 'min'"></span>
                                        </div>
                                        <button @click="doBuild(b.type)"
                                                :disabled="busy === b.type"
                                                class="w-full px-3 py-1.5 text-xs font-medium text-white bg-blue-600 rounded-md hover:bg-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                                                x-text="busy === b.type ? '{{ __('...') }}' : '{{ __('Upgrade') }}'">
                                        </button>
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

            <div class="flex gap-4">
                <a href="{{ route('wars.map', $war) }}"
                   class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-500 transition">
                    {{ __('Back to Map') }}
                </a>
                <a href="{{ route('wars.show', $war) }}"
                   class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 transition">
                    {{ __('War Overview') }}
                </a>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pixi.js/7.3.2/pixi.min.js"></script>
    <script>
        function basePreviewPixi(listUrl, saveUrl, baseName) {
            return {
                buildings: [], loading: true, app: null, guards: [], animId: null,
                destroyed: false, dirty: false, saving: false, builtSprites: [],
                dragTarget: null, dragOffX: 0, dragOffY: 0, cx: 0, cy: 0,

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
                        backgroundColor: 0x2d4a1e,
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
                    this.cx = W / 2; this.cy = H / 2;
                    const cx = this.cx, cy = this.cy;
                    const tileSize = Math.max(16, Math.floor(W / 20));

                    const grid = new PIXI.Graphics();
                    for (let x = 0; x < W; x += tileSize) {
                        for (let y = 0; y < H; y += tileSize) {
                            const shade = ((x / tileSize + y / tileSize) % 2 === 0) ? 0x3a6a1e : 0x2d5a14;
                            grid.beginFill(shade);
                            grid.drawRect(x, y, tileSize, tileSize);
                            grid.endFill();
                        }
                    }
                    app.stage.addChild(grid);

                    const built = this.buildings.filter(b => b.level > 0);

                    // --- Base HQ at center ---
                    const hqSize = 18 + built.length * 2;
                    const hq = new PIXI.Graphics();
                    hq.beginFill(0x556677, 0.9);
                    hq.drawRoundedRect(-hqSize / 2, -hqSize / 2, hqSize, hqSize, 3);
                    hq.endFill();
                    hq.lineStyle(2, 0x88aacc, 0.7);
                    hq.drawRoundedRect(-hqSize / 2, -hqSize / 2, hqSize, hqSize, 3);
                    hq.x = cx; hq.y = cy;
                    app.stage.addChild(hq);

                    const hqLabel = new PIXI.Text(baseName, { fontSize: 10, fill: '#fff', fontFamily: 'monospace', fontWeight: 'bold' });
                    hqLabel.anchor.set(0.5, 1);
                    hqLabel.x = cx; hqLabel.y = cy + hqSize / 2 + 4;
                    app.stage.addChild(hqLabel);

                    // --- Wall/fence circle ---
                    const wallLevel = built.find(b => b.type === 'base_wall')?.level || 0;
                    const wallRadius = 80 + built.length * 6;
                    const fence = new PIXI.Graphics();
                    if (wallLevel > 0) {
                        const t = Math.min(1, (wallLevel - 1) / 4);
                        const r = Math.round(139 * (1 - t) + 128 * t);
                        const g = Math.round(90 * (1 - t) + 128 * t);
                        const b = Math.round(43 * (1 - t) + 128 * t);
                        fence.lineStyle(2 + wallLevel * 2, (r << 16) | (g << 8) | b, 0.8);
                    } else {
                        fence.lineStyle(1, 0x7a5a3a, 0.2);
                    }
                    fence.drawCircle(cx, cy, wallRadius);
                    app.stage.addChild(fence);

                    // --- Buildings in a ring around HQ ---
                    const colors = [0x8B4513, 0x4682B4, 0x6B8E23, 0xB22222, 0xDAA520, 0x7B68EE];
                    const self = this;
                    this.builtSprites = [];

                    built.forEach((b, i) => {
                        const size = 10 + b.level * 2;
                        const col = colors[i % colors.length];

                        let bx = b.pos_x != null ? b.pos_x : 0;
                        let by = b.pos_y != null ? b.pos_y : 0;
                        if (b.pos_x == null) {
                            const angle = (i / built.length) * Math.PI * 2 - Math.PI / 2;
                            const dist = 35 + Math.min(i, 5) * 5;
                            bx = cx + Math.cos(angle) * dist;
                            by = cy + Math.sin(angle) * dist;
                        }

                        const container = new PIXI.Container();
                        container.x = bx; container.y = by;

                        const gfx = new PIXI.Graphics();
                        gfx.beginFill(col, 0.9);
                        gfx.drawRoundedRect(-size / 2, -size / 2, size, size, 2);
                        gfx.endFill();
                        container.addChild(gfx);

                        const lt = new PIXI.Text(b.name || b.type, { fontSize: 8, fill: '#ddd', fontFamily: 'monospace' });
                        lt.anchor.set(0.5, 0); lt.y = size / 2 + 2;
                        container.addChild(lt);

                        const lv = new PIXI.Text('Lv' + b.level, { fontSize: 7, fill: '#ffd700', fontFamily: 'monospace' });
                        lv.anchor.set(0.5, 1); lv.y = -size / 2 - 2;
                        container.addChild(lv);

                        container.eventMode = 'static';
                        container.cursor = 'move';
                        container.on('pointerdown', (e) => {
                            self.dragTarget = container;
                            self.dragOffX = e.globalX - container.x;
                            self.dragOffY = e.globalY - container.y;
                            container.alpha = 0.7;
                        });
                        container.on('pointerup', () => self.endDrag());
                        container.on('pointerupoutside', () => self.endDrag());

                        app.stage.on('pointermove', (e) => {
                            if (!self.dragTarget) return;
                            self.dragTarget.x = e.globalX - self.dragOffX;
                            self.dragTarget.y = e.globalY - self.dragOffY;
                        });

                        app.stage.addChild(container);
                        this.builtSprites.push({ type: b.type, container });
                    });

                    // --- Guard NPCs patrolling ---
                    const numGuards = Math.max(0, Math.min(4, built.length));
                    for (let i = 0; i < numGuards; i++) {
                        const guard = new PIXI.Graphics();
                        guard.beginFill(0xcc4444); guard.drawRect(-2, -3, 4, 6); guard.endFill();
                        guard.beginFill(0x884422); guard.drawCircle(0, -2, 1.5); guard.endFill();
                        const startAngle = (i / numGuards) * Math.PI * 2;
                        guard.x = cx + Math.cos(startAngle) * (wallRadius - 10);
                        guard.y = cy + Math.sin(startAngle) * (wallRadius - 10);
                        app.stage.addChild(guard);

                        this.guards.push({
                            gfx: guard, cx, cy, radius: wallRadius - 10,
                            angle: startAngle, speed: 0.003 + Math.random() * 0.002,
                        });
                    }

                    // --- Scouts patrolling outside ---
                    for (let i = 0; i < Math.min(2, built.length); i++) {
                        const scout = new PIXI.Graphics();
                        scout.beginFill(0x4488cc); scout.drawCircle(0, 0, 2); scout.endFill();
                        scout.beginFill(0x2266aa); scout.drawRect(-1, -2, 2, 4); scout.endFill();
                        const sa = (i / 2) * Math.PI * 2;
                        scout.x = cx + Math.cos(sa) * (wallRadius + 20);
                        scout.y = cy + Math.sin(sa) * (wallRadius + 20);
                        app.stage.addChild(scout);

                        this.guards.push({
                            gfx: scout, cx, cy, radius: wallRadius + 20,
                            angle: sa + 0.5, speed: 0.002 + Math.random() * 0.002,
                        });
                    }

                    this.startLoop();
                },

                endDrag() {
                    if (!this.dragTarget) return;
                    this.dragTarget.alpha = 1;
                    this.dragTarget = null;
                    this.dirty = true;
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
                        this.guards.forEach(g => {
                            g.angle += g.speed;
                            g.gfx.x = g.cx + Math.cos(g.angle) * g.radius;
                            g.gfx.y = g.cy + Math.sin(g.angle) * g.radius;
                        });
                        this.animId = requestAnimationFrame(loop);
                    };
                    this.animId = requestAnimationFrame(loop);
                },
            };
        }

        function baseBuildings(listUrl, buildUrl) {
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
    </script>
    @endpush
</x-app-layout>

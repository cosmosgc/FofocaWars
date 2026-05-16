<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ $war->name }} — {{ __('Map') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden">
                <div id="map-area" class="relative w-full" style="height: 70vh;">
                    <div id="pixi-container" class="absolute inset-0"></div>

                    <div id="map-coords" class="absolute bottom-2 right-2 bg-black/60 text-white text-xs px-2 py-1 rounded select-none pointer-events-none z-10">
                        x: 0, y: 0
                    </div>

                    <div id="map-legend" class="absolute bottom-2 left-2 bg-black/60 text-white text-xs px-2 py-1 rounded space-y-0.5 select-none pointer-events-none z-10">
                        <div class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-sm shrink-0" style="background:#90c96a"></span> {{ __('Plain') }}</div>
                        <div class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-sm shrink-0" style="background:#2d6a27"></span> {{ __('Forest') }}</div>
                        <div class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-sm shrink-0" style="background:#8a7f6e"></span> {{ __('Mountain') }}</div>
                        <div class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-sm shrink-0" style="background:#4a90d9"></span> {{ __('Water') }}</div>
                        <div class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-sm shrink-0" style="background:#e8d5a3"></span> {{ __('Desert') }}</div>
                        <div class="flex items-center gap-1.5 mt-1"><span class="w-2.5 h-2.5 rounded-full shrink-0" style="background:#f5a623"></span> {{ __('City') }}</div>
                    </div>

                    <div id="city-info" class="absolute top-1/2 right-4 -translate-y-1/2 bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 p-4 pr-7 hidden min-w-[240px] text-sm text-gray-900 dark:text-gray-100 z-20">
                        <button id="city-info-close" class="absolute top-1 right-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 text-lg leading-none">&times;</button>
                        <div id="city-info-content"></div>
                    </div>

                    <div id="tile-info" class="absolute top-1/2 left-4 -translate-y-1/2 bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 p-4 pr-7 hidden min-w-[220px] text-sm text-gray-900 dark:text-gray-100 z-20">
                        <button id="tile-info-close" class="absolute top-1 right-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 text-lg leading-none">&times;</button>
                        <div id="tile-info-content"></div>
                    </div>

                    <div class="absolute top-2 right-2 flex flex-col gap-2 z-10">
                        <div class="flex items-center gap-2 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 px-2.5 py-1.5">
                            <span class="text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap">{{ $cities->count() }} {{ __('cities') }}</span>
                            <select id="city-select" class="text-xs bg-transparent text-gray-900 dark:text-gray-100 border-0 cursor-pointer outline-none focus:ring-0">
                                <option value="">{{ __('Center...') }}</option>
                                @foreach($cities as $city)
                                    <option value="{{ $city->id }}" data-x="{{ $city->tile_x }}" data-y="{{ $city->tile_y }}">{{ $city->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex self-end rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 shadow-sm">
                            <button id="zoom-in" class="px-3 py-1 text-lg font-bold hover:bg-gray-100 dark:hover:bg-gray-600 leading-none border-r border-gray-300 dark:border-gray-600">+</button>
                            <button id="zoom-out" class="px-3 py-1 text-lg font-bold hover:bg-gray-100 dark:hover:bg-gray-600 leading-none">&minus;</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">{{ __('Your Territories') }}</h3>
                    @if($cities->isEmpty())
                        <p class="text-gray-500 dark:text-gray-400">{{ __('You have no cities in this war.') }}</p>
                    @else
                        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach($cities as $city)
                                <a href="{{ route('cities.show', [$war, $city]) }}"
                                   class="block border dark:border-gray-700 rounded-lg p-4 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                    <div class="flex items-center justify-between mb-2">
                                        <h4 class="font-medium text-gray-900 dark:text-gray-100">{{ $city->name }}</h4>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">({{ $city->tile_x }}, {{ $city->tile_y }})</span>
                                    </div>
                                    <div class="grid grid-cols-2 gap-x-4 gap-y-1 text-sm">
                                        <div class="text-gray-600 dark:text-gray-400">👥 {{ number_format($city->population) }}</div>
                                        <div class="text-gray-600 dark:text-gray-400">🪵 {{ number_format($city->wood) }}</div>
                                        <div class="text-gray-600 dark:text-gray-400">🪨 {{ number_format($city->stone) }}</div>
                                        <div class="text-gray-600 dark:text-gray-400">🍖 {{ number_format($city->food) }}</div>
                                        <div class="text-gray-600 dark:text-gray-400">⚙️ {{ number_format($city->metal) }}</div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pixi.js/7.3.2/pixi.min.js"></script>
    <script>
        const __i18n = (s) => s;

        document.addEventListener('DOMContentLoaded', async () => {
            const container = document.getElementById('pixi-container');
            const tilesUrl = "{{ route('api.wars.tiles', $war) }}";
            const citiesUrl = "{{ route('api.wars.cities', $war) }}";
            const movementsUrl = "{{ route('api.wars.armies.map-movements', $war) }}";
            const foundTileUrl = "{{ route('api.wars.tiles.found', $war) }}";
            const cityUrls = @json($cities->mapWithKeys(fn($c) => [$c->id => route('cities.show', [$war, $c])]));
            const playerCityCount = {{ $playerCityCount }};
            const constructionSpeed = {{ $war->construction_speed }};
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
            const coordsEl = document.getElementById('map-coords');
            const width = container.clientWidth;
            const height = container.clientHeight;

            const app = new PIXI.Application({
                width,
                height,
                backgroundColor: 0x1a1a2e,
                antialias: true,
                resolution: window.devicePixelRatio || 1,
                autoDensity: true,
            });

            container.appendChild(app.view);

            const worldContainer = new PIXI.Container();
            app.stage.addChild(worldContainer);

            const tileSize = 32;
            const terrainColors = {
                plain: 0x90c96a,
                forest: 0x2d6a27,
                mountain: 0x8a7f6e,
                water: 0x4a90d9,
                desert: 0xe8d5a3,
            };

            let tiles = [];
            let cities = [];
            let movements = [];

            try {
                const [tilesRes, citiesRes, movRes] = await Promise.all([
                    fetch(tilesUrl),
                    fetch(citiesUrl),
                    fetch(movementsUrl),
                ]);
                tiles = await tilesRes.json();
                cities = await citiesRes.json();
                movements = await movRes.json();
            } catch (e) {
                console.error('Failed to load map data:', e);
                container.innerHTML = '<p class="text-red-500 p-4">Failed to load map data.</p>';
                return;
            }

            const tileGraphics = new PIXI.Graphics();
            worldContainer.addChild(tileGraphics);

            const gridGraphics = new PIXI.Graphics();
            worldContainer.addChild(gridGraphics);

            let maxX = 0, maxY = 0;

            const cityInfoEl = document.getElementById('city-info');
            const cityInfoContent = document.getElementById('city-info-content');
            const cityInfoClose = document.getElementById('city-info-close');

            const tileInfoEl = document.getElementById('tile-info');
            const tileInfoContent = document.getElementById('tile-info-content');
            const tileInfoClose = document.getElementById('tile-info-close');
            tileInfoClose.onclick = () => tileInfoEl.classList.add('hidden');
            tileInfoEl.addEventListener('click', (e) => { if (e.target === tileInfoEl) tileInfoEl.classList.add('hidden'); });

            function computeBounds() {
                maxX = tiles.length > 0 ? Math.max(...tiles.map(t => t.x)) * tileSize : 0;
                maxY = tiles.length > 0 ? Math.max(...tiles.map(t => t.y)) * tileSize : 0;
            }

            computeBounds();
            redrawTiles();
            redrawGrid();
            redrawCities();

            function showCityInfo(city) {
                cityInfoContent.innerHTML = `
                    <div class="font-semibold text-base mb-2">${city.name}</div>
                    <div class="space-y-1 text-gray-600 dark:text-gray-300">
                        <div class="flex justify-between"><span>${__i18n('Coordinates')}</span><span class="font-mono">(${city.tile_x}, ${city.tile_y})</span></div>
                        <div class="flex justify-between"><span>${__i18n('Population')}</span><span>${city.population?.toLocaleString() || '0'}</span></div>
                        <div class="flex justify-between"><span>${__i18n('Wood')}</span><span>${city.wood?.toLocaleString() || '0'}</span></div>
                        <div class="flex justify-between"><span>${__i18n('Stone')}</span><span>${city.stone?.toLocaleString() || '0'}</span></div>
                        <div class="flex justify-between"><span>${__i18n('Food')}</span><span>${city.food?.toLocaleString() || '0'}</span></div>
                        <div class="flex justify-between"><span>${__i18n('Metal')}</span><span>${city.metal?.toLocaleString() || '0'}</span></div>
                        ${city.owner_name ? `<div class="flex justify-between"><span>${__i18n('Owner')}</span><span>${city.owner_name}</span></div>` : ''}
                    </div>
                    <a href="${cityUrls[city.id]}" class="mt-3 block text-center text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 underline underline-offset-2">
                        ${__i18n('View city details')} &rarr;
                    </a>
                `;
                cityInfoEl.classList.remove('hidden');
            }

            cityInfoClose.onclick = () => cityInfoEl.classList.add('hidden');
            cityInfoEl.addEventListener('click', (e) => { if (e.target === cityInfoEl) cityInfoEl.classList.add('hidden'); });

            const movContainer = new PIXI.Container();
            worldContainer.addChild(movContainer);
            let movDots = [];

            function rebuildMovements() {
                movContainer.removeChildren();
                movDots = [];
                for (const m of movements) {
                    const ox = m.origin.x * tileSize + tileSize / 2;
                    const oy = m.origin.y * tileSize + tileSize / 2;
                    const tx = m.target.x * tileSize + tileSize / 2;
                    const ty = m.target.y * tileSize + tileSize / 2;

                    const line = new PIXI.Graphics();
                    line.lineStyle(2, 0xff4444, 0.5);
                    line.moveTo(ox, oy);
                    line.lineTo(tx, ty);
                    movContainer.addChild(line);

                    const dot = new PIXI.Graphics();
                    dot.beginFill(0xff4444);
                    dot.drawCircle(0, 0, 5);
                    dot.endFill();
                    dot.beginFill(0xffffff, 0.3);
                    dot.drawCircle(0, 0, 10);
                    dot.endFill();

                    m._ox = ox; m._oy = oy; m._tx = tx; m._ty = ty;
                    m._start = new Date(m.created_at).getTime();
                    m._end = new Date(m.arrival_at).getTime();
                    m._total = m._end - m._start;

                    movContainer.addChild(dot);
                    movDots.push({ dot, m });
                }
            }

            rebuildMovements();

            app.ticker.add(() => {
                const now = Date.now();
                for (const { dot, m } of movDots) {
                    const p = m._total > 0 ? Math.min(1, Math.max(0, (now - m._start) / m._total)) : 0;
                    dot.x = m._ox + (m._tx - m._ox) * p;
                    dot.y = m._oy + (m._ty - m._oy) * p;
                    dot.alpha = p < 1 ? 1 : 0.3;
                }
            });

            setInterval(async () => {
                try {
                    const r = await fetch(movementsUrl);
                    movements = await r.json();
                    rebuildMovements();
                } catch(e) {}
            }, 15000);

            if (cities.length > 0) {
                const firstCity = cities[0];
                const targetX = firstCity.tile_x * tileSize + tileSize / 2;
                const targetY = firstCity.tile_y * tileSize + tileSize / 2;
                worldContainer.x = width / 2 - targetX;
                worldContainer.y = height / 2 - targetY;
            } else {
                worldContainer.x = width / 2 - (maxX / 2);
                worldContainer.y = height / 2 - (maxY / 2);
            }

            if (maxX > 0) {
                const mapW = maxX + tileSize;
                const mapH = maxY + tileSize;
                const scaleX = width / mapW;
                const scaleY = height / mapH;
                const fitScale = Math.min(scaleX, scaleY) * 0.8;
                if (fitScale < 1) {
                    worldContainer.scale.set(fitScale);
                    worldContainer.x = (width - mapW * fitScale) / 2;
                    worldContainer.y = (height - mapH * fitScale) / 2;
                }
            }

            let isDragging = false;
            let dragStart = { x: 0, y: 0, screenX: null, screenY: null };
            const dragThreshold = 4;

            function reloadMapData() {
                return Promise.all([
                    fetch(tilesUrl).then(r => r.json()),
                    fetch(citiesUrl).then(r => r.json()),
                ]);
            }

            async function refetchTilesAndCities() {
                try {
                    const [newTiles, newCities] = await reloadMapData();
                    tiles = newTiles;
                    cities = newCities;
                    worldContainer.removeChild(tileGraphics);
                    worldContainer.removeChild(gridGraphics);
                    redrawTiles();
                    redrawGrid();
                    redrawCities();
                } catch(e) {}
            }

            function redrawTiles() {
                tileGraphics.clear();
                for (const tile of tiles) {
                    const color = terrainColors[tile.terrain_type] || terrainColors.plain;
                    const px = tile.x * tileSize;
                    const py = tile.y * tileSize;
                    tileGraphics.beginFill(color);
                    tileGraphics.drawRect(px, py, tileSize, tileSize);
                    tileGraphics.endFill();
                    if (tile.owner_id) {
                        tileGraphics.beginFill(0xffffff, 0.12);
                        tileGraphics.drawRect(px, py, tileSize, tileSize);
                        tileGraphics.endFill();
                    }
                }
                worldContainer.addChildAt(tileGraphics, 0);
            }

            function redrawGrid() {
                gridGraphics.clear();
                gridGraphics.lineStyle(0.5, 0x000000, 0.08);
                for (let x = 0; x <= maxX + tileSize; x += tileSize) {
                    gridGraphics.moveTo(x, 0);
                    gridGraphics.lineTo(x, maxY + tileSize);
                }
                for (let y = 0; y <= maxY + tileSize; y += tileSize) {
                    gridGraphics.moveTo(0, y);
                    gridGraphics.lineTo(maxX + tileSize, y);
                }
                worldContainer.addChildAt(gridGraphics, 1);
            }

            function redrawCities() {
                const oldCities = worldContainer.children.filter(c => c._isCityContainer);
                oldCities.forEach(c => worldContainer.removeChild(c));

                for (const city of cities) {
                    const cx = city.tile_x * tileSize + tileSize / 2;
                    const cy = city.tile_y * tileSize + tileSize / 2;

                    const cityGfx = new PIXI.Graphics();
                    cityGfx.beginFill(0xf5a623);
                    cityGfx.drawCircle(cx, cy, 8);
                    cityGfx.endFill();
                    cityGfx.beginFill(0xffd700, 0.3);
                    cityGfx.drawCircle(cx, cy, 14);
                    cityGfx.endFill();

                    const label = new PIXI.Text(city.name, {
                        fontSize: 11,
                        fill: 0xffffff,
                        stroke: 0x000000,
                        strokeThickness: 3,
                    });
                    label.anchor.set(0.5, 0);
                    label.x = cx;
                    label.y = cy + 14;

                    const cc = new PIXI.Container();
                    cc._isCityContainer = true;
                    cc.addChild(cityGfx, label);
                    cc.eventMode = 'static';
                    cc.cursor = 'pointer';
                    cc.on('pointerdown', (e) => {
                        e.stopPropagation();
                        showCityInfo(city);
                    });

                    worldContainer.addChild(cc);
                }
            }

            function clickedTile(e) {
                const worldX = (e.global.x - worldContainer.x) / worldContainer.scale.x;
                const worldY = (e.global.y - worldContainer.y) / worldContainer.scale.y;
                const tileX = Math.floor(worldX / tileSize);
                const tileY = Math.floor(worldY / tileSize);
                const tile = tiles.find(t => t.x === tileX && t.y === tileY);
                if (!tile) return;

                const terrainNames = { plain: '{{ __('Plain') }}', forest: '{{ __('Forest') }}', mountain: '{{ __('Mountain') }}', water: '{{ __('Water') }}', desert: '{{ __('Desert') }}' };
                const terrainLabel = terrainNames[tile.terrain_type] || tile.terrain_type;

                const isOwned = !!tile.owner_id;
                const isWater = tile.terrain_type === 'water';
                const canFound = !isOwned && !isWater;

                let costHtml = '';
                if (canFound) {
                    if (playerCityCount === 0) {
                        costHtml = `<div class="mt-2 text-green-600 dark:text-green-400 font-medium text-xs">${__i18n('Free (first city)')}</div>`;
                    } else {
                        const cs = Math.max(1, constructionSpeed);
                        const costs = { wood: Math.round(200 * cs), stone: Math.round(150 * cs), food: Math.round(100 * cs), metal: Math.round(50 * cs) };
                        costHtml = `
                            <div class="mt-2 text-xs text-gray-500 dark:text-gray-400 font-medium">${__i18n('Cost')}:</div>
                            <div class="grid grid-cols-2 gap-x-2 gap-y-0.5 mt-1 text-xs">
                                <div class="flex items-center gap-1 text-gray-600 dark:text-gray-300"><span>🪵</span> <span>${costs.wood.toLocaleString()}</span></div>
                                <div class="flex items-center gap-1 text-gray-600 dark:text-gray-300"><span>🪨</span> <span>${costs.stone.toLocaleString()}</span></div>
                                <div class="flex items-center gap-1 text-gray-600 dark:text-gray-300"><span>🍖</span> <span>${costs.food.toLocaleString()}</span></div>
                                <div class="flex items-center gap-1 text-gray-600 dark:text-gray-300"><span>⚙️</span> <span>${costs.metal.toLocaleString()}</span></div>
                            </div>`;
                    }
                }

                tileInfoContent.innerHTML = `
                    <div class="font-semibold text-base mb-2">${__i18n('Tile')} (${tileX}, ${tileY})</div>
                    <div class="space-y-1 text-gray-600 dark:text-gray-300">
                        <div class="flex justify-between"><span>${__i18n('Terrain')}</span><span>${terrainLabel}</span></div>
                        ${isOwned ? `<div class="flex justify-between"><span>${__i18n('Owner')}</span><span>${__i18n('Occupied')}</span></div>` : ''}
                    </div>
                    ${costHtml}
                    ${canFound ? `
                        <button id="found-city-btn" class="mt-3 w-full text-center text-sm font-medium px-3 py-2 rounded bg-yellow-600 text-white hover:bg-yellow-500 transition">
                            ${__i18n('Found City')}
                        </button>
                        <div id="found-city-status" class="mt-2 text-xs hidden"></div>
                    ` : ''}
                `;
                tileInfoEl.classList.remove('hidden');

                if (canFound) {
                    const btn = document.getElementById('found-city-btn');
                    const status = document.getElementById('found-city-status');
                    btn.onclick = async () => {
                        btn.disabled = true;
                        btn.textContent = '...';
                        status.classList.remove('hidden');
                        status.textContent = '{{ __('Processing...') }}';
                        try {
                            const res = await fetch(foundTileUrl, {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                                body: JSON.stringify({ x: tileX, y: tileY }),
                            });
                            const data = await res.json();
                            if (data.success) {
                                status.textContent = '{{ __('City founded!') }}';
                                btn.textContent = '{{ __('Done') }}';
                                btn.disabled = true;
                                setTimeout(() => { tileInfoEl.classList.add('hidden'); }, 2000);
                                await refetchTilesAndCities();
                            } else {
                                status.textContent = data.error || '{{ __('Error') }}';
                                btn.disabled = false;
                                btn.textContent = '{{ __('Found City') }}';
                            }
                        } catch(e) {
                            status.textContent = '{{ __('Error') }}';
                            btn.disabled = false;
                            btn.textContent = '{{ __('Found City') }}';
                        }
                    };
                }
            }

            app.stage.eventMode = 'static';
            app.stage.hitArea = app.screen;

            app.stage.on('pointerdown', (e) => {
                isDragging = false;
                dragStart.x = e.global.x - worldContainer.x;
                dragStart.y = e.global.y - worldContainer.y;
                dragStart.screenX = e.global.x;
                dragStart.screenY = e.global.y;
            });

            app.stage.on('pointermove', (e) => {
                if (!dragStart.screenX && dragStart.screenX !== 0) {
                    const worldX = (e.global.x - worldContainer.x) / worldContainer.scale.x;
                    const worldY = (e.global.y - worldContainer.y) / worldContainer.scale.y;
                    coordsEl.textContent = `x: ${Math.floor(worldX / tileSize)}, y: ${Math.floor(worldY / tileSize)}`;
                    return;
                }
                const dx = e.global.x - dragStart.screenX;
                const dy = e.global.y - dragStart.screenY;
                if (Math.abs(dx) > dragThreshold || Math.abs(dy) > dragThreshold) {
                    isDragging = true;
                }
                if (isDragging) {
                    worldContainer.x = e.global.x - dragStart.x;
                    worldContainer.y = e.global.y - dragStart.y;
                }
            });

            app.stage.on('pointerup', (e) => {
                if (!isDragging) {
                    clickedTile(e);
                }
                isDragging = false;
                dragStart.screenX = null;
                dragStart.screenY = null;
            });
            app.stage.on('pointerupoutside', () => {
                isDragging = false;
                dragStart.screenX = null;
                dragStart.screenY = null;
            });

            function zoomAt(scaleDelta, cx, cy) {
                const newScale = worldContainer.scale.x * scaleDelta;
                if (newScale < 0.05 || newScale > 10) return;
                const worldPos = {
                    x: (cx - worldContainer.x) / worldContainer.scale.x,
                    y: (cy - worldContainer.y) / worldContainer.scale.y,
                };
                worldContainer.scale.set(newScale);
                worldContainer.x = cx - worldPos.x * worldContainer.scale.x;
                worldContainer.y = cy - worldPos.y * worldContainer.scale.y;
            }

            app.stage.on('wheel', (e) => {
                e.preventDefault();
                const rect = app.view.getBoundingClientRect();
                zoomAt(e.deltaY > 0 ? 0.9 : 1.1, e.clientX - rect.left, e.clientY - rect.top);
            });

            let pinchDist = 0;
            app.view.addEventListener('touchstart', (e) => {
                if (e.touches.length === 2) {
                    e.preventDefault();
                    const dx = e.touches[0].clientX - e.touches[1].clientX;
                    const dy = e.touches[0].clientY - e.touches[1].clientY;
                    pinchDist = Math.hypot(dx, dy);
                }
            }, { passive: false });
            app.view.addEventListener('touchmove', (e) => {
                if (e.touches.length === 2) {
                    e.preventDefault();
                    const dx = e.touches[0].clientX - e.touches[1].clientX;
                    const dy = e.touches[0].clientY - e.touches[1].clientY;
                    const dist = Math.hypot(dx, dy);
                    if (pinchDist > 0) {
                        const rect = app.view.getBoundingClientRect();
                        const cx = (e.touches[0].clientX + e.touches[1].clientX) / 2 - rect.left;
                        const cy = (e.touches[0].clientY + e.touches[1].clientY) / 2 - rect.top;
                        zoomAt(dist / pinchDist, cx, cy);
                    }
                    pinchDist = dist;
                }
            }, { passive: false });
            app.view.addEventListener('touchend', () => { pinchDist = 0; });

            document.getElementById('zoom-in').onclick = () => {
                zoomAt(1.3, width / 2, height / 2);
            };
            document.getElementById('zoom-out').onclick = () => {
                zoomAt(0.7, width / 2, height / 2);
            };

            document.getElementById('city-select').onchange = function () {
                const option = this.options[this.selectedIndex];
                const x = parseFloat(option.dataset.x);
                const y = parseFloat(option.dataset.y);
                if (!isNaN(x) && !isNaN(y)) {
                    const targetX = x * tileSize + tileSize / 2;
                    const targetY = y * tileSize + tileSize / 2;
                    worldContainer.x = width / 2 - targetX * worldContainer.scale.x;
                    worldContainer.y = height / 2 - targetY * worldContainer.scale.y;
                }
                this.selectedIndex = 0;
            };

            window.addEventListener('resize', () => {
                const newWidth = container.clientWidth;
                const newHeight = container.clientHeight;
                app.renderer.resize(newWidth, newHeight);
                app.stage.hitArea = app.screen;
            });
        });
    </script>
    @endpush
</x-app-layout>

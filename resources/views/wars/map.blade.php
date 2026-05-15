<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ $war->name }} — {{ __('Map') }}
            </h2>
            <span class="text-sm text-gray-500 dark:text-gray-400">{{ $cities->count() }} {{ $cities->count() !== 1 ? __('Cities') : __('City') }}@if(!$cities->isEmpty()) @endif</span>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden">
                <div id="map-area" class="relative w-full" style="height: 70vh;">
                    <div id="pixi-container" class="absolute inset-0"></div>
                    <div id="map-coords" class="absolute bottom-2 right-2 bg-black/60 text-white text-xs px-2 py-1 rounded select-none pointer-events-none z-10">
                        x: 0, y: 0
                    </div>
                    <div id="map-zoom" class="absolute top-2 right-2 flex flex-col gap-1 z-10">
                        <button id="zoom-in" class="bg-white dark:bg-gray-700 rounded-t px-3 py-1 text-lg font-bold hover:bg-gray-100 dark:hover:bg-gray-600 border border-gray-300 dark:border-gray-600 leading-none">+</button>
                        <button id="zoom-out" class="bg-white dark:bg-gray-700 rounded-b px-3 py-1 text-lg font-bold hover:bg-gray-100 dark:hover:bg-gray-600 border-x border-b border-gray-300 dark:border-gray-600 leading-none">&minus;</button>
                    </div>
                    <div id="map-legend" class="absolute bottom-2 left-2 bg-black/60 text-white text-xs px-2 py-1 rounded space-y-0.5 select-none pointer-events-none z-10">
                        <div class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-sm shrink-0" style="background:#90c96a"></span> {{ __('Plain') }}</div>
                        <div class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-sm shrink-0" style="background:#2d6a27"></span> {{ __('Forest') }}</div>
                        <div class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-sm shrink-0" style="background:#8a7f6e"></span> {{ __('Mountain') }}</div>
                        <div class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-sm shrink-0" style="background:#4a90d9"></span> {{ __('Water') }}</div>
                        <div class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-sm shrink-0" style="background:#e8d5a3"></span> {{ __('Desert') }}</div>
                        <div class="flex items-center gap-1.5 mt-1"><span class="w-2.5 h-2.5 rounded-full shrink-0" style="background:#f5a623"></span> {{ __('City') }}</div>
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
        document.addEventListener('DOMContentLoaded', async () => {
            const container = document.getElementById('pixi-container');
            const tilesUrl = "{{ route('api.wars.tiles', $war) }}";
            const citiesUrl = "{{ route('api.wars.cities', $war) }}";
            const cityUrls = @json($cities->mapWithKeys(fn($c) => [$c->id => route('cities.show', [$war, $c])]));
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

            try {
                const [tilesRes, citiesRes] = await Promise.all([
                    fetch(tilesUrl),
                    fetch(citiesUrl),
                ]);
                tiles = await tilesRes.json();
                cities = await citiesRes.json();
            } catch (e) {
                console.error('Failed to load map data:', e);
                container.innerHTML = '<p class="text-red-500 p-4">Failed to load map data.</p>';
                return;
            }

            const tileGraphics = new PIXI.Graphics();
            worldContainer.addChild(tileGraphics);

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

            const gridGraphics = new PIXI.Graphics();
            gridGraphics.lineStyle(0.5, 0x000000, 0.08);
            const maxX = tiles.length > 0 ? Math.max(...tiles.map(t => t.x)) * tileSize : 0;
            const maxY = tiles.length > 0 ? Math.max(...tiles.map(t => t.y)) * tileSize : 0;
            for (let x = 0; x <= maxX + tileSize; x += tileSize) {
                gridGraphics.moveTo(x, 0);
                gridGraphics.lineTo(x, maxY + tileSize);
            }
            for (let y = 0; y <= maxY + tileSize; y += tileSize) {
                gridGraphics.moveTo(0, y);
                gridGraphics.lineTo(maxX + tileSize, y);
            }
            worldContainer.addChild(gridGraphics);

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

                const cityContainer = new PIXI.Container();
                cityContainer.addChild(cityGfx, label);
                cityContainer.eventMode = 'static';
                cityContainer.cursor = 'pointer';
                cityContainer.on('pointerdown', () => {
                    if (cityUrls[city.id]) window.location.href = cityUrls[city.id];
                });

                worldContainer.addChild(cityContainer);
            }

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
            let dragStart = { x: 0, y: 0 };

            app.stage.eventMode = 'static';
            app.stage.hitArea = app.screen;

            app.stage.on('pointerdown', (e) => {
                isDragging = true;
                dragStart.x = e.global.x - worldContainer.x;
                dragStart.y = e.global.y - worldContainer.y;
            });

            app.stage.on('pointermove', (e) => {
                if (!isDragging) {
                    const worldX = (e.global.x - worldContainer.x) / worldContainer.scale.x;
                    const worldY = (e.global.y - worldContainer.y) / worldContainer.scale.y;
                    coordsEl.textContent = `x: ${Math.floor(worldX / tileSize)}, y: ${Math.floor(worldY / tileSize)}`;
                    return;
                }
                worldContainer.x = e.global.x - dragStart.x;
                worldContainer.y = e.global.y - dragStart.y;
            });

            app.stage.on('pointerup', () => { isDragging = false; });
            app.stage.on('pointerupoutside', () => { isDragging = false; });

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

            document.getElementById('zoom-in').onclick = () => {
                zoomAt(1.3, width / 2, height / 2);
            };
            document.getElementById('zoom-out').onclick = () => {
                zoomAt(0.7, width / 2, height / 2);
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

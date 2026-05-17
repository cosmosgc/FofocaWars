@php $c = $theme->config; @endphp
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit Theme') }} — {{ $theme->label }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('admin.themes.update', $theme) }}" enctype="multipart/form-data" class="space-y-6">
                        @csrf @method('PUT')

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="label" :value="__('Display Name')" />
                                <x-text-input id="label" name="label" type="text" class="mt-1 block w-full" value="{{ old('label', $theme->label) }}" required />
                                <x-input-error :messages="$errors->get('label')" class="mt-2" />
                            </div>
                            <div class="flex items-center gap-2 pt-6">
                                <input type="checkbox" id="is_default" name="is_default" value="1"
                                    {{ $theme->is_default ? 'checked' : '' }}
                                    class="rounded border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                <label for="is_default" class="text-sm text-gray-700 dark:text-gray-300">{{ __('Set as default theme') }}</label>
                            </div>
                        </div>

                        <div>
                            <x-input-label for="description" :value="__('Description')" />
                            <textarea id="description" name="description" rows="2" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('description', $theme->description) }}</textarea>
                        </div>

                        <hr class="border-gray-300 dark:border-gray-600">

                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3">{{ __('Colors') }}</h3>
                            <div class="grid grid-cols-3 gap-4">
                                <div>
                                    <x-input-label for="colors_primary" :value="__('Primary')" />
                                    <input id="colors_primary" name="colors_primary" type="color" value="{{ old('colors_primary', $c['colors']['primary']) }}" class="mt-1 block w-full h-10 rounded cursor-pointer border-gray-300 dark:border-gray-700">
                                </div>
                                <div>
                                    <x-input-label for="colors_secondary" :value="__('Secondary')" />
                                    <input id="colors_secondary" name="colors_secondary" type="color" value="{{ old('colors_secondary', $c['colors']['secondary']) }}" class="mt-1 block w-full h-10 rounded cursor-pointer border-gray-300 dark:border-gray-700">
                                </div>
                                <div>
                                    <x-input-label for="colors_accent" :value="__('Accent')" />
                                    <input id="colors_accent" name="colors_accent" type="color" value="{{ old('colors_accent', $c['colors']['accent']) }}" class="mt-1 block w-full h-10 rounded cursor-pointer border-gray-300 dark:border-gray-700">
                                </div>
                            </div>
                        </div>

                        <div>
                            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Terrain Colors') }}</h4>
                            <div class="grid grid-cols-5 gap-3">
                                @foreach(['plain','forest','mountain','water','desert'] as $terrain)
                                    <div>
                                        <x-input-label for="colors_terrain_{{ $terrain }}" :value="__(ucfirst($terrain))" />
                                        <input id="colors_terrain_{{ $terrain }}" name="colors_terrain_{{ $terrain }}" type="color"
                                               value="{{ old('colors_terrain_' . $terrain, $c['colors']['terrain'][$terrain] ?? '#ccc') }}"
                                               class="mt-1 block w-full h-10 rounded cursor-pointer border-gray-300 dark:border-gray-700">
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="colors_city_fill" :value="__('City Fill')" />
                                <input id="colors_city_fill" name="colors_city_fill" type="color" value="{{ old('colors_city_fill', $c['colors']['city']['fill'] ?? '#f5a623') }}" class="mt-1 block w-full h-10 rounded cursor-pointer border-gray-300 dark:border-gray-700">
                            </div>
                            <div>
                                <x-input-label for="colors_city_stroke" :value="__('City Stroke')" />
                                <input id="colors_city_stroke" name="colors_city_stroke" type="color" value="{{ old('colors_city_stroke', $c['colors']['city']['stroke'] ?? '#ffd700') }}" class="mt-1 block w-full h-10 rounded cursor-pointer border-gray-300 dark:border-gray-700">
                            </div>
                        </div>

                        <hr class="border-gray-300 dark:border-gray-600">

                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">{{ __('Battle Effect') }}</h3>
                            <select name="battle_effect" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                @foreach(['explosion','sandstorm','snow','cyber'] as $effect)
                                    <option value="{{ $effect }}" {{ old('battle_effect', $c['battle_effect'] ?? 'explosion') === $effect ? 'selected' : '' }}>{{ __(ucfirst($effect)) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <hr class="border-gray-300 dark:border-gray-600">

                        @php
                        $spriteFields = ['terrain_plain' => 'Plain', 'terrain_forest' => 'Forest', 'terrain_mountain' => 'Mountain', 'terrain_water' => 'Water', 'terrain_desert' => 'Desert', 'city' => 'City'];
                        @endphp
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3">{{ __('Sprites') }}</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">{{ __('Leave empty to keep existing, or upload to replace. Set crop size to slice a sprite sheet into multiple tiles for random variety on the map.') }}</p>

                            <div class="grid grid-cols-2 gap-6">
                                @foreach($spriteFields as $key => $label)
                                    @php
                                        $configKey = $key === 'city' ? 'sprites/city' : 'sprites/terrain/' . str_replace('terrain_', '', $key);
                                        $current = $c[$configKey] ?? null;
                                        $currentUrl = is_string($current) ? $current : ($current['url'] ?? null);
                                        $currentTileW = is_array($current) ? ($current['tile_w'] ?? 32) : 32;
                                        $currentTileH = is_array($current) ? ($current['tile_h'] ?? 32) : 32;
                                    @endphp
                                    <div x-data="spriteCrop({{ $currentTileW }}, {{ $currentTileH }})">
                                        <div class="flex items-start gap-3">
                                            <div class="grow">
                                                <x-input-label for="sprite_{{ $key }}" :value="__($label . ' Sprite')" />
                                                <input id="sprite_{{ $key }}" name="sprite_{{ $key }}" type="file" accept="image/png,image/gif,image/jpeg,image/webp"
                                                       @change="loadFile($event)"
                                                       class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                                                <div class="flex gap-3 mt-1.5">
                                                    <div class="flex items-center gap-1">
                                                        <label class="text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap">{{ __('Crop W') }}</label>
                                                        <input type="number" name="crop_w_{{ $key }}" x-model="cropW" @input="extract()"
                                                               min="8" max="256" class="w-16 text-xs border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded shadow-sm">
                                                    </div>
                                                    <div class="flex items-center gap-1">
                                                        <label class="text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap">{{ __('Crop H') }}</label>
                                                        <input type="number" name="crop_h_{{ $key }}" x-model="cropH" @input="extract()"
                                                               min="8" max="256" class="w-16 text-xs border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded shadow-sm">
                                                    </div>
                                                    <div x-show="img" class="text-xs text-gray-400 self-center">
                                                        <span x-text="imgWidth + 'x' + imgHeight + 'px'"></span>
                                                        <span x-show="previews.length > 1" x-text="' → ' + previews.length + ' tiles'" class="text-indigo-500"></span>
                                                    </div>
                                                </div>
                                                <div x-show="previews.length" class="flex flex-wrap gap-1 mt-2 p-2 bg-gray-50 dark:bg-gray-900/50 rounded border border-gray-200 dark:border-gray-700">
                                                    <template x-for="(dataUrl, i) in previews" :key="i">
                                                        <img :src="dataUrl" alt=""
                                                             class="block border border-gray-300 dark:border-gray-600 rounded"
                                                             :style="'width:' + Math.min(64, cropW) + 'px;height:' + Math.min(64, cropH) + 'px'">
                                                    </template>
                                                </div>
                                            </div>
                                            @if($currentUrl)
                                                <div class="shrink-0">
                                                    <img src="{{ $currentUrl }}" alt="{{ $label }}"
                                                         class="w-10 h-10 rounded object-cover border border-gray-300 dark:border-gray-600 mt-6">
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        @push('scripts')
                        <script>
                            function spriteCrop(initialW, initialH) {
                                return {
                                    cropW: initialW || 32,
                                    cropH: initialH || 32,
                                    previews: [],
                                    img: null,
                                    imgWidth: 0,
                                    imgHeight: 0,

                                    loadFile(e) {
                                        const file = e.target.files[0];
                                        if (!file) { this.img = null; this.previews = []; return; }
                                        const reader = new FileReader();
                                        reader.onload = (ev) => {
                                            const img = new Image();
                                            img.onload = () => {
                                                this.img = img;
                                                this.imgWidth = img.width;
                                                this.imgHeight = img.height;
                                                this.extract();
                                            };
                                            img.src = ev.target.result;
                                        };
                                        reader.readAsDataURL(file);
                                    },

                                    extract() {
                                        if (!this.img) return;
                                        const tw = parseInt(this.cropW) || 32;
                                        const th = parseInt(this.cropH) || 32;
                                        if (tw < 8 || th < 8) return;
                                        const cols = Math.floor(this.img.width / tw);
                                        const rows = Math.floor(this.img.height / th);
                                        if (cols < 1 || rows < 1) return;
                                        const canvas = document.createElement('canvas');
                                        canvas.width = tw;
                                        canvas.height = th;
                                        const ctx = canvas.getContext('2d');
                                        this.previews = [];
                                        for (let r = 0; r < rows; r++) {
                                            for (let c = 0; c < cols; c++) {
                                                ctx.clearRect(0, 0, tw, th);
                                                ctx.drawImage(this.img, c * tw, r * th, tw, th, 0, 0, tw, th);
                                                this.previews.push(canvas.toDataURL());
                                            }
                                        }
                                    },
                                };
                            }
                        </script>
                        @endpush

                        <div class="flex items-center gap-4">
                            <x-primary-button>{{ __('Update Theme') }}</x-primary-button>
                            <a href="{{ route('admin.themes.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline">{{ __('Cancel') }}</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

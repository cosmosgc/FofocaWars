<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Create Theme') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('admin.themes.store') }}" enctype="multipart/form-data" class="space-y-6">
                        @csrf

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="name" :value="__('Theme Key')" />
                                <x-text-input id="name" name="name" type="text" class="mt-1 block w-full font-mono text-sm" required placeholder="my_theme" />
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('Letters, numbers, underscores only. Used as the internal key.') }}</p>
                                <x-input-error :messages="$errors->get('name')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="label" :value="__('Display Name')" />
                                <x-text-input id="label" name="label" type="text" class="mt-1 block w-full" required />
                                <x-input-error :messages="$errors->get('label')" class="mt-2" />
                            </div>
                        </div>

                        <div>
                            <x-input-label for="description" :value="__('Description')" />
                            <textarea id="description" name="description" rows="2" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"></textarea>
                            <x-input-error :messages="$errors->get('description')" class="mt-2" />
                        </div>

                        <div class="flex items-center gap-2">
                            <input type="checkbox" id="is_default" name="is_default" value="1"
                                   class="rounded border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500">
                            <label for="is_default" class="text-sm text-gray-700 dark:text-gray-300">{{ __('Set as default theme') }}</label>
                        </div>

                        <hr class="border-gray-300 dark:border-gray-600">

                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3">{{ __('Colors') }}</h3>
                            <div class="grid grid-cols-3 gap-4">
                                <div>
                                    <x-input-label for="colors_primary" :value="__('Primary')" />
                                    <input id="colors_primary" name="colors_primary" type="color" value="#4f46e5" class="mt-1 block w-full h-10 rounded cursor-pointer border-gray-300 dark:border-gray-700">
                                    <x-input-error :messages="$errors->get('colors_primary')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="colors_secondary" :value="__('Secondary')" />
                                    <input id="colors_secondary" name="colors_secondary" type="color" value="#059669" class="mt-1 block w-full h-10 rounded cursor-pointer border-gray-300 dark:border-gray-700">
                                </div>
                                <div>
                                    <x-input-label for="colors_accent" :value="__('Accent')" />
                                    <input id="colors_accent" name="colors_accent" type="color" value="#d97706" class="mt-1 block w-full h-10 rounded cursor-pointer border-gray-300 dark:border-gray-700">
                                </div>
                            </div>
                        </div>

                        <div>
                            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Terrain Colors') }}</h4>
                            <div class="grid grid-cols-5 gap-3">
                                @foreach(['plain' => '#90c96a', 'forest' => '#2d6a27', 'mountain' => '#8a7f6e', 'water' => '#4a90d9', 'desert' => '#e8d5a3'] as $terrain => $default)
                                    <div>
                                        <x-input-label for="colors_terrain_{{ $terrain }}" :value="__(ucfirst($terrain))" />
                                        <input id="colors_terrain_{{ $terrain }}" name="colors_terrain_{{ $terrain }}" type="color" value="{{ $default }}" class="mt-1 block w-full h-10 rounded cursor-pointer border-gray-300 dark:border-gray-700">
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="colors_city_fill" :value="__('City Fill')" />
                                <input id="colors_city_fill" name="colors_city_fill" type="color" value="#f5a623" class="mt-1 block w-full h-10 rounded cursor-pointer border-gray-300 dark:border-gray-700">
                            </div>
                            <div>
                                <x-input-label for="colors_city_stroke" :value="__('City Stroke')" />
                                <input id="colors_city_stroke" name="colors_city_stroke" type="color" value="#ffd700" class="mt-1 block w-full h-10 rounded cursor-pointer border-gray-300 dark:border-gray-700">
                            </div>
                        </div>

                        <hr class="border-gray-300 dark:border-gray-600">

                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">{{ __('Battle Effect') }}</h3>
                            <select name="battle_effect" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="explosion">{{ __('Explosion') }}</option>
                                <option value="sandstorm">{{ __('Sandstorm') }}</option>
                                <option value="snow">{{ __('Snow') }}</option>
                                <option value="cyber">{{ __('Cyber') }}</option>
                            </select>
                        </div>

                        <hr class="border-gray-300 dark:border-gray-600">

                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3">{{ __('Sprites (optional)') }}</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">{{ __('Upload sprite images per terrain type. Set crop size to slice a sprite sheet into multiple tiles for random variety on the map. PNG, GIF, JPG, WebP. Max 1MB each.') }}</p>

                            @php $spriteFields = ['terrain_plain' => 'Plain', 'terrain_forest' => 'Forest', 'terrain_mountain' => 'Mountain', 'terrain_water' => 'Water', 'terrain_desert' => 'Desert', 'city' => 'City']; @endphp
                            <div class="grid grid-cols-2 gap-6">
                                @foreach($spriteFields as $key => $label)
                                    <div x-data="spriteCrop()">
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
                                @endforeach
                            </div>
                        </div>

                        @push('scripts')
                        <script>
                            function spriteCrop() {
                                return {
                                    cropW: 32,
                                    cropH: 32,
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
                            <x-primary-button>{{ __('Create Theme') }}</x-primary-button>
                            <a href="{{ route('admin.themes.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline">{{ __('Cancel') }}</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

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
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">{{ __('Upload PNG, GIF, JPG or WebP images to replace solid colors on the map. Max 1MB each.') }}</p>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <x-input-label for="sprite_terrain_plain" :value="__('Plain Sprite')" />
                                    <input id="sprite_terrain_plain" name="sprite_terrain_plain" type="file" accept="image/png,image/gif,image/jpeg,image/webp" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                                </div>
                                <div>
                                    <x-input-label for="sprite_terrain_forest" :value="__('Forest Sprite')" />
                                    <input id="sprite_terrain_forest" name="sprite_terrain_forest" type="file" accept="image/png,image/gif,image/jpeg,image/webp" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                                </div>
                                <div>
                                    <x-input-label for="sprite_terrain_mountain" :value="__('Mountain Sprite')" />
                                    <input id="sprite_terrain_mountain" name="sprite_terrain_mountain" type="file" accept="image/png,image/gif,image/jpeg,image/webp" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                                </div>
                                <div>
                                    <x-input-label for="sprite_terrain_water" :value="__('Water Sprite')" />
                                    <input id="sprite_terrain_water" name="sprite_terrain_water" type="file" accept="image/png,image/gif,image/jpeg,image/webp" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                                </div>
                                <div>
                                    <x-input-label for="sprite_terrain_desert" :value="__('Desert Sprite')" />
                                    <input id="sprite_terrain_desert" name="sprite_terrain_desert" type="file" accept="image/png,image/gif,image/jpeg,image/webp" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                                </div>
                                <div>
                                    <x-input-label for="sprite_city" :value="__('City Sprite')" />
                                    <input id="sprite_city" name="sprite_city" type="file" accept="image/png,image/gif,image/jpeg,image/webp" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                                </div>
                            </div>
                        </div>

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

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

                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3">{{ __('Sprites') }}</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">{{ __('Leave empty to keep existing. Upload PNG, GIF, JPG or WebP. Max 1MB.') }}</p>
                            <div class="space-y-4">
                                @php $spriteFields = ['terrain_plain' => 'Plain', 'terrain_forest' => 'Forest', 'terrain_mountain' => 'Mountain', 'terrain_water' => 'Water', 'terrain_desert' => 'Desert', 'city' => 'City']; @endphp
                                @foreach($spriteFields as $key => $label)
                                    @php
                                        $configKey = $key === 'city' ? 'sprites/city' : 'sprites/terrain/' . str_replace('terrain_', '', $key);
                                        $current = $c[$configKey] ?? null;
                                    @endphp
                                    <div class="flex items-center gap-4">
                                        <div class="grow">
                                            <x-input-label for="sprite_{{ $key }}" :value="__($label . ' Sprite')" />
                                            <input id="sprite_{{ $key }}" name="sprite_{{ $key }}" type="file" accept="image/png,image/gif,image/jpeg,image/webp"
                                                   class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                                        </div>
                                        @if($current)
                                            <div class="shrink-0 mt-5">
                                                @if(preg_match('/\.gif$/i', $current))
                                                    <img src="{{ $current }}" alt="{{ $label }}" class="w-10 h-10 rounded object-cover border border-gray-300 dark:border-gray-600">
                                                @else
                                                    <img src="{{ $current }}" alt="{{ $label }}" class="w-10 h-10 rounded object-cover border border-gray-300 dark:border-gray-600">
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>

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

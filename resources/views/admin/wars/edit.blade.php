<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.wars.index') }}" class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">
                &larr; {{ __('Back') }}
            </a>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Edit War') }} — {{ $war->name }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('admin.wars.update', $war) }}" class="space-y-6">
                        @csrf @method('PUT')

                        <div>
                            <x-input-label for="name" :value="__('War Name')" />
                            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" value="{{ old('name', $war->name) }}" required />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="theme" :value="__('Theme')" />
                                <select id="theme" name="theme" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                    @foreach(['medieval', 'modern', 'space'] as $t)
                                        <option value="{{ $t }}" {{ old('theme', $war->theme) === $t ? 'selected' : '' }}>{{ __(ucfirst($t)) }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('theme')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="status" :value="__('Status')" />
                                <select id="status" name="status" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                    @foreach(['setup', 'running', 'ended'] as $s)
                                        <option value="{{ $s }}" {{ old('status', $war->status) === $s ? 'selected' : '' }}>{{ __(ucfirst($s)) }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('status')" class="mt-2" />
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="map_width" :value="__('Map Width')" />
                                <x-text-input id="map_width" name="map_width" type="number" class="mt-1 block w-full" value="{{ old('map_width', $war->map_width) }}" min="10" max="500" required />
                                <x-input-error :messages="$errors->get('map_width')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="map_height" :value="__('Map Height')" />
                                <x-text-input id="map_height" name="map_height" type="number" class="mt-1 block w-full" value="{{ old('map_height', $war->map_height) }}" min="10" max="500" required />
                                <x-input-error :messages="$errors->get('map_height')" class="mt-2" />
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="resource_multiplier" :value="__('Resource Multiplier')" />
                                <x-text-input id="resource_multiplier" name="resource_multiplier" type="number" step="0.1" class="mt-1 block w-full" value="{{ old('resource_multiplier', $war->resource_multiplier) }}" min="0.1" max="10" required />
                                <x-input-error :messages="$errors->get('resource_multiplier')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="construction_speed" :value="__('Construction Speed')" />
                                <x-text-input id="construction_speed" name="construction_speed" type="number" step="0.1" class="mt-1 block w-full" value="{{ old('construction_speed', $war->construction_speed) }}" min="0.1" max="10" required />
                                <x-input-error :messages="$errors->get('construction_speed')" class="mt-2" />
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="troop_speed_multiplier" :value="__('Troop Speed')" />
                                <x-text-input id="troop_speed_multiplier" name="troop_speed_multiplier" type="number" step="0.1" class="mt-1 block w-full" value="{{ old('troop_speed_multiplier', $war->troop_speed_multiplier) }}" min="0.1" max="10" required />
                                <x-input-error :messages="$errors->get('troop_speed_multiplier')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="max_bases_per_player" :value="__('Max Bases')" />
                                <x-text-input id="max_bases_per_player" name="max_bases_per_player" type="number" class="mt-1 block w-full" value="{{ old('max_bases_per_player', $war->max_bases_per_player) }}" min="1" max="50" required />
                                <x-input-error :messages="$errors->get('max_bases_per_player')" class="mt-2" />
                            </div>
                        </div>

                        <div class="flex items-center gap-4">
                            <x-primary-button>{{ __('Save') }}</x-primary-button>
                            <a href="{{ route('admin.wars.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline">{{ __('Cancel') }}</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

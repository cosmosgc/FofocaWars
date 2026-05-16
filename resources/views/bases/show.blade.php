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
</x-app-layout>

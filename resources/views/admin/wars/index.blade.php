<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Admin') }} — {{ __('Wars') }}
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('admin.themes.index') }}"
                   class="inline-flex items-center px-4 py-2 bg-purple-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-500">
                    {{ __('Themes') }}
                </a>
                <a href="{{ route('admin.wars.create') }}"
                   class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-500">
                    + {{ __('Create War') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="bg-green-100 dark:bg-green-900 border border-green-400 dark:border-green-700 text-green-700 dark:text-green-300 px-4 py-3 rounded mb-6">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
                                <th class="text-left px-4 py-3 font-medium text-gray-600 dark:text-gray-400">ID</th>
                                <th class="text-left px-4 py-3 font-medium text-gray-600 dark:text-gray-400">{{ __('Name') }}</th>
                                <th class="text-left px-4 py-3 font-medium text-gray-600 dark:text-gray-400">{{ __('Theme') }}</th>
                                <th class="text-left px-4 py-3 font-medium text-gray-600 dark:text-gray-400">{{ __('Status') }}</th>
                                <th class="text-center px-4 py-3 font-medium text-gray-600 dark:text-gray-400">{{ __('Players') }}</th>
                                <th class="text-left px-4 py-3 font-medium text-gray-600 dark:text-gray-400">{{ __('Map') }}</th>
                                <th class="text-right px-4 py-3 font-medium text-gray-600 dark:text-gray-400">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($wars as $war)
                                <tr class="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    <td class="px-4 py-3 text-gray-500 dark:text-gray-400">#{{ $war->id }}</td>
                                    <td class="px-4 py-3 font-medium text-gray-900 dark:text-gray-100">{{ $war->name }}</td>
                                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ __(ucfirst($war->themeData?->label ?? $war->theme)) }}</td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                            @if($war->status === 'running') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300
                                            @elseif($war->status === 'setup') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300
                                            @else bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300
                                            @endif">
                                            {{ __($war->status) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center text-gray-600 dark:text-gray-400">{{ $war->war_players_count }}</td>
                                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $war->map_width }}x{{ $war->map_height }}</td>
                                    <td class="px-4 py-3 text-right space-x-2">
                                        <a href="{{ route('admin.wars.edit', $war) }}"
                                           class="inline-flex items-center px-3 py-1.5 text-xs bg-indigo-600 text-white rounded-md hover:bg-indigo-500">
                                            {{ __('Edit') }}
                                        </a>
                                        @if($war->status === 'setup')
                                            <form action="{{ route('wars.start', $war) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit"
                                                   class="inline-flex items-center px-3 py-1.5 text-xs bg-green-600 text-white rounded-md hover:bg-green-500">
                                                    {{ __('Start') }}
                                                </button>
                                            </form>
                                        @endif
                                        <form action="{{ route('admin.wars.destroy', $war) }}" method="POST" class="inline"
                                              onsubmit="return confirm('Delete this war and all its data?')">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                               class="inline-flex items-center px-3 py-1.5 text-xs bg-red-600 text-white rounded-md hover:bg-red-500">
                                                {{ __('Delete') }}
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                            @if($wars->isEmpty())
                                <tr>
                                    <td colspan="7" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                        {{ __('No wars created yet.') }}
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

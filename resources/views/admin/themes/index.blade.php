<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Admin') }} — {{ __('Themes') }}
            </h2>
            <a href="{{ route('admin.themes.create') }}"
               class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-500">
                + {{ __('Create Theme') }}
            </a>
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
                                <th class="text-left px-4 py-3 font-medium text-gray-600 dark:text-gray-400">{{ __('Name') }}</th>
                                <th class="text-left px-4 py-3 font-medium text-gray-600 dark:text-gray-400">{{ __('Label') }}</th>
                                <th class="text-center px-4 py-3 font-medium text-gray-600 dark:text-gray-400">{{ __('Default') }}</th>
                                <th class="text-center px-4 py-3 font-medium text-gray-600 dark:text-gray-400">{{ __('Wars') }}</th>
                                <th class="text-left px-4 py-3 font-medium text-gray-600 dark:text-gray-400">{{ __('Battle Effect') }}</th>
                                <th class="text-left px-4 py-3 font-medium text-gray-600 dark:text-gray-400">{{ __('Preview') }}</th>
                                <th class="text-right px-4 py-3 font-medium text-gray-600 dark:text-gray-400">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($themes as $theme)
                                @php $c = $theme->config; @endphp
                                <tr class="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    <td class="px-4 py-3 font-mono text-xs text-gray-600 dark:text-gray-400">{{ $theme->name }}</td>
                                    <td class="px-4 py-3 font-medium text-gray-900 dark:text-gray-100">{{ $theme->label }}</td>
                                    <td class="px-4 py-3 text-center">
                                        @if($theme->is_default)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">{{ __('Yes') }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center text-gray-600 dark:text-gray-400">{{ $theme->wars_count }}</td>
                                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $c['battle_effect'] ?? '-' }}</td>
                                    <td class="px-4 py-3">
                                        <div class="flex gap-1">
                                            @foreach(['plain','forest','mountain','water','desert'] as $t)
                                                <span class="w-4 h-4 rounded inline-block" title="{{ ucfirst($t) }}"
                                                      style="background: {{ $c['colors']['terrain'][$t] ?? '#ccc' }}"></span>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-right space-x-2">
                                        <a href="{{ route('admin.themes.edit', $theme) }}"
                                           class="inline-flex items-center px-3 py-1.5 text-xs bg-indigo-600 text-white rounded-md hover:bg-indigo-500">
                                            {{ __('Edit') }}
                                        </a>
                                        <form action="{{ route('admin.themes.destroy', $theme) }}" method="POST" class="inline"
                                              onsubmit="return confirm('Delete this theme? Wars using it will fall back to the default theme.')">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                               class="inline-flex items-center px-3 py-1.5 text-xs bg-red-600 text-white rounded-md hover:bg-red-500">
                                                {{ __('Delete') }}
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                            @if($themes->isEmpty())
                                <tr>
                                    <td colspan="7" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                        {{ __('No themes created yet.') }}
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

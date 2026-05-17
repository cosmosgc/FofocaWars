<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Wars') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @auth
                @if(auth()->user()->isAdmin())
                    <div class="mb-6">
                        <a href="{{ route('admin.wars.create') }}"
                           class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-500 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            {{ __('+ Create War') }}
                        </a>
                    </div>
                @endif
            @endauth
            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                @foreach($wars as $war)
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                {{ $war->name }}
                            </h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                {{ __('Theme') }}: {{ __(ucfirst($war->themeData?->label ?? $war->theme)) }}
                            </p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                {{ __('Status') }}: <span class="capitalize">{{ __($war->status) }}</span>
                            </p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                {{ __('Map') }}: {{ $war->map_width }}x{{ $war->map_height }}
                            </p>
                            <div class="mt-4 flex gap-2">
                                <a href="{{ route('wars.show', $war) }}"
                                   class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none transition ease-in-out duration-150">
                                    {{ __('View') }}
                                </a>
                                @if($war->status === 'setup')
                                    <form action="{{ route('wars.join', $war) }}" method="POST">
                                        @csrf
                                        <button type="submit"
                                           class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                            {{ __('Join') }}
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>

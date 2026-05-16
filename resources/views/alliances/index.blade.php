<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ $war->name }} — {{ __('Alliances') }}
            </h2>
            @if(!$myAlliance)
                <button onclick="document.getElementById('create-form').classList.toggle('hidden')"
                   class="px-4 py-2 bg-indigo-600 text-white text-xs font-semibold rounded-md hover:bg-indigo-500">
                    {{ __('Create Alliance') }}
                </button>
            @endif
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if(session('success'))
                <div class="bg-green-100 dark:bg-green-900 border border-green-400 dark:border-green-700 text-green-700 dark:text-green-300 px-4 py-3 rounded">{{ session('success') }}</div>
            @endif

            <div id="create-form" class="hidden bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">{{ __('New Alliance') }}</h3>
                <form action="{{ route('alliances.store', $war) }}" method="POST" class="space-y-4 max-w-md">
                    @csrf
                    <div>
                        <x-input-label for="name" :value="__('Name')" />
                        <x-text-input id="name" name="name" class="mt-1 block w-full" required maxlength="50" />
                    </div>
                    <div>
                        <x-input-label for="tag" :value="__('Tag')" />
                        <x-text-input id="tag" name="tag" class="mt-1 block w-full" required maxlength="10" />
                    </div>
                    <div>
                        <x-input-label for="description" :value="__('Description')" />
                        <textarea id="description" name="description" rows="3" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm text-sm"></textarea>
                    </div>
                    <x-primary-button>{{ __('Create') }}</x-primary-button>
                </form>
            </div>

            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                @foreach($alliances as $a)
                    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden">
                        <div class="p-6">
                            <div class="flex items-start justify-between">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                        <a href="{{ route('alliances.show', [$war, $a]) }}" class="hover:underline">{{ $a->name }}</a>
                                    </h3>
                                    <span class="text-xs bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-300 px-2 py-0.5 rounded font-mono">[{{ $a->tag }}]</span>
                                </div>
                                <span class="text-sm text-gray-500 dark:text-gray-400">{{ $a->members_count }} {{ __('members') }}</span>
                            </div>
                            @if($a->description)
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">{{ $a->description }}</p>
                            @endif
                            <div class="mt-3 flex items-center gap-2">
                                <a href="{{ route('alliances.show', [$war, $a]) }}"
                                   class="text-xs px-3 py-1.5 bg-gray-600 text-white rounded hover:bg-gray-500">{{ __('View') }}</a>
                                @if(!$myAlliance)
                                    <form action="{{ route('alliances.join', [$war, $a]) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="text-xs px-3 py-1.5 bg-blue-600 text-white rounded hover:bg-blue-500">{{ __('Join') }}</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
                @if($alliances->isEmpty())
                    <div class="col-span-full text-center text-gray-500 dark:text-gray-400 py-8">{{ __('No alliances yet.') }}</div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>

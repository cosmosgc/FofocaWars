<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ $war->name }} — {{ __('Messages') }}
            </h2>
            <button onclick="document.getElementById('new-msg').classList.toggle('hidden')"
               class="px-4 py-2 bg-indigo-600 text-white text-xs font-semibold rounded-md hover:bg-indigo-500">
                {{ __('New Message') }}
            </button>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div id="new-msg" class="hidden bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">{{ __('Send Message') }}</h3>
                <form action="{{ route('messages.new', $war) }}" method="POST" class="space-y-4 max-w-md">
                    @csrf
                    <div>
                        <x-input-label for="recipient_id" :value="__('To')" />
                        <select name="recipient_id" id="recipient_id" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm text-sm">
                            @foreach($players as $p)
                                <option value="{{ $p->id }}">{{ $p->warUser->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="content" :value="__('Message')" />
                        <textarea name="content" id="content" rows="4" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm text-sm" required></textarea>
                    </div>
                    <x-primary-button>{{ __('Send') }}</x-primary-button>
                </form>
            </div>

            <div class="space-y-4">
                @foreach($conversations as $conv)
                    <a href="{{ route('messages.show', [$war, $conv]) }}" class="block bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ $conv->title ?? implode(', ', $conv->participants->filter(fn($p) => $p->war_player_id !== $player->id)->map(fn($p) => $p->warPlayer->warUser->name)->toArray()) }}
                                </div>
                                @if($conv->messages->isNotEmpty())
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1 truncate">{{ $conv->messages->last()->content }}</div>
                                @endif
                            </div>
                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ $conv->updated_at->diffForHumans() }}</span>
                        </div>
                    </a>
                @endforeach
                @if($conversations->isEmpty())
                    <div class="text-center text-gray-500 dark:text-gray-400 py-8">{{ __('No conversations yet.') }}</div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>

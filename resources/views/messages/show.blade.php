<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('messages.index', $war) }}" class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">&larr; {{ __('Messages') }}</a>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ $conversation->title ?? __('Conversation') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <div class="space-y-4 mb-6 max-h-96 overflow-y-auto">
                    @foreach($messages as $msg)
                        <div class="flex {{ $msg->sender_id === $player->id ? 'justify-end' : 'justify-start' }}">
                            <div class="max-w-md {{ $msg->sender_id === $player->id ? 'bg-indigo-50 dark:bg-indigo-900/30' : 'bg-gray-50 dark:bg-gray-700' }} rounded-lg px-4 py-2">
                                <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">{{ $msg->sender->warUser->name }}</div>
                                <div class="text-sm text-gray-900 dark:text-gray-100">{{ $msg->content }}</div>
                                <div class="text-xs text-gray-400 dark:text-gray-500 mt-1">{{ $msg->created_at->format('H:i') }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <form action="{{ route('messages.send', $war) }}" method="POST" class="flex gap-2">
                    @csrf
                    <input type="hidden" name="conversation_id" value="{{ $conversation->id }}">
                    <textarea name="content" rows="2" class="flex-1 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm text-sm" placeholder="{{ __('Type a message...') }}" required></textarea>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-md hover:bg-indigo-500 self-end">{{ __('Send') }}</button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('alliances.index', $war) }}" class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">&larr; {{ __('Alliances') }}</a>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ $alliance->name }} <span class="text-sm font-mono text-indigo-500">[{{ $alliance->tag }}]</span>
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if(session('success'))
                <div class="bg-green-100 dark:bg-green-900 border border-green-400 dark:border-green-700 text-green-700 dark:text-green-300 px-4 py-3 rounded">{{ session('success') }}</div>
            @endif

            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('Members') }} ({{ $members->count() }})</h3>
                    @if($myMembership && !in_array($myMembership->role, ['leader']))
                        <form action="{{ route('alliances.leave', [$war, $alliance]) }}" method="POST">
                            @csrf
                            <button type="submit" class="text-xs px-3 py-1.5 bg-red-600 text-white rounded hover:bg-red-500">{{ __('Leave') }}</button>
                        </form>
                    @endif
                </div>
                <div class="space-y-2">
                    @foreach($members as $m)
                        <div class="flex items-center justify-between py-2 border-b dark:border-gray-700 last:border-0">
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $m->warPlayer->warUser->name }}</span>
                                <span class="text-xs px-2 py-0.5 rounded
                                    @if($m->role === 'leader') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300
                                    @elseif($m->role === 'officer') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300
                                    @else bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400 @endif">
                                    {{ ucfirst($m->role) }}
                                </span>
                            </div>
                            @if($myMembership && $myMembership->role === 'leader' && $m->role !== 'leader')
                                <div class="flex gap-1">
                                    @if($m->role !== 'officer')
                                        <form action="{{ route('alliances.promote', [$war, $alliance, $m]) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="text-xs px-2 py-1 bg-blue-600 text-white rounded hover:bg-blue-500">Promote</button>
                                        </form>
                                    @endif
                                    <form action="{{ route('alliances.kick', [$war, $alliance, $m]) }}" method="POST" onsubmit="return confirm('Kick this member?')">
                                        @csrf
                                        <button type="submit" class="text-xs px-2 py-1 bg-red-600 text-white rounded hover:bg-red-500">Kick</button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            @if($myMembership && in_array($myMembership->role, ['leader', 'officer']))
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">{{ __('Diplomacy') }}</h3>
                    <form action="{{ route('diplomacy.set', [$war, $alliance]) }}" method="POST" class="flex gap-4 items-end">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('With') }}</label>
                            <select name="target_id" class="mt-1 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm text-sm">
                                @foreach($war->alliances()->where('id', '!=', $alliance->id)->get() as $other)
                                    <option value="{{ $other->id }}">{{ $other->name }} [{{ $other->tag }}]</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Relation') }}</label>
                            <select name="type" class="mt-1 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm text-sm">
                                <option value="neutral">Neutral</option>
                                <option value="allied">Allied</option>
                                <option value="war">War</option>
                                <option value="trade_pact">Trade Pact</option>
                                <option value="non_aggression">Non-Aggression Pact</option>
                            </select>
                        </div>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-md hover:bg-indigo-500">Set</button>
                    </form>

                    @if($relations->isNotEmpty())
                        <div class="mt-4 space-y-2">
                            @foreach($relations as $r)
                                @php $other = $r->alliance_id_1 === $alliance->id ? $r->alliance2 : $r->alliance1; @endphp
                                <div class="flex items-center gap-3 text-sm py-1">
                                    <span class="text-gray-900 dark:text-gray-100 font-medium">{{ $other->name }}</span>
                                    <span class="text-xs px-2 py-0.5 rounded
                                        @if($r->type === 'allied') bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300
                                        @elseif($r->type === 'war') bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300
                                        @else bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400 @endif">
                                        {{ ucfirst(str_replace('_', ' ', $r->type)) }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</x-app-layout>

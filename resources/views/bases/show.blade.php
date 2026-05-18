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
            @if(session('success'))
                <div class="bg-green-100 dark:bg-green-900 border border-green-400 dark:border-green-700 text-green-700 dark:text-green-300 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

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

                    <hr class="my-4 border-gray-300 dark:border-gray-600">

                    <form method="POST" action="{{ route('bases.rename', [$war, $base]) }}" class="flex items-end gap-4">
                        @csrf
                        <div class="grow">
                            <x-input-label for="rename" :value="__('Rename Base')" />
                            <x-text-input id="rename" name="name" type="text" class="mt-1 block w-full" value="{{ $base->name }}" required maxlength="100" />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>
                        <x-primary-button>{{ __('Rename') }}</x-primary-button>
                    </form>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg"
                 x-data="basePreview('{{ route('api.wars.bases.buildings', [$war, $base]) }}', '{{ $base->name }}')"
                 x-init="init()">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('Base Preview') }}</h3>
                    </div>

                    <template x-if="loading">
                        <p class="text-sm text-gray-400">{{ __('Loading...') }}</p>
                    </template>

                    <div class="relative w-full aspect-video bg-gray-900 rounded-lg overflow-hidden border border-gray-700"
                         x-show="!loading">
                        <div class="absolute inset-0 flex items-center justify-center" x-html="svg"></div>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg"
                 x-data="baseBuildings('{{ route('api.wars.bases.buildings', [$war, $base]) }}', '{{ route('api.wars.bases.build', [$war, $base]) }}')"
                 x-init="init()">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">{{ __('Constructions') }}</h3>

                    <template x-if="loading">
                        <p class="text-sm text-gray-400">{{ __('Loading...') }}</p>
                    </template>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4" x-show="!loading">
                        <template x-for="b in buildings" :key="b.type">
                            <div class="border dark:border-gray-700 rounded-lg p-4">
                                <div class="flex items-center gap-3">
                                    <div class="text-2xl" x-text="b.icon"></div>
                                    <div class="grow">
                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100" x-text="b.name"></div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400" x-text="b.description"></div>
                                        <div class="mt-1">
                                            <span class="inline-block px-2 py-0.5 rounded-full text-xs font-bold"
                                                  :class="b.level === 0 ? 'bg-gray-600 text-gray-300' : 'bg-emerald-600 text-white'">
                                                <span x-text="b.level > 0 ? 'Lv ' + b.level + '/' + b.max_level : '{{ __('Not Built') }}'"></span>
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <template x-if="b.is_under_construction">
                                    <div class="mt-2 flex items-center gap-2 text-sm">
                                        <span class="text-yellow-500 font-medium">{{ __('Under Construction') }}</span>
                                        <span class="text-gray-400 text-xs" x-text="'Ready: ' + new Date(b.finishes_at).toLocaleString()"></span>
                                    </div>
                                </template>

                                <template x-if="b.can_upgrade">
                                    <div class="mt-3">
                                        <div class="text-xs text-gray-400 mb-1">
                                            <template x-for="(val, key) in b.next_costs" :key="key">
                                                <span x-show="val > 0" x-text="key + ':' + val + ' '"></span>
                                            </template>
                                            <span x-text="b.build_time + 'min'"></span>
                                        </div>
                                        <button @click="doBuild(b.type)"
                                                :disabled="busy === b.type"
                                                class="w-full px-3 py-1.5 text-xs font-medium text-white bg-blue-600 rounded-md hover:bg-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                                                x-text="busy === b.type ? '{{ __('...') }}' : '{{ __('Upgrade') }}'">
                                        </button>
                                    </div>
                                </template>

                                <template x-if="!b.can_upgrade && !b.is_under_construction && b.level > 0">
                                    <div class="mt-2 text-xs text-green-500 font-medium">{{ __('Max Level') }}</div>
                                </template>
                            </div>
                        </template>
                    </div>

                    <div x-show="message" x-text="message" class="mt-3 text-sm" :class="error ? 'text-red-600' : 'text-green-600'"></div>
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

    @push('scripts')
    <script>
        function basePreview(listUrl, baseName) {
            return {
                buildings: [], loading: true, svg: '',
                async init() { await this.fetch(); setInterval(() => this.fetch(), 15000); },
                async fetch() {
                    try {
                        const r = await fetch(listUrl);
                        const d = await r.json();
                        this.buildings = d.buildings;
                        this.renderSvg();
                        this.loading = false;
                    } catch(e) {}
                },
                renderSvg() {
                    const colors = ['#8B4513', '#4682B4', '#6B8E23', '#B22222', '#DAA520'];
                    const positions = [
                        { x: 120, y: 155 }, { x: 150, y: 140 },
                        { x: 250, y: 140 }, { x: 280, y: 155 },
                    ];

                    const withLevel = this.buildings.filter(b => b.level > 0);
                    let buildingsHtml = '';

                    withLevel.forEach((b, i) => {
                        const c = colors[i % colors.length];
                        const p = positions[i] || { x: 200 + (i * 20), y: 160 };
                        const w = 14 + (b.level * 2);
                        const h = 12 + (b.level * 3);

                        buildingsHtml += `<g transform="translate(${p.x},${p.y})">`;
                        buildingsHtml += `<rect x="${-w/2}" y="${-h}" width="${w}" height="${h}" fill="${c}" rx="2" stroke="#ffd700" stroke-width="1"/>`;
                        buildingsHtml += `<text x="0" y="${-h/2 + 2}" text-anchor="middle" fill="white" font-size="7" font-weight="bold">Lv${b.level}</text>`;
                        buildingsHtml += `</g>`;
                    });

                    this.svg = `<svg viewBox="0 0 400 250" class="w-full h-full">
                        <defs>
                            <linearGradient id="sky-b" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#1a1a2e"/>
                                <stop offset="100%" stop-color="#2d1b4e"/>
                            </linearGradient>
                            <linearGradient id="ground-b" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#3a5a1e"/>
                                <stop offset="100%" stop-color="#2d4016"/>
                            </linearGradient>
                        </defs>
                        <rect x="0" y="0" width="400" height="180" fill="url(#sky-b)"/>
                        <rect x="0" y="180" width="400" height="70" fill="url(#ground-b)"/>
                        <circle cx="50" cy="20" r="1" fill="white" opacity="0.6"/>
                        <circle cx="150" cy="35" r="1" fill="white" opacity="0.5"/>
                        <circle cx="280" cy="15" r="1.5" fill="white" opacity="0.7"/>
                        <circle cx="350" cy="30" r="1" fill="white" opacity="0.4"/>
                        <line x1="200" y1="120" x2="200" y2="190" stroke="#888" stroke-width="2"/>
                        <text x="200" y="195" text-anchor="middle" fill="#ccc" font-size="8">${baseName}</text>
                        <rect x="160" y="170" width="80" height="14" fill="#555" rx="3"/>
                        ${buildingsHtml}
                    </svg>`;
                },
            };
        }

        function baseBuildings(listUrl, buildUrl) {
            return {
                buildings: [], loading: true, message: '', error: false, busy: null,
                async init() { await this.fetch(); setInterval(() => this.fetch(), 15000); },
                async fetch() {
                    try {
                        const r = await fetch(listUrl);
                        const d = await r.json();
                        this.buildings = d.buildings;
                        this.loading = false;
                    } catch(e) {}
                },
                async doBuild(type) {
                    this.busy = type; this.message = ''; this.error = false;
                    try {
                        const res = await fetch(buildUrl, {
                            method: 'POST',
                            headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
                            body: JSON.stringify({type: type})
                        });
                        const data = await res.json();
                        if (!res.ok) { this.error = true; this.message = data.error || 'Error'; }
                        else { this.message = data.message; this.fetch(); }
                    } catch(e) { this.error = true; this.message = 'Request failed.'; }
                    this.busy = null;
                },
            };
        }
    </script>
    @endpush
</x-app-layout>

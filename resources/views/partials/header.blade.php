@push('head')
  <link rel="preconnect" href="{{ config('app.url') }}" crossorigin>
  <link rel="dns-prefetch" href="{{ config('app.url') }}">
@endpush
<header
    x-data="{ open:false, sub:null }"
    class="bg-white/90 backdrop-blur border-b border-neutral-200"
>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">

        {{-- Logo --}}
        <a href="{{ route('home') }}" class="flex items-center gap-3">
                @if($settings?->logo_path)
                    <img src="{{ asset('storage/'.$settings->logo_path) }}" alt="{{ $settings->site_name }}" class="h-8 w-auto" width="160" height="40" fetchpriority="low">
                @else
                    <span class="font-bold text-lg">{{ $settings->site_name ?? config('app.name') }}</span>
                @endif
            </a>

        {{-- Desktop nav --}}
        <nav class="hidden md:flex items-center gap-6">
            @isset($menus)
                @foreach($menus as $item)
                    @php $hasChildren = $item->children->isNotEmpty(); @endphp

                    @if(!$hasChildren)
                        <a href="{{ $item->resolved_url }}" class="text-sm font-bold text-neutral-700 hover:text-amber-600">
                            {{ $item->label }}
                        </a>
                    @else
                        <div class="relative group">
                            <button
                                class="text-sm font-bold text-neutral-700 hover:text-amber-600 inline-flex items-center gap-1"
                                aria-haspopup="true" aria-expanded="false"
                            >
                                {{ $item->label }}
                                <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z" clip-rule="evenodd"/></svg>
                            </button>
                            <div class="invisible opacity-0 group-hover:visible group-hover:opacity-100 transition
                                        absolute left-0  w-52 bg-white shadow-lg ring-1 ring-neutral-200 rounded-xl py-2">
                                @foreach($item->children as $child)
                                    <a href="{{ $child->resolved_url }}" class="block px-3 py-2 text-sm text-neutral-700 hover:bg-neutral-50">
                                        {{ $child->label }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforeach
            @endisset
        </nav>
        <form action="{{ route('search') }}" method="get" class="hidden md:block">
            <input
                type="text"
                name="q"
                placeholder="Cari…"
                class="rounded-xl ring-1 ring-neutral-300 bg-white px-3 py-2 text-sm focus:ring-amber-500"
            />
        </form>
        {{-- Social icons (desktop) --}}
        @if(!empty($profile))
            <div class="hidden md:flex items-center gap-3 mr-2 text-neutral-500">
                @include('partials.social-icons', ['profile' => $profile, 'size' => 20])
            </div>
        @endif
        {{-- Mobile hamburger --}}
        {{-- Mobile toggle (hamburger <-> close) --}}
        <button
            @click="open = !open"
            :aria-expanded="open"
            :aria-label="open ? 'Tutup menu' : 'Buka menu'"
            class="md:hidden inline-flex items-center justify-center rounded-lg p-2 ring-1 ring-neutral-300
                transition-colors"
            :class="open ? 'bg-neutral-100 ring-neutral-400' : ''"
        >
            {{-- Ikon hamburger (tampil saat closed) --}}
            <svg x-cloak x-show="!open" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>

            {{-- Ikon close (tampil saat open) --}}
            <svg x-cloak x-show="open" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>

    </div>
{{-- Drawer (mobile) tanpa teleport, mendorong konten di bawah --}}
<div
    x-show="open"
    x-collapse
    x-cloak
    class="md:hidden w-full bg-white border-t border-neutral-200"
    @keydown.window.escape="open=false"
>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        {{-- Kontainer daftar menu: full screen feel di mobile --}}
        <div class="mt-3 max-h-[calc(100vh-8rem)] overflow-y-auto pb-4">
            <nav class="space-y-1">
                @isset($menus)
                    @foreach($menus as $item)
                        @php $hasChildren = $item->children->isNotEmpty(); @endphp

                        @if (!$hasChildren)
                            <a href="{{ $item->resolved_url }}"
                               @click="open=false"
                               class="block px-3 py-2 rounded-lg text-sm font-medium text-neutral-800 hover:bg-neutral-100">
                                {{ $item->label }}
                            </a>
                        @else
                            <div class="border rounded-lg">
                                <button
                                    x-on:mouseenter.passive
                                    x-on:mouseleave.passive
                                    @click="sub === {{ $item->id }} ? sub = null : sub = {{ $item->id }}"
                                    class="w-full flex items-center justify-between px-3 py-2 text-left text-sm font-medium"
                                >
                                    <span>{{ $item->label }}</span>
                                    <svg class="h-4 w-4 transition-transform duration-200"
                                         :class="sub === {{ $item->id }} ? 'rotate-180' : ''"
                                         viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z" clip-rule="evenodd"/>
                                    </svg>
                                </button>

                                <div x-show="sub === {{ $item->id }}" x-collapse>
                                    @foreach($item->children as $child)
                                        <a href="{{ $child->resolved_url }}"
                                           @click="open=false"
                                           class="block px-5 py-2 text-sm text-neutral-700 hover:bg-neutral-50">
                                            {{ $child->label }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endforeach
                @endisset
            </nav>

            {{-- Ikon sosmed --}}
            @if(!empty($profile))
                <div class="mt-4 pt-3 border-t flex gap-4 text-neutral-500">
                    @include('partials.social-icons', ['profile' => $profile, 'size' => 22])
                </div>
            @endif
        </div>
    </div>
</div>
</header>

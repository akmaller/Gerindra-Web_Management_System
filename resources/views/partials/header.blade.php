@push('head')
  <link rel="preconnect" href="{{ config('app.url') }}" crossorigin>
  <link rel="dns-prefetch" href="{{ config('app.url') }}">
@endpush
<header
    x-data="{ open:false }"
    x-on:close-menu.window="open = false"
    class="relative z-40 bg-white border-b border-neutral-200"
>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">

        {{-- Logo --}}
        <a href="{{ route('home') }}" class="flex items-center">
                @if($settings?->logo_path)
                    <img src="{{ asset('storage/'.$settings->logo_path) }}" alt="{{ $settings->site_name }}" class="h-12 w-auto" width="160" height="40" fetchpriority="low">
                @else
                    <span class="font-bold text-lg">{{ $settings->site_name ?? config('app.name') }}</span>
                @endif
            </a>

        <div class="flex items-center gap-4">
            {{-- Desktop nav --}}
            <nav class="hidden md:flex items-center gap-6">
                @if(isset($menus) && $menus->isNotEmpty())
                    @include('partials.navigation.desktop-items', ['items' => $menus, 'level' => 0])
                @endif
            </nav>

            {{-- Social icons (desktop) --}}
            @if(!empty($profile))
                <div class="hidden md:flex items-center gap-3 text-[color:var(--brand-primary)] opacity-70">
                    @include('partials.social-icons', ['profile' => $profile, 'size' => 20])
                </div>
            @endif

            <form action="{{ route('search') }}" method="get" class="hidden md:block">
                <input
                    type="text"
                    name="q"
                    placeholder="Cari…"
                    class="rounded-xl ring-1 ring-neutral-300 bg-white px-3 py-2 text-sm focus:ring-[color:var(--brand-primary)]"
                />
            </form>
        </div>
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
            @if(isset($menus) && $menus->isNotEmpty())
                @include('partials.navigation.mobile-items', ['items' => $menus, 'level' => 0])
            @endif

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

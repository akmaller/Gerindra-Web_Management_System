{{-- resources/views/search/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Pencarian')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

    {{-- Form pencarian --}}
    <form action="{{ route('search') }}" method="get" class="mb-6">
        <div class="flex items-center gap-3">
            <input
                type="text"
                name="q"
                value="{{ $q }}"
                placeholder="Cari berita atau halaman…"
                class="w-full rounded-xl ring-1 ring-neutral-300 focus:ring-amber-500 bg-white px-4 py-3"
                autofocus
            />
            <button class="inline-flex items-center gap-2 px-4 py-3 rounded-xl bg-amber-500 text-white hover:bg-amber-600">
                <x-bi-search class="w-5 h-5" />
                Cari
            </button>
        </div>
    </form>

    {{-- Info hasil --}}
    @if($q)
        <p class="text-sm text-neutral-600 mb-4">
            Hasil untuk: <span class="font-semibold">"{{ $q }}"</span> —
            {{ number_format($results->total()) }} item
        </p>
    @endif

    {{-- Daftar hasil --}}
    @if($results->count())
        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            @foreach($results as $item)
                <article class="rounded-2xl ring-1 ring-neutral-200 overflow-hidden bg-white hover:shadow-md transition">
                    @if($item['thumb'])
                        <a href="{{ $item['url'] }}">
                            <img src="{{ $item['thumb'] }}" alt="{{ $item['title'] }}" class="w-full h-40 object-cover">
                        </a>
                    @endif
                    <div class="p-4">
                        <div class="flex items-center gap-2 text-xs text-neutral-500 mb-2">
                            <span class="inline-flex items-center rounded-full bg-neutral-100 px-2 py-1 text-[11px] font-medium">
                                {{ $item['badge'] }}
                            </span>
                            <span>{{ \Carbon\Carbon::parse($item['date'])->translatedFormat('d M Y') }}</span>
                        </div>
                        <a href="{{ $item['url'] }}" class="block font-semibold text-lg leading-snug hover:text-amber-600">
                            {{ $item['title'] }}
                        </a>
                        <p class="mt-2 text-sm text-neutral-600">{{ $item['excerpt'] }}</p>
                        <div class="mt-4">
                            <a href="{{ $item['url'] }}" class="text-sm font-medium text-amber-600 hover:underline">Baca selengkapnya →</a>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        <div class="mt-8">
            {{ $results->withQueryString()->links() }}
        </div>
    @else
        @if($q)
            <div class="rounded-2xl ring-1 ring-neutral-200 bg-white p-6">
                <p class="text-neutral-700">Tidak ditemukan hasil untuk pencarian tersebut.</p>
            </div>
        @endif
    @endif
</div>
@endsection

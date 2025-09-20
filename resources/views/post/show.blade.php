@extends('layouts.app')

@section('content')

{{-- GRID 2:1 --}}
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-6">
  <div class="grid grid-cols-12 gap-6">

    {{-- MAIN CONTENT (2 bagian) --}}
    <article class="col-span-12 lg:col-span-8">

      {{-- Judul --}}
      <h1 class="text-3xl/tight font-extrabold text-neutral-900">
        {{ $post->title }}
      </h1>

      {{-- Meta --}}
      <div class="mt-2 flex flex-wrap items-center gap-3 text-sm text-neutral-500">
        @if($post->category)
          <a href="{{ route('category.show', $post->category->slug) }}"
             class="inline-flex items-center rounded-full bg-amber-100 px-3 py-1 text-amber-800 font-medium hover:bg-amber-200">
            {{ $post->category->name }}
          </a>
        @endif
        <span>•</span>
        <time datetime="{{ optional($post->published_at)->toDateString() }}">
          {{ optional($post->published_at)->translatedFormat('d M Y') }}
        </time>
      </div>

      <figure  class="mt-5 rounded-xl overflow-hidden bg-neutral-100">
        @php
            $origUrl = $post->thumbnail ? asset('storage/'.$post->thumbnail) : asset('images/example.webp');
            $baseNoExt = preg_replace('/\.(jpe?g|png|webp)$/i', '', $origUrl);
            $jpgThumb  = preg_replace('/\.(jpe?g|png|webp)$/i', '-thumb.jpg',  $origUrl);
            $webpThumb  = $baseNoExt . '-thumb.webp';
        @endphp
            <source srcset="{{ $webpThumb }}" type="image/webp">
            <img src="{{ $webpThumb }}" alt="{{ $post->title }}" class="w-full aspect-[16/9] object-cover" width="1280" height="720" decoding="async" fetchpriority="high" loading="lazy">
        </figure>

      {{-- Isi --}}
      <div class="prose prose-neutral max-w-none mt-6 post-content">
        {!! $post->content !!}
      </div>
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <x-ad-slot location="below_post" />
        </div>

      {{-- TAGS --}}
      @if($post->tags?->count())
        <div class="mt-8 flex flex-wrap items-center gap-2">
          @foreach($post->tags as $tag)
            <a href="{{ route('tag.show', $tag->slug) }}"
               class="px-3 py-1 rounded-full bg-neutral-100 hover:bg-amber-100 text-neutral-700 text-sm">
              #{{ $tag->name }}
            </a>
          @endforeach
        </div>
      @endif

{{-- SHARE --}}
<div class="mt-8 flex items-center gap-3">
  <span class="text-sm font-medium text-neutral-600">Bagikan:</span>
  @php
      $shareUrl = route('posts.show', [
          'tahun'  => $post->published_at?->format('Y'),
          'bulan' => $post->published_at?->format('m'),
          'slug'  => $post->slug
      ]);
  @endphp

  {{-- Facebook --}}
  <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($shareUrl) }}"
     target="_blank" rel="noopener">
      <x-bi-facebook class="w-4 h-4 text-blue-600"/>
  </a>

  {{-- Twitter / X --}}
  <a href="https://twitter.com/intent/tweet?url={{ urlencode($shareUrl) }}&text={{ urlencode($post->title) }}"
     target="_blank" rel="noopener"
     class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-neutral-100 hover:bg-amber-100 text-sky-500">
      <x-bi-twitter-x />
  </a>

  {{-- WhatsApp --}}
  <a href="https://wa.me/?text={{ urlencode($post->title . ' ' . $shareUrl) }}"
     target="_blank" rel="noopener"
     class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-neutral-100 hover:bg-amber-100 text-green-600">
      <x-bi-whatsapp />
  </a>

  {{-- Telegram --}}
  <a href="https://t.me/share/url?url={{ urlencode($shareUrl) }}&text={{ urlencode($post->title) }}"
     target="_blank" rel="noopener"
     class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-neutral-100 hover:bg-amber-100 text-sky-600">
      <x-bi-telegram />
  </a>

  {{-- Copy Link --}}
  <button type="button"
          x-data
          @click="navigator.clipboard.writeText('{{ $shareUrl }}'); $dispatch('notify', {title:'Link tersalin'})"
          class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-neutral-100 hover:bg-amber-100 text-neutral-700">
      <x-bi-copy />
  </button>
</div>

      {{-- ARTIKEL TERKAIT --}}
      @if($related?->count())
        <section class="mt-10">
          <h2 class="text-lg font-semibold mb-4">Artikel terkait</h2>

          <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach($related as $rel)
              @php
                $relCover = $rel->thumbnail
                  ? asset('storage/'.$rel->thumbnail)
                  : asset('images/example.webp');
                $relbaseNoExt = preg_replace('/\.(jpe?g|png|webp)$/i', '', $relCover);
                $reljpgSmall  = preg_replace('/\.(jpe?g|png|webp)$/i', '-small.jpg',  $relCover);
                $relwebpSmall  = $relbaseNoExt . '-small.webp';
              @endphp
              <a href="{{ route('posts.show', ['tahun'=>$rel->published_at?->format('Y'),'bulan'=>$rel->published_at?->format('m'),'slug'=>$rel->slug]) }}"
                 class="group block rounded-xl overflow-hidden ring-1 ring-neutral-200 hover:ring-amber-200 bg-white">
                <div class="aspect-[16/9] overflow-hidden bg-neutral-100">
                  <img src="{{ $relwebpSmall }}" alt="{{ $rel->title }}"
                       class="w-full aspect-[16/9] object-cover group-hover:opacity-95 transition" decoding="async" loading="lazy">
                </div>
                <div class="p-3">
                  <div class="text-xs text-neutral-500 mb-1">
                    {{ optional($rel->published_at)->translatedFormat('d M Y') }}
                  </div>
                  <h3 class="text-sm font-semibold text-neutral-800 line-clamp-2">
                    {{ $rel->title }}
                  </h3>
                </div>
              </a>
            @endforeach
          </div>
        </section>
      @endif

    </article>

    {{-- SIDEBAR (1 bagian) --}}
    <aside class="col-span-12 lg:col-span-4">

      {{-- Populer --}}
      @if($popularPosts?->count())
        <section class="mb-8">
          <h2 class="text-lg font-semibold mb-3">Populer</h2>
          <ul class="space-y-3">
            @foreach($popularPosts as $item)
              @php
                $thumb = $item->thumbnail ? asset('storage/'.$item->thumbnail) : asset('images/example.webp');
                $thumbbaseNoExt = preg_replace('/\.(jpe?g|png|webp)$/i', '', $thumb);
                $thumbjpgSmall  = preg_replace('/\.(jpe?g|png|webp)$/i', '-small.jpg',  $thumb);
                $thumbwebpSmall  = $thumbbaseNoExt . '-small.webp';
              @endphp
              <li>
                <a href="{{ route('posts.show', ['tahun'=>$item->published_at?->format('Y'),'bulan'=>$item->published_at?->format('m'),'slug'=>$item->slug]) }}"
                   class="grid grid-cols-12 gap-3 items-center rounded-lg hover:bg-neutral-50 p-2">
                  <div class="col-span-4">
                    <div class="aspect-[16/10] rounded-md overflow-hidden bg-neutral-100">
                      <img src="{{ $thumbwebpSmall }}" alt="{{ $item->title }}" class="w-full h-auto rounded object-cover flex-none"  decoding="async" loading="lazy">
                    </div>
                  </div>
                  <div class="col-span-8">
                    <div class="text-[11px] text-neutral-500 mb-1">
                      {{ optional($item->published_at)->translatedFormat('d M Y') }}
                    </div>
                    <h3 class="text-sm font-semibold text-neutral-800 line-clamp-2">
                      {{ $item->title }}
                    </h3>
                  </div>
                </a>
              </li>
            @endforeach
          </ul>
        </section>
      @endif

      <div class="mb-8">
            <x-ad-slot location="sidebar" />
        </div>

      {{-- Terbaru --}}
      @if($latest?->count())
        <section>
          <h2 class="text-lg font-semibold mb-3">Terbaru</h2>
          <ul class="space-y-3">
            @foreach($latest as $item)
              @php
                $thumb = $item->thumbnail ? asset('storage/'.$item->thumbnail) : asset('images/example.webp');
                $thumbbaseNoExt = preg_replace('/\.(jpe?g|png|webp)$/i', '', $thumb);
                $thumbjpgSmall  = preg_replace('/\.(jpe?g|png|webp)$/i', '-small.jpg',  $thumb);
                $thumbwebpSmall  = $thumbbaseNoExt . '-small.webp';
              @endphp
              <li>
                <a href="{{ route('posts.show', ['tahun'=>$item->published_at?->format('Y'),'bulan'=>$item->published_at?->format('m'),'slug'=>$item->slug]) }}"
                   class="grid grid-cols-12 gap-3 items-center rounded-lg hover:bg-neutral-50 p-2">
                  <div class="col-span-4">
                    <div class="aspect-[16/10] rounded-md overflow-hidden bg-neutral-100">
                      <img src="{{ $thumbwebpSmall }}" alt="{{ $item->title }}" class="w-full h-auto rounded object-cover flex-none"  width="192" height="128" decoding="async" loading="lazy">
                    </div>
                  </div>
                  <div class="col-span-8">
                    <div class="text-[11px] text-neutral-500 mb-1">
                      {{ optional($item->published_at)->translatedFormat('d M Y') }}
                    </div>
                    <h3 class="text-sm font-semibold text-neutral-800 line-clamp-2">
                      {{ $item->title }}
                    </h3>
                  </div>
                </a>
              </li>
            @endforeach
          </ul>
        </section>
      @endif

    </aside>
  </div>
</div>
@endsection

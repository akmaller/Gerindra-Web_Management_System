{{-- resources/views/pages/show.blade.php --}}
@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-6">
        {{-- Breadcrumbs sederhana --}}
        <nav class="text-sm text-neutral-500 mb-6">
            <a href="{{ route('home') }}" class="hover:text-amber-600">Beranda</a>
            <span class="mx-2">/</span>
            <span class="text-neutral-700">{{ $page->title }}</span>
        </nav>

    <div class="grid grid-cols-12 gap-6 lg:gap-8">
            {{-- Konten --}}
            <article class="col-span-12 lg:col-span-8 md:col-span-8 sm:col-span-8">
                @if(!empty($page->thumbnail))
                    <figure class="mt-6">
                        @php
                            $pageorigUrl = $page->thumbnail;
                            $pagebaseNoExt = preg_replace('/\.(jpe?g|png|webp)$/i', '', $pageorigUrl);
                            $pagejpgThumb  = preg_replace('/\.(jpe?g|png|webp)$/i', '-thumb.jpg',  $pageorigUrl);
                            $pagewebpThumb  = $pagebaseNoExt . '-thumb.webp';
                        @endphp
                        <img
                            class="w-full rounded-xl object-cover"
                            src="{{ asset('storage/'.$pagewebpThumb) }}"
                            alt="{{ $page->title }}"
                            loading="lazy">
                    </figure>
                @endif

                {{-- Isi halaman (HTML) --}}
                <div class="prose prose-neutral max-w-none mt-6 post-content">
                    {!! $page->content !!}
                </div>

                {{-- Iklan bawah konten --}}
                <div class="mt-10">
                    @includeIf('partials.ad-slot', ['location' => 'below_post'])
                </div>
            </article>

            {{-- SIDEBAR (1 bagian) --}}
    <aside class="col-span-12 lg:col-span-4 md:col-span-4">

      <div class="mb-8">
            <x-ad-slot location="sidebar" />
        </div>

      {{-- Terbaru --}}
      @if($latestPosts?->count())
        <section>
          <h2 class="text-lg font-semibold mb-3">Terbaru</h2>
          <ul class="space-y-3">
            @foreach($latestPosts as $item)
               @php
                    $origUrl = $item->thumbnail ? asset('storage/'.$item->thumbnail) : asset('images/example.webp');
                    $baseNoExt = preg_replace('/\.(jpe?g|png|webp)$/i', '', $origUrl);
                    $jpgThumb  = preg_replace('/\.(jpe?g|png|webp)$/i', '-thumb.jpg',  $origUrl);
                    $webpThumb  = $baseNoExt . '-thumb.webp';
                @endphp
              <li>
                <a href="{{ route('posts.show', ['tahun'=>$item->published_at?->format('Y'),'bulan'=>$item->published_at?->format('m'),'slug'=>$item->slug]) }}"
                   class="grid grid-cols-12 gap-3 items-center rounded-lg hover:bg-neutral-50 p-2">
                  <div class="col-span-4">
                    <div class="aspect-[16/10] rounded-md overflow-hidden bg-neutral-100">
                      <img src="{{ $webpThumb }}" alt="{{ $item->title }}" class="w-full h-full object-cover">
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
    </main>
@endsection

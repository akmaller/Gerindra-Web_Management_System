@extends('layouts.app')

@section('content')

{{-- HERO SLIDER + LIST --}}
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-6">
  <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

    {{-- HERO SLIDER kiri (span 2 kolom) --}}
    <div class="md:col-span-2 relative z-0 overflow-hidden rounded-2xl">
      <div x-data="{ active: 0, posts: {{ $featuredPosts->toJson() }} }"
           x-init="setInterval(() => { active = (active + 1) % posts.length }, 5000)"
           class="relative z-0 w-full h-96">

        <template x-for="(post, index) in posts" :key="index">
          <a :href="'/post/' + new Date(post.published_at).getMonth() + '/' + new Date(post.published_at).getFullYear() + '/' + post.slug"
             class="absolute inset-0 transition-opacity duration-700"
             x-show="active === index">
            <img :src="post.thumbnail
                ? '/storage/' + post.thumbnail.replace(/\.(jpe?g|png)$/i, '-thumb.webp')
                : 'images/example-thumb.webp'" alt=""class="w-full h-full object-cover rounded-2xl" loading="lazy" decoding="async">
            <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/70 to-transparent p-6">
              <h2 class="text-white text-2xl font-bold" x-text="post.title"></h2>
              <p class="text-sm text-gray-200 mt-2">
                <span x-text="post.category?.name"></span> ·
                <span x-text="new Date(post.published_at).toLocaleDateString('id-ID', { day:'2-digit', month:'short', year:'numeric' })"></span>
              </p>
            </div>
          </a>
        </template>
      </div>
    </div>

    {{-- LIST kanan --}}
    <div class="space-y-4">
      @foreach($featuredPosts as $post)
        <div class="border-b border-neutral-200 pb-3">
          <a href="{{ $post->permalink }}" class="block">
            <h3 class="font-semibold text-lg leading-snug hover:text-amber-600 transition">
              {{ $post->title }}
            </h3>
          </a>
          <p class="text-xs text-neutral-500 mt-1">
            {{ $post->published_at?->translatedFormat('l, d M Y H:i') }} WIB
          </p>
        </div>
      @endforeach
    </div>
  </div>
</section>

{{-- GRID 3 TERBARU --}}
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-10">
  <div class="flex items-end justify-between mb-4">
    <h3 class="text-xl font-bold">Terbaru</h3>
    <a href="{{ route('posts.index') }}" class="text-amber-600 text-sm">Lihat semua</a>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    @foreach($latest as $post)
      <article class="bg-white rounded-2xl overflow-hidden shadow ring-1 ring-neutral-200 hover:shadow-md transition">
        <a href="{{ $post->permalink }}">
          <div class="aspect-[16/9] overflow-hidden">
            @php
                $origUrl = $post->thumbnail ? asset('storage/'.$post->thumbnail) : asset('images/example.webp');
                $baseNoExt = preg_replace('/\.(jpe?g|png|webp)$/i', '', $origUrl);
                $jpgMed  = preg_replace('/\.(jpe?g|png|webp)$/i', '-thumb.jpg',  $origUrl);
                $webpMed  = $baseNoExt . '-middle.webp';
            @endphp
            <img src="{{ $webpMed }}"
                 alt="{{ $post->title }}" class="w-full aspect-[16/9] object-cover" width="800" height="450" loading="lazy" decoding="async">
          </div>
        </a>
        <div class="p-5">
          @if($post->category)
            <a href="{{ route('category.show', $post->category->slug) }}"
               class="text-xs uppercase tracking-wider text-amber-600 font-semibold">
              {{ $post->category->name }}
            </a>
          @endif
          <h4 class="mt-2 text-xl font-semibold leading-tight">
            <a href="{{ $post->permalink }}">{{ $post->title }}</a>
          </h4>
          <div class="mt-3 text-xs text-neutral-500">
            {{ $post->published_at?->translatedFormat('d M Y') }}
          </div>
        </div>
      </article>
    @endforeach
  </div>

  @php
      // bagi jadi 3 kolom dengan masing-masing 3 item
      $columns = $latestList->chunk(3);
  @endphp

  <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mt-8">
    @foreach($columns as $col)
      <ul class="space-y-6">
        @foreach($col as $post)
          <li>
            <a href="{{ $post->permalink }}" class="group block">
              <h5 class="font-semibold text-lg leading-snug group-hover:underline">
                {{ $post->title }}
              </h5>
            </a>
            <div class="text-sm text-neutral-500 mt-1">
              @if($post->category)
                <a href="{{ route('category.show', $post->category->slug) }}"
                   class="hover:text-amber-600">
                  {{ $post->category->name }}
                </a>
                <span class="mx-1">·</span>
              @endif
              {{ $post->published_at?->translatedFormat('d M Y') }}
            </div>
          </li>
        @endforeach
      </ul>
    @endforeach
  </div>
</section>

<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-14 mb-16">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        @foreach ($categoryBlocks as $block)
            @include('partials.home.category-block', [
                'title'   => $block['title'],
                'posts'   => $block['posts'],
                'moreUrl' => $block['more_url'],
            ])
        @endforeach
    </div>
</section>

@endsection

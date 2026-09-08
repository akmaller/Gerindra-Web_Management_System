@extends('layouts.app')

@section('content')
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-2xl md:text-3xl font-bold">{{ $category->name }}</h1>
    @if($category->description)
        <p class="mt-2 text-neutral-600">{{ $category->description }}</p>
    @endif

    <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($posts as $post)
            <article class="bg-white rounded-xl overflow-hidden shadow ring-1 ring-neutral-200 hover:shadow-md transition">
                <a href="{{ $post->permalink }}">
                    <div class="aspect-[16/9] overflow-hidden">
                        @php
                            $origUrl = $post->thumbnail ? asset('storage/'.$post->thumbnail) : asset('images/example.webp');
                            $baseNoExt = preg_replace('/\.(jpe?g|png|webp)$/i', '', $origUrl);
                            $jpgMed  = preg_replace('/\.(jpe?g|png|webp)$/i', '-thumb.jpg',  $origUrl);
                            $webpMed  = $baseNoExt . '-middle.webp';
                        @endphp
                        <img src="{{ $webpMed }}"
                             alt="{{ $post->title }}" class="w-full h-full object-cover">
                    </div>
                </a>
                <div class="p-4">
                    <h2 class="text-lg font-semibold leading-tight line-clamp-2">
                        <a href="{{ $post->permalink }}">{{ $post->title }}</a>
                    </h2>
                    <div class="mt-2 text-xs text-neutral-500">
                        {{ $post->published_at?->translatedFormat('d M Y') }}
                    </div>
                    @if($post->tags->isNotEmpty())
                        <div class="mt-3 flex flex-wrap gap-2">
                            @foreach($post->tags as $tag)
                                <a href="{{ route('tag.show', $tag->slug) }}" class="text-xs px-2 py-1 rounded bg-neutral-100 hover:bg-neutral-200">
                                    #{{ $tag->name }}
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </article>
        @empty
            <div class="col-span-full text-neutral-500">Belum ada artikel.</div>
        @endforelse
    </div>

    <div class="mt-8">{{ $posts->links() }}</div>
</section>
@endsection

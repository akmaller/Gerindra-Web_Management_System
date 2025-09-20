@extends('layouts.app')

@section('content')
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-2xl md:text-3xl font-bold">Tag: {{ $tag->name }}</h1>

    <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($posts as $post)
            <article class="bg-white rounded-xl overflow-hidden shadow ring-1 ring-neutral-200 hover:shadow-md transition">
                <a href="{{ $post->permalink }}">
                    <div class="aspect-[16/9] overflow-hidden">
                        <img src="{{ $post->thumbnail ? asset('storage/'.$post->thumbnail) : 'https://picsum.photos/600/338?random='.$post->id }}"
                             alt="{{ $post->title }}" class="w-full h-full object-cover">
                    </div>
                </a>
                <div class="p-4">
                    @if($post->category)
                        <a href="{{ route('category.show', $post->category->slug) }}"
                           class="text-xs uppercase tracking-wider text-amber-600 font-semibold">
                           {{ $post->category->name }}
                        </a>
                    @endif
                    <h2 class="mt-1 text-lg font-semibold leading-tight line-clamp-2">
                        <a href="{{ $post->permalink }}">{{ $post->title }}</a>
                    </h2>
                    <div class="mt-2 text-xs text-neutral-500">
                        {{ $post->published_at?->translatedFormat('d M Y') }}
                    </div>
                </div>
            </article>
        @empty
            <div class="col-span-full text-neutral-500">Belum ada artikel.</div>
        @endforelse
    </div>

    <div class="mt-8">{{ $posts->links() }}</div>
</section>
@endsection

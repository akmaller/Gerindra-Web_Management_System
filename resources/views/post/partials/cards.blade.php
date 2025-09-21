{{-- resources/views/post/partials/cards.blade.php --}}
@foreach($posts as $post)
    @php
        $thumbSource = $post->thumbnail
            ? asset('storage/' . $post->thumbnail)
            : asset('images/example.webp');
        $thumbBase = preg_replace('/\.(jpe?g|png|webp)$/i', '', $thumbSource);
        $thumbWebp = $thumbBase . '-middle.webp';
    @endphp
    <article class="bg-white rounded-2xl overflow-hidden shadow ring-1 ring-neutral-200 hover:shadow-md transition">
        <a href="{{ $post->permalink }}" class="block">
            <div class="aspect-[16/9] overflow-hidden">
                <img src="{{ $thumbWebp }}" alt="{{ $post->title }}"
                     class="w-full h-full object-cover" loading="lazy" decoding="async">
            </div>
        </a>
        <div class="p-5 space-y-2">
            @if($post->category)
            <a href="{{ route('category.show', $post->category->slug) }}"
               class="text-xs uppercase tracking-wider text-[color:var(--brand-primary)] font-semibold">
                    {{ $post->category->name }}
                </a>
            @endif
            <h3 class="text-lg font-semibold leading-tight">
        <a href="{{ $post->permalink }}" class="hover:text-[color:var(--brand-primary)]">
                    {{ $post->title }}
                </a>
            </h3>
            <p class="text-sm text-neutral-500">
                {{ $post->published_at?->translatedFormat('d M Y') }}
            </p>
        </div>
    </article>
@endforeach

@props([
    'title' => 'Kategori',
    'posts' => collect(),
    'moreUrl' => '#',
])

<section class="bg-white rounded-2xl shadow ring-1 ring-neutral-200 overflow-hidden">
    {{-- Header gradien --}}
    <div class="bg-gradient-to-r from-orange-700 to-orange-500 px-4 sm:px-5 py-3">
        <div class="text-white italic tracking-wider font-extrabold text-lg">
            {{ strtoupper($title) }}
        </div>
    </div>

    <div class="divide-y divide-neutral-200">
        @forelse ($posts as $post)
            <a href="{{ $post->permalink }}" class="flex gap-4 p-4 hover:bg-neutral-50">
                <div class="shrink-0 w-28 h-20 sm:w-32 aspect-[16/9] overflow-hidden rounded-xl ring-1 ring-neutral-200">
                    @php
                        $origUrl = $post->thumbnail ? asset('storage/'.$post->thumbnail) : asset('images/example.webp');
                        $baseNoExt = preg_replace('/\.(jpe?g|png|webp)$/i', '', $origUrl);
                        $jpgSmall  = preg_replace('/\.(jpe?g|png|webp)$/i', '-thumb.jpg',  $origUrl);
                        $webpSmall  = $baseNoExt . '-small.webp';
                    @endphp
                    <img
                        src="{{ $webpSmall }}"
                        alt="{{ $post->title }}"
                        class="w-full h-auto object-cover" decoding="async" loading="lazy">
                </div>
                <div class="flex-1">
                    <h3 class="text-base sm:text-lg font-semibold leading-snug">
                        {{ $post->title }}
                    </h3>
                    <div class="mt-1 text-xs text-neutral-500">
                        {{ $post->published_at?->translatedFormat('d M Y') }}
                    </div>
                </div>
            </a>
        @empty
            <div class="p-5 text-sm text-neutral-500">Belum ada artikel.</div>
        @endforelse
    </div>

    {{-- Footer link --}}
    <div class="px-4 sm:px-5 py-3">
        <a href="{{ $moreUrl }}" class="text-sm font-medium text-orange-500 hover:text-orange-700">
            Lihat lebih banyak →
        </a>
    </div>
</section>

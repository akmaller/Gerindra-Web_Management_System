@extends('layouts.app')

@section('content')
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-6">
        <nav class="text-sm text-neutral-500 mb-6" aria-label="Breadcrumb">
            <a href="{{ route('home') }}" class="hover:text-amber-600">Beranda</a>
            <span class="mx-2">/</span>
            <span class="text-neutral-700">Semua Berita</span>
        </nav>

        <div class="grid grid-cols-12 gap-6 lg:gap-8">
            <div class="col-span-12 lg:col-span-8 space-y-6">
                @if($posts->count())
                    <div id="post-grid" class="grid grid-cols-1 md:grid-cols-2 gap-6" data-next="{{ $posts->nextPageUrl() }}">
                        @include('post.partials.cards', ['posts' => $posts])
                    </div>

                    <div id="load-more-container" class="mt-4 text-center">
                        @if($posts->hasMorePages())
                            <button id="load-more"
                                    type="button"
                                    data-next="{{ $posts->nextPageUrl() }}"
                                    class="inline-flex items-center px-5 py-2.5 rounded-full bg-amber-600 text-white text-sm font-semibold hover:bg-amber-500 focus:outline-none focus:ring focus:ring-amber-300">
                                Load more
                            </button>
                        @endif
                    </div>
                @else
                    <div class="rounded-2xl bg-white p-10 text-center text-neutral-500">
                        Belum ada berita yang bisa ditampilkan.
                    </div>
                @endif
            </div>

            <aside class="col-span-12 lg:col-span-4 space-y-6">
                @if($popularPosts?->count())
                    <section>
                        <h2 class="text-lg font-semibold mb-4">Berita Populer</h2>
                        <ul class="space-y-3">
                            @foreach($popularPosts as $item)
                                @php
                                    $thumbSource = $item->thumbnail
                                        ? asset('storage/' . $item->thumbnail)
                                        : asset('images/example.webp');
                                    $thumbBase = preg_replace('/\.(jpe?g|png|webp)$/i', '', $thumbSource);
                                    $thumbWebp = $thumbBase . '-small.webp';
                                @endphp
                                <li>
                                    <a href="{{ route('posts.show', [
                                                'tahun' => $item->published_at?->format('Y'),
                                                'bulan' => $item->published_at?->format('m'),
                                                'slug' => $item->slug,
                                            ]) }}"
                                       class="grid grid-cols-12 gap-3 items-center rounded-lg hover:bg-neutral-50 p-2">
                                        <div class="col-span-4">
                                            <div class="aspect-[16/10] rounded-md overflow-hidden bg-neutral-100">
                                                <img src="{{ $thumbWebp }}" alt="{{ $item->title }}" class="w-full h-full object-cover" loading="lazy" decoding="async">
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
    </section>

    @if($posts->hasMorePages())
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const button = document.getElementById('load-more');
                const grid = document.getElementById('post-grid');
                if (!button || !grid) {
                    return;
                }

                let isLoading = false;

                button.addEventListener('click', async () => {
                    const nextUrl = button.dataset.next;
                    if (!nextUrl || isLoading) {
                        return;
                    }

                    isLoading = true;
                    button.disabled = true;
                    const originalText = button.textContent;
                    button.textContent = 'Memuat...';

                    try {
                        const requestUrl = new URL(nextUrl, window.location.origin);
                        const response = await fetch(requestUrl, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                            },
                        });

                        if (!response.ok) {
                            throw new Error('Gagal memuat data');
                        }

                        const payload = await response.json();

                        if (payload.items) {
                            grid.insertAdjacentHTML('beforeend', payload.items);
                        }

                        if (payload.next_page_url) {
                            button.dataset.next = payload.next_page_url;
                            button.disabled = false;
                            button.textContent = originalText;
                        } else {
                            button.remove();
                        }
                    } catch (error) {
                        console.error(error);
                        button.disabled = false;
                        button.textContent = 'Coba lagi';

                        setTimeout(() => {
                            button.textContent = originalText;
                        }, 2000);
                    } finally {
                        isLoading = false;
                    }
                });
            });
        </script>
    @endif
@endsection

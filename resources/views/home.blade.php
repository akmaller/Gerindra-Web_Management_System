@extends('layouts.app')

@php
    $variantUrl = function (?string $path, string $suffix = 'middle', string $fallback = 'images/example-middle.webp') {
        if (! $path) {
            return asset($fallback);
        }

        $normalized = ltrim($path, '/');

        if (\Illuminate\Support\Str::startsWith($normalized, 'storage/')) {
            $normalized = \Illuminate\Support\Str::after($normalized, 'storage/');
        }

        if (! \Illuminate\Support\Facades\Storage::disk('public')->exists($normalized)) {
            return asset($fallback);
        }

        $base = preg_replace("/\.(jpe?g|png|webp)$/i", '', $normalized);

        foreach (['webp', 'jpg', 'png'] as $ext) {
            $candidate = $base . '-' . $suffix . '.' . $ext;

            if (\Illuminate\Support\Facades\Storage::disk('public')->exists($candidate)) {
                return \Illuminate\Support\Facades\Storage::url($candidate);
            }
        }

        return \Illuminate\Support\Facades\Storage::url($normalized);
    };
@endphp

@section('content')
    @if($heroSlides->isNotEmpty())
        <section class="relative w-full overflow-hidden">
            <div
                x-data="{
                    slides: @js($heroSlides),
                    current: 0,
                    timer: null,
                    interval: 5000,
                    init() {
                        if (this.slides.length > 1) {
                            this.start();
                        }
                    },
                    start() {
                        if (this.slides.length <= 1) {
                            return;
                        }

                        this.stop();
                        this.timer = setInterval(() => this.advance(), this.interval);
                    },
                    stop() {
                        if (this.timer) {
                            clearInterval(this.timer);
                            this.timer = null;
                        }
                    },
                    advance() {
                        this.current = (this.current + 1) % this.slides.length;
                    },
                    next() {
                        if (this.slides.length <= 1) {
                            return;
                        }

                        this.stop();
                        this.current = (this.current + 1) % this.slides.length;
                        this.start();
                    },
                    prev() {
                        if (this.slides.length <= 1) {
                            return;
                        }

                        this.stop();
                        this.current = (this.current - 1 + this.slides.length) % this.slides.length;
                        this.start();
                    },
                    go(index) {
                        if (this.slides.length <= 1) {
                            this.current = index;
                            return;
                        }

                        this.stop();
                        this.current = index;
                        this.start();
                    }
                }"
                x-init="init()"
                @mouseenter="stop()"
                @mouseleave="start()"
                class="relative w-full h-[50vh] md:h-[70vh]"
            >
                <template x-for="(slide, index) in slides" :key="index">
                    <div
                        x-show="current === index"
                        x-transition.opacity.duration.700ms
                        class="absolute inset-0"
                    >
                        <img
                            :src="slide.image_url"
                            :alt="slide.title ?? `Slide ${index + 1}`"
                            class="h-full w-full object-cover"
                            loading="lazy"
                        >
                        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>
                        <div class="absolute inset-x-0 bottom-0 px-6 pb-16 pt-20 md:px-16 md:pb-20 text-white space-y-4 max-w-5xl">
                            <p x-show="slide.subtitle" x-text="slide.subtitle" class="text-sm md:text-base text-neutral-200 max-w-xl"></p>
                            <h2 x-show="slide.title" x-text="slide.title" class="text-3xl md:text-5xl font-bold leading-tight"></h2>
                            <div x-show="slide.link_url && slide.link_label">
                                <a
                                    :href="slide.link_url"
                                    class="inline-flex items-center rounded-full bg-[color:var(--brand-primary)] px-5 py-2 text-sm font-semibold uppercase tracking-wide text-[color:var(--brand-primary-contrast)] shadow-lg transition hover:bg-[color:var(--brand-secondary)]"
                                >
                                    <span x-text="slide.link_label"></span>
                                </a>
                            </div>
                        </div>
                    </div>
                </template>

                <template x-if="slides.length > 1">
                    <div class="pointer-events-none absolute inset-y-0 left-0 right-0 flex items-center justify-between px-4">
                        <button
                            type="button"
                            class="pointer-events-auto hidden h-12 w-12 items-center justify-center rounded-full bg-white/80 text-neutral-800 shadow-lg transition hover:bg-white md:flex"
                            @click.prevent="prev()"
                            x-transition.opacity
                            aria-label="Sebelumnya"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-5 w-5">
                                <path fill-rule="evenodd" d="M12.79 3.22a.75.75 0 0 1 0 1.06L8.06 9l4.73 4.72a.75.75 0 1 1-1.06 1.06l-5.25-5.25a.75.75 0 0 1 0-1.06l5.25-5.25a.75.75 0 0 1 1.06 0Z" clip-rule="evenodd" />
                            </svg>
                        </button>

                        <button
                            type="button"
                            class="pointer-events-auto hidden h-12 w-12 items-center justify-center rounded-full bg-white/80 text-neutral-800 shadow-lg transition hover:bg-white md:flex"
                            @click.prevent="next()"
                            x-transition.opacity
                            aria-label="Berikutnya"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-5 w-5">
                                <path fill-rule="evenodd" d="M7.21 3.22a.75.75 0 0 1 1.06 0l5.25 5.25a.75.75 0 0 1 0 1.06l-5.25 5.25a.75.75 0 0 1-1.06-1.06L11.94 9 7.21 4.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                </template>

                <div class="absolute bottom-10 left-1/2 -translate-x-1/2 flex space-x-3" x-show="slides.length > 1">
                    <template x-for="(slide, index) in slides" :key="`dot-${index}`">
                        <button
                            type="button"
                            class="h-2 w-10 rounded-full transition"
                            :class="current === index ? 'bg-white' : 'bg-white/40'"
                            @click.prevent="go(index)"
                        ></button>
                    </template>
                </div>
            </div>
        </section>
    @endif

    @if($customButtons->isNotEmpty())
        <section class="w-full bg-[color:var(--brand-primary)] {{ $heroSlides->isNotEmpty() ? '-mt-14 md:-mt-16' : 'mt-12' }}">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-10">
                <div class="flex flex-col md:flex-row divide-y divide-white/20 md:divide-y-0 md:divide-x md:divide-white/20 rounded-3xl bg-white/5 backdrop-blur px-4 md:px-0">
                    @foreach($customButtons as $button)
                        <a
                            href="{{ $button['url'] }}"
                            class="group flex items-center justify-center px-6 py-6 text-center text-sm md:text-base font-semibold uppercase tracking-wide text-white md:flex-1 transition hover:bg-white/10"
                        >
                            <span class="block">{{ $button['label'] }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-16">
        <div class="flex items-center justify-between gap-4 mb-6">
            <h3 class="text-2xl font-bold text-neutral-900">Berita Terbaru</h3>
            <a href="{{ route('posts.index') }}" class="text-sm font-semibold text-[color:var(--brand-primary-contrast)] md:text-[color:var(--brand-primary)] hover:text-[color:var(--brand-secondary)]">Lihat semua</a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach($latestPosts as $post)
                <article class="group overflow-hidden rounded-2xl border border-neutral-200 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-lg">
                    <a href="{{ $post->permalink }}" class="block">
                        <div class="aspect-[16/9] overflow-hidden">
                            @php
                                $cardImage = $variantUrl($post->thumbnail, 'middle', 'images/example-middle.webp');
                            @endphp
                            <img src="{{ $cardImage }}" alt="{{ $post->title }}" class="h-full w-full object-cover transition duration-700 group-hover:scale-105" loading="lazy">
                        </div>
                    </a>
                    <div class="p-5 space-y-3">
                        @if($post->primary_category)
                            <a href="{{ route('category.show', $post->primary_category->slug) }}" class="text-xs font-semibold uppercase tracking-wide text-[color:var(--brand-primary)]">
                                {{ $post->primary_category->name }}
                            </a>
                        @endif
                        <h4 class="text-xl font-semibold leading-tight text-neutral-900">
                            <a href="{{ $post->permalink }}" class="hover:text-[color:var(--brand-primary)]">{{ $post->title }}</a>
                        </h4>
                        <div class="text-xs text-neutral-500">
                            {{ $post->published_at?->translatedFormat('d M Y') }}
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    </section>

    @if($tabSections->isNotEmpty())
        <section class="mt-16 bg-[color:var(--brand-primary)]">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
                <div
                    x-data="{
                        tabs: @js($tabSections),
                        active: 0,
                        setTab(index) {
                            this.active = index;
                        },
                    }"
                    class="rounded-3xl border border-white/20 bg-white/10 shadow-lg overflow-hidden flex flex-col backdrop-blur text-white"
                >
                    <div class="flex flex-wrap gap-2 border-b border-white/15 bg-transparent px-4 py-4 md:px-6">
                        <template x-for="(tab, index) in tabs" :key="index">
                            <button
                                type="button"
                                class="rounded-full border px-4 py-2 text-sm font-semibold transition"
                                :class="active === index
                                    ? 'border-white bg-white/20 text-white shadow'
                                    : 'border-white/40 bg-white/10 text-white/80 hover:bg-white/20 hover:text-white'"
                                @click="setTab(index)"
                            >
                                <span x-text="tab.title"></span>
                            </button>
                        </template>
                    </div>

                    <div class="flex-1 overflow-y-auto p-5 md:p-8 max-h-[600px]">
                        <template x-for="(tab, index) in tabs" :key="`panel-${index}`">
                            <div
                                x-show="active === index"
                                x-transition.opacity
                                class="prose max-w-none prose-invert"
                                x-html="tab.content"
                            ></div>
                        </template>
                    </div>
                </div>
            </div>
        </section>
    @endif

    @if($managementTeam->isNotEmpty())
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-20">
            <div class="mb-6">
                <h3 class="text-2xl font-bold text-neutral-900">Susunan Pengurus</h3>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($managementTeam as $member)
                    <div class="flex flex-col items-center rounded-2xl border border-neutral-200 bg-white p-6 text-center shadow-sm transition hover:-translate-y-1 hover:shadow-lg">
                        <div class="relative mb-4 h-32 w-32 overflow-hidden rounded-full border-4 border-[color:var(--brand-border)] shadow-inner">
                            @if($member['photo_url'])
                                <img src="{{ $member['photo_url'] }}" alt="{{ $member['name'] }}" class="h-full w-full object-cover" loading="lazy">
                            @else
                                <div class="flex h-full w-full items-center justify-center bg-[color:var(--brand-surface)] text-2xl font-semibold text-[color:var(--brand-primary)]">
                                    {{ Str::of($member['name'])->trim()->substr(0, 2)->upper() }}
                                </div>
                            @endif
                        </div>
                        <div class="space-y-1">
                            <h4 class="text-lg font-semibold text-neutral-900">{{ $member['name'] }}</h4>
                            <p class="text-sm font-medium uppercase tracking-wide text-[color:var(--brand-primary)]">{{ $member['position'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    @if($categorySections->isNotEmpty())
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-20 mb-20">
            <div class="mb-6">
                <h3 class="text-2xl font-bold text-[color:var(--brand-primary)]">Berita Berdasarkan Kategori</h3>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                @foreach($categorySections as $section)
                    <div class="flex h-full flex-col border border-[color:var(--brand-primary)] bg-[color:var(--brand-primary)] p-6 shadow-lg">
                        <div class="flex items-center justify-between gap-2">
                            <h4 class="text-xl font-semibold text-[color:var(--brand-primary-contrast)]">{{ $section['title'] }}</h4>
                            <a href="{{ $section['more_url'] }}" class="text-xs font-semibold uppercase tracking-wide text-[color:var(--brand-accent)] hover:text-[color:var(--brand-secondary)]">Lihat semua</a>
                        </div>
                        <div class="mt-5 space-y-4">
                            @foreach($section['posts'] as $post)
                                <div class="flex gap-4">
                                    <a href="{{ $post->permalink }}" class="block h-20 w-28 flex-shrink-0 overflow-hidden border border-white/30">
                                        @php
                                            $thumbImage = $variantUrl($post->thumbnail, 'small', 'images/example-small.webp');
                                        @endphp
                                        <img src="{{ $thumbImage }}" alt="{{ $post->title }}" class="h-full w-full object-cover transition duration-500 hover:scale-105" loading="lazy">
                                    </a>
                                    <div class="flex flex-1 flex-col justify-between">
                                        <h5 class="text-sm font-semibold leading-snug text-[color:var(--brand-primary-contrast)]">
                                            <a href="{{ $post->permalink }}" class="text-white hover:text-[color:var(--brand-accent)]">{{ $post->title }}</a>
                                        </h5>
                                        <div class="text-xs text-white/70">
                                            {{ $post->published_at?->translatedFormat('d M Y') }}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif
@endsection

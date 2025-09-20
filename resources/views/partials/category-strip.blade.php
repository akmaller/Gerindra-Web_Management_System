@props(['cats' => collect()])

@if($cats->isNotEmpty())
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div>
            <div class="overflow-x-auto scrollbar-thin scrollbar-thumb-neutral-300 scrollbar-track-transparent">
                <div class="flex gap-2 overflow-x-auto py-2 scroll-px-4 snap-x snap-mandatory [-webkit-overflow-scrolling:touch]">
                    @foreach($cats as $i => $cat)
                        <a href="{{ route('category.show', $cat->slug) }}"
                           class="inline-flex snap-start items-center gap-2 px-3.5 py-2 rounded-full text-white text-sm font-medium hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2"
                           style="background-color: chocolate;"
                           title="{{ $cat->name }}">
                           {{ $cat->name }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endif
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <x-ad-slot location="header" />
</div>

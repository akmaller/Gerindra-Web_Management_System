@props(['items', 'level' => 0])

@foreach($items as $item)
    @php $hasChildren = $item->children->isNotEmpty(); @endphp

    @if($level === 0)
        <div
            class="relative"
            x-data="{ open: false }"
            @mouseenter="open = true"
            @mouseleave="open = false"
            @focusin="open = true"
            @focusout="open = false"
        >
            <a
                href="{{ $item->resolved_url }}"
                class="inline-flex items-center gap-1 text-sm font-bold text-[color:var(--brand-primary)] hover:text-[color:var(--brand-secondary)]"
                @if($item->open_in_new_tab) target="_blank" rel="noopener" @endif
            >
                <span>{{ $item->label }}</span>

                @if($hasChildren)
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 10.94l3.71-3.71a.75.75 0 1 1 1.06 1.06l-4.24 4.24a.75.75 0 0 1-1.06 0L5.21 8.29a.75.75 0 0 1 .02-1.08Z" clip-rule="evenodd" />
                    </svg>
                @endif
            </a>

            @if($hasChildren)
                <div
                    x-cloak
                    x-show="open"
                    x-transition.origin-top.duration.150ms
                    class="absolute left-1/2 top-full z-50 -translate-x-1/2"
                >
                    <div class="pt-3">
                        <div class="min-w-[220px] rounded-xl bg-white shadow-lg ring-1 ring-neutral-200 py-2">
                            <div class="flex flex-col gap-1 px-2">
                                @include('partials.navigation.desktop-items', ['items' => $item->children, 'level' => $level + 1])
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @else
        <div
            class="relative"
            x-data="{ open: false }"
            @mouseenter="open = true"
            @mouseleave="open = false"
        >
            <a
                href="{{ $item->resolved_url }}"
                class="flex items-center justify-between gap-2 px-3 py-2 text-sm text-[color:var(--brand-primary)] hover:bg-neutral-50"
                @if($item->open_in_new_tab) target="_blank" rel="noopener" @endif
            >
                <span>{{ $item->label }}</span>

                @if($hasChildren)
                    <svg class="h-3.5 w-3.5 text-neutral-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M7.21 4.22a.75.75 0 0 1 1.06 0l5.25 5.25a.75.75 0 0 1 0 1.06l-5.25 5.25a.75.75 0 1 1-1.06-1.06L11.44 10 7.21 5.78a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
                    </svg>
                @endif
            </a>

            @if($hasChildren)
                <div
                    x-cloak
                    x-show="open"
                    x-transition.origin-left.duration.150ms
                    class="absolute left-full top-0 z-50"
                >
                    <div class="pl-3">
                        <div class="min-w-[200px] rounded-xl bg-white shadow-lg ring-1 ring-neutral-200 py-2">
                            <div class="flex flex-col gap-1 px-2">
                                @include('partials.navigation.desktop-items', ['items' => $item->children, 'level' => $level + 1])
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @endif
@endforeach

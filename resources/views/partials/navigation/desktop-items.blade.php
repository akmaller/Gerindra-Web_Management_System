@props(['items', 'level' => 0])

@foreach($items as $item)
    @php $hasChildren = $item->children->isNotEmpty(); @endphp

    @if($level === 0)
        <div class="relative group">
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
                <div class="absolute left-0 top-full z-50 hidden min-w-[220px] rounded-xl bg-white shadow-lg ring-1 ring-neutral-200 py-2 group-hover:block">
                    @include('partials.navigation.desktop-items', ['items' => $item->children, 'level' => $level + 1])
                </div>
            @endif
        </div>
    @else
        <div class="relative group">
            <a
                href="{{ $item->resolved_url }}"
                class="flex items-center justify-between gap-2 px-3 py-2 text-sm text-[color:var(--brand-primary)] hover:bg-neutral-50"
                @if($item->open_in_new_tab) target="_blank" rel="noopener" @endif
            >
                <span>{{ $item->label }}</span>

                @if($hasChildren)
                    <svg class="h-3.5 w-3.5 text-neutral-400 group-hover:text-neutral-600" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M7.21 4.22a.75.75 0 0 1 1.06 0l5.25 5.25a.75.75 0 0 1 0 1.06l-5.25 5.25a.75.75 0 1 1-1.06-1.06L11.44 10 7.21 5.78a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
                    </svg>
                @endif
            </a>

            @if($hasChildren)
                <div class="absolute left-full top-0 z-50 ml-2 hidden min-w-[200px] rounded-xl bg-white shadow-lg ring-1 ring-neutral-200 py-2 group-hover:block">
                    @include('partials.navigation.desktop-items', ['items' => $item->children, 'level' => $level + 1])
                </div>
            @endif
        </div>
    @endif
@endforeach

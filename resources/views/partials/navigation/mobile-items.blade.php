@props(['items', 'level' => 0])

<ul @class([
    'space-y-1',
    'mt-2 pl-4 border-l border-neutral-200' => $level > 0,
])>
    @foreach($items as $item)
        @php $hasChildren = $item->children->isNotEmpty(); @endphp

        <li x-data="{ open: false }">
            <div class="flex items-center gap-2">
                <a
                    href="{{ $item->resolved_url }}"
                    class="flex-1 rounded-lg px-3 py-2 text-sm font-medium text-[color:var(--brand-primary)] hover:bg-neutral-100"
                    @if($item->open_in_new_tab) target="_blank" rel="noopener" @endif
                    @click="$dispatch('close-menu')"
                >
                    {{ $item->label }}
                </a>

                @if($hasChildren)
                    <button
                        type="button"
                        class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-neutral-100 text-neutral-600"
                        @click.prevent="open = !open"
                    >
                        <svg :class="{ 'rotate-180': open }" class="h-4 w-4 transition-transform" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 10.94l3.71-3.71a.75.75 0 1 1 1.06 1.06l-4.24 4.24a.75.75 0 0 1-1.06 0L5.21 8.29a.75.75 0 0 1 .02-1.08Z" clip-rule="evenodd" />
                        </svg>
                    </button>
                @endif
            </div>

            @if($hasChildren)
                <div x-show="open" x-collapse class="mt-1">
                    @include('partials.navigation.mobile-items', ['items' => $item->children, 'level' => $level + 1])
                </div>
            @endif
        </li>
    @endforeach
</ul>

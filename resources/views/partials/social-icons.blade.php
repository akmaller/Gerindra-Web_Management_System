{{-- resources/views/partials/social-icons.blade.php --}}
@props(['profile', 'size' => 20])

<div class="flex items-center gap-3">
    @if(!empty($profile->facebook))
        <a href="{{ $profile->facebook }}" target="_blank" rel="noopener"
           class="text-neutral-500 hover:text-blue-600">
            <x-bi-facebook :class="'w-['.$size.'px] h-['.$size.'px]'" />
        </a>
    @endif

    @if(!empty($profile->twitter))
        <a href="{{ $profile->twitter }}" target="_blank" rel="noopener"
           class="text-neutral-500 hover:text-sky-500">
            <x-bi-twitter-x :class="'w-['.$size.'px] h-['.$size.'px]'" />
        </a>
    @endif

    @if(!empty($profile->instagram))
        <a href="{{ $profile->instagram }}" target="_blank" rel="noopener"
           class="text-neutral-500 hover:text-pink-600">
            <x-bi-instagram :class="'w-['.$size.'px] h-['.$size.'px]'" />
        </a>
    @endif

    @if(!empty($profile->youtube))
        <a href="{{ $profile->youtube }}" target="_blank" rel="noopener"
           class="text-neutral-500 hover:text-red-600">
            <x-bi-youtube :class="'w-['.$size.'px] h-['.$size.'px]'" />
        </a>
    @endif

    @if(!empty($profile->tiktok))
        <a href="{{ $profile->tiktok }}" target="_blank" rel="noopener"
           class="text-neutral-500 hover:text-black">
            <x-bi-tiktok :class="'w-['.$size.'px] h-['.$size.'px]'" />
        </a>
    @endif

    @if(!empty($profile->telegram))
        <a href="{{ $profile->telegram }}" target="_blank" rel="noopener"
           class="text-neutral-500 hover:text-sky-500">
            <x-bi-telegram :class="'w-['.$size.'px] h-['.$size.'px]'" />
        </a>
    @endif

    @if(!empty($profile->whatsapp))
        <a href="https://wa.me/{{ $profile->whatsapp }}" target="_blank" rel="noopener"
           class="text-neutral-500 hover:text-green-500">
            <x-bi-whatsapp :class="'w-['.$size.'px] h-['.$size.'px]'" />
        </a>
    @endif
</div>

{{-- resources/views/partials/social-icons.blade.php --}}
@props(['profile', 'size' => 20])

<div class="flex items-center gap-3">
    @if(!empty($profile->facebook))
        <a aria-label="Facebook (tab baru)" href="{{ $profile->facebook }}" target="_blank" rel="noopener"
           class="text-neutral-500 hover:text-blue-600">
            <x-bi-facebook :style="'width:'.$size.'px;height:'.$size.'px'" aria-hidden="true" />
        </a>
    @endif

    @if(!empty($profile->twitter))
        <a aria-label="X / Twitter (tab baru)" href="{{ $profile->twitter }}" target="_blank" rel="noopener"
           class="text-neutral-500 hover:text-sky-500">
            <x-bi-twitter-x :style="'width:'.$size.'px;height:'.$size.'px'" aria-hidden="true" />
        </a>
    @endif

    @if(!empty($profile->instagram))
        <a aria-label="Instagram (tab baru)" href="{{ $profile->instagram }}" target="_blank" rel="noopener"
           class="text-neutral-500 hover:text-pink-600">
            <x-bi-instagram :style="'width:'.$size.'px;height:'.$size.'px'" aria-hidden="true" />
        </a>
    @endif

    @if(!empty($profile->youtube))
        <a aria-label="YouTube (tab baru)" href="{{ $profile->youtube }}" target="_blank" rel="noopener"
           class="text-neutral-500 hover:text-red-600">
            <x-bi-youtube :style="'width:'.$size.'px;height:'.$size.'px'" aria-hidden="true" />
        </a>
    @endif

    @if(!empty($profile->tiktok))
        <a aria-label="TikTok (tab baru)" href="{{ $profile->tiktok }}" target="_blank" rel="noopener"
           class="text-neutral-500 hover:text-black">
            <x-bi-tiktok :style="'width:'.$size.'px;height:'.$size.'px'" aria-hidden="true" />
        </a>
    @endif

    @if(!empty($profile->telegram))
        <a aria-label="Telegram (tab baru)" href="{{ $profile->telegram }}" target="_blank" rel="noopener"
           class="text-neutral-500 hover:text-sky-500">
            <x-bi-telegram :style="'width:'.$size.'px;height:'.$size.'px'" aria-hidden="true" />
        </a>
    @endif

    @if(!empty($profile->whatsapp))
        <a aria-label="WhatsApp (tab baru)" href="https://wa.me/{{ $profile->whatsapp }}" target="_blank" rel="noopener"
           class="text-neutral-500 hover:text-green-500">
            <x-bi-whatsapp :style="'width:'.$size.'px;height:'.$size.'px'" aria-hidden="true" />
        </a>
    @endif
</div>

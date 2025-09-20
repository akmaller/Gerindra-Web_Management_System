@php
    $heading = 'Gerindra-Web_Management System';
@endphp

<x-filament-widgets::widget>
    <x-filament::section
        icon="heroicon-o-information-circle"
        :heading="$heading"
    >
        <div class="text-sm text-neutral-400">
        </div>

        <div class="mt-4">
            <x-filament::button
                tag="a"
                href=" {{ route('home') }}"
                target="_blank"
                icon="heroicon-o-home"
            >
                Kunjungi Web
            </x-filament::button>
            <x-filament::button
                tag="a"
                href="https://www.rcknet.id/Gerindra-Web_Management_System_Documentation.html#konfigurasi"
                target="_blank"
                icon="heroicon-o-book-open"
            >
                Dokumentasi
            </x-filament::button>

            <x-filament::button
                tag="a"
                href="https://github.com/akmaller/Gerindra-Web_Management_System"
                target="_blank"
                icon="heroicon-o-information-circle"
            >
                Github
            </x-filament::button>

        </div>
    </x-filament::section>
</x-filament-widgets::widget>

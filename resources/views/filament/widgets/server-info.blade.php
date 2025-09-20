<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
        Server details
    </x-slot>

    <x-slot name="description">
        Informasi server ditampilkan disini.
    </x-slot>


            @foreach ($items as $item)
    <x-filament::fieldset>
        <x-slot name="label">
                        @if(!empty($item['meta']))
                                {{ $item['meta'] }}
                        @endif
    </x-slot>
                        {{ $item['value'] }}
</x-filament::fieldset>
            @endforeach
    </x-filament::section>
</x-filament-widgets::widget>

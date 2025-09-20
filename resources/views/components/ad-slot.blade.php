@props([
    'location' => null, // 'header', 'sidebar', 'below_post', 'footer'
])

@php
    // cache biar hemat query
    $ads = cache()->remember('ad_settings_first', 60, fn () => \App\Models\AdSetting::query()->first());

    $enabled = false;
    $html = null;

    if ($ads && $location) {
        switch ($location) {
            case 'header':
                $enabled = (bool) $ads->header_enabled;
                $html    = $ads->header_html;
                break;
            case 'sidebar':
                $enabled = (bool) $ads->sidebar_enabled;
                $html    = $ads->sidebar_html;
                break;
            case 'below_post':
                $enabled = (bool) $ads->below_post_enabled;
                $html    = $ads->below_post_html;
                break;
            case 'footer':
                $enabled = (bool) $ads->footer_enabled;
                $html    = $ads->footer_html;
                break;
        }
    }
@endphp

@if($enabled && $html)
    <div class="ad-slot ad-{{ $location }}">
        {!! $html !!}
    </div>
@endif

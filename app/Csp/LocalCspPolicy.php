<?php

namespace App\Csp;

use Spatie\Csp\Policies\Basic;

class LocalCspPolicy extends Basic
{
    public function configure()
    {
        parent::configure();

        // izinkan preview blob:, request XHR Livewire, dan websocket dev
        $this
            ->addDirective('img-src', ["'self'", 'data:', 'blob:', 'https:'])
            ->addDirective('media-src', ['blob:'])
            ->addDirective('connect-src', ["'self'", 'ws:', 'http:', 'https:'])
            ->addDirective('script-src', ["'self'", "'unsafe-inline'", "'unsafe-eval'"])
            ->addDirective('style-src', ["'self'", "'unsafe-inline'"]);
    }
}

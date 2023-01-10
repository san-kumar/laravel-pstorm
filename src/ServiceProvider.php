<?php

namespace San\Pstorm;

use Barryvdh\Debugbar\LaravelDebugbar;
use Illuminate\Support\ServiceProvider as LaravelServiceProvider;
use San\Pstorm\events\CaptureViews;

class ServiceProvider extends LaravelServiceProvider {
    /**
     * {@inheritdoc}
     */
    public function register() {
    }

    /**
     * {@inheritdoc}
     */
    public function boot() {
        if (config('app.debug') && config('debugbar.inject') && class_exists(LaravelDebugbar::class) && (php_sapi_name() !== 'cli')) {
            app('Illuminate\Contracts\Http\Kernel')->pushMiddleware(DebugbarTab::class);
        }
    }


    /**
     * {@inheritdoc}
     */
    public function provides() {
        return [];
    }
}

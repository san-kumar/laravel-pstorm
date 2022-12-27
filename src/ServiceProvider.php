<?php

namespace San\Pstorm;

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
        new CaptureViews();
    }

    /**
     * {@inheritdoc}
     */
    public function provides() {
        return [
        ];
    }
}

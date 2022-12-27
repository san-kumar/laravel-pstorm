<?php

namespace San\Pstorm\events;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;

class CaptureViews {
    protected $views = [];

    public function __construct() {
        Event::listen('composing*', function ($eventName, array $data) {
            $viewName = $data[0]->getName();
            $this->views[] = ['name' => $viewName, 'url' => $this->toPstormUrl(\View::getFinder()->find($viewName))];

            if ($viewName === 'layouts.app') {
                $script = Blade::render(file_get_contents(__DIR__ . '/stubs/script.blade.php'), ['views' => $this->getViews(), 'controller' => $this->getController()]);

                echo $script;
            }
        });
    }

    protected function getViews() {
        return $this->views;
    }

    protected function getController() {
        $controller = \Route::current()->getAction('controller');
        $controller = explode('@', $controller);
        $controller = $controller[0];
        $controller = str_replace('App\\Http\\Controllers\\', '', $controller);
        $controllerPath = app_path('Http/Controllers/' . $controller . '.php');

        return ['name' => $controller, 'url' => $this->toPstormUrl($controllerPath)];
    }

    protected function toPstormUrl($path) {
        return sprintf('phpstorm://open?file=%s', $path);
    }
}
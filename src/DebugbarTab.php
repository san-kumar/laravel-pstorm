<?php

namespace San\Pstorm;

use Barryvdh\Debugbar\LaravelDebugbar;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;

class DebugbarTab {
    protected $views = [];

    public function __construct() {
        $this->viewSet = []; // Auxiliary set for fast lookup

        Event::listen('composing*', function ($eventName, array $data) {
            $viewName = $data[0]->getName();

            // Check if the view is already in the set and process it only once
            if (!isset($this->viewSet[$viewName])) {
                // Add the view to the views array
                $this->views[] = ['name' => $viewName, 'url' => $this->toPstormUrl(\View::getFinder()->find($viewName))];
                // Mark the view as added in the set
                $this->viewSet[$viewName] = true;
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
        // Normalize the path to use the platform-specific directory separator
        $normalizedPath = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $path);
        // Escape the path for windows
        $escapedPath = str_replace('\\', '\\\\', $normalizedPath);

        return sprintf('phpstorm://open?file=%s&line=0', $escapedPath);
    }

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request                                                                          $request
     * @param \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse) $next
     *
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle($request, Closure $next, $guard = NULL) {
        /** @var Response $response */
        $response = $next($request);

        if ($response instanceof Response && !app()->runningUnitTests() && str_contains($response->headers->get('Content-Type'), 'text/html')) {
            $content = $response->getContent();
            if (($head = mb_strpos($content, '</head>')) !== FALSE) {
                $script = Blade::render(file_get_contents(__DIR__ . '/stubs/script.blade.php'), ['views' => $this->getViews(), 'controller' => $this->getController()]);
                $response->setContent(mb_substr($content, 0, $head) . $script . mb_substr($content, $head));
            }
        }

        return $response;
    }
}

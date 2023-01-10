<?php

namespace San\Pstorm;

use Barryvdh\Debugbar\LaravelDebugbar;
use Closure;
use Illuminate\Http\Request;
use \Illuminate\Http\Response;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;

class DebugbarTab {
    protected $views = [];

    public function __construct() {
        Event::listen('composing*', function ($eventName, array $data) {
            $viewName = $data[0]->getName();
            $this->views[] = ['name' => $viewName, 'url' => $this->toPstormUrl(\View::getFinder()->find($viewName))];
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

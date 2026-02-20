<?php

namespace LaravelGenerators\PostmanGenerator\Services;

use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Route as RouteFacade;
use LaravelGenerators\PostmanGenerator\Contracts\RouteScanner as Contract;
use LaravelGenerators\PostmanGenerator\DataObjects\RouteData;

class RouteScanner implements Contract
{
    public function scan(): array
    {
        $routes = RouteFacade::getRoutes();
        $apiRoutes = [];

        $targetGroup = config('postman-generator.route_groups', 'api');
        $excludeRoutes = config('postman-generator.exclude_routes', []);
        $excludeMiddlewares = config('postman-generator.exclude_middlewares', []);

        foreach ($routes as $route) {
            if (!$this->shouldInclude($route, $targetGroup, $excludeRoutes, $excludeMiddlewares)) {
                continue;
            }

            $apiRoutes[] = new RouteData(
                method: strtoupper($route->methods()[0]),
                uri: $route->uri(),
                controllerClass: $this->getControllerClass($route),
                actionMethod: $this->getActionMethod($route),
                middlewares: $route->gatherMiddleware(),
                routeName: $route->getName(),
                parameterNames: $route->parameterNames(),
            );
        }

        return $apiRoutes;
    }

    protected function shouldInclude(Route $route, string $targetGroup, array $excludeRoutes, array $excludeMiddlewares): bool
    {
        $methods = $route->methods();
        $primaryMethod = strtoupper($methods[0]);
        
        if (in_array($primaryMethod, ['HEAD', 'OPTIONS'])) {
            return false;
        }

        $middlewares = $route->gatherMiddleware();
        
        // Check if it belongs to the target group
        if (!in_array($targetGroup, $middlewares)) {
            return false;
        }

        // Check if it only has excluded middlewares
        if (!empty($excludeMiddlewares)) {
            $otherMiddlewares = array_diff($middlewares, $excludeMiddlewares);
            if (empty($otherMiddlewares)) {
                return false;
            }
        }

        // Check path exclusion
        $uri = $route->uri();
        foreach ($excludeRoutes as $pattern) {
            if (fnmatch($pattern, $uri)) {
                return false;
            }
        }

        return true;
    }

    protected function getControllerClass(Route $route): ?string
    {
        $action = $route->getAction();
        if (isset($action['controller']) && is_string($action['controller'])) {
            return explode('@', $action['controller'])[0];
        }
        return null;
    }

    protected function getActionMethod(Route $route): ?string
    {
        $action = $route->getAction();
        if (isset($action['controller']) && is_string($action['controller'])) {
            $parts = explode('@', $action['controller']);
            return isset($parts[1]) ? $parts[1] : null;
        }
        return null;
    }
}

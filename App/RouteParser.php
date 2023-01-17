<?php

declare(strict_types=1);

namespace Phphleb\Sitemapgen\App;


class RouteParser
{
    private array $routeNames;

    public function __construct()
    {
        $path = HLEB_STORAGE_CACHE_ROUTES_DIRECTORY . DIRECTORY_SEPARATOR . 'routes.txt';
        if (!file_exists($path)) {
            throw new \ErrorException('Route cache file not found at address: ' . $path . '! run command `php console --update-routes-cache`');
        }
        $routes = json_decode(file_get_contents($path), true) ?? [];

        $this->routeNames = $this->getNames($routes);
    }

    public function get() {
       return $this->routeNames;
    }

    private function getNames(array $routes)
    {
        $result = [];
        
        foreach ($routes as $route) {
            if (empty($route['number'])) {
                continue;
            }
            $name = [];
            foreach ($route['actions'] as $action) {
                if (isset($action['name'])) {
                    $name = $action['name'];
                }
            }

            if ($name) {
                if (isset($route["method_type_name"]) || isset($route["type"])) {
                    if ((is_string($route["method_type_name"]) && $route["method_type_name"] === "get") ||
                        (is_array($route["method_type_name"]) && in_array("get", $route["method_type_name"])) ||
                        (isset($route["type"]) && is_array($route["type"]) && in_array("get", $route["type"]))
                    ) {
                        $result[] = $name;
                    }
                }
            }
        }
        return $result;
    }
}


<?php

    /**
    * Sourcerer Components
    * Router Component
    *
    * @author        Sidney Tougeiro
    * @copyright     Copyright(c) 2020, sidneytougeiro.com
    * @license       MIT
    */

    namespace Sourcerer;

    use Sourcerer\Contracts\SingletonTrait;
    use Sourcerer\Router\Exceptions\RouterException;


    final class Router
    {
        use SingletonTrait;


        private static
            $base = "/",
            $prefix = "",
            $routes = [],
            $pageNotFound = null,
            $shortcuts = [
                ':any'   => '(.*)',
                ':id'    => '([0-9]+)',
                ':name'  => '([a-zA-Z]+)',
                ':slug'  => '([a-z0-9\-]+)',
                ':hexa'  => '([A-F0-9]+)',
                ':year'  => '([0-9]{4})',
                ':month' => '([0][1-9]|[1][0-2])',
                ':day'   => '([0][1-9]|[12][0-9]|[3][01])'
            ];


        public static function getBase(): string
        {
            return self::$base;
        }

        public static function setBase(string $_base): void
        {
            $_base = '/'. $_base .'/';
            $_base = self::sanitize($_base);

            self::$base = $_base;
        }

        public static function getShortcut(string $_shortcut): string
        {
            $_shortcut = preg_replace('/\:{2,}/i', ':', ':'.$_shortcut);

            if (array_key_exists($_shortcut, self::$shortcuts)) {
                return self::$shortcuts[$_shortcut];
            }

            return "";
        }

        public static function getShortcuts(): array
        {
            return self::$shortcuts;
        }

        public static function upsertShortcut(string $_shortcut, string $_regex): void
        {
            $_shortcut = preg_replace('/\:{2,}/i', ':', ':'.$_shortcut);

            self::$shortcuts[$_shortcut] = $_regex;
        }

        public static function clearShortcuts(): void
        {
            self::$shortcuts = [];
        }

        public static function getURI(): string
        {
            $uri = $_SERVER['REQUEST_URI'];
            $url = parse_url($uri, PHP_URL_PATH);

            if (false === $url) {
                return $uri;
            }

            $url = explode('/', $url);

            if (isset($url[0]) && $url[0] == '') {
                array_shift($url);
            }

            $last = count($url) - 1;

            if (isset($url[$last]) && $url[$last] == '') {
                array_pop($url);
            }

            return '/'. implode('/', $url) . (count($url) ? '/' : '');
        }

        public static function add(string $_route, callable $_callback): void
        {
            $route = self::$base . self::$prefix .'/'. $_route .'/';
            $route = self::sanitize($route);
            $route = self::compile($route);

            if (array_key_exists($route, self::$routes)) {
                throw new RouterException("Route '$_route' already exists. The route cannot be overwritten.");
            }

            self::$routes[$route] = $_callback;
        }

        public static function group(string $_group, callable $_callback): void
        {
            $temp = self::$prefix;

            self::$prefix .= '/'. $_group .'/';
            self::$prefix = self::sanitize(self::$prefix);

            $_callback();

            self::$prefix = $temp;
        }

        public static function pageNotFound(callable $_callback): void
        {
            self::$pageNotFound = $_callback;
        }

        public static function show404(): void
        {
            if ( ! is_callable(self::$pageNotFound)) {
                throw new RouterException("'Router::pageNotFound' method is not defined.");
            }

            call_user_func_array(self::$pageNotFound, [self::getURI()]);
        }

        public static function listen(): bool
        {
            $uri = self::getURI();
            $match = false;

            foreach (self::$routes as $route => $callback) {
                if (preg_match($route, $uri, $variables)) {
                    $match = true;

                    array_shift($variables);
                    call_user_func_array($callback, array_values($variables));

                    break;
                }
            }

            if ( ! $match) {
                self::show404();
            }

            return $match;
        }


        private static function sanitize(string $_route): string
        {
            $_route = preg_replace('/\s/i', '', $_route);
            $_route = preg_replace('/\/{2,}/i', '/', $_route);

            return $_route;
        }

        private static function compile(string $_route): string
        {
            foreach (self::$shortcuts as $shortcut => $regex) {
                $_route = str_replace($shortcut, $regex, $_route);
            }

            $_route = '/^' . str_replace('/', '\/', $_route) . '$/';

            return $_route;
        }
    }
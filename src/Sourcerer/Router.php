<?php

    /**
    * Sourcerer Component
    * Router
    *
    * @author        Sidney Tougeiro
    * @copyright     Copyright(c) 2020, sidneytougeiro.com
    * @license       MIT
    */

    namespace Sourcerer;


    use Sourcerer\Contracts\SingletonTrait;

    final class Router
    {
        use SingletonTrait;

        public static
            $_BASE = "/",
            $_ROUTES = [],
            $_SHORTCUTS = [
                ':any'   => '(.*)',
                ':id'    => '([0-9]+)',
                ':name'  => '([a-zA-Z]+)',
                ':slug'  => '([a-z0-9\-]+)',
                ':hexa'  => '([A-F0-9]+)',
                ':year'  => '([0-9]{4})',
                ':month' => '([0][1-9]|[1][0-2])',
                ':day'   => '([0][1-9]|[12][0-9]|[3][01])'
            ];

        private static
            $_PREFIX = "",
            $_PAGENOTFOUND = null;


        public static function getBase() {
            return self::$_BASE;
        }

        public static function setBase(string $_base) {
            $_base = '/'. $_base .'/';
            $_base = self::sanitize($_base);

            self::$_BASE = $_base;
        }

        public static function getShortcuts() {
            return self::$_SHORTCUTS;
        }

        public static function upsertShortcut(string $_shortcut, string $_regex) {
            $_shortcut = preg_replace('/\:{2,}/i', ':', ':'.$_shortcut);

            self::$_SHORTCUTS[$_shortcut] = $_regex;
        }

        public static function getURI() {
            $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            $uri = trim($uri);
            $uri = explode('/', $uri);

            if (isset($uri[0]) && $uri[0] == '')
                array_shift($uri);

            $last = count($uri) - 1;
            if (isset($uri[$last]) && $uri[$last] == '')
                array_pop($uri);

            return '/'. implode('/', $uri) . (count($uri) ? '/' : '');
        }

        public static function add(string $_route, callable $_callback) {
            $_route = self::$_BASE . self::$_PREFIX .'/'. $_route .'/';
            $_route = self::sanitize($_route);
            $_route = self::shortcut2regex($_route);

            if ( ! array_key_exists($_route, self::$_ROUTES))
                self::$_ROUTES[$_route] = $_callback;
        }

        public static function group(string $_group, callable $_callback) {
            $_group = '/'. $_group .'/';
            $_group = self::sanitize($_group);

            $temp = self::$_PREFIX;
            self::$_PREFIX = $_group;

            $_callback();

            self::$_PREFIX = $temp;
        }

        public static function pageNotFound(callable $_callback) {
            self::$_PAGENOTFOUND = $_callback;
        }

        public static function listen() {
            $uri = self::getURI();
            $match = false;

            foreach (self::$_ROUTES as $route => $callback)
                if (preg_match($route, $uri, $variables)) {
                    $match = true;

                    array_shift($variables);
                    call_user_func_array($callback, array_values($variables));

                    break;
                }

            if ( ! $match)
                self::show404();

            return $match;
        }


        private static function show404() {
            $uri = self::getURI();

            if (is_callable(self::$_PAGENOTFOUND))
                call_user_func_array(self::$_PAGENOTFOUND, [$uri]);
        }

        private static function sanitize($_route) {
            $_route = preg_replace('/\s/i', '', $_route);
            $_route = preg_replace('/\/{2,}/i', '/', $_route);

            return $_route;
        }

        private static function shortcut2regex($_route) {
            foreach (self::$_SHORTCUTS as $shortcut => $regex)
                $_route = str_replace($shortcut, $regex, $_route);

            $_route = '/^' . str_replace('/', '\/', $_route) . '$/';

            return $_route;
        }
    }

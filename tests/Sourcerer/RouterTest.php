<?php

    use PHPUnit\Framework\TestCase;
    use Sourcerer\Router;

    final class RouterTest extends TestCase
    {
        public function test_BASE_IsStringAndBeginsRoot(): void {
            $this->assertIsString(Router::$_BASE);
            $this->assertEquals(
                '/',
                Router::$_BASE
            );
        }

        public function test_ROUTES_IsArrayAndBeginsEmpty(): void {
            $this->assertIsArray(Router::$_ROUTES);
            $this->assertEmpty(Router::$_ROUTES);
        }

        public function test_SHORTCUTS_IsArrayAndBeginsNotEmpty(): void {
            $this->assertIsArray(Router::$_SHORTCUTS);
            $this->assertCount(
                8,
                Router::$_SHORTCUTS
            );
        }


        public function testGetBase(): void {
            $this->assertEquals(
                Router::$_BASE,
                Router::getBase()
            );
        }

        public function testSetBase(): void {
            Router::setBase('');
            $this->assertEquals(
                '/',
                Router::getBase()
            );

            Router::setBase('/ /   /');
            $this->assertEquals(
                '/',
                Router::getBase()
            );

            Router::setBase('api');
            $this->assertEquals(
                '/api/',
                Router::getBase()
            );

            Router::setBase('////user//');
            $this->assertEquals(
                '/user/',
                Router::getBase()
            );
        }

        public function testGetShortcuts(): void {
            $this->assertEquals(
                Router::$_SHORTCUTS,
                Router::getShortcuts()
            );
        }

        public function testUpsertShortcuts(): void {
            $count = count(Router::$_SHORTCUTS);
            $shortcut = ':hour';
            $regex = '([012][0-9])';

            Router::upsertShortcut($shortcut, $regex);
            $count++;

            $this->assertCount(
                $count,
                Router::$_SHORTCUTS
            );
            $this->assertArrayHasKey(
                $shortcut,
                Router::$_SHORTCUTS
            );
            $this->assertEquals(
                Router::$_SHORTCUTS[$shortcut],
                $regex
            );

            /*
            ** If try to add the same shortcut, will be updated.
            ** upsert: (update or insert)
            */

            $regex = '([0]|[1-9]|[1][0-9]|[2][0-3])';
            Router::upsertShortcut($shortcut, $regex);

            $this->assertCount(
                $count,
                Router::$_SHORTCUTS
            );
            $this->assertArrayHasKey(
                $shortcut,
                Router::$_SHORTCUTS
            );
            $this->assertEquals(
                Router::$_SHORTCUTS[$shortcut],
                $regex
            );
        }

        public function testGetURI(): void {
            Router::setBase('/');

            $_SERVER['REQUEST_URI'] = 'http://www.example.com/user/15/?groupBy=test#title';
            $this->assertEquals(
                '/user/15/',
                Router::getURI()
            );

            $_SERVER['REQUEST_URI'] = 'http://www.example.com/user?groupBy=test';
            $this->assertEquals(
                '/user/',
                Router::getURI()
            );

            $_SERVER['REQUEST_URI'] = 'http://www.example.com//wrong/uri';
            $this->assertEquals(
                '//wrong/uri/',
                Router::getURI()
            );

            $_SERVER['REQUEST_URI'] = 'http://www.example.com//wrong/uri2/';
            $this->assertEquals(
                '//wrong/uri2/',
                Router::getURI()
            );

            $_SERVER['REQUEST_URI'] = 'http://www.example.com/wrong//uri3/';
            $this->assertEquals(
                '/wrong//uri3/',
                Router::getURI()
            );

            $_SERVER['REQUEST_URI'] = 'http://www.example.com/wrong/uri4//';
            $this->assertEquals(
                '/wrong/uri4//',
                Router::getURI()
            );
        }

        public function testAddRoute(): void {
            $count = count(Router::$_ROUTES);

            Router::add('/test', function() {
                echo "test";
            });
            $count++;

            $regex_route = '/^\/test\/$/';

            $this->assertCount(
                $count,
                Router::$_ROUTES
            );
            $this->assertArrayHasKey(
                $regex_route,
                Router::$_ROUTES
            );
            $this->assertIsObject(Router::$_ROUTES[$regex_route]);
            $this->assertIsCallable(Router::$_ROUTES[$regex_route]);

            /*
            ** If try to add the same route, will be ignored.
            */

            Router::add('/test', function() {
                echo "test";
            });

            $this->assertCount(
                $count,
                Router::$_ROUTES
            );
        }

        public function testGroup(): void {
            $count = count(Router::$_ROUTES);

            Router::group('/api/', function() {
                Router::add('/', function() {
                    echo "api";
                });

                Router::add('/test/', function() {
                    echo "api/test";
                });
            });
            $count += 2;

            $regex_route = [];
            $regex_route[] = '/^\/api\/$/';
            $regex_route[] = '/^\/api\/test\/$/';

            $this->assertCount(
                $count,
                Router::$_ROUTES
            );

            foreach ($regex_route as $route) {
                $this->assertArrayHasKey(
                    $route,
                    Router::$_ROUTES
                );
                $this->assertIsObject(Router::$_ROUTES[$route]);
                $this->assertIsCallable(Router::$_ROUTES[$route]);
            }
        }

        public function testListen(): void {
            Router::setBase('/');

            // Cleaning the routes purposely
            Router::$_ROUTES = [];

            Router::add('test', function() {
                echo "test";
            });

            Router::group('api', function() {

                Router::add('test', function() {
                    echo "api/test";
                });

            });


            $_SERVER['REQUEST_URI'] = 'http://www.example.com/test';

            $this->assertTrue(Router::listen());


            $_SERVER['REQUEST_URI'] = 'http://www.example.com/api';

            $this->assertFalse(Router::listen());


            $_SERVER['REQUEST_URI'] = 'http://www.example.com/api/test';

            $this->assertTrue(Router::listen());


            $_SERVER['REQUEST_URI'] = 'http://www.example.com/api/users';

            $this->assertFalse(Router::listen());
        }
    }
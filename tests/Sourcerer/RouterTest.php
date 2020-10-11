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

            $regex = '([0-9]|[1][0-9]|[2][0-3])';
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
            // Cleaning the routes purposely
            Router::$_ROUTES = [];

            $count = count(Router::$_ROUTES);


            Router::setBase('/');

            // Adding route
            Router::add('/test', function() {
                // echo "test";
            });

            // 1 route added
            $count += 1;

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
                // echo "test";
            });

            $this->assertCount(
                $count,
                Router::$_ROUTES
            );
        }

        public function testGroup(): void {
            // Cleaning the routes purposely
            Router::$_ROUTES = [];

            $count = count(Router::$_ROUTES);


            Router::setBase('/');

            // Adding routes
            Router::group('/api/', function() {

                Router::add('/', function() {
                    // echo "/api/";
                });

                Router::add('/test/', function() {
                    // echo "/api/test/";
                });

            });

            // 2 routes added
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

                // Deleting the verified route
                unset(Router::$_ROUTES[$route]);
            }

            // Verifying if there are no added extra routes
            $this->assertCount(
                0,
                Router::$_ROUTES
            );
        }

        public function testRecursiveGroup(): void {
            // Cleaning the routes purposely
            Router::$_ROUTES = [];

            $count = count(Router::$_ROUTES);


            Router::setBase('/');

            // Adding routes out of order purposely
            Router::add('/', function() {
                // echo "/";
            });

            Router::group('/api/', function() {

                Router::add('/', function() {
                    // echo "/api/";
                });

                Router::group('/admin/', function() {

                    Router::add('/', function() {
                        // echo "/api/admin/";
                    });

                    Router::add('/test/', function() {
                        // echo "/api/admin/test/";
                    });

                });

                Router::add('/test/', function() {
                    // echo "/api/test/";
                });

            });

            Router::add('/test/', function() {
                // echo "/test/";
            });

            // 6 routes added
            $count += 6;

            $regex_route = [];
            $regex_route[] = '/^\/$/';
            $regex_route[] = '/^\/api\/$/';
            $regex_route[] = '/^\/api\/admin\/$/';
            $regex_route[] = '/^\/api\/admin\/test\/$/';
            $regex_route[] = '/^\/api\/test\/$/';
            $regex_route[] = '/^\/test\/$/';

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

                // Deleting the verified route
                unset(Router::$_ROUTES[$route]);
            }

            // Verifying if there are no added extra routes
            $this->assertCount(
                0,
                Router::$_ROUTES
            );
        }

        public function testDynamicRoutes(): void {
            // Cleaning the routes purposely
            Router::$_ROUTES = [];

            $count = count(Router::$_ROUTES);


            Router::setBase('/');

            Router::add('/:name', function($name) {
                // echo "The name is ", $name;
            });

            Router::group('/api/', function() {

                Router::add('/:name', function($name) {
                    // echo "The API name is ", $name;
                });

            });

            Router::add('/blog/:year/:month/:slug', function($year, $month, $slug) {
                // echo $year, "/", $month, ": ", $slug;
            });

            // 3 routes added
            $count += 3;

            $regex_route = [];
            $regex_route[] = '/^\/([a-zA-Z]+)\/$/';
            $regex_route[] = '/^\/api\/([a-zA-Z]+)\/$/';
            $regex_route[] = '/^\/blog\/([0-9]{4})\/([0][1-9]|[1][0-2])\/([a-z0-9\-]+)\/$/';

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

                // Deleting the verified route
                unset(Router::$_ROUTES[$route]);
            }

            // Verifying if there are no added extra routes
            $this->assertCount(
                0,
                Router::$_ROUTES
            );
        }

        public function testListen(): void {
            // Cleaning the routes purposely
            Router::$_ROUTES = [];

            Router::setBase('/');

            Router::add('/test', function() {
                // echo "/test/";
            });

            Router::group('/api', function() {

                Router::add('/', function() {
                    // echo "/api/";
                });

                Router::add('/test', function() {
                    // echo "/api/test/";
                });

            });

            Router::add('/:name', function($name) {
                // echo "The name is ", $name;

                $this->assertEquals(
                    'stougeiro',
                    $name
                );
            });

            Router::add('/blog/:year/:month/:slug', function($year, $month, $slug) {
                // echo $year, "/", $month, ": ", $slug;

                $this->assertEquals(
                    '2020',
                    $year
                );

                $this->assertEquals(
                    '05',
                    $month
                );

                $this->assertContains(
                    $slug, [
                        'just-a-simple-test',
                        '4-reasons-for-meaning',
                        'reasons4meaning',
                        'simpletext'
                    ]
                );
            });


            $_SERVER['REQUEST_URI'] = 'http://www.example.com/test';
            $this->assertTrue(Router::listen());

            $_SERVER['REQUEST_URI'] = 'http://www.example.com/api';
            $this->assertTrue(Router::listen());

            $_SERVER['REQUEST_URI'] = 'http://www.example.com/api/test';
            $this->assertTrue(Router::listen());

            $_SERVER['REQUEST_URI'] = 'http://www.example.com/api/users';
            $this->assertFalse(Router::listen());

            $_SERVER['REQUEST_URI'] = 'http://www.example.com/stougeiro';
            $this->assertTrue(Router::listen());

            $_SERVER['REQUEST_URI'] = 'http://www.example.com/st07g31r0';
            $this->assertFalse(Router::listen());

            $_SERVER['REQUEST_URI'] = 'http://www.example.com/blog/2020/05/just-a-simple-test';
            $this->assertTrue(Router::listen());

            $_SERVER['REQUEST_URI'] = 'http://www.example.com/blog/2020/00/just-a-simple-test';
            $this->assertFalse(Router::listen());

            $_SERVER['REQUEST_URI'] = 'http://www.example.com/blog/2020/13/just-a-simple-test';
            $this->assertFalse(Router::listen());

            $_SERVER['REQUEST_URI'] = 'http://www.example.com/blog/2020/05/4-reasons-for-meaning';
            $this->assertTrue(Router::listen());

            $_SERVER['REQUEST_URI'] = 'http://www.example.com/blog/2020/05/reasons4meaning';
            $this->assertTrue(Router::listen());

            $_SERVER['REQUEST_URI'] = 'http://www.example.com/blog/2020/05/simpletext';
            $this->assertTrue(Router::listen());
        }
    }
<?php

    use PHPUnit\Framework\TestCase;
    use Sourcerer\Router;


    final class RouterTest extends TestCase
    {
        public function testGetBase(): void
        {
            $this->assertIsString(Router::getBase());

            $this->assertEquals(
                '/',
                Router::getBase()
            );
        }

        public function testSetBase(): void
        {
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

        public function testGetShortcut(): void
        {
            $this->assertEquals(
                '(.*)',
                Router::getShortcut('any')
            );

            $this->assertEquals(
                '([A-F0-9]+)',
                Router::getShortcut('hexa')
            );

            $this->assertEquals(
                '',
                Router::getShortcut('hour')
            );
        }

        public function testGetShortcuts(): void
        {
            $this->assertEquals(
                [
                    ':any'   => '(.*)',
                    ':id'    => '([0-9]+)',
                    ':name'  => '([a-zA-Z]+)',
                    ':slug'  => '([a-z0-9\-]+)',
                    ':hexa'  => '([A-F0-9]+)',
                    ':year'  => '([0-9]{4})',
                    ':month' => '([0][1-9]|[1][0-2])',
                    ':day'   => '([0][1-9]|[12][0-9]|[3][01])'
                ],
                Router::getShortcuts()
            );
        }

        public function testUpsertShortcuts(): void
        {
            $count = count(Router::getShortcuts());

            $this->assertCount(
                $count,
                Router::getShortcuts()
            );

            $shortcut = ':hour';
            $regex = '([012][0-9])';

            Router::upsertShortcut($shortcut, $regex);

            $count++;

            $this->assertCount(
                $count,
                Router::getShortcuts()
            );

            $this->assertArrayHasKey(
                $shortcut,
                Router::getShortcuts()
            );

            $this->assertEquals(
                $regex,
                Router::getShortcut($shortcut)
            );

            /*
            ** If try to add the same shortcut, will be updated.
            ** upsert: (update or insert)
            */

            $regex = '([0-9]|[1][0-9]|[2][0-3])';

            Router::upsertShortcut($shortcut, $regex);

            $this->assertCount(
                $count,
                Router::getShortcuts()
            );

            $this->assertArrayHasKey(
                $shortcut,
                Router::getShortcuts()
            );

            $this->assertEquals(
                $regex,
                Router::getShortcut($shortcut)
            );
        }

        public function testClearShortcuts(): void
        {
            Router::clearShortcuts();

            $count = count(Router::getShortcuts());

            $this->assertEquals(
                $count,
                0
            );

            $this->assertCount(
                $count,
                Router::getShortcuts()
            );
        }

        public function testGetURI(): void
        {
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

            $_SERVER['REQUEST_URI'] = 'http://www.example.com/';

            $this->assertEquals(
                '/',
                Router::getURI()
            );

            $_SERVER['REQUEST_URI'] = 'http://www.example.com//';

            $this->assertEquals(
                '//',
                Router::getURI()
            );

            $_SERVER['REQUEST_URI'] = 'http://www.example.com//wrong/uri/';

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

        public function testAddRoute(): void
        {
            Router::setBase('/');

            Router::add('/tar/', function() {
                // echo "/tar/";
            });

            $_SERVER['REQUEST_URI'] = 'http://www.example.com/tar/';
            $match = Router::listen();

            $this->assertTrue($match);
        }

        public function testAddRouteWithBase(): void
        {
            Router::setBase('/wb/');

            Router::add('/tar/', function() {
                // echo "/wb/tar/";
            });

            $_SERVER['REQUEST_URI'] = 'http://www.example.com/wb/tar/';
            $match = Router::listen();

            $this->assertTrue($match);

        }

        public function testGroup(): void
        {
            Router::setBase('/');

            Router::group('/tg/', function() {

                Router::add('/', function() {
                    // echo "/tg/";
                });

                Router::add('/test/', function() {
                    // echo "/tg/test/";
                });

            });

            $_SERVER['REQUEST_URI'] = 'http://www.example.com/tg/';
            $match = Router::listen();

            $this->assertTrue($match);

            $_SERVER['REQUEST_URI'] = 'http://www.example.com/tg/test/';
            $match = Router::listen();

            $this->assertTrue($match);
        }

        public function testGroupWithBase(): void
        {
            Router::setBase('/wb/');

            Router::group('/tg/', function() {

                Router::add('/', function() {
                    // echo "/wb/tg/";
                });

                Router::add('/test/', function() {
                    // echo "/wb/tg/test/";
                });

            });

            $_SERVER['REQUEST_URI'] = 'http://www.example.com/wb/tg/';
            $match = Router::listen();

            $this->assertTrue($match);

            $_SERVER['REQUEST_URI'] = 'http://www.example.com/wb/tg/test/';
            $match = Router::listen();

            $this->assertTrue($match);
        }

        public function testRecursiveGroup(): void
        {
            Router::setBase('/');

            Router::group('/trg/', function() {

                Router::add('/', function() {
                    // echo "/trg/";
                });

                Router::group('/admin/', function() {

                    Router::add('/', function() {
                        // echo "/trg/admin/";
                    });

                    Router::add('/test/', function() {
                        // echo "/trg/admin/test/";
                    });

                });

                Router::add('/test/', function() {
                    // echo "/trg/test/";
                });

            });

            $_SERVER['REQUEST_URI'] = 'http://www.example.com/trg/';
            $match = Router::listen();

            $this->assertTrue($match);

            $_SERVER['REQUEST_URI'] = 'http://www.example.com/trg/admin/';
            $match = Router::listen();

            $this->assertTrue($match);

            $_SERVER['REQUEST_URI'] = 'http://www.example.com/trg/admin/test/';
            $match = Router::listen();

            $this->assertTrue($match);

            $_SERVER['REQUEST_URI'] = 'http://www.example.com/trg/test/';
            $match = Router::listen();

            $this->assertTrue($match);
        }

        public function testRecursiveGroupWithBase(): void
        {
            Router::setBase('/wb/');

            Router::group('/trg/', function() {

                Router::add('/', function() {
                    // echo "/wb/trg/";
                });

                Router::group('/admin/', function() {

                    Router::add('/', function() {
                        // echo "/wb/trg/admin/";
                    });

                    Router::add('/test/', function() {
                        // echo "/wb/trg/admin/test/";
                    });

                });

                Router::add('/test/', function() {
                    // echo "/wb/trg/test/";
                });

            });

            $_SERVER['REQUEST_URI'] = 'http://www.example.com/trg/';
            $match = Router::listen();

            $this->assertTrue($match);

            $_SERVER['REQUEST_URI'] = 'http://www.example.com/trg/admin/';
            $match = Router::listen();

            $this->assertTrue($match);

            $_SERVER['REQUEST_URI'] = 'http://www.example.com/trg/admin/test/';
            $match = Router::listen();

            $this->assertTrue($match);

            $_SERVER['REQUEST_URI'] = 'http://www.example.com/trg/test/';
            $match = Router::listen();

            $this->assertTrue($match);
        }

        public function testDynamicRoutes(): void
        {
            Router::setBase('/');

            Router::upsertShortcut(':name', '([a-zA-Z]+)');
            Router::upsertShortcut(':year', '([0-9]{4})');
            Router::upsertShortcut(':month', '([0][1-9]|[1][0-2])');
            Router::upsertShortcut(':slug', '([a-z0-9\-]+)');

            Router::add('/:year', function($year) {
                // echo "The year is ", $year;
            });

            Router::group('/tdr/', function() {

                Router::add('/:name', function($name) {
                    // echo "The API name is ", $name;
                });

            });

            Router::add('/blog/:year/:month/:slug', function($year, $month, $slug) {
                // echo $year, "/", $month, ": ", $slug;
            });

            $_SERVER['REQUEST_URI'] = 'http://www.example.com/2020/';
            $match = Router::listen();

            $this->assertTrue($match);

            $_SERVER['REQUEST_URI'] = 'http://www.example.com/tdr/sourcerer/';
            $match = Router::listen();

            $this->assertTrue($match);

            $_SERVER['REQUEST_URI'] = 'http://www.example.com/blog/2020/10/just-a-little-test-in-2020-10';
            $match = Router::listen();

            $this->assertTrue($match);
        }

        public function testDynamicRoutesWithBase(): void
        {
            Router::setBase('/wb/');

            Router::upsertShortcut(':name', '([a-zA-Z]+)');
            Router::upsertShortcut(':year', '([0-9]{4})');
            Router::upsertShortcut(':month', '([0][1-9]|[1][0-2])');
            Router::upsertShortcut(':slug', '([a-z0-9\-]+)');

            Router::add('/:year', function($year) {
                // echo "The year is ", $year;
            });

            Router::group('/tdr/', function() {

                Router::add('/:name', function($name) {
                    // echo "The API name is ", $name;
                });

            });

            Router::add('/blog/:year/:month/:slug', function($year, $month, $slug) {
                // echo $year, "/", $month, ": ", $slug;
            });

            $_SERVER['REQUEST_URI'] = 'http://www.example.com/wb/2020/';
            $match = Router::listen();

            $this->assertTrue($match);

            $_SERVER['REQUEST_URI'] = 'http://www.example.com/wb/tdr/sourcerer/';
            $match = Router::listen();

            $this->assertTrue($match);

            $_SERVER['REQUEST_URI'] = 'http://www.example.com/wb/blog/2020/10/just-a-little-test-in-2020-10';
            $match = Router::listen();

            $this->assertTrue($match);
        }

        public function testListen(): void
        {
            Router::setBase('/app/');

            Router::add('/', function() {
                // echo "/";
            });

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

            Router::add('/user/:name', function($name) {
                // echo "/user/", $name;

                $this->assertEquals(
                    'stougeiro',
                    $name
                );
            });

            Router::add('/blog/:year/:month/:slug', function($year, $month, $slug) {
                // echo $year, "/", $month, "/", $slug;

                $this->assertEquals(
                    $year,
                    '2020'
                );

                $this->assertContains(
                    $month,
                    [
                        '05',
                        '07',
                        '09',
                        '11',
                    ]
                );

                $this->assertContains(
                    $slug,
                    [
                        'just-a-simple-test',
                        '4-reasons-for-meaning',
                        'reasons4meaning',
                        'simpletext'
                    ]
                );
            });


            $_SERVER['REQUEST_URI'] = 'http://www.example.com/app/';
            $match = Router::listen();

            $this->assertTrue($match);

            $_SERVER['REQUEST_URI'] = 'http://www.example.com/app/test/';
            $match = Router::listen();

            $this->assertTrue($match);

            $_SERVER['REQUEST_URI'] = 'http://www.example.com/app/api/';
            $match = Router::listen();

            $this->assertTrue($match);

            $_SERVER['REQUEST_URI'] = 'http://www.example.com/app/api/test';
            $match = Router::listen();

            $this->assertTrue($match);

            $_SERVER['REQUEST_URI'] = 'http://www.example.com/app/user/stougeiro/';
            $match = Router::listen();

            $this->assertTrue($match);

            $_SERVER['REQUEST_URI'] = 'http://www.example.com/blog/2020/05/just-a-simple-test';
            $match = Router::listen();

            $this->assertTrue($match);

            $_SERVER['REQUEST_URI'] = 'http://www.example.com/blog/2020/07/4-reasons-for-meaning';
            $match = Router::listen();

            $this->assertTrue($match);

            $_SERVER['REQUEST_URI'] = 'http://www.example.com/blog/2020/09/reasons4meaning';
            $match = Router::listen();

            $this->assertTrue($match);

            $_SERVER['REQUEST_URI'] = 'http://www.example.com/blog/2020/11/simpletext';
            $match = Router::listen();

            $this->assertTrue($match);
        }
    }
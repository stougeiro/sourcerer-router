# Sourcerer\Router

Provides a small but simple and customizable routing class for PHP 7.1+ applications.
It utilizes REGEX and PHP's anonymous functions to create a lightweight and fast routing system.
The router supports dynamic path parameters and 404 special route.
The codebase is very small and very easy to understand.

This routing class is performance oriented.
Therefore, I believe that checking the HTTP verbs is not the responsibility of this routing class.



### Package dependencies

```
"require": {
    "php": "^7.1",
    "stougeiro/sourcerer-contracts": "^1.0"
}
```



## Features

- Performance oriented simple routing system.
- Custom regular expression routing.
- Dynamic routing using URL segments as parameters.




## Getting started

You need PHP >= 7.1.0 to use `Sourcerer\Router`.

### Installation

Easy to install with Composer:

```sh
$ composer require stougeiro/sourcerer-router
```

### Friendly URL

Create simple .htaccess file on your root directory if you're using Apache with mod_rewrite enabled.

```apache
Options -Indexes

ErrorDocument 403 /

RewriteEngine On

RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d

RewriteRule ^(.*)$ index.php [L]
```

If you're using NGINX, setup your server section as following:

```nginx
autoindex off;

error_page 403 /;

location / {
    if (!-e $request_filename){
        rewrite ^(.*)$ /index.php break;
    }
}
```

### Usage

```php
<?php

    // Requiring the Composer autoload
    require "./vendor/autoload.php";

    // Using the class
    use Sourcerer\Router;


    // Add the first route
    Router::add('/', function() {
        echo "Hello World!";
    });

    // Adding a dynamic route
    Router::add('/:name', function($name) {
        echo "Hello ", ucfirst($name), "!";
    });

    // Listening the URI to match routes
    Router::listen();
```



## How to custom

### Using a different basepath

If your application lives in a subfolder (e.g. `/app`) set the basepath with this method:

```php
<?php

    require "./vendor/autoload.php";

    use Sourcerer\Router;


    Router::setBase('/app');

    Router::add('/', function() {
        echo "/app/";
    });

    Router::listen();
```

### Update or insert a REGEX shortcut for dynamic routing

`Sourcerer\Router` have a predefined REGEX shortcuts for dynamic routing.

```php
Array
(
    [:any] => (.*)
    [:id] => ([0-9]+)
    [:name] => ([a-zA-Z]+)
    [:slug] => ([a-z0-9\-]+)
    [:hexa] => ([A-F0-9]+)
    [:year] => ([0-9]{4})
    [:month] => ([0][1-9]|[1][0-2])
    [:day] => ([0][1-9]|[12][0-9]|[3][01])
)
```

If you need to update any definition or add a new shortcut, use the `upsertShortcut` method:

```php
<?php

    require "./vendor/autoload.php";

    use Sourcerer\Router;


    /*
    ** Cleaning the SHORTCUTS variable for example purpose.
    */
    Router::clearShortcuts();


    /*
    ** print_r(Router::getShortcuts());
    **
    ** Array
    ** (
    ** )
    */

    /*
    ** If the shortcut NOT exists, the method will insert.
    */
    Router::upsertShortcut(':any', '(.*)');
    Router::upsertShortcut(':id', '([0-9]+)');

    /*
    ** print_r(Router::getShortcuts());
    **
    ** Array
    ** (
    **     [:any] => (.*)
    **     [:id] => ([0-9]+)
    ** )
    */

    /*
    ** If the shortcut EXISTS, the method will update.
    */
    Router::upsertShortcut(':id', '([0-9]{2})');

    /*
    ** print_r(Router::getShortcuts());
    **
    ** Array
    ** (
    **     [:any] => (.*)
    **     [:id] => ([0-9]{2})
    ** )
    */


    Router::setBase('/');

    Router::add('/users', function() {
        echo "/users";
    });

    Router::add('/user/:id', function($id) {
        echo "/user/id", "<br />";
        echo "id: ", $id;
    });

    Router::listen();
```

### Using dynamic routes

```php
<?php

    require "./vendor/autoload.php";

    use Sourcerer\Router;


    Router::add('/:name', function($name) {
        echo "Hello ", $name, "!";
    });

    Router::add('/blog/:year/:month/:slug', function($year, $month, $slug) {
        echo "Blog Post (", $year, "/", $month, ") <br />";
        echo "<h1>", ucfirst(str_replace('-', ' ', $slug)), "</h1>";
    });

    Router::listen();
```

### Using grouped routes

```php
<?php

    require "./vendor/autoload.php";

    use Sourcerer\Router;


    Router::add('/', function() {
        echo "Hello World!";
    });

    // Defining the grouped route
    Router::group('/api', function() {

        // This method accepts recursive use
        Router::group('/v1', function() {

            Router::add('/', function() {
                http_response_code(200);
                header('HTTP/1.1 200 OK');
                header('Content-Type: application/json');

                echo json_encode([
                    'data' => [
                        'type' => 'text',
                        'message' => 'Hello APIv1!'
                    ]
                ]);
            });

        });

        Router::group('/v2', function() {
            // Under development
        });

    });

    Router::listen();
```

### Defining the 404 special route

The `listen()` method will match the URI and return the defined route.  
If not, it will execute the `pageNotFound()` method.

```php
<?php

    require "./vendor/autoload.php";

    use Sourcerer\Router;


    Router::add('/', function() {
        echo "Hello World!";
    });

    Router::pageNotFound( function() {
        echo "Error 404: Page not found!";
    });

    Router::listen();
```

By default, this method receives the URI of the route not found as a parameter.

```php
<?php

    require "./vendor/autoload.php";

    use Sourcerer\Router;


    Router::add('/', function() {
        echo "Hello World!";
    });

    Router::pageNotFound( function($uri) {
        echo "Error 404: Page not found!";
        echo "(", $uri, ")";
    });

    Router::listen();
```



## Something does not work?

- Don't forget to set the correct basepath in the application and in your .htaccess file.
- Make sure the mod_rewrite is enable in your APACHE settings.
- Define the 404 special route.



## Contributing

Contributions are **welcome** and will be fully credited.  
We accept contributions via Pull Requests.

### Pull Requests

- **Document any change in behaviour** - Make sure the README and any other relevant documentation are kept up-to-date.
- **One pull request per feature** - If you want to do more than one thing, send multiple pull requests.
- **Send coherent history** - Make sure each individual commit in your pull request is meaningful. If you had to make multiple intermediate commits while developing, please squash them before submitting.

### Contributors

- S. Tougeiro ([stougeiro](https://github.com/stougeiro))



## Support

 If you discover any security related issues or had an improvement suggestion, please use the issue tracker.



## License

This project is licensed under the MIT License.  
See [LICENSE](https://github.com/stougeiro/sourcerer-router/blob/master/LICENSE) for further information.
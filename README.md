# router
A small router library for php - [download](#downloads)

## How to use
Create a main php file called `app.php` (if you want to use other name, go to `.htaccess` and replace `app.php` with your main script dir). Your first lines should look like:
```php
require_once 'lib/router/router.php';
```

Start by adding new routes. The `route()` function takes a route path as a string and a callback function.
```php
route('/', function() {
  echo 'Hello world!';
})
```

Templates are rendered using Twig, which has really similar syntax to jinja2 (a Python library)

In `templates/` folder create your views and display them using `render()` function which takes template path and optionally an arguments array
```php
route('/', function() {
  render('index.html');
})
```

If you want to get params from your paths, write a route path like `/somepath/:name/otherpath/:language/:age/` and get the values with the callback function:
```php
route('/somepath/:name/otherpath/:language/:age/', function($args) {
  print_r($args);
})
```

Error handling: Create a `error.html` template in `templates/` dir. Use `{{code}}` to display the error code

Throw an error by calling the `error()` function that takes an error code

Redirect to other page by calling the `redirect()` function which takes a new path 

## Downloads
[router-master.zip](https://minhaskamal.github.io/DownGit/#/home?url=https://github.com/morswin22/router)

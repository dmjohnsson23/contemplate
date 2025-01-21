Contemplate
===========

"Contemplate" is short for "Controllers and Templates". It is somewhat more than a mere templating library, but a great deal less than a full web framework. It is primarily intended as a progressive enhancement library to gradually bring a standalone (meaning "sans-framework") legacy PHP application into the modern era. It is designed to require minimal refactoring to being using, to allow legacy PHP code to continue to operate beside it. It aims to be flexible enough to allow you to gradually work toward a place similar to what you'd get with a full PHP framework--PSR7, routing, and so forth--without actually *requiring* any of these things to begin using.

This is an extended fork of [Plates](https://github.com/thephpleague/plates) that adds support for additional functionality, such as:

* Loading controllers (or, any arbitrary function or object) using the same loader used to load templates.
* Loading static resources (but *not* public web assets...for now) using the same loader used to load templates.
* Name-based associations between templates, controllers, and resources.
* An optional extension adding integration with [Twig](https://twig.symfony.com)

Plates is a very handy little project, but doesn't appear to be receiving new features or responding to pull requests. Contemplate is a drop-in replacement for Plates; you should be able to simply change the import, and everything should "just work" so long as you don't have any custom template functions whose names interfere with new methods added by Contemplate. You can then add additional features over time using Contemplate's extended functionality.

Loading controllers and resources via the template loader system has a few advantages:

* Organization: it's nice to have all the code for a request live close together in your project structure.
* Extensibility and modularity: Using Themes, you can override the functionality of certain controllers or resources for a specific theme, but fall back to the base theme if an override does not exist.

## Documentation

The original documentation for Plates can be found at [platesphp.com](https://platesphp.com/). Additional documentation for Contemplate-specific features will be forthcoming, but a brief overview of the differences can be found below.

First, `Template` has been generalized to `Resolvable`. `Resolvable` can be used as a base class for loading other types of resources (controllers or static resources). `Template` is a subclass of `Resolvable`.

Second, many methods now take an optional `type` parameter. This parameter is a string used to specify which type of resource to resolve. For example, you may have a directory structure like this for your templates and other resources:

```
app
+-- index.get.php
+-- index.tpl.php
+-- some_form.get.php
+-- some_form.post.php
+-- some_form.tpl.php
+-- some_article.get.php
+-- some_article.tpl.php
+-- some_article.md
```

This structure represents a theoretical site with three pages: index, some_form, and some_article. However, each of these pages has multiple different resolvable resources associated with it. All three have a template (`x.tpl.php`) and a controller for GET requests (`x.get.php`). The form has an additional controller for POST requests (`some_form.post.php`), and the article contains some content in a markdown document (`some_article.md`).

You can associate these different types of resolvable objects with different file extensions:

```php
// The default file extension for unknown or unspecified types
$engine->setFileExtension('php');
// File extensions for special built-in types
// Using these types is optional, but provides some additional features for convenience
$engine->setFileExtension('tpl.php', Resolvable::TYPE_TEMPLATE);
$engine->setFileExtension('get.php', Resolvable::TYPE_CONTROLLER_HTTP_GET);
$engine->setFileExtension('post.php', Resolvable::TYPE_CONTROLLER_HTTP_POST);
// Custom extensions for custom types
// These names are arbitrary--you can use whatever makes sense for your application
$engine->setFileExtension('md', 'markdown');
```

Then, when interacting with the engine to resolve objects, you can specify the relevant type either implicitly or explicitly to resolve different objects:

```php
// Templates implicitly use `Resolvable::TYPE_TEMPLATE`
$engine->make('index');
// Controllers (a resolvable type unique to Contemplate) can be called implicitly using the detected HTTP method
$engine->autoCallHttpController('some_form');
// Or explicitly using a built-in type
$engine->callController('some_form', Resolvable::TYPE_CONTROLLER_HTTP_GET);
// Or explicitly using a custom type
$engine->callController('some_form', 'delegated_function');
// Custom types are specified via the type parameter
$engine->path('some_article', 'markdown');
// `import` can be used to get values returned from arbitrary PHP scripts
$form_handler_object = $engine->import('some_form', type:'form_handler_object');
```

### Controllers

As mentioned previously, the main thing Contemplate adds on top of Plates is the concept of loadable controllers that can live beside your templates. These controllers are PHP files which return a callable, which will be executed by the engine. You can best think of Contemplate controllers as a sort of auto-loaded function. For example:

```php
// my_controller.get.php
use \Psr\Http\Message\ServerRequestInterface;
/**
 * This is the controller for my_page and does xxx and yyy.
 */
return function(ServerRequestInterface $request){
    // ... do some logic here ...
    // Note: `renderAssociated` returns a string, not a PSR7 `Response` object. If you require a 
    // `Response` object, you'll need to handle the conversion yourself.
    return $this->renderAssociated([
        'var1'=>$var1,
        'var2'=>$var2,
    ]);
}
```

Which could be called from like so, perhaps from your router:
```php
$response = $engine->autoCallHttpController('my_controller', [$request]);
// Here, `$response` is whatever you returned from the controller above
```

Controllers can delegate to other controllers via `$this->delegate`:

```php
// my_controller.get.php
use \Psr\Http\Message\ServerRequestInterface;
return function(ServerRequestInterface $request){
    // Load and call authentication_handler.delegate.php
    $this->delegate('authentication_handler', [$request]);
    // ... do remaining controller logic here ...
}
```

Alternatively, you can define controller decorators that can be applied to controllers:

```php
// MyDecorator.php
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_FUNCTION | Attribute::IS_REPEATABLE)]
class MyDecorator extends ControllerDecorator
{
    public function __invoke($target, $next, $args)
    {
        echo 'do this before the controller runs';
        $result = $next($args); // Run the controller
        echo 'do this after the controller runs';
        return $result;
    }
}
```

Then in the controller:

```php
// my_controller.get.php
use \Psr\Http\Message\ServerRequestInterface;
#[MyDecorator]
return function(ServerRequestInterface $request){
    // ... do controller logic here ...
}
```

Decorators wrap the controller and can be used for common tasks, such as converting the response into a `Response` object, or checking authentication headers.

### Twig Interop

This library also introduces an optional extension you can use to bridge Contemplate with Twig, meaning you can use both template systems simultaneously. The bridge is very small and light-weight, opting for simplicity and low overhead over full interop (e.g., a Twig template can't extend a Plates template and vice-versa; though they can include one another and share data).

The syntax of Twig is nicer than the regular regular PHP code used in Contemplate/Plates, and has a lot of niceties like auto-escaping. But the native Plates-style templates do have the advantages of being more flexible for advanced user cases, and easier to convert to from legacy plain-PHP code. It can be nice to have both available.

```php
$toothpick = new DMJohnson\Contemplate\Extension\ContemplateTwig\ContemplateTwig([
    // twig options go here
    'cache' => '/path/to/cache/',
    'autoescape' => 'html',
]);
$engine->loadExtension($toothpick);
// After loading the extension, you can use the ContemplateTwig instance as a proxy for the Twig Environment
$toothpick->addGlobal('CONFIG', $yourConfig);
// You can also expose Plates extension functions as functions/filters in Twig
// (These functions may not work if they rely on access to the Template object, however)
$toothpick->passthruFunction('uri')
$toothpick->passthruFilter('asset')
```

Then, in a controller or template, do this:

```php
$this->renderTwig('profile', ['name'=>'Gath', 'location'=>'Foo']);
// Or, you can render the Twig template directly from outside a template or controller like this:
$toothpick->render('profile', ['name'=>'Gath', 'location'=>'Foo']);
```

The Contemplate `Engine` object is also exposed to Twig templates.

```twig
{{ contemplate.render('template_name') | raw }}
```

Data added via `$engine->addData` will automatically be exposed to Twig templates in addition to Contemplate templates.

### Using with a router

While Contemplate does not include a router, it is designed with the intent of being usable with one. Below is a minimal example of how it could be used with [Aura router](https://github.com/auraphp/Aura.Router/tree/3.x). Any router could likely be used, but one that allows arbitrary values for the handler or controller is best. This allows the Contemplate controller name to be used as the handler for the router, and then loaded via the Contemplate engine as normal.

```php
use Aura\Router\RouterContainer;
use DMJohnson\Contemplate\Engine;
use GuzzleHttp\Psr7\ServerRequest;
// Setup the Contemplate engine
$engine = new Engine(...);
// Load routes
$routerContainer = new RouterContainer();
$map = $routerContainer->getMap();
// We use the controller name as the "handler" for each route
$map->get('home', '', 'pages/home');
$map->get('help', '/help', 'pages/help');
$map->get('about', '/about', 'pages/about_us');
// Expose the route generator to templates
$engine->addData(['routes'=>$routerContainer->getGenerator()]);
// Create a request object and match a route
$matcher = $routerContainer->getMatcher();
$request = ServerRequest::fromGlobals();
$route = $matcher->match($request);
// If we failed to match a route
if ($route === false) {
    $failedRoute = $matcher->getFailedRoute();
    // Code to render error page goes here
    die();
}
// If we reach this point, we successfully matched a route
// Now we can dispatch using the `autoCallHttpController` method
$engine->autoCallHttpController(
    $route->handler, // Route handler from above (the controller name)
    [$request, $route->attributes], // Parameters to pass to controller (request + url parameters)
    $request->getMethod(), // Use the HTTP method to determine which controller to call
);
```

## License

The MIT License (MIT). Please see [License File](https://github.com/thephpleague/plates/blob/master/LICENSE) for more information.

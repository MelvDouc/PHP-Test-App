## Application

A class whose single instance will ultimately hold and dispatch all of the app's routes as well as connect to the database.

## Database

A class instantiated as property of the `Application` instance. Provides CRUD-style methods for interacting with an SQL database.

## Router

A router object is meant to match controller actions with URLs and HTTP method.

```php
$authRouter = new Router("/auth");
$auth->addMiddleware([AuthController::class, "mainMiddleware"]);
$auth->addRoute("app-signin", [
  "path" => "signin",
  "middleware" => [AuthController::class, "signInMiddleware"]
  "methods" => [
    "GET" => [AuthController::class, "signIn_GET"],
    "POST" => [
      [AuthController::class, "signInPOSTMiddleware"],
      [AuthController::class, "signIn_POST"]
    ]
  ]
]);
```

* The first and only argument of the `Router` constructor is the **basePath**. It's the path every route will be prefixed with. Thus, the path to the sign-in page will be "/auth/signin" instead of just "/signin".
* **Middleware** can be defined in no less than three places:
  1. At the router level, meaning the middleware functions will run before each and every controller action.
  2. At the route level, meaning the middleware functions will run before action specific to the route.
  3. At the method level, the middleware functions will only run for the relevant action and HTTP method.
* The first argument of `addRoute` is the **route name**, which is a string the route can be referred to as in redirections and templates. This is useful not only for describing the route's role but also if one decides to the change the path, in which case it can simply be done in the route definition rather than across the whole application.

## Controller
A controller class regroups related **static** methods. Each method corresponds to a specific route and HTTP method as defined in a router instance. Every action must take exactly two arguments, a `Request` instance and a `Response` instance.

## Request
A request object contains various properties and methods related to the current server request such as `$_GET`, a sanitized `$_POST` (referred to as *body*), the current request URI and method, as well as dynamic route parameters and middleware data which can be passed down to the next action in line.

## Response
A response object is responsible for rendering templates. It also holds a `Session` object.

## Session
The `Session` class serves to interact with the `$_SESSION` superglobal. It can hold information about the user who's currently logged in as well as so-called flash messages which get removed after use.

## Model
Models are database entities. `Model` is an abstract class meant to be expanded to create new entities like an app user, products, categories, etc. The usual CRUD operations can be performed on entities with referring to the `Database` instance directly.
<h1>Views</h1>

- [Introduction](views.md?id=introduction)
- [Creating and Rendering Views](views.md?id=creating-and-rendering-views)
    - [Defining a View Template](views.md?id=defining-a-view-template)
    - [Rendering the View](views.md?id=rendering-the-view)
    - [Passing Data to Views](views.md?id=passing-data-to-views)
    - [Nested View Directories](views.md?id=nested-view-directories)
    - [Defining Nested Views](views.md?id=defining-nested-views)
    - [Rendering Nested Views](views.md?id=rendering-nested-views)
- [Sharing Data With All Views](views.md?id=sharing-data-with-all-views)
    - [Sharing Data Using the View Facade](views.md?id=sharing-data-using-the-view-facade)
    - [Example of a View Composer Class](views.md?id=example-of-a-view-composer-class)
    - [Accessing Shared Data in Views](views.md?id=accessing-shared-data-in-views)
    - [Defining a View Composer](views.md?id=defining-a-view-composer)
    - [Accessing Shared Data in Views](views.md?id=accessing-shared-data-in-views)
    - [Attaching a Composer to Multiple Views](views.md?id=attaching-a-composer-to-multiple-views)
        - [Defining a Composer for Multiple Views](views.md?id=defining-a-composer-for-multiple-views)
        - [Accessing Shared Data in Multiple Views](views.md?id=accessing-shared-data-in-multiple-views)
    - [View Creators](views.md?id=view-creators)
        - [Defining a View Creator](views.md?id=defining-a-view-creator)
        - [Accessing Initialized Data in Views](views.md?id=accessing-initialized-data-in-views)

## Introduction

Views in Tonka are designed to be modular and reusable, allowing developers to create dynamic and interactive user interfaces with ease. This section will guide you through the basics of creating and using views in the Tonka framework, including how to define view templates, bind data to views, and handle user interactions.

When using Tonka, view templates are usually written using the Twig templating language. A simple view might look something like this:

```twig
{% extends "base.html.twig" %}

{% block content %}
    <h1>{{ title }}</h1>
    <p>{{ message }}</p>
{% endblock %}
```

In this example, the view extends a base template and defines a content block that displays a title and a message. The `{{ }}` syntax is used to output variables passed to the view.

Since this view is stored at `resources/views/greeting.html.twig`, we may return it using the global view helper like so:

```php
Route::get('/greeting', function () {
    return view('greeting', ['title' => 'Hello, World!', 'message' => 'Welcome to Tonka!']);
});
```

!> Looking for more information on how to write Twig templates? Check out the full [Twig documentation](https://twig.symfony.com/) to get started.

## Creating and Rendering Views

To create a view in Tonka, you need to define a template file using the Twig templating language. Once you have your template, you can render it in your application by returning it from a route or controller.

### Defining a View Template

Create a new file in the `resources/views` directory, for example, `resources/views/welcome.html.twig`, and add the following content:

```twig
{% extends "base.html.twig" %}

{% block content %}
    <h1>{{ title }}</h1>
    <p>{{ message }}</p>
{% endblock %}
```

### Rendering the View

To render the view, you can use the global `view` helper function in a route or controller. Here's an example of how to return the `welcome` view from a route:

```php
Route::get('/welcome', function () {
    return view('welcome', ['title' => 'Welcome to Tonka', 'message' => 'This is your first view!']);
});
```

When you visit the `/welcome` URL in your browser, you should see the rendered view with the title and message you provided.

### Passing Data to Views

You can pass data to your views by providing an associative array as the second argument to the `view` helper function. The keys of the array will be available as variables in your Twig template.

```php
Route::get('/profile', function () {
    $user = ['name' => 'John Doe', 'email' => 'john.doe@example.com'];
    return view('profile', ['user' => $user]);
});
```

In your `profile.html.twig` template, you can access the `user` variable like this:

```twig
{% extends "base.html.twig" %}

{% block content %}
    <h1>{{ user.name }}</h1>
    <p>{{ user.email }}</p>
{% endblock %}
```

By following these steps, you can create and render views in Tonka, making your application more dynamic and interactive.

## Nested View Directories

Tonka allows you to organize your view templates into nested directories, making it easier to manage complex applications with many views. For example, you might have a directory structure like this:

```
resources/views/
├── layouts/
│   └── app.html.twig
├── partials/
│   └── header.html.twig
└── pages/
    ├── home.html.twig
    └── about.html.twig
```

### Defining Nested Views

To define a nested view, create the necessary directories and files within the `resources/views` directory. For example, to create a `home` view inside the `pages` directory, you would create a file at `resources/views/pages/home.html.twig` with the following content:

```twig
{% extends "layouts.app" %}

{% block content %}
    <h1>{{ title }}</h1>
    <p>{{ message }}</p>
{% endblock %}
```

### Rendering Nested Views

To render a nested view, use the dot notation to specify the path to the view file. For example, to return the `home` view from a route, you would do the following:

```php
Route::get('/home', function () {
    return view('pages.home', ['title' => 'Home Page', 'message' => 'Welcome to the home page!']);
});
```

When you visit the `/home` URL in your browser, you should see the rendered view with the title and message you provided.

By organizing your views into nested directories, you can keep your project structure clean and maintainable, especially as your application grows.

## Sharing Data With All Views

In some cases, you might want to share data with all views in your application. Tonka provides a convenient way to achieve this using view composers. View composers allow you to bind data to views globally, so you don't have to pass the same data to every view manually.

### Sharing Data Using the View Facade

Occasionally, you may need to share data with all views that are rendered by your application. You may do so using the `View` facade's `share` method. Typically, you should place calls to the `share` method within a service provider's `boot` method. You are free to add them to the `App\Providers\AppServiceProvider` class or generate a separate service provider to house them:

```php
use Illuminate\Support\Facades\View;

public function boot()
{
    View::share('key', 'value');
}
```

In this example, the `View::share` method is used to bind a piece of data to all views. The first argument is the key that will be available in the views, and the second argument is the value.

### Example of a View Composer Class

Now that we have registered the composer, the `compose` method of the `App\View\Composers\ProfileComposer` class will be executed each time the profile view is being rendered. Let's take a look at an example of the composer class:

```php
namespace App\View\Composers;

use Illuminate\View\View;
use App\Models\User;

class ProfileComposer
{
    /**
     * The user repository implementation.
     *
     * @var \App\Models\User
     */
    protected $users;

    /**
     * Create a new profile composer.
     *
     * @param  \App\Models\User  $users
     * @return void
     */
    public function __construct(User $users)
    {
        // Dependencies automatically resolved by the service container...
        $this->users = $users;
    }

    /**
     * Bind data to the view.
     *
     * @param  \Illuminate\View\View  $view
     * @return void
     */
    public function compose(View $view)
    {
        $view->with('count', $this->users->count());
    }
}
```

In this example, the `ProfileComposer` class is responsible for binding the total user count to the profile view. The `compose` method is called with an instance of the view, and the `with` method is used to bind the data to the view.

By using view composers, you can encapsulate the logic for preparing data for views, making your code more modular and maintainable.

### Accessing Shared Data in Views

Once you have shared the data using the `share` method, you can access it in any view. For example, in your `base.html.twig` template, you might display the shared data:

```twig
<!DOCTYPE html>
<html>
<head>
    <title>{{ title }}</title>
</head>
<body>
    <header>
        <p>Shared Data: {{ key }}</p>
    </header>
    <main>
        {% block content %}{% endblock %}
    </main>
</body>
</html>
```

By using the `share` method, you can easily share data across all views in your application, making it more efficient and maintainable.

### Defining a View Composer

To define a view composer, you can use the `View::composer` method in your application's service provider. For example, you might want to share the authenticated user's information with all views:

```php
use Illuminate\Support\Facades\View;

public function boot()
{
    View::composer('*', function ($view) {
        $view->with('authUser', auth()->user());
    });
}
```

In this example, the `View::composer` method is used to bind the `authUser` variable to all views. The `'*'` wildcard means that the data will be available in every view.

### Accessing Shared Data in Views

Once you have defined a view composer, you can access the shared data in any view. For example, in your `base.html.twig` template, you might display the authenticated user's name:

```twig
<!DOCTYPE html>
<html>
<head>
    <title>{{ title }}</title>
</head>
<body>
    <header>
        <p>Welcome, {{ authUser.name }}</p>
    </header>
    <main>
        {% block content %}{% endblock %}
    </main>
</body>
</html>
```

By using view composers, you can easily share data across all views in your application, making it more efficient and maintainable.

## Attaching a Composer to Multiple Views

If you need to attach a composer to multiple views, you can specify an array of view names when defining the composer. This allows you to share data with a specific set of views without affecting others.

### Defining a Composer for Multiple Views

To define a composer for multiple views, use the `View::composer` method and pass an array of view names as the first argument. For example, you might want to share the same data with the `dashboard` and `profile` views:

```php
use Illuminate\Support\Facades\View;

public function boot()
{
    View::composer(['dashboard', 'profile'], function ($view) {
        $view->with('sharedData', 'This data is shared with dashboard and profile views');
    });
}
```

In this example, the `sharedData` variable will be available in both the `dashboard` and `profile` views.

### Accessing Shared Data in Multiple Views

Once you have defined the composer, you can access the shared data in the specified views. For example, in your `dashboard.html.twig` and `profile.html.twig` templates, you might display the shared data:

```twig
<!-- dashboard.html.twig -->
{% extends "base.html.twig" %}

{% block content %}
    <h1>Dashboard</h1>
    <p>{{ sharedData }}</p>
{% endblock %}
```

```twig
<!-- profile.html.twig -->
{% extends "base.html.twig" %}

{% block content %}
    <h1>Profile</h1>
    <p>{{ sharedData }}</p>
{% endblock %}
```

By attaching a composer to multiple views, you can efficiently share data with specific parts of your application, keeping your code organized and maintainable.

## View Creators

In addition to view composers, Tonka also supports view creators. View creators are similar to composers, but they are executed immediately when the view is instantiated. This allows you to perform any necessary setup or initialization before the view is rendered.

### Defining a View Creator

To define a view creator, use the `View::creator` method in your application's service provider. For example, you might want to initialize some data for the `dashboard` view:

```php
use Illuminate\Support\Facades\View;

public function boot()
{
    View::creator('dashboard', function ($view) {
        $view->with('initData', 'This data is initialized for the dashboard view');
    });
}
```

In this example, the `initData` variable will be available in the `dashboard` view as soon as it is instantiated.

### Accessing Initialized Data in Views

Once you have defined the view creator, you can access the initialized data in the specified view. For example, in your `dashboard.html.twig` template, you might display the initialized data:

```twig
{% extends "base.html.twig" %}

{% block content %}
    <h1>Dashboard</h1>
    <p>{{ initData }}</p>
{% endblock %}
```

By using view creators, you can perform any necessary setup or initialization for your views, making your application more flexible and maintainable.
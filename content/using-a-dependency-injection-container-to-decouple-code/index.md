---
title: Using a Dependency Injection (DI) Container to decouple your code
date: "2020-06-03"
---

Dependency Injection is the method of passing objects to another (usually during instantiation) to invert the dependency created when you use an object. A Container is often used as a collection of the objects used in your system, to achieve separation between usage and instantiation.

## What is Dependency Injection

Take for example, the repository pattern whereby you use a separate class to handle database access so that you can separate that functionality from your application's business logic. For example, you might instantiate a new Repository in a Service:
```php
class BookService
{
    public function getSomeBooks()
    {
        $repository = new BookRepository();
        return $repository->getAll();
    }
}
```
It doesn't really matter at this point what the `getAll` function does in `BookRepository`, but the main point is that by instantiating dependencies in the same place as where they are used creates an implicit dependency on the `BookRepository` leading to tightly coupled and hard to change code. In the example above, we're no longer able to test it without having a database connected, nor can we switch it out at runtime or setup, meaning less overall flexibility.

Instead, we could declare a private member variable on the `BookService` class, and assign it during instantiation of the `BookService` class itself, and ensure whatever is passed in, implements a specific interface, so that functions you call on the repository are guarunteed to have been implemented.

```php
class BookService
{
    private $repository;

    public function __construct(RepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function getSomeBooks()
    {
        return $this->repository->getAll();
    }
}
```

Now, BookService has no idea what specific repository will be passed in, and it doesn't need to care because it knows it implements the `RepositoryInterface`. This is an example of [Dependency Inversion Principle](https://en.wikipedia.org/wiki/Dependency_inversion_principle) and is critical to understanding why Dependency Injection (DI) in PHP (and most other languages) is an important concept and to understand why a DI container exists.

## How a DI Container makes this a lot easier
We've seen how dependency injection can make testing easier, along with decoupling your code so that it can easily change over time, you might also consider what your codebase might look like if you had more complex objects than the `BookService` and you had to use it all over your codebase.
Everywhere you need to get some books, you need to instantiate both the `BookService` and it's dependency `BookRepository`, so that it can be passed into the constructor.

```php
$bookRepository = new BookRepository();
$bookService = new BookService($bookRepository);
```

This is a great first step forward, but there's more that can be done to control how a `BookService` is instantiated and with what repository, since now it can be switched out with relative ease.
This is where a container comes in. If you've ever used [Slim Framework](http://www.slimframework.com/docs/v3/concepts/di.html), you might have noticed you can set up a DI container for your app.

```php
$container = new \Slim\Container;
$app = new \Slim\App($container);

// Add a service to Slim container:
$container = $app->getContainer();
$container['BookService'] = function ($container) {
    $bookRepository = new BookRepository();
    return new BookService($bookRepository);
};
```
Wherever in your code you need a new `BookService`, you can simply use the container to build a new object for you with the repository.
```php
// Use your service
$bookService = $container->get('BookService');
$bookService->getSomeBooks();
```
This makes any code that uses the `BookService` independent of the service as well, so by using the container, we're inverting the dependency on the `BookService`.

As you might have realised, the function we defined as the value for the `BookService` key in the container, will be passed the container as an argument, meaning you can pull off any other dependencies that exist in the container already, such as the repository itself:
```php
$container['BookService'] = function ($container) {
    return new BookService($container->get('BookRepository'));
};
```
There are endless possibilities for how you can use a Dependency Injection (DI) Container to your advantage, to decouple related objects and remove implicit dependencies so that your software can grow over time with boundaries in place. 


---
title: The Facade Pattern
date: "2019-07-05T09:00:00.000Z"
---

Design Patterns allow you to create abstractions that decouple sections of a codebase with the purpose of making a change to the code later a much easier process. 
They are a set of blueprints for solving specific sets of problems, and hopefully don’t over-complicate. 

There’s nothing worse than seeing an abstraction in a codebase that actually makes it harder to understand than without the abstraction.
Of course, it’s a trade off but often times an easy way to see when you should create an abstraction is when you start to see a pattern or repetition in the behaviours in your code - not necessarily just duplicated code. 

I’ve been digging in to some design patterns lately, and one that I had to research again was the Facade Pattern. 
If you don’t know what it is, you have probably already seen or used it many times before, but after reading this article, hopefully you’ll be able to identify the Facade Pattern in your own code. 

## What does Facade mean?
Facade literally means a deceptive outward appearance, and that’s potentially the wrong angle for thinking about solving a problem with software. 
When you create a new function, it’s unlikely you’ll name it anything other than exactly what the function does. Naming things is hard in itself but that should at least be the aim. 

The Facade Pattern used in your code should be a simple interface for doing something more complicated. It should group related things together to make it easier to use.

If you’ve ever integrated a third party library into your application you may have subconsciously used this pattern without realising. Say you’re building an app where users can purchase things, you might want to create a new customer account, charge the customers credit card and send an invoice email. 

## An example of a Facade
Rather than having to think about each of these requirements whenever a customer makes a purchase, we can wrap this functionality in a specific class created for the purpose of making a purchase, then the construction of the internal objects in the application are centralised and consistent regardless of the type of purchase. 
This type of abstraction hides away some of the complicated parts of the process behind a friendly interface that can be used throughout the application with relative ease. 
It is this interface that can be described as a Facade. 

```php
class Customer {
  public function __construct(array $details) {}
}
class PaymentService implements PaymentGateway {
  public function createCustomer(Customer $customer) {}
  public function createCharge(Customer $customer) {}
}
class Mailer {
  public static function send(string $to) {}
}


class PaymentFacade 
{
   public static function purchase(array $customerDetails, Item $item)
   {
      $customer = new Customer($customerDetails);
      $service = new PaymentService;
      $result = $service->createCustomer($customer)->createCharge($customer, $item->price);

      if ($result) {
        Mailer::send($customer->email);
      }

      return $customer;
   }
}
```

For me, the Facade Pattern was a bit confusing so I took some time to figure out exactly why and when it was used. To really assist in learning about design patterns in software, I would recommend reading popular projects source code so you can see how certain patterns are used - then you’ll be able to identify it in your own code. 

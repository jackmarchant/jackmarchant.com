---
title: Using Protocols to decouple implementation details
date: "2018-09-26T09:00:00.000Z"
---

Protocols are a way to implement polymorphism in Elixir. We can use it to apply a function to multiple object types or structured data types, which are specific to the object itself. There are two steps; defining a protocol in the form of function(s), and one or many implementations for that protocol.

You've probably seen this example before either in Elixir or as an Interface in other languages:
```elixir
defprotocol Area do
  @doc "Calculate the area for a given object"
  def area(object)
end

defimpl Area, for: Rectangle do
  def area(rectangle) do
    rectangle.width * rectangle.length
  end
end

defimpl Area, for: Circle do
  def area(circle) do
    :math.pow(circle.radius * :math.pi, 2)
  end
end

# These are arbritrary shape structs, but ignoring that fact, 
# we have defined a protocol and a couple of implementations. 
# Usage is then as easy as:

iex> Area.area(%Rectangle{width: 5, length: 3})
15

iex> Area.area(%Circle{radius: 5})
246.74011002723395
```

## What is Polymorphism?
__[Source: Wikipedia](https://en.wikipedia.org/wiki/Polymorphism_(computer_science))__
> Polymorphism is the provision of a single interface to entities of different types.

I think this definition best explains what Polymorhism is in Elixir Protocols, as you define a single protocol that is used as an interface to different structured data types, keeping implementation separate from your calling code.

The goal of Polymorphism is to define abstractions around how types are used in your application, including which operations or functions are able to be performed on them. These abstractions allow your code to be decoupled from implementation details that aren't relevant. 

In Elixir, this means that we can define implementations of specific protocols, and then call the protocol functions on any of those object types, without knowing which object it is at run-time.

## Use-cases
A typical situation you might find yourself in is wanting to translate an internal data structure, to an external one, perhaps for use in an API call.
Let's say we want to translate a (contrived) `User` struct into a `ExternalUser` struct, but our calling code should be generic so that it can be used to translate other types as well.

```elixir
# lib/my_app/protocols/external.ex
defprotocol MyApp.External do
  @doc "Transform data from internal objects to external"
  def transform(data)
end

# lib/my_app/user/implementations/external.ex
defimpl MyApp.External, for: MyApp.User do
  @doc """
  For our mythical external API, 
  we only need ID and name of the user
  """
  def transform(user) do
    %ExternalUser{
      id: user.id,
      name: user.name,
    }
  end
end

# lib/my_app/api.ex
defmodule MyApp.API do
  @moduledoc """
  Transform data and push it to an external service.
  """
  
  @doc "Push transformed data with some options"
  def push(data, opts \\ []) do
    data
    |> MyApp.External.transform()
    |> ExternalAPI.push(opts)
  end
end
```
We've now decoupled our API pushing service, `MyApp.API` is not aware of what it is pushing, only that it needs to transform the structured data first before making the request.

###  Enumerable - you already use a protocol
In case you weren't already aware, if you've been using any `Enum` functions, such as `Enum.map/2` and `Enum.filter/2`, the data types you pass as the first argument to those functions implement the [Enumerable](https://hexdocs.pm/elixir/Enumerable.html) Protocol. This particular protocol defines four functions that need to be implemented for any type you wish to use with it `reduce/3`, `count/1`, `member?/2` and `slice/1`. You can see these functions defined in the [Elixir code on Github](https://github.com/elixir-lang/elixir/blob/v1.7.3/lib/elixir/lib/enum.ex#L1).

One of the greatest things about Elixir is you can easily browse source code to see how the standard library we use all the time is implemented internally. In theory, you can implement your own Enumerable type, but I'm not sure how useful that would be in practice!
---
title: Streaming large datasets in Elixir
date: "2018-06-27T21:34:00.000Z"
---

We often think about Streaming as being the way we watch multimedia content such as video/audio. We press play and the content is bufferred and starts sending data over the wire. The client receiving the data will handle those packets and show the content, while at the same time requesting more data. Streaming has allowed us to consume large media content types such as tv shows or movies over the internet.

A [Stream](https://hexdocs.pm/elixir/Stream.html) in the Elixir sense of the word, is a composable way to lazily evaluate transformations on collections. When managing large datasets, traditionally you would load all the records into memory, say from a database query, and use the [Enum](https://hexdocs.pm/elixir/Enum.html) module to apply various transformations with each call to an `Enum` function. With Streams, you can call Stream functions in a composable way, but only when `Stream.run/1` is called or it's converted to an enumerable does it actual perform those computations.

When we create a new Stream by calling one of the many functions on the Stream module, for example `Stream.map/2`, we pass it an enumerable and a function, which we want to be applied lazily. We can see the Stream that is returned, keeps a reference to the original enumerable: `enum` and the function(s): `funs` we want applied. 

It's only when we convert the Stream to an Enumerable, that the functions run against the enumerable, and we have our result.

```elixir
# Create a new stream
> [1, 2, 3, 4, 5] |> Stream.map(&(&1 * 2))
#Stream<[
  enum: [1, 2, 3, 4, 5],
  funs: [#Function<48.71542911/1 in Stream.map/2>]
]>

# do some more code things

# Ok, let's evaluate the stream by converting it to an enumerable
> Enum.to_list(stream)
[2, 4, 6, 8, 10]
```

## Why should you use a Stream?
There are three advantages I can see with using Streams:
1. Functions can be lazily evaluated and thus built up over time, until the stream is finally converted or run.
2. Large datasets can be split into smaller chunks, reducing the amount of memory needed to consume them.
3. Streams encourage function composability without needing to write complex code in an `Enum.reduce`.

These advantages are a bit easier to describe in code:

```elixir
defmodule StreamOrNotToStream do
  @doc "Without Streams, we enumerate over the range 3 times, every time we call Enum.map/2"
  def without_stream(enumerable) do
    enumerable
    |> Enum.map(&(&1 * 2))
    |> Enum.map(&(&1 + 1))
    |> Enum.map(&(&1 - 1))
  end
  
  @doc "With Streams, we build up all of the transformations and enumerate only once!"
  def with_stream(enumerable) do
    enumerable
    |> Stream.map(&(&1 * 2))
    |> Stream.map(&(&1 + 1))
    |> Stream.map(&(&1 - 1))
    |> Enum.to_list()
  end
end

> StreamOrNotToStream.without_stream(1..100) # fun fact - Ranges are also Streams!
[2, 4, 6, ...]

> StreamOrNotToStream.with_stream(1..100) # same result - but we got there differently
[2, 4, 6, ...]
```

You can see how having large datasets, and enumerating over the entire list for each transformation would be more expensive. With Streams and its lazy evaluation, we can defer getting the value until it's needed, which means it can reduce the time spent doing potentially expensive calculations.

Streams are a powerful concept that allows you to efficiently manage even infinite datasets through encouraging composition of functions. A Stream is a handy substitute for what might otherwise be a complex `Enum.reduce/3` function. Using a Stream not only cleans up your code, but will give you a clearer mental picture of the transformations happening on the data.
Composing functions is what Elixir is good at, Streams allow you to still break up the data transformations, and perhaps even do them at separate times - this wouldn't be easy in a reducer function.

## Working with Ecto (or other data sources)

Streams can be really powerful when using them with a database, specifically with either [Repo.stream/2](https://hexdocs.pm/ecto/Ecto.Repo.html#c:stream/2) or [Stream.resource/3](https://hexdocs.pm/elixir/Stream.html#resource/3). The latter is a bit more generic so we'll use that as our example.

With `Stream.resouce/3` you can chunk your dataset into specified amounts, and emit them through a stream. It allows you to keep track of the last record that was seen through an identifier, and pick up where it left off for the next chunk. All you need to do think about is what transformations to apply and when to evaluate them.

We could apply these concepts for other data sources, not just Ecto or even a database. This could be used to receive results from an API that uses pagination to move through the data.

Using Streams, we can compose functions and push them on to a stack until such a time that we're ready for Elixir to evaluate the result of all of the functions together. This is a powerful concept and I'm looking forward to doing more with them in the future.
---
title: The problem with Elixir Umbrella Apps
date: "2019-05-03T18:00:00.000Z"
---

[Umbrella apps](https://elixir-lang.org/getting-started/mix-otp/dependencies-and-umbrella-projects.html) are big projects that contain multiple mix projects. Using umbrella apps feels more like getting poked in the eye from an actual umbrella. 

There are a few misconceptions about umbrella apps surrounding their purpose, how to effectively manage a growing project and deploying the app somewhere in production. I’d like to present a case for not using an umbrella app, if you were considering doing so.

### A typical umbrella app
Umbrella apps are meant to help developers split different concerns into different apps thinking that a parallel can be drawn to a Service Oriented Architecture (i.e micro-services). An umbrella app can be thought about in terms of a single application, and in most cases is deployed as such. 

**Let’s think through an example app:**
In an application that has a web component serving traffic, type and resolver definitions for a graphql API, data contexts for interacting with the database, we could potentially have three apps in our umbrella:
- web
- graphql
- data

We can still deploy this as a single project given the wonders of an umbrella app, and get some minor conveniences with config and testing. When a request comes in, the web app handles it, calling a resolver function in the graphql app, which in turn retrieved some data from the data app. The dependencies are in a single order: web -> graphql -> data, so when you compile it starts with the inner most dependency and works it’s way back, as you’d expect - however if everything is deployed together you can still technically access modules that are technically circular dependencies, which kind of breaks the separation concept. 

### What’s the problem with this application?
The main issue with this architecture is that the apps aren’t really split for the right reason. In a growing (in terms of code added over time) application it will most likely slow you down the more code you add as the boundaries become more brittle and blurred. 
The reason for this effect is that umbrella child apps are intended to be created as a way to **deploy** each of them separately, hence the individual configuration and mix project. So unless you’re deploying the apps separately, there is no benefit from using an umbrella app.

There may come a time when you need to, but I can guarantee moving into an umbrella app configuration, retrofitting on an existing app is the easier option than consolidating child apps.

>Umbrella child apps are intended to be created as a way to **deploy** each of them separately

### A better alternative
I’m not advocating to never use umbrella apps, but I think in most cases it’s better not to use one until you have the requirement to deploy a child app separately. 

The alternative is to create a good old elixir application using ‘mix new’ and placing all of your apps into their own folders. You can still accomplish the same architecture without using an umbrella app and as a side bonus you’ll be able to quickly iterate and change your mind on decisions as you learn more about your business domain and perhaps elixir too! 
This is a much easier way to get started with Elixir, in fact [Phoenix recommends to structure your apps in this way](https://hexdocs.pm/phoenix/contexts.html) through contexts.

My experience with umbrella apps has mostly been one of trying to reduce its complexity and favour modules over apps.

In fact, in [Elixir's official documentation](https://elixir-lang.org/getting-started/mix-otp/dependencies-and-umbrella-projects.html#dont-drink-the-kool-aid) where it explains some of the benefits of using Umbrella apps, it does state a disclaimer:
> While it provides a degree of separation between applications, those applications are not fully decoupled, as they are assumed to share the same configuration and the same dependencies.

Maybe your experience has been different? If anyone has found success with umbrella apps I’d love to discuss it! 
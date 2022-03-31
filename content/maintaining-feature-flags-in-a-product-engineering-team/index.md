---
title: Maintaining feature flags in a product engineering team
date: "2022-04-01T18:00:00.000Z"
---

I have mixed feelings about feature flags. They are part of the product development workflow and you would be hard pressed to find a product engineering team that doesn’t use them. Gone are the days of either shipping and hoping the code will work first time or testing the life out of a feature so much that it delays the project.

The benefits of using feature flags certainly outweigh the bad, but it doesn’t stop teams from cursing them every time a major bug is reported or an incident occurs as a result of enabled (or disabled) feature flags.

In this post I will discuss the benefits and some drawbacks of using feature flags, to help you learn from some of the lessons I’ve personally learned, in the hopes that you can avoid the mistakes.

First, let’s understand what a feature flag does, and why it’s there:

A feature flag is (at its simplest - there are more advanced controls you can use) an on/off switch to release some new functionality to your product through the code base. 

Teams can safely ship code knowing the feature can be enabled for small groups of users at a time, and released to more customers as confidence in a feature or behaviour grows. 

Before feature flags, you only had one shot to ship code to production and make sure it works, which meant a longer build up to releasing code for the first time, or complicated infrastructure to support canary releases. 

The main problem with feature flags is what happens when you have too many and they start conflicting with each other or you have so many different flows to test that the team spends much longer on a feature than they should. 

This leads me to the first lesson:

## Lesson #1

### The number of feature flags you maintain will spiral out of control

Every time you create a feature flag, you’re introducing a different behaviour for your code that may only be initially released to parts of your user base, meaning you now support two different behaviours (feature flag on and feature flag off). 

This is empowering for a growing product and engineering team. As the number of feature flags in use in production grows, so too does the frustration of testing all of those different cases and receiving bug reports where your first instinct is to check which feature flags are enabled or disabled. 

Keeping track of feature flags means making them attributed to your team so there’s ownership of the feature flags and tracking the rollout status, including ensuring rollout continues to happen, or the flag is retired. 

When feature flags have already spiralled and are out of control, the best thing to do is pause development of new features and clean up any flags that are rolled out or no longer required. 

Getting feature flags back under control should be a priority, given the impact on development and testing time for related features. 

This is a hard lesson to learn because there’s only one way out. Clean up the flags!

## Lesson #2

### Clean up feature flags regularly, make it part of the development cycle

After some time in production, feature flags can become stale and turn into technical debt, which must be paid back at some point, or risk cumulating over time and being at the point of no return. In some ways you will always have to live with feature flags, and they become part of the work you do. 

The difference between a feature flag that is new and part of the feature actively being worked on, versus a flag that has lost purpose is enormous. 

To avoid this dysfunctional reality, we must clean up feature flags after the feature has been rolled out or no longer needed. Sometimes this will be a minor piece of work that involves only one person making the code change and testing it and other times it can require re-testing an entire feature. 

In my experience, how well you have built up your automated testing around the feature will impact whether it’s a minor change or a major one. 

Recently in my team, we had built up a lot of feature flags for a variety of reasons, whether it was changing teams, forgotten features or slow rollouts and so we had to take a week or two to clean up around 10-12 feature flags in a short period of time. 

We ended up doing the work for these in a short time frame, then merging and releasing the changes incrementally and over a longer period just in case anything went wrong. 

This proved to be successful in the end and the team swarmed on the work to get it done. 

We’re working on keeping track of feature flags more closely now to make it part of our development cycle to clean up feature flags rather than waiting for the eventual build up. 

When we release a new feature, with a corresponding flag, we document it and ensure it keeps rolling out and create a ticket for the future to clean it up.

Progressing with the rollout usually means opening up the feature to more customers and this brings me to the final lesson.

## Lesson #3

#### Always be rolling out

Feature flags should be temporary and are meant to increase the velocity of your team by allowing you to ship quickly and get real feedback from smaller groups of users in a safe way. 

Flags, therefore, should be intended to be rolled out completely to your whole user base at some point. It’s normal to start with a small group and then build up incrementally to larger groups, but this should always be on a timeline. 

Once you forget about it or move on to the next thing and leave the flag there, it will become stale and your team falls into the trap of having to maintain it and test both states of the flag should a change to that area of the code base be required. 

There’s no one right time frame for a feature flag to exist, it will always depend on the feature and the group of customers using it to give you feedback directly or indirectly, through usage. 

That’s why keeping track of the current state of feature flags in your control is important including managing the continual rollout to more users. 

As you find bugs you can pause the rollout until the bugs are fixed, but if you haven’t hit any road blocks it’s critical to keep forging ahead so removing the feature flag becomes possible once it has been made available for all of your users. 

Feature flags are both a blessing and a curse, it’s probably no secret to most engineering teams. What’s missing in my opinion is a framework to manage feature flags over time and throughout engineering organisations. 

They help keep the product working and make it easy to rollback changes without code deployment, and give on-call engineers peace of mind when they can safely turn off a flag that has caused an incident. 

If left unchecked, feature flags can slow engineering teams down to a crawl so create each feature flag with caution and a plan for its eventual removal. 

Feature flags have given product teams the confidence to move fast, with a plan to rollback at the click of a button, but with great power and flexibility, comes a cost which should not be underestimated.
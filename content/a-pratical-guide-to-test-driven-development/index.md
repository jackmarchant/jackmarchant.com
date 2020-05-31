---
title: A practical guide to Test Driven Development
date: "2019-09-12T12:18:00.000Z"
---

It’s been a while since I last wrote about why testing is important, but in this post I thought I would expand on that and talk about why not only unit testing is important, but how a full spectrum of automated tests can improve productivity, increase confidence pushing code and help keep users happy. 

## Why do we need to test code?

Code gets tested every time users interact with your software, whether it’s through an application or part of an API. The unfortunate reality is that by the time your code is in the hands of users, it’s too late to find out it doesn’t work. 

To reduce the chances of this occurring, we test code during development, after development (sometimes called Quality Assurance testing), right before releasing the code to users and even right after releasing. 

At each step, it’s possible we could find a defect in the code and need to revert or write a fix to remedy the situation. The later the defect is found, the larger the impact and slower the turnaround to getting it fixed. 

It is for these reasons that we test code at each step, building up confidence that the code does what we expect so that it may progress to the next stage in the development and release process.

__Definitions__

The following are definitions of terms I use throughout this post, and serve as a description of how I think about each type of test (these aren't necessarily textbook definitions).

**Unit Test**: A test where the subject is an isolated block of code, typically a single function with no dependencies.

**Integration Test**: A test where the subject could be a function with dependencies, or multiple functions/classes tested simultaneously.

**User Acceptance Test**: The closest test to how a user will interact with your software, sometimes referred to as a Functional Test.

## Tests are a crucial - regardless of when they happen

It is always in your best interests as a developer writing code to find bugs as early as possible. The ideal scenario being that you find it as you’re working on the code itself, by making a change and then running automated unit tests. This way, you can identify the problem, fix it by writing a test case for that scenario and moving on. 
Not all bugs are created equally however and by the nature of software development, some code is harder to test than others. This is why we introduce other forms of testing later in the development cycle, such as integration testing and user acceptance testing. 

These three forms of testing: *Unit*, *Integration* and *User Acceptance*, build on top of each other to create a test pyramid. The general idea being that unit tests should be easy to create and run as they are without external dependencies. Integration tests allow you to see how different modules, when hooked up together, respond to certain inputs. Finally, User Acceptance tests may place an entire vertical slice (incorporating many parts of your software which may be slow or brittle) under test. As you go from most (unit), many (integration) to some (user acceptance), confidence in the overall system to be working correctly should increase. 

Having tests doesn’t make bugs disappear completely, but it does reduce the frequency of them, along with ensuring that changes you make don’t have unintended side effects. 

Now that we’ve discussed some of the terminology and theory behind testing practices in software development, it’s much easier said than done. So, let’s talk about some ways you can incorporate testing into your development workflow. 

In a large codebase, it’s worth having a few strategies for testing depending on the code needing to be tested, for example:

__New code integrating with existing code:__

In this scenario it makes sense to unit test any new code you write, as much as possible. The point at which you integrate the new code into an existing code path, you may not be able to easily test, but because you have confidence from the unit tests, you can try either an integration or user acceptance test. The former will likely be running the existing code path and making sure the new code is being run, while simultaneously ensuring the existing code runs successfully. The latter, may require manual or automated testing of the entire feature, during which time your code is run. This has a slower feedback cycle, but equally an important step nonetheless. 

__Fixing a bug in existing code:__

When you find a bug in your code, whether it’s during development or reported by a user, the best way to fix it is to write a test (any type will do) and then fix the code, ensuring that the test passes. 

This will have a short term and long term effect:
- It will ensure you have actually fixed the bug. 
- And, allows the test to be run again in the future, making sure further changes haven’t caused a regression. 

__Approaching the Test Pyramid from scratch:__

Without any tests or very little, often the code is hard to test so it can be worthwhile starting from the top of the pyramid and working downwards. User Acceptance testing can be a good way to get started because you can mimic how a user interacts with the software. Then, as more tests are added, confidence that overall features are working might enable engineers to start building integration and unit tests with a bit of refactoring along the way.

>Having tests doesn’t make bugs disappear completely, but it does reduce the frequency of them, along with ensuring that changes you make don’t have unintended side effects. 

## The effects of Testing over time

Improving the maintainability of a codebase through increasing test coverage over time has a dramatic affect on teams, individuals and businesses. There are a number of fallacies surrounding testing that exist in software development teams in regards to testing that hinder their collective ability to be productive. 

A system that’s hard to test becomes a black box for developers, because it’s impossible to say with any certainty how something works. That being said, it is possible to open up the box and take parts out to figure out how they work. The best way I’ve found to learn a system is by introducing new tests.

There’s a common belief that Test Driven Development can only be practiced successfully through writing tests as if they are requirements, then writing code to satisfy the requirements. I would suggest that in reality, this is not how much of the software in the world is created - because it’s hard to do. 

Instead, Test Driven Development to me, is the practice of incorporating any kind of testing into your development cycle, meaning you’re not always writing tests first - it could be after you’re done or midway through - the important part is to use automated and manual testing together to drive a faster feedback loop between writing code and knowing whether or not it works. 

In practice there are trade offs, just as in any other engineering decision, which need to be considered when adding tests to your development workflow. Let's stop debating about whether TDD means red-green-refactor, all it does is discourage people from actually writing tests, for fear they're not doing it right.

There are always going to be tests, but which ones, and how will they be run? In answering these questions and developing with tests, you’ll find it increases your own productivity writing code and in the end it will improve the reliability of your software for your users.


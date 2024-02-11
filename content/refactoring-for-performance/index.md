---
title: Refactoring for Performance
date: "2024-02-12T09:00:00.000Z"
---

I spend most of my time thinking about performance improvements. Refactoring is tricky work, even more so when you’re unfamiliar with the feature or part of the codebase.

Some refactoring might be simple, but in this post I’ll attempt to dissect my approach to solving performance issues in the hopes it’ll provide value for others.

## Where do we start?
Before we can design a solution to a performance issue we must understand the problem. For example, is a page not loading or is it very slow? Are there more queries than necessary to get data? Can we see a slow part in the process? How do we know it’s slow? Answering these questions first is a must.

Once we can see the slow part over and over again, if code is the culprit, I start by taking that piece out and seeing how fast it could be without it even though it may break or be incomplete. This helps me to see what the maximum amount of improvement we’ll get through performance optimisation – as if code didn’t run at all. 

This is the incentive. If I know how much performance improvement is possible, it’s worth investing time into figuring out a solution. If I see marginal or little to no improvement, I’m either in the wrong place or it wasn’t as slow as I thought - time to move on.

The solution to the performance problem could be as simple as adding an index and as complicated as a complete rebuild. Code optimisation will naturally take longer than query optimisation because the behaviour of the code will generally change. If the problem is not that the query is slow but that the query runs thousands of times in a single request - those are two different problems to solve.

## Going from prototype to production

The easiest way I get from identifying something slow to being able to fix the problem is to prototype the way I think it should work to be fast. Creating a prototype gives me the confidence the solution works at a high level, without addressing all of the edge cases. At minimum, I try to identify blockers standing in the way.

Once I’ve proven the solution works, I can invest more time to understand the product behaviour and the experience. How does the user actually use this feature? What are they trying to accomplish?

To be clear: this is the hardest point and often where the solution can fall over. If I misunderstand requirements or forget to include some parts, however minor they may seem, it undermines the performance optimisation and deflates any confidence in it when it comes time to release it.

Confidence is a fickle thing - it can be gone in an instant and hard to get back quickly. Customers are never going to applaud performance improvements - maybe it should have been fast to begin with - but many performance improvements add up to a better experience.

## Testing builds confidence

Testing a performance improvement is like any other test of a change with the addition of a specific metric that you want to improve. For example if the goal of the refactor was to reduce page load time, compare the previous and current page load speed. If reducing the number of queries was the goal, show that the number of queries has gone down. I often start with manual tests to confirm impact on the user experience supported by some quantifiable metric. Screenshots, videos or links to observability metrics all support the fact that the refactor does what was intended.

Once I’ve covered the performance gains, the next thing to verify is correctness. To do this, I start with a few manual scenarios and compare the result of using the feature with and without my change. The most comprehensive way to do this is through a test spreadsheet which marks pass or failure for some scenarios. A user clicks a few buttons and assert the result is the same. Using a spreadsheet helps maintain regression tests and add test cases over time. Some features won’t be big enough that you’d need it, but even if you never share the results with anyone and use it for your own testing - it beats remembering all cases every time you test.

One day you could even turn those manual tests into automated tests, if that’s not readily possible now. At least creating automated tests for any new code is a task worth doing.

How do performance improvements differ from features?
Feature development creates new functionality where it didn’t exist before, so there’s often time to assess its effectiveness and test with customers who might be more forgiving if something is not working. To break an existing feature that may be slow is to take it away. We must have extra care when dealing with something that is working today for some, even if it’s slow.

A performance improvement must be:

- Cheaper or faster
- At least equal, ideally better behaviour

It’s an unforgiving task, but rewarding when you can quantify performance improvements with a better experience for customers. Monitoring the outcome after release is a good place to start, even in the short term to verify the improvement was a success.

__The hardest question, which will remain unanswered, is how can we know when performance optimisations are done?__
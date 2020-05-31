---
title: Making Software - a three step process
date: "2020-04-14T08:22:00.000Z"
---
One of the most useful tips that has guided much of my decision over the years has been this simple principle: three steps, executed in sequential order;

1. Make it work
2. Make it right
3. Make it fast

These steps outline the process through which software should be made. You should refer back to these steps and discover for yourself which step you are currently in while creating software. This will also help you identify whether you need to or indeed can move to the next step.
An important distinction to make up-front is that you don't necessarily need to complete all steps in order to ship software to users. Let me explain.

## Make it work
The first step should be the most obvious, but the key to (and the most difficult) is acknowledging you are at step 1. Making software work is about glueing all of the pieces together until the thing you're trying to build actually works. 
Imagine you're building a skateboard and you take a plank of wood and screw 4 wheels to the bottom. There should be criteria to allow you to recognise when it's finished and can move to the next step, for example:
- Can you stand on it?
- Does it move forward when you push off?
If the answer to both of these questions is yes, then congratulations you've got a working skateboard.

Keen readers will poke holes in the analogy - the skateboard will break easily because the wheels aren't correctly fastened to the plank and will buckle after the first ride. While this may be true, the first step is making it work and we may not be expecting to produce thousands of these skateboards and allow customers to purchase them at this stage.

Bringing this back to software, if you catch yourself on step 1 and you're already thinking about how to optimise for performance or you've got the best abstraction idea that is extensible to the nth degree, then the battle may very well be lost because if you can't make it work, nothing else matters.
This step is more about what you don't do straight away, as opposed to what you do.

## Make it right
Continuing the skateboard analogy, this next step provides you with the time built into the process to take a step (pardon the pun) back and look at the big picture. Such as, the wheels need to be fitted correctly with the proper materials and safety considerations to withstand the rough and tumble expected in the riding of a skateboard.
This is no different to the wear and tear of software - without the proper guards in place such as tests, abstractions and extensibility in place the software will likely buckle under the pressure of real users.

This step is the right time to take the working thing and build it properly, regardless of whether you start it from scratch. The first step is more discovery than anything else and provides you with the confidence and knowledge of how to build the thing, so that in this step you can build the thing right.
Making software right ensures you have the correct checks and balances in place, such as applying principles to common problems and giving it the best chance of long-term sustainability.

At each step, a decision needs to be made about whether to progress to the next - for example there might be times in building software when making something work is more important than making it right. The key difference between this and pure negligence is the fact that this trade-off is a conscious choice, which makes it hard to see in hindsight unless well documented. It is often seen as tech debt, accrued for a purpose, but don't be fooled into thinking it's at all similar to financial debt (a common misconception, that may be for another time). 

While I wouldn't recommend stopping before making it right, there are times where making something is better than not making anything at all. The hardest part is accepting the snowball effect this will have later down the track and whether you are actually prepared for the true cost.

Making it right, while technically a choice (hence it being a different step) is probably where most early software needs to land to be effective. Going further than this may become detrimental unless you have a reason to make it fast.

## Make it fast
The final step is where all guns are blazing, software is often in production and you need the last piece of the puzzle to ensure the software works as intended (i.e. fast enough for the user). This step is all about optimising what you have done in the previous two. To keep the skateboard analogy going, at this step we would want to focus on the speed of the rider ensuring they maximise speed.

In software on the other hand, this may only happen once it's actually in the hands of users, so you know where the bottlenecks are. Sure, you could throw more hardware at it temporarily but eventually you have to make the software fast enough to scale correctly and appropriately.

In each step there are trade-offs but in this step the smallest decision can have the biggest impact, so you should always make data-driven decisions based on real usage rather than making educated guesses and hoping for the best. In time, experience will often tell you where the bottlenecks are, but this isn't always the case and depends on the system. Making it fast requires skill and practice and sometimes you don't even need this step until the software becomes popular enough that you have a reason to focus on speed. This is why it's best to analyse the data before embarking on this step.

## One step at a time
In each step there is particular nuance to doing it right and doing it well. It's not like a recipe where the instructions tell you exactly what's required. All you can do is take it one step at a time and figure out where you can draw the lines around it. At times it may even be harder than building the software on it's own, but being able to do this consistently will help you in the long-run.

I have found great focus from following these three steps when building any software professionally.
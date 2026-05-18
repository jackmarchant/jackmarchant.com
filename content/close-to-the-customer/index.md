---
title: Close to the Customer
date: "2026-05-18T09:00:00.000Z"
tldr: Most engineers experience customers through tickets and dashboards, not people, and the ones who build the best software carry the customer's world around in their head while data acts as a compass rather than a guardrail.
tags: practices, product
---

Every team I've worked on has claimed to be close to the customer. Very few of them actually were. The phrase gets used so loosely that it's worth pulling apart what it actually means, why most engineers never get there, and what it costs them.

## What "close to the customer" actually means

The default interpretation is physical or organisational proximity. You sit near the support team, you read the feedback channel, you get pulled into the occasional sales call. That's not nothing, but it's not it either.

Most engineers don't experience customers at all. They experience artefacts of customers: a Jira ticket that's been through three rounds of translation, a dashboard that aggregates a million people into a single line, a Slack message that starts with "a customer is asking for". By the time the customer reaches you, they've been compressed into a feature request. You're not solving their problem anymore, you're solving the description of their problem written by someone who also never spoke to them.

Being close to the customer isn't about where you sit. It's about how much of the customer's world you carry around in your head when you're making decisions. When you're choosing between two implementations, can you predict which one will annoy them? When you're cutting scope under deadline, do you know which corner they'll never notice and which one will quietly destroy their trust? That knowledge doesn't come from a ticket. It comes from having watched real people use the thing, repeatedly, until their reactions become something you can simulate without them in the room.

Most engineers never get there because the system is designed to keep them away from it. Specialisation is efficient. Letting engineers talk to customers feels like a waste of an expensive resource, so layers get inserted to protect their time, and each layer strips out the texture that made the customer's problem worth understanding in the first place. The org chart optimises for throughput and accidentally optimises away the thing that makes the throughput worth anything.

## When intuition beat the data

A few years ago we were building a feature that, by every metric we had, people wanted. The usage numbers on the prototype were strong. The feedback in research sessions was positive. The data said ship it and double down.

I couldn't shake the feeling that we were measuring the wrong thing. The people using the prototype heavily were our most engaged users — the ones who would use almost anything we put in front of them. The behaviour I kept noticing, sitting in on sessions, was a small hesitation right before people committed to the core action. Nobody mentioned it. It didn't show up anywhere in the funnel because it happened in the half-second before the event we were tracking even fired.

We argued about it. The data was clear and my objection was, by definition, a gut read. But I'd watched enough sessions that the hesitation had become legible to me, and I was fairly sure it was a sign that people didn't trust what was about to happen. We pulled the feature, reworked the moment of commitment to make the consequence obvious before the click, and shipped that instead. Adoption ended up well beyond what the original numbers had projected, and the support load we'd quietly braced for never arrived.

The honest version of this story is that I can't prove the original version would have failed. That's the nature of intuition — it doesn't come with a counterfactual. But the read only existed because I'd spent enough time near actual people that I noticed something the instrumentation was structurally incapable of seeing. The data wasn't wrong. It just couldn't see the half-second that mattered.

## Where the guardrail ends and the paralysis begins

I want to be careful here, because the lesson is not "trust your gut, ignore the data". I've watched that go badly more often than I've watched it go well. Intuition uncorrected by reality is just confidence, and confidence is not a feature.

The useful distinction is what you're asking the data to do. Data is excellent as a guardrail. It tells you when you're drifting off course, when something you shipped is quietly making things worse, when the story you're telling yourself doesn't survive contact with what people actually do. If you've changed something important and the numbers haven't moved, the data has earned the right to make you nervous. That's the job it's good at, and you should let it do that job ruthlessly.

The trouble starts when you ask data to tell you where to go next. It can't. Every dashboard you have is a measurement of the world you already built. Optimise hard against it and you will get very good at the thing you already know how to do, while the genuinely new direction — the one that doesn't register on any existing metric because nothing about it exists yet — stays invisible. That's not data being used well. That's data being used as a substitute for judgement, which is exactly the situation it can't help you with.

Paralysis begins the moment "what does the data say" becomes the answer to questions data was never able to answer. The teams that get this right treat data as a compass, not a map. A compass keeps you honest about which direction you're facing. It does not tell you that there's a continent over there worth walking to. Knowing the continent is there comes from the same place the half-second hesitation came from: time spent close enough to real people that their world is something you carry with you, even when no one's looking at the dashboard.

That's the whole argument, really. Get close enough to customers that you build real intuition, use data hard to keep that intuition honest, and never confuse the thing that stops you driving off the road with the thing that tells you where you're going.

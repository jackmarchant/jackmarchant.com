---
title: Using coding agents in my workflow in 2025
date: "2025-12-05T09:00:00.000Z"
---

There's a lot going on in the world of AI right now, so in an effort to cut through the noise, I thought it would be interesting to write down my current practices with AI — both as a time capsule to refer back to next year, and as a challenge to rethink anything I assumed wasn't previously possible without AI. The assumption being that a lot will change a year from now.

## My Everyday Tools & Workflow
I'm using Cursor a lot. It's become my main editor after having used VS Code for a long time. Like everyone else, I started by giving it some test cases to write and letting autocomplete finish code — which felt superhuman at the time.

Now, I'm running research and planning tasks before building, or if the task is small enough, one-shotting a prompt to take a trivial task off my mental plate.

I'm also experimenting with coding agents to perform more meaningful tasks and accelerate development at a speed that feels like cheating. The good thing is, a lot of the time it's for tasks I've already thought a lot about but haven't had the time to write by hand.

## Why Agents Work
Although using agents still feels like a stretch sometimes, it's really just another layer of abstraction to prevent me from using valuable brain compute time on syntax, code structure or design. With enough experience, you know what “good” looks like — clear separation of concerns, implementing interfaces, and single responsibility principles, to name a few.

Clean code can be produced by AI with minimal context (e.g. “use these files as reference”).

In a large codebase an issue could arise that code is not strictly "clean" and so it would produce more of the same, you can easily tell agents to write code that conforms to an existing structure.

Building your own agent, apart from being the cool new thing to do, allows you to customise to your preferred workflow and optimise context on your projects without exposing details to the cloud or sharing between teams. I still think there's a place for personal agents alongside team agents, each with their own strengths. Personal agents might be concerned with the individual developer workflow, where team agents help run checks and balances against diffs before they can be merged, with more context about the wider system.

## My Current Workflow for Non‑Trivial Tasks
1. Perform research and planning to produce a specifications document.
2. Iterate intensely on the specs — in some ways this can be treated like code (versioned and improved over time).
3. Build incrementally with an agent, allowing smaller context windows and clear completion points.
4. Review the LLM's “thought process” for producing the code.
5. Create documentation for how the feature works.

At each step there's a review of the agent's decisions and approach — tweaking and redirecting where needed. Some tasks require more experimentation than others and so it's cheap to produce; it's easy to throw away and start from scratch.

## A Changing Development Lifecycle
There's still a lot we're all learning and experimenting with. I expect the development lifecycle to be very different by the end of 2026.

A recent live stream talk was particularly eye‑opening. It connected a few dots for me:

### Two Big Takeaways
1. **Minimal context windows** — allows agents to focus on specific tasks and not get distracted. There's a strong parallel to humans here — we talk about context switching and information overload. It turns out agents suffer the same.
2. **Controlling agent process with something as simple as a while loop** — when something so fundamental is taken to a basic level, it opens up possibilities I hadn't considered before.

## It's Not Just About Productivity
It's not just productivity for its own sake. AI is changing what we spend our time doing as engineers.

We're moving away from writing perfect code from scratch. Now, with agents, it's also specs — which can produce intent for the code very cheaply.

Anyone who has debugged legacy code knows half the battle is understanding not just what the code does, but why the engineer chose that approach in the first place. This is huge for future systems we maintain — where codebases could be rewritten at intense pace because the original plans already exist, and can be updated with minimal effort to produce a wildly different version of the system.

## Some Interesting Media
- [I ran Claude in a loop for three months, and it created a Gen‑Z programming language called “cursed”](https://ghuntley.com/cursed/)
- [Ralph Wiggum as a “software engineer”](https://ghuntley.com/ralph/)
- [Youtube live stream talk](https://www.youtube.com/live/fOPvAPdqgPo?si=oUltz4JUIPVOlLAU)
- [How AI is changing software engineering at Shopify with Farhan Thawar](https://open.spotify.com/episode/3XTdfnHeQlROSmlAHNqEUs?si=c1537e00ddb944e0)
- [AWS re:Invent 2025 - How Amazon Teams Use AI Assistants to Accelerate Development (DEV403)](https://www.youtube.com/watch?v=cf-WOKVn768)



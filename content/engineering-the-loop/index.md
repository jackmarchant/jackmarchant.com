---
title: engineering the loop
date: "2026-03-16T09:00:00.000Z"
tags: ai, practices
---

I've been spending more time thinking about how I work with agents than the actual code they produce. That shift in focus, from output to process, is what engineering the loop is about.

The loop is the automation pipeline that sits between me and a coding agent. Done well, it lets me hand off well-defined pieces of work to Claude Code and stay focused on the bigger picture: system design, architecture decisions, the things that actually require judgment.

## What the loop looks like

My current setup is fairly simple. I write specs into Jira tickets or pull context together in Confluence pages. Claude Code picks those up as a starting point. The goal is that by the time I'm handing something off, the work is already decomposed enough that the agent isn't guessing at intent.

The quality of input matters far more than I expected. A vague ticket produces vague code. A well-structured spec with clear acceptance criteria produces something reviewable on the first pass.

Writing a good spec is also a forcing function. If I can't describe the work clearly enough for an agent to act on, I probably haven't thought it through clearly enough to build it myself either.

## What's changed

Two things have shifted since I started taking this seriously.

The first is focus. When smaller, mechanical tasks have a clear home, a defined input, a defined outcome, an agent to run them, they stop taking up mental space. I'm spending more time on architecture and less time holding implementation details I'd rather not be thinking about.

The second is throughput on the boring parts. Not glamorous, but real. The SDLC has a long tail of tasks that are well-understood but time-consuming: boilerplate, tests, doc updates. These are good candidates for the loop. The human decision has already been made, execution is what remains.

## Where it still breaks down

Review and QA is the current bottleneck. The loop produces code faster than I can meaningfully review it, which creates its own kind of pressure. Speed without oversight isn't actually a win.

The other rough edge is knowing what not to hand off. Some tasks look automatable on the surface but require judgment at each step, the kind of decision-making that's hard to encode in a spec. I'm still calibrating where that line is.

## What to take from this

If you're building your own loop, a few things worth starting with:

Invest in the spec, not the prompt. A well-written ticket with context, constraints, and a clear definition of done is more valuable than a clever one-liner. The agent will only be as good as the brief.

Identify your handoff criteria. Before running anything through the loop, ask: is the decision already made, or is the agent being asked to make it? Execution is a good handoff. Decision-making usually isn't.

Treat review as part of the loop, not outside it. Build in the time. An unreviewed PR is just deferred work.

The loop isn't finished. It's a system under construction. But even in its current rough state, it's changed how I spend my day, and that's enough to keep iterating on it.

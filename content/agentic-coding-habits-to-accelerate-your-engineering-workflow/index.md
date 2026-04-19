---
title: Agentic coding habits to accelerate your engineering workflow
date: "2026-04-19T09:00:00.000Z"
tldr: Five practical habits for working effectively with coding agents, shifting from writing code to designing the systems that write it, and accelerating the move toward systems thinking.
---

Coding agents aren't just speeding up development, they're changing what it means to be a senior engineer.

I've been coding with agents for a while, watching the tools evolve from rough experiments to something I now rely on daily. Last year I was hand-rolling workflows with Cursor. Now I'm all in on Claude, for better or worse.

It's an unusual time to be a software engineer. I'm also very aware of where this sits in my career.

As you become more senior, your role shifts from writing code to improving how others write it. That comes through teaching, guiding system design, and making architectural decisions shaped by experience.

A junior engineer can now generate production-level code without fully understanding it. That's new.

The shift isn't about writing code faster. It's about designing the system that writes the code.

So these are some habits I've picked up that have served me well so far.

## 1. Make it work, tune it, automate it

This applies broadly to engineering, but it's especially relevant when working with coding agents.

When building a new skill or workflow, it's easy to get stuck trying to design the perfect solution upfront. Instead:

- **Make it work:** Get a minimal set of steps that produces most of the desired outcome.
- **Tune it:** Improve reliability, remove manual steps, and refine edge cases.
- **Automate it:** Lock it in so it runs consistently without intervention.

Perfection is the end state, not the starting point.

## 2. Review intent, structure, and complexity, not line by line

Once teams adopt agentic workflows, pull request volume tends to increase. That raises the question: why are we still reviewing code line by line?

In reality, full line-by-line comprehension was always rare.

Effective reviews focus on:

- **Intent:** what problem is this solving?
- **Structure:** how is the system organised?
- **Complexity:** where are the risks and failure modes?

Senior engineers already work this way. They map systems mentally, question assumptions, and evaluate how code evolves over time.

That doesn't change with agent-generated code. If anything, it becomes more important.

When speed matters, reviewing together with the author can dramatically accelerate understanding.

## 3. 80/20 rule for planning

I rarely plan to the nth degree when working with agents.

Instead, I aim for a plan that captures roughly 80% of the value, and deliberately leave 20% undefined.

The key is knowing what needs precision and what can be discovered through iteration.

Plans are cheap. Execution reveals truth.

Rather than endlessly refining a plan, it's often faster to build something concrete, identify weaknesses, and iterate.

There have been times I've built multiple versions of the same solution, extracted the learnings, and used that to inform a better approach.

## 4. Trust in determinism, commit to scripts

Watching an agent generate scripts on the fly sounds impressive. Most of the time, it's doing something I don't actually want.

It also introduces unnecessary variability.

When a workflow requires data processing or repeatable steps, I generate a script once, review it carefully, and reuse it.

Determinism matters.

A known, verified script run 100 times is far more valuable than something "clever" generated differently each time.

## 5. Outsource what is easy, invest in what is hard

To focus on high-impact work, I constantly look for what can be offloaded.

This usually includes:

- Code generation
- Git workflows
- Managing stacked pull requests
- Prioritising review feedback

These tasks still require oversight, but not constant attention.

By identifying repeatable parts of my workflow and delegating them to agents, I create more space for solving harder problems.

My work has shifted from writing and designing code to planning and verifying it.

A simple state machine is often enough to formalise and automate these workflows over time.

If a task is repeatable, it's a candidate for removal.

## Conclusion

The biggest shift isn't that agents can write code. It's that they force us to think in systems, not implementations.

Senior engineers have always been moving in this direction. Agents just accelerate it.

The real question isn't how good the tools get, but how quickly we adapt to working through them.

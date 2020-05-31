---
title: Help me, help you - Code Review
date: "2019-10-24T12:18:00.000Z"
---

Code Reviews are one of the easiest ways to help your team-mates. There are a number of benefits for both the reviewer and pull request author:

- Product knowledge sharing through anecdotes or code samples

- Sharing techniques for writing maintainable code

- Differing perspectives collaborating on a single solution

I find it helpful to consider the intent of each party participating in a code review. Let’s think about what’s important to both the reviewer and author and how each can contribute to the success of the other.

### As an Author, I want to submit my code for peer review, so that I can gather feedback and iterate on my solution

When you submit a new pull request, depending on your previous experiences, you might feel nervous about the response from your peers. It’s normal to be attached to your own code if you’ve spent a long time writing it, however this attachment can be a detriment to your willingness to accept feedback. While it’s not easy not to take feedback personally, it’s better to assume that the person providing feedback has the same good intentions you had when writing the code.

The time it takes from when you open a pull request, to when it is merged varies widely based on it’s content, risk and testability (among other things). When specific people or teams are the best people to review your code, it helps to reach out to those people and let them know you’d like them to review it. It is your responsibility to follow up with them and get your code reviewed.

It can make a huge difference if the pull request has a clear (and sometimes, thorough) description, breaking down context for the change, what the effect of the change is, and how it will be tested. In an ideal world, we’d be submitting automated tests along with our code, but this is not always possible. We should strive as a team to prove that our code works as expected, and including tests (manual or automated). This will aid the reviewer in understanding the reason for your change, so that they can in turn help you, by giving feedback. 

Pull requests that are matched to tickets are often seen as a 1-1 relationship. I don’t subscribe to this idea, and instead I’d prefer to see one large code change broken down into multiple chunks (1 ticket, many pull requests) that can be easily pieced together (if necessary) and follow a progression to a releasable version of your code. Too often we try to build the whole solution and release it to users all at once, often forgetting the risk involved with doing so. In many cases, hiding new code paths with feature flags can make your code releasable, without necessarily running in production straight away. Breaking code down into manageable chunks, makes the review process easier for both you and the reviewer.

**TLDR;**

- Make the pull request clear and concise.

- Follow-up reviewers to look at your pull request

- Provide comprehensive reasoning, where possible for the change

- Break down large code changes into smaller chunks

## As a Reviewer, I want to review code to ensure quality and accuracy and provide relevant feedback to help the Author move forward

Reviewing code can be difficult at times - when there are a large number of lines or not enough context in the description, it can make reviewing painful. As the reviewer, you want to ensure the correctness of the code in terms of the objective, rather than aesthetic - however the quality of the code organisation and formatting can have an impact on the maintainability of the code in the long term. In a large team with many contributors, or an open source codebase, this is particularly important. Thankfully, there are a number of automated formatting and code linters available in many different languages to help code authors meet this standard more easily. 

When reviewing code, it’s easy to look at the code at face value and forget about the intent, requirements and restrictions under which the author wrote said code. Without knowing all of this, the reviewer should make an attempt not to assume the worst of the author and instead work with them to provide feedback in a polite and professional manner. 

As a reviewer, you can only provide feedback from the information you’ve been given, whether that’s code, a description of the problem or a diagram of the solution. However, sometimes that is not enough, and you need to also seek further information from the author in order to provide great feedback. Your approach may vary depending on the size of the pull request, as with anything else in software development - it’s not a one size fits all situation, however it’s up to you as a reviewer to provide feedback that helps move the process forward.

**TLDR;**

- Automate as much of the formatting concerns as possible

- Discover the intent behind the code, not just the code itself

- Seek further information, if something is not clear

## Before your next pull request

You can only get out of the pull request process as you put in, both as a reviewer and an author. On both sides of the equation, there are many things to consider before code gets shipped to production, many of which I haven’t discussed today. Before you review or open your next pull request, understand how you can make the process easier for you and your colleagues - help them, help you.
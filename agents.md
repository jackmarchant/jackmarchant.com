# Agents Quick Start

## Project overview
- PHP + Slim Framework app
- Twig templates render markdown blog content
- Main source code lives in `src/`

## Key paths
- `src/` application code (controllers, services, routes, DI config)
- `content/` markdown posts
- `templates/` Twig templates
- `public/` static assets and web root

## Local development
1. Install dependencies: `make install`
2. Start the local server: `make up`
3. Open: `http://localhost:8000`

## Working rules
- Keep changes focused and minimal.
- Prefer editing existing files over adding new architecture.
- Follow existing code and template style.
- Do not commit build artifacts or dependencies.

## Blog post requirements
- Every blog post **must** include a `tldr` field in its frontmatter.
- The `tldr` should be a single line (no newlines) of 1–2 sentences summarising the key points and main takeaways.
- Place the `tldr` field after the `date` field in the frontmatter block.
- Avoid double-quote characters (`"`) inside the `tldr` value.

Example frontmatter:
```
---
title: My Post Title
date: "2024-01-01T09:00:00.000Z"
tldr: A brief summary of the key points and action items readers will take away from this post.
---
```

## Validation
- There is no dedicated lint/test suite configured in this repository.
- For app changes, at minimum run local server (`make up`) and verify relevant pages load.

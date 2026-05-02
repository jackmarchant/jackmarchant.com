# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Commands

```bash
make install   # install Composer dependencies
make up        # start local dev server at http://localhost:8000
make build     # generate static site into dist/
```

There is no test suite. After any app change, run `make up` and verify the affected pages load correctly.

## Architecture

This is a PHP blog built with Slim Framework 4 and Twig. It has two deployment modes from the same codebase:

- **Dynamic (Heroku):** `public/index.php` bootstraps Slim, PHP-DI, and serves requests. The `Procfile` points Apache at `public/`.
- **Static (Netlify):** `build.php` runs the same services outside of Slim and writes pre-rendered HTML to `dist/`. `netlify.toml` invokes this as the build command.

### Request flow (dynamic mode)

`public/index.php` → creates PHP-DI container → loads `src/dependencies.php` (registers services) → `src/routes.php` (two routes: `/` → `BlogListController`, `/{post}` → `BlogPostController`) → controller calls `PostService` → renders `templates/index.twig`.

### The single template

`templates/index.twig` is the only template. It conditionally renders two layouts:
- When `posts` is set → blog listing with tag filter bar
- When `post` is set → individual post view

The same template is used by both the dynamic controllers and `build.php`.

### Content and PostService

Blog posts live in `content/{slug}/index.md`. Each file has a frontmatter block delimited by `---`, followed by the body.

`PostService` parses frontmatter with a custom colon-split parser (not a YAML library) — **avoid colons in frontmatter values** since the parser splits on the first `:` per line. Double-quote characters are stripped from values.

Tags can be declared in frontmatter (`tags: elixir, php`) or will be inferred from the post title and content via `inferTags()` keyword patterns. If no keywords match, the tag defaults to `engineering`.

### Markdown rendering

`Markdown` wraps the Parsedown library and adds one post-processing step: bare Twitter/X URLs that Parsedown converts to `<p><a>` links are replaced with styled `.tweet-card` divs.

### Dependency injection

Controllers accept the full `DI\Container` in their constructor and pull named services from it (`logger`, `renderer`, `PostService`, `settings`). New services are registered in `src/dependencies.php`.

### Markdown content response

Both controllers support returning raw Markdown when the request includes `Accept: text/markdown`. This renders a plain-text representation of posts for LLM/agent consumers.

## Blog post conventions

Every post **must** include a `tldr` field in frontmatter, placed after the `date` field:

```
---
title: My Post Title
date: "2024-01-01T09:00:00.000Z"
tldr: A single line (no newlines) of 1-2 sentences summarising key points.
---
```

- No double-quote characters (`"`) inside `tldr`
- No colons (`:`) in frontmatter values
- The post directory name becomes the URL slug

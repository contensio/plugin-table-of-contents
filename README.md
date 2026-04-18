# Table of Contents

Auto-generates a table of contents from H2 and H3 headings in the post body. Rendered server-side (SEO-friendly links) and injected inline just before the content blocks. A small JS snippet adds matching `id` attributes to the rendered headings so the anchor links work.

The TOC is only shown when a post has **3 or more** qualifying headings.

---

## Requirements

- Contensio 2.0 or later

---

## Installation

### Composer

```bash
composer require contensio/plugin-table-of-contents
```

### Manual

Copy the plugin directory and register the service provider via the admin plugin manager.

No migrations required.

---

## How it works

### Heading extraction

`HeadingExtractor::extract($blocks, $langId)` scans every block in document order:

| Block type | How headings are extracted |
|---|---|
| `heading` | `data.level` (h2 or h3) + `translations[$langId].text` |
| `richtext` | `/<(h[23])[^>]*>(.*?)<\/h[23]>/` matched against the HTML |

H4 and deeper are intentionally excluded — they're too granular for navigation.

### Slug algorithm

Both PHP and JavaScript use the same algorithm:

```
1. Lowercase
2. Remove non-word, non-space, non-hyphen characters
3. Replace spaces/underscores with hyphens
4. Collapse repeated hyphens
5. If empty → 'section'
6. Deduplicate: append -2, -3, … for repeated headings
```

This guarantees that the TOC `href="#slug"` always matches the `id` added by the JS.

### Hook placement

Hooks into `contensio/frontend/post-before-content` — fires just before the blocks wrapper `<div class="contensio-post-body">`.

### JS heading IDs

A small inline `<script>` runs on `DOMContentLoaded`. It scans `.contensio-post-body h2, h3`, applies the same slug algorithm, and sets `el.id`. This handles:
- `heading` blocks rendered as bare `<h2>` / `<h3>` (no `id` by default)
- H2/H3 inside `richtext` blocks' HTML

---

## Minimum headings threshold

The TOC is suppressed for posts with fewer than 3 qualifying headings. Change this in `TableOfContentsServiceProvider`:

```php
private const MIN_HEADINGS = 2;
```

---

## Customising

### Override the Blade view

```
resources/views/vendor/toc/partials/toc.blade.php
```

Available: `$headings` — array of `['level' => 'h2'|'h3', 'text' => string, 'id' => string]`.

### Collapsible TOC

Wrap the `<ol>` with Alpine.js to make it expandable/collapsible:

```html
<div x-data="{ open: true }">
    <button @click="open = !open" class="...">
        Contents <span x-text="open ? '▲' : '▼'"></span>
    </button>
    <ol x-show="open" class="...">
        ...
    </ol>
</div>
```

### Sticky sidebar

To float the TOC in a sidebar, change the post layout to a two-column grid and render the TOC in the sidebar column. The `$headings` data can be passed through a shared view composer or a second pass of `HeadingExtractor::extract()`.

---

## Styling

The TOC wraps in `<nav class="contensio-toc …">`. H3 items get `pl-4` indentation to show hierarchy. Target in CSS:

```css
.contensio-toc { … }
.contensio-toc ol { … }
.contensio-toc a { … }
.contensio-toc li.pl-4 { … }  /* H3 level */
```

---

## Hook reference

| Hook | Type | Args | Description |
|------|------|------|-------------|
| `contensio/frontend/post-before-content` | Render | `Content, ContentTranslation` | Just before the post blocks wrapper |

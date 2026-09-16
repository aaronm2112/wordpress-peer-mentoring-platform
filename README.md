# Portfolio Showcase

Sanitized, standalone code excerpts from a WordPress peer mentoring platform. These files are meant to be read, not run — they illustrate implementation patterns rather than a wired-together application.

## Examples

| File | Demonstrates |
| --- | --- |
| [01-custom-post-types.php](examples/01-custom-post-types.php) | A hierarchical `guide` post type containing ordered `activity` posts, linked by meta rather than `post_parent`, with a helper to fetch a guide's activities in sequence |
| [02-template-registration.php](examples/02-template-registration.php) | Registering a nested page template and resolving it with a child-theme-first, parent-theme-fallback lookup |
| [03-session-ajax-handler.php](examples/03-session-ajax-handler.php) | A nonce-verified AJAX endpoint gated by capability checks, input validation, and a facilitator/participant relationship check |
| [04-activity-progress-tracking.php](examples/04-activity-progress-tracking.php) | Idempotent activity start/complete tracking that avoids duplicate open events when a user revisits or re-navigates |
| [05-activity-navigation.js](examples/05-activity-navigation.js) | Client-side AJAX navigation that defers to a server-chosen full-page-render fallback instead of always patching the DOM |

## How the Post Types Relate

A `peer_guide` is a container (optionally nested) for an ordered sequence of `peer_activity` posts. Each activity links back to its guide through a `guide_id` meta field rather than `post_parent`, which keeps the guide hierarchy free for organizing sections independently of activity order. Progress is tracked per user, per guide, per activity, using a lightweight `peer_progress_event` post rather than a bespoke database table — enough to demonstrate state transitions without reproducing production schema.

## Accessibility

Accessibility is treated as an engineering practice rather than a separate audit step: semantic HTML and native form controls first, ARIA only to fill genuine gaps, keyboard-operable interactions, and visible focus and error handling on any dynamic content. These principles apply across the templates and AJAX flows above, even though a dedicated form example isn't included here.

## Scope and Privacy

These files use generic names, WordPress-native data structures, and no real identifiers, credentials, URLs, or client-specific content. They are excerpts adapted from a larger private codebase and are not a complete, runnable plugin or application.

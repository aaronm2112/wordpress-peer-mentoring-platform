# Peer Mentoring Platform Showcase

A sanitized WordPress mini-plugin that demonstrates four implementation patterns from a peer mentoring application. It is designed for a public portfolio repository, not as a complete product.

## What It Demonstrates

| Example | Engineering focus |
| --- | --- |
| [Plugin bootstrap](peer-mentoring-platform/peer-mentoring-platform.php) | Plugin organization, explicit load order, and a visible WordPress entry point |
| [Nested template registry](peer-mentoring-platform/includes/class-template-registry.php) | Page-template registration plus child-theme, parent-theme, and plugin fallbacks |
| [Session endpoint](peer-mentoring-platform/includes/class-session-endpoints.php) | Nonce verification, capability checks, relationship authorization, request validation, and JSON responses |
| [Activity progress service](peer-mentoring-platform/includes/class-activity-progress.php) | Server-derived context, activity transitions, idempotent open-event tracking, and safe AJAX content loading |
| [Activity navigation](peer-mentoring-platform/assets/js/activity-navigation.js) | `fetch`, loading feedback, structured error handling, and a server-directed full-render fallback |
| [Reflection form](peer-mentoring-platform/templates/partials/reflection-form.php) | Semantic labels, help text, validation feedback, and keyboard-friendly error recovery |

## Installation

1. Copy `peer-mentoring-platform` into `wp-content/plugins/` in a disposable local WordPress installation.
2. Activate **Peer Mentoring Platform Showcase** from WordPress Admin.
3. Create a page and choose **Peer Session Dashboard** in Page Attributes, or add the shortcode below to any page.

```text
[peer_mentoring_showcase activity_id="123"]
```

For the activity demo, create a `peer_activity` post, set its `peer_program_id` custom field to an existing `peer_program` post ID, and pass the activity ID to the shortcode. The default access policy permits any signed-in user; projects can replace it with their own policy through the documented WordPress filter in the activity service.

## Manual Verification

1. Confirm the **Peer Session Dashboard** template is available in the page-template selector.
2. Assign the template to a page and confirm the page renders the showcase shortcode.
3. Open a page containing the shortcode while signed in and inspect the activity region and live status message.
4. Add a button or link with `data-peer-activity-id` set to a valid activity ID; confirm activity content loads without a full page refresh.
5. Set the activity's `peer_requires_full_render` custom field to a truthy value; confirm the browser follows the server-provided URL instead of injecting the content asynchronously.
6. Submit the reflection form empty using the keyboard; confirm focus moves to the error summary and the textarea exposes an error state.
7. Submit a nonempty reflection; confirm client-side validation allows the native form submission path.
8. Call `peer_mentoring_create_session` with invalid nonce, capability, relationship, participant, and duration values; confirm each request returns a structured error response.
9. Load the same activity repeatedly; confirm only one open `peer_progress_event` record exists for the user, program, and activity.
10. Navigate to another activity; confirm the previous open event is marked completed before the next activity starts.

## Privacy and Scope

This showcase uses generic names, WordPress post types, user meta, and custom fields in place of the original application's private data model. It intentionally excludes production configuration, user records, client content, real identifiers, database exports, private URLs, and licensed third-party integrations.

Before publishing, review the Git history as well as the current files. A clean portfolio repository should contain this directory and selected documentation only, never an entire WordPress installation or its configuration.
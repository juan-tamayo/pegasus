# Module: gmail-connection

Inherits commands, structure, style, testing, and boundaries from SPEC.md.

Depends on mailbox-connections. This is the Google-specific adapter; common mailbox lifecycle and optional owner password login are specified there.

## Responsibility
Identify the owner through Google, then obtain separate read-only Gmail consent and provide server-side credentials to the sync module.

## Contract
- Google identity scopes: openid, email, profile. Gmail scope: https://www.googleapis.com/auth/gmail.readonly. Do not request modify, send, or full-mail scopes.
- Use server-side OAuth authorization code flow with state validation and offline access for background synchronization. Preserve an existing refresh token when a response omits a new one.
- Restrict login to configured owner identity; verify Google subject and verified email, never silently link accounts on untrusted email alone. Mailbox consent is bound to the authenticated owner session and connection intent using OAuth state. Additional Google mailboxes may have different subjects; identify each by its returned Google subject and never silently replace another connection.
- Expose safe connection state: disconnected, connected, reconnect_required. No tokens in Inertia props.
- The Gmail adapter requests an access token through this module, with serialized refresh handling. Permanent authorization failure becomes reconnect_required and stops retry loops.

## Routes
GET /login; GET /auth/google/redirect; GET /auth/google/callback; POST /logout.
Authenticated: GET /settings/mailboxes; POST /settings/mailboxes/google/connect; GET /settings/gmail/callback; DELETE /settings/mailboxes/{connection}.
OAuth callbacks validate session state. All state-changing application forms use CSRF protection. Connect may return an Inertia external redirect to Google.

## Disconnect
Immediately mark disabled, increment connection generation, block queued jobs, and erase local tokens; attempt remote revocation and report if revocation could not be confirmed. Each job must verify connection generation before external access and writes. Retained history remains viewable through Google identity or the provisioned owner login.

## Acceptance and tests
- Valid owner login regenerates session; non-owner, mismatched connection intent, invalid state, denied consent, or missing required scope never creates an active connection.
- Refresh succeeds without exposing credentials; missing refresh token has an actionable reconnect state.
- Disconnect blocks pending jobs and erases tokens; callback and concurrent-job races cannot resurrect a disabled connection.

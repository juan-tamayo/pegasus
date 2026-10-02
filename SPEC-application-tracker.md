# Module: application-tracker

Inherits shared conventions from SPEC.md; depends on classification read models.

## Routes and view models
GET /applications with validated search, status, mailbox, review, sort, and page parameters. GET /applications/{application}; PATCH /applications/{application} for corrections. GET /reviews. DELETE /settings/data clears local Pegasus records and disconnects all mailboxes after explicit in-app confirmation.

Inertia props provide paginated ApplicationSummary rows: ID, source connection/address/provider, company/role nullable, effective status, review flag, known/unknown application date, last message date, last response date, waiting days nullable. Details add chronological evidence events. Connection state, scan range, and last successful sync remain visible. Escape plain-text email content; never render raw email HTML or remote images.

## Derived fields
Effective status = manual override when present, otherwise detected status. Awaiting days = whole elapsed days since known application timestamp in the user timezone, only while awaiting response; unknown dates display Unknown, not zero. An observed confirmation date can be labeled as such when the original submission date is unknown. Display 'No further response found' with scan coverage rather than claiming exhaustive knowledge.

## UX
Desktop table and mobile cards, keyboard-accessible filters and dialogs, text plus color status badges, useful empty states, retry/reconnect actions, and clear progress. Source links use validated provider identifiers and never expose tokens. A newly detected status change is visible through an updated indicator/timeline, with no outgoing notifications in MVP.

## Acceptance and tests
- Filters and pagination preserve query state; status counts reflect persisted owner-scoped records.
- Corrections survive reload and future sync; dismissing a false positive removes it from the active review queue.
- Other users' record IDs cannot be read, modified, assigned, or deleted; mobile and keyboard journeys support the same core actions.

Show per-mailbox freshness and partial/error states in combined views. Only offer Open in mailbox when a validated provider link exists; generic IMAP has no universal message deep link, so always provide the local evidence view. Never construct a Gmail URL for an IMAP message.

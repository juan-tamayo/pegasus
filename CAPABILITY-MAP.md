# Pegasus capability map

Pegasus is a personal web app that reads messages from Gmail, Namecheap Private Email, and compatible custom-domain IMAP mailboxes related to job applications and shows the latest known status for each company and role.

## Starting assumptions
- First version serves one owner with multiple connected mailboxes.
- Request read-only Gmail access through Google OAuth.
- An automatic receipt confirms an application; it does not count as a recruiting decision.
- No response means no later response found in the messages scanned; it never implies rejection.
- Track separate roles at the same company separately.
- Show the source email and allow correction of uncertain classifications.

## Capabilities
| Module | Responsibility | Depends on |
| --- | --- | --- |
| mailbox-connections | Owner authentication, provider-neutral mailbox records, IMAP setup, disconnect | — |
| gmail-connection | Google identity and Gmail OAuth adapter | mailbox-connections |
| email-sync | Retrieve and incrementally synchronize relevant Gmail messages | mailbox-connections, gmail-connection |
| application-classification | Read message content; distinguish confirmation, recruiting response, rejection, offer, and uncertain results; associate company and role | email-sync |
| application-tracker | Display applications, latest status, elapsed time, supporting messages, and manual corrections | application-classification |

Build order: mailbox-connections → gmail-connection → email-sync → application-classification → application-tracker.

## First-version behavior
- Dashboard statuses: Awaiting response, Response received, Interview, Offer, Rejected, Needs review.
- Display company, role when known, application date when known, last update, and days waiting.
- Preserve message chronology so an older confirmation cannot overwrite a newer rejection.
- Avoid treating newsletters, job alerts, and general recruiting advertisements as applications.
- Show the date range and last successful synchronization; never claim a complete history after a partial scan.
- Present uncertain matches for review instead of inventing a company or application.

## Scope baseline and stack
The user specified Laravel and Vue and requested scope preparation. Personal use and a six-month initial scan are working defaults; see [scope](docs/SCOPE.md). Use Laravel 13 + Vue 3 with Inertia, server-side Google OAuth, and queued email processing.

## Module specifications
- [mailbox-connections](SPEC-mailbox-connections.md)
- [gmail-connection](SPEC-gmail-connection.md)
- [email-sync](SPEC-email-sync.md)
- [application-classification](SPEC-application-classification.md)
- [application-tracker](SPEC-application-tracker.md)

Shared conventions: [SPEC.md](SPEC.md). External setup and provider decisions: [docs/SETUP.md](docs/SETUP.md). Build plan and checklist: [tasks/plan.md](tasks/plan.md), [tasks/todo.md](tasks/todo.md).

## Current state
Initial Laravel/Vue implementation is running locally. Login, application tracker, and mailbox setup are implemented. Automatic sync/classification and live-account verification remain pending; see [build status](docs/PROGRESS.md).

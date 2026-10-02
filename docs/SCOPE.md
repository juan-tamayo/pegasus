# Pegasus product scope

## Outcome
Know which companies have acknowledged an application, which have replied, and which have rejected it without manually sorting mailboxes. Organize by application (company + role + application cycle), not just company.

## Scope baseline
The user specified Laravel and Vue and authorized preparation of the scope and coding plan. Working defaults: one owner, last six calendar months, English-language classification first. These are editable product defaults, not claims that the user explicitly selected every detail.

## First-version journey
1. Sign in with Google or a locally provisioned owner account. No Google account is required for IMAP-only use; public registration stays disabled.
2. Add Gmail through Google consent, Namecheap Private Email through its preset, or another compatible IMAP mailbox through custom settings. Keep mailbox credentials separate from Pegasus login.
3. See the scan date range and start the initial scan. The default scans received inbox messages; archived-mail inclusion (or selected IMAP folders) is an explicit option. Exclude spam and trash.
4. Watch progress without waiting in a long-running web request.
5. View company, role, current status, last email date, and days awaiting response.
6. Open an application to see a chronological evidence timeline and source-message links.
7. Review uncertain classifications, correct company/role/status, or dismiss irrelevant mail.
8. Sync manually; while the worker and scheduler run, check every 15 minutes. Show meaningful new status changes in the app.
9. Disconnect an individual mailbox or separately delete stored Pegasus data.

## Status rules
| Status | Meaning |
| --- | --- |
| Awaiting response | Confirmation or user-confirmed application, with no subsequent substantive response found |
| Response received | Substantive recruiting reply without enough evidence for a more specific stage |
| Interview | Explicit interview invitation or scheduling/progression message |
| Offer | Explicit job offer |
| Rejected | Explicit rejection for this application |
| Needs review | Insufficient or conflicting evidence; not a recruiting outcome |

An automatic receipt is an application confirmation, not a recruiting decision. Needs review is also an independent flag: uncertainty about a new message must preserve a previously confirmed status. Never infer rejection from elapsed time. Never infer an application solely from a job advertisement. An application with only a rejection email is valid, with application date unknown.

## Screens
- Sign-in: concise explanation and Google or owner email/password sign-in.
- Connection/setup: provider selection, mailbox connection, scan range, folder selection, connection errors.
- Applications: search company/role; status and mailbox filters; count summaries; paginated table on desktop and readable cards on mobile; scan coverage and freshness always visible.
- Application detail: company, role, status, timeline, source evidence, correction controls.
- Review: uncertain messages with suggested classification and confirm/correct/dismiss actions.
- Settings: account connection, sync preferences, disconnect, delete data.

Visual direction: restrained productivity interface, readable typography, quiet neutral surfaces, one accent color, explicit text labels alongside status colors. Provide loading, empty, partial-sync, offline-provider, and reconnect states. No fabricated records in the real dashboard; demonstration data must be clearly labeled and development-only.

## Out of scope
Sending email, applying to jobs, resume editing, attachments/OCR, calendar scheduling, public signup, billing, mobile apps, push notifications, outbound notification email, and production deployment in this preparation phase.

## Product acceptance
- Confirmation followed by rejection produces one rejected application with both events.
- Two roles at the same company remain separate; repeat applications are not merged blindly.
- Repeated synchronization creates no duplicate messages or timeline events.
- Ambiguous messages can be corrected and the correction survives subsequent syncs.
- Awaiting response is qualified by scanned date range and last successful sync.
- A partial scan or failed classification never presents itself as complete.
- All app data stays scoped to its owner; disconnect stops future access to that mailbox.

## Supported mailbox connections
- Gmail and Google Workspace: Google OAuth + Gmail API.
- Namecheap Private Email: IMAP over verified TLS, with a preset for mail.privateemail.com:993 and the full email address as username.
- Other custom domains: configurable IMAP host, port, username, and password/app password. A custom domain identifies an address, not its hosting protocol; its provider must offer compatible IMAP access. OAuth-only IMAP services need a future provider adapter and are not promised by generic IMAP support.
- Multiple mailboxes feed the same dashboard with source labels and independent synchronization health. An error in one mailbox must not block the others. Matching across mailboxes requires explicit review; never silently merge applications by company alone.

Gmail permission is provider-enforced read-only. IMAP credentials may allow broader access; Pegasus enforces read-only operations, never sets Seen, and never sends, moves, or deletes email. No SMTP settings are needed.

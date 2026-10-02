# Pegasus implementation plan

## Outcome and baseline
Implement the personal application tracker described in docs/SCOPE.md, using the user-required Laravel + Vue stack. Scope and planning preparation are authorized; this handoff contains no implemented application. Tasks are tracked in [todo.md](todo.md).

## Decisions
Use the official Laravel Vue/Inertia starter, same-origin session auth, server-only Gmail OAuth and IMAP access, a relational SQLite database for personal use, and database-backed queued synchronization. Do not substitute a JavaScript backend or static-only site host. Gmail uses read-only permission; IMAP uses verified TLS and application-enforced read-only operations.

## Dependency order
Foundation → owner authentication/common mailbox contract → Google identity and Gmail consent/token lifecycle → IMAP connection → initial sync → incremental sync → classification → projection/review → dashboard/details → final QA. Develop screens against clearly labeled synthetic fixtures while external configuration is unavailable.

## Vertical delivery slices
1. Bootable Pegasus shell and Google sign-in screen.
2. Owner can connect/disconnect Gmail, Namecheap, and custom IMAP mailboxes and see connection state.
3. Owner can start a bounded scan and see progress/errors.
4. Emails produce testable application records with source evidence.
5. Owner can inspect, filter, and correct the application tracker.
6. Full synthetic journey and live-provider smoke test when configured.

## Checkpoints
After identity, after sync, after classification, and after tracker: run focused tests and applicable build checks, record evidence in todo.md, and present a working slice for review. Avoid asking for permission again for routine work already authorized. Any next request to start implementation can begin task 1 directly.

## Risks and responses
| Risk | Response |
| --- | --- |
| False rejection or wrong company association | Evidence validation, review queue, manual correction |
| Incomplete inbox coverage | Visible dates/labels, paginated scans, history reconciliation |
| Slow imports or API quotas | Queue batches, backoff, locks, resumable checkpoints |
| Revoked credentials | Explicit reconnect state and stop background access |
| Private email exposure | Minimal transient text, encryption, redacted logs, provider disclosure |
| Unknown provider behavior | Small synthetic labeled evaluation before live processing |
| Incorrect waiting claims | Separate receipts from substantive replies; never infer rejection |

## Ready-to-code checklist
Product scope, module contracts, data model, routes, expected commands, testing strategy, and ordered tasks are documented. External setup gaps are in docs/SETUP.md and do not block scaffolding or fixture-based development. Preserve these documents when generating the starter; revise them when implementation decisions change.

## Mailbox expansion sequencing
Keep existing numbered task IDs stable. Execute 2a after 2; 3a after 3; 6a–6b after 6; 7a after 7; 10a after 10. Then continue 11–19 with both providers. Added tasks and extended acceptance are in todo.md. Google-only identity is no longer a prerequisite for using IMAP. Generic mailboxes replace the former single Gmail-connection schema; Google-specific module IDs remain stable.

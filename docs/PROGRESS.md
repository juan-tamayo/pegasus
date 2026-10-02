# Pegasus first build

## Implemented
- Official Laravel Vue starter updated to Laravel 13.34 with Vue 3, Inertia 2, TypeScript, and locked dependencies.
- Private owner provisioning/reset, hashed passwords, session login/logout, login throttling, closed public registration and recovery.
- Google identity sign-in and separate read-only Gmail consent, session-bound state checks through Socialite, verified-email/scope checks, encrypted tokens, and explicit setup/error messages.
- Namecheap/custom IMAP connection form. Validate allowed mail host and encrypted port combination; pin a public IPv4 address with TLS peer-name verification; connect/authenticate/read-only EXAMINE INBOX through Webklex before saving encrypted credentials. No real owner mailbox test yet.
- Independent connected-mailbox records, credential hiding, local disconnect with generation increment, retained history.
- Database models for applications/messages/evidence events; owner-scoped search, pagination, status/mailbox filters, counts, dates/unknown values, and waiting days.
- Application detail timeline and persistent company/role/status corrections; manual status remains separate from detected status.
- Responsive desktop table/mobile cards, explicit empty/error/sample states, favicon.
- Opt-in local sample account with six fictional applications; production guard; sample cannot connect real accounts.

## Verification
Backend tests: 39 passing / 124 assertions, including owner scoping, credential encryption, private provisioning/reset, closed registration, OAuth consent intent/scopes, IMAP host validation, independent disconnect, and production-disabled demo.

Frontend type checking and production build passed. Browser journey verified in Chromium at 1440px desktop, 390px phone, and 320px small-phone sizes: filter, details, evidence, correction, reload persistence, reset to detected status, mailbox page; no JS page exceptions. A mobile overflow found during this check was corrected, and mobile cards now expose the same details.

npm and Composer vulnerability audits report no advisories. PHP style check uses Pint. Browser screenshots are local ignored artifacts in test-results, not committed mail data.

## Remaining work
- Provider-neutral sync contracts and normalized MIME values; Gmail token refresh and remote revocation; Google identity linking for a password-provisioned owner.
- Durable initial/incremental sync jobs, checkpoints, Gmail history reconciliation, IMAP UIDVALIDITY/cursors, scheduled polling, generation/race tests, provider-safe operational error handling.
- Configured semantic classifier, schema/evidence validation, labeled fixture evaluation, application matching/projection and review queue.
- Data deletion UI/background-race handling, scan progress/coverage and meaningful update indicators.
- Live Gmail/Namecheap connection and read-flag checks after owner credentials are configured; production host/backup policy before deployment.

Connecting a mailbox currently tests/stores the connection; it does not import mail. Sample records are explicitly fictional and are not results of email classification.

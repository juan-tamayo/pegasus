# Pegasus coding checklist

Implementation has started. Only verified completed tasks are checked; partial items and evidence are listed in docs/PROGRESS.md. Paths below are anticipated locations under the structure in SPEC.md; preserve starter conventions when choosing final names. Each task should touch about five product files or fewer; split further if needed. Task 1 is a mechanical starter generation exception.

## Task 1: Scaffold Laravel Vue foundation

- [x] Complete
- **Dependencies:** None
- **Acceptance:** Official starter boots; existing planning documents survive; lockfiles capture compatible versions.
- **Verification:** php artisan test; npm run build; verify the sign-in page renders.
- **Likely files:** Official starter generated files (one mechanical scaffold exception to the five-file guideline).
- **Scope:** Mechanical scaffold

## Task 2: Establish validation commands

- [x] Complete
- **Dependencies:** 1
- **Acceptance:** Type-check and formatting commands work; synthetic test conventions documented.
- **Verification:** npm run typecheck; ./vendor/bin/pint --test; php artisan test.
- **Likely files:** package.json, package-lock.json, tests/Pest.php or PHPUnit config, README.md
- **Scope:** Small/medium

## Task 3: Implement owner Google login

- [ ] Complete
- **Dependencies:** 2
- **Acceptance:** Owner can sign in; invalid state and non-owner are rejected; session is regenerated.
- **Verification:** php artisan test --filter=GoogleLogin; manually inspect signed-out screen using fake identity.
- **Likely files:** GoogleAuthController, user migration/model, routes/web.php, GoogleLoginTest
- **Scope:** Small/medium

### Checkpoint after task 3

- [ ] Focused tests and applicable frontend checks pass.
- [ ] Record results and remaining limitations; show the working slice for review.

## Task 4: Implement Gmail consent

- [ ] Complete
- **Dependencies:** 3
- **Acceptance:** Separate read-only consent stored encrypted; wrong mailbox and missing scope rejected.
- **Verification:** php artisan test --filter=GmailConsent.
- **Likely files:** MailboxConnection migration/model, GmailConsentController, routes/web.php, GmailConsentTest
- **Scope:** Small/medium

## Task 5: Implement token lifecycle

- [ ] Complete
- **Dependencies:** 4
- **Acceptance:** Refresh preserves an existing refresh token; revoked access requests reconnect; token values never serialize.
- **Verification:** php artisan test --filter=GoogleToken.
- **Likely files:** GoogleTokenService, MailboxConnection, GoogleTokenTest
- **Scope:** Small/medium

## Task 6: Expose connection and disconnect controls

- [ ] Complete
- **Dependencies:** 5
- **Acceptance:** Settings show safe connection status; disconnect disables queued work and erases credentials.
- **Verification:** php artisan test --filter=GmailDisconnect; npm run typecheck; npm run build.
- **Likely files:** MailboxSettingsController, settings/Mailboxes.vue, routes/web.php, GmailDisconnectTest
- **Scope:** Small/medium

### Checkpoint after task 6

- [ ] Focused tests and applicable frontend checks pass.
- [ ] Record results and remaining limitations; show the working slice for review.

## Task 7: Normalize Gmail messages

- [ ] Complete
- **Dependencies:** 6
- **Acceptance:** Paginated adapter handles multipart and HTML fallback; excludes attachments; bounded text carries truncation flag.
- **Verification:** php artisan test --filter=GmailMessage; synthetic malformed/large MIME cases.
- **Likely files:** GmailClient, NormalizedEmail, GmailMessageNormalizer, GmailMessageTest, synthetic fixture file
- **Scope:** Small/medium

## Task 8: Persist resumable scan progress

- [ ] Complete
- **Dependencies:** 7
- **Acceptance:** Message IDs deduplicate; run state survives failure; one active run per connection.
- **Verification:** php artisan test --filter=SyncPersistence.
- **Likely files:** sync_runs migration, email_messages migration, SyncRun model, EmailMessage model, SyncPersistenceTest
- **Scope:** Small/medium

## Task 9: Run initial import through a progress screen

- [ ] Complete
- **Dependencies:** 8
- **Acceptance:** Owner starts six-month scan; pages queue safely; progress distinguishes partial and complete.
- **Verification:** php artisan test --filter=InitialSync; npm run build; manually trigger synthetic scan.
- **Likely files:** InitialSyncJob, GmailSyncController, routes/web.php, SyncProgress.vue, InitialSyncTest
- **Scope:** Small/medium

## Task 10: Add incremental scheduling

- [ ] Complete
- **Dependencies:** 9
- **Acceptance:** History changes reconcile; expired cursor rescans; rate limits back off; disconnected jobs stop.
- **Verification:** php artisan test --filter=IncrementalSync, adjusting filter syntax to installed test runner; exercise expired-history fixture.
- **Likely files:** IncrementalSyncJob, routes/console.php, GmailClient, IncrementalSyncTest, MailboxConnection
- **Scope:** Small/medium

### Checkpoint after task 10

- [ ] Focused tests and applicable frontend checks pass.
- [ ] Record results and remaining limitations; show the working slice for review.

## Task 11: Implement structured classification

- [ ] Complete
- **Dependencies:** 10
- **Acceptance:** Validated classifier contract supports fixture/live adapters; unavailable provider is explicit; injection cannot trigger actions.
- **Verification:** php artisan test --filter=Classification; run labeled synthetic evaluation.
- **Likely files:** EmailClassifier contract, ClassificationResult, StructuredEmailClassifier, ClassificationTest, labeled fixture file
- **Scope:** Small/medium

## Task 12: Persist application evidence

- [ ] Complete
- **Dependencies:** 11
- **Acceptance:** Application/event/review records enforce relationships and deduplication; unknown values remain nullable.
- **Verification:** php artisan test --filter=ApplicationPersistence.
- **Likely files:** application tables migration, JobApplication model, ApplicationEvent model, ClassificationReview model, ApplicationPersistenceTest
- **Scope:** Small/medium

## Task 13: Project application status

- [ ] Complete
- **Dependencies:** 12
- **Acceptance:** Chronology, role matching, duplicate delivery, conflicting outcomes, and manual overrides follow module rules.
- **Verification:** php artisan test --filter=ApplicationProjection; assert confirmation/interview/rejection out-of-order sequence.
- **Likely files:** ClassifyMessageJob, ApplicationMatcher, ProjectApplicationStatus, ApplicationProjectionTest, EmailMessage
- **Scope:** Small/medium

### Checkpoint after task 13

- [ ] Focused tests and applicable frontend checks pass.
- [ ] Record results and remaining limitations; show the working slice for review.

## Task 14: Build application list

- [x] Complete
- **Dependencies:** 13
- **Acceptance:** Owner-scoped searchable/filterable paginated results render on desktop/mobile; freshness and coverage visible.
- **Verification:** php artisan test --filter=ApplicationList; npm run typecheck; npm run build.
- **Likely files:** ApplicationController, ApplicationPolicy, routes/web.php, applications/Index.vue, ApplicationListTest
- **Scope:** Small/medium

## Task 15: Build application details and corrections

- [x] Complete
- **Dependencies:** 14
- **Acceptance:** Timeline shows source evidence; corrections persist; unsupported cross-owner edits rejected.
- **Verification:** php artisan test --filter=ApplicationCorrection; keyboard-check correction flow.
- **Likely files:** ApplicationController, UpdateApplicationRequest, applications/Show.vue, routes/web.php, ApplicationCorrectionTest
- **Scope:** Small/medium

## Task 16: Build review queue

- [ ] Complete
- **Dependencies:** 15
- **Acceptance:** Confirm/correct/dismiss work transactionally; resolving twice creates no duplicate event.
- **Verification:** php artisan test --filter=ClassificationReview; npm run build.
- **Likely files:** ClassificationReviewController, ResolveReviewRequest, reviews/Index.vue, routes/web.php, ClassificationReviewTest
- **Scope:** Small/medium

## Task 17: Implement local data deletion

- [ ] Complete
- **Dependencies:** 16
- **Acceptance:** Confirmed deletion disconnects and blocks pending work; stored records removed; Gmail untouched.
- **Verification:** php artisan test --filter=DeletePegasusData including queued-job race.
- **Likely files:** DeletePegasusData action, settings controller, settings/Mailboxes.vue, routes/web.php, DeletePegasusDataTest
- **Scope:** Small/medium

## Task 18: Verify complete browser journey

- [ ] Complete
- **Dependencies:** 17
- **Acceptance:** Synthetic login/connect/import/filter/detail/correct/disconnect journey passes at desktop/mobile sizes; keyboard and error states checked.
- **Verification:** npx playwright test; php artisan test; npm run typecheck; ./vendor/bin/pint --test; npm run build.
- **Likely files:** playwright.config.ts, tests/e2e/pegasus.spec.ts, package.json, package-lock.json, development fixture seeder
- **Scope:** Small/medium

### Checkpoint after task 18

- [ ] Focused tests and applicable frontend checks pass.
- [ ] Record results and remaining limitations; show the working slice for review.

## Task 19: Complete configured live-account smoke test

- [ ] Complete
- **Dependencies:** 18 plus external setup
- **Acceptance:** Real owner consent, token refresh, Gmail and IMAP read-only scans, representative classification, and disconnect verified; gaps recorded honestly.
- **Verification:** Manual live smoke test after provider and classifier setup; confirm stored evidence matches mailbox and no send/delete scopes.
- **Likely files:** docs/SETUP.md, docs/VERIFICATION.md, README.md, tasks/todo.md
- **Scope:** Small/medium

## Execution evidence

Initial verification: 39 backend tests passed (124 assertions); type checking and production build passed; desktop/mobile browser journey passed; npm and Composer audits report no vulnerabilities. Final evidence is in docs/PROGRESS.md. Record actual command outcomes and manual checks as work lands. Never mark live integration verified using fixture-only tests.

## Mailbox expansion tasks
These tasks extend the existing checklist; original task IDs remain stable. Use the execution order in plan.md.

### Task 2a: Provision owner login without Google
- [x] Complete
- Dependencies: 2.
- Acceptance: privately provisioned owner signs in with a hashed password; public signup disabled; throttling/session regeneration and private reset supported.
- Verification: owner-login tests for valid/invalid credentials and session behavior.
- Likely files (medium): owner command, login controller, login page, routes, owner-login tests.

### Task 3a: Establish common mailbox contracts
- [ ] Complete
- Dependencies: 3 and 2a.
- Acceptance: multiple owner-scoped connections; provider-neutral IDs/cursors; Google adapter uses same lifecycle; mailbox identity separate from login identity.
- Verification: contract/ownership tests; Google connection intent state tests.
- Likely files (medium): mailbox migration/model, MailboxProvider contract, NormalizedEmail, contract tests.

### Task 6a: Implement secure IMAP adapter
- [ ] Complete
- Dependencies: 6 and 3a.
- Acceptance: Namecheap preset/custom hosts, verified TLS, read-only fetching, allowed public targets, bounded operations.
- Verification: adapter tests covering TLS failure, internal IPv4/IPv6/DNS targets, timeout, read-flag preservation, wrong credentials.
- Likely files (medium): ImapProvider, host validator, composer.json, composer.lock, adapter tests.

### Task 6b: Add mailbox management forms
- [ ] Complete
- Dependencies: 6a.
- Acceptance: owner tests/adds/disconnects separate mailboxes; password never returned; another connection remains active when one fails.
- Verification: connection feature tests and keyboard form walkthrough; typecheck/build.
- Likely files (medium): mailbox controller, request validation, Mailboxes.vue, routes, mailbox tests.

### Task 7a: Normalize IMAP MIME content
- [ ] Complete
- Dependencies: 7 and 6b.
- Acceptance: transfer encodings/folder identities normalize; attachment bodies excluded; truncation explicit.
- Verification: fixture tests for quoted-printable, multipart, missing Message-ID, and duplicate Message-ID across accounts.
- Likely files (small): IMAP normalizer, normalization tests, synthetic fixtures.

### Task 10a: Implement IMAP checkpoints and reconciliation
- [ ] Complete
- Dependencies: 10 and 7a.
- Acceptance: per-folder UIDVALIDITY/UID checkpoints survive retries; epoch reset safely rescans; independent mailbox locks and progress.
- Verification: incremental tests for UID reuse, copies, reset, partial processing, disconnect races, and one failed mailbox alongside one successful scan.
- Likely files (medium): mailbox_sync_states migration/model, ImapSyncJob, scheduling integration, sync tests.

## Extended acceptance for original tasks 11–19
- [ ] Classification and association are source-aware; cross-mailbox matches require review.
- [ ] Tracker includes mailbox filters, source labels, per-mailbox freshness/errors, and no fabricated IMAP deep links.
- [ ] Deletion/disconnect tests cover one mailbox and all mailboxes; pending jobs cannot recreate deleted data.
- [ ] Browser journeys cover IMAP-only owner and combined Gmail + Namecheap accounts.
- [ ] Live smoke test records Namecheap TLS connection, unchanged unread flags, incremental sync, and independent disconnect. Fixture tests do not satisfy this live check.

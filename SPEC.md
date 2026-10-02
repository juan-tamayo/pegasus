# Pegasus shared technical specification

## Objective and module index
Implement [the product scope](docs/SCOPE.md). Module boundaries and dependency order are recorded in [CAPABILITY-MAP.md](CAPABILITY-MAP.md); detailed provider/consumer contracts live in the SPEC-<module>.md files. This specification supplies conventions shared by all modules.

## Architecture
- Laravel 13 (^13.0), PHP 8.3+, official Vue starter, Vue 3, TypeScript, Inertia, Vite, Tailwind. Resolve compatible Inertia and UI-package versions from the starter at scaffold time; preserve its lockfiles.
- One same-origin Laravel application renders Vue pages through Inertia. Session cookies and CSRF protection; no browser-stored bearer tokens or separate public API.
- Owner email/password login (privately provisioned, no public signup), with optional Google login through Socialite. Gmail HTTP and IMAP adapters and classifier adapter behind small interfaces so tests do not contact real services.
- SQLite and database queue/cache for the personal MVP. Persistent storage, a queue worker, and scheduler required in any future hosting environment. Production database/host choice is deferred until deployment.
- Keep controllers thin; validation in Form Requests, authorization in policies, orchestration in actions/jobs, rendering in Vue.

## Intended structure
- app/Http/Controllers, Requests: validated HTTP entry points.
- app/Models, Policies, Enums: persisted data, ownership, status vocabulary.
- app/Services/Mail, Gmail, Imap, Classification: external-provider adapters.
- app/Actions, Jobs: synchronization and status projection.
- resources/js/pages, components, types: Vue UI and serialized view models.
- database/migrations, factories: schema and synthetic test data.
- tests/Unit, Feature: classification, projection, OAuth, sync, ownership tests.
- tests/e2e: browser journeys using synthetic inbox fixtures.

## Data model
All provider IDs are strings, never integer casts. Dates stored in UTC; render in the user's timezone.

| Table | Important fields and constraints |
| --- | --- |
| users | id, google_subject nullable unique, email unique, password_hash nullable, name, timezone |
| mailbox_connections | user_id, provider, address, display_name, encrypted credentials, expiry/scopes nullable, validated IMAP host/port/TLS settings nullable, connection_state, generation, last_success_at, scan settings; multiple per user |
| mailbox_sync_states | connection_id + folder key unique, Gmail history_id nullable or IMAP UIDVALIDITY/highest processed UID; provider-specific checkpoints |
| sync_runs | connection_id, state, scan_start/end, page checkpoint, counters, safe error code, started/completed_at |
| email_messages | connection_id + provider_message_key unique, provider_thread_id nullable, received_at, sender, subject, encrypted short evidence, relevance, processing_state; body is transient |
| job_applications | user_id, connection_id, company nullable, role nullable, applied_at nullable, detected_status, manual_status nullable, review_required, last_message_at, last_response_at |
| application_events | application_id, message_id, event_type, evidence excerpt, classification_version; unique message_id for MVP one-application-per-message assignment |
| classification_reviews | message_id unique, proposed structured result, reason, resolved_at, resolution |

A message covering several roles goes to review instead of arbitrarily attaching it to one. No uniqueness constraint on company + role: repeat applications are possible. Cross-owner relation assignment must fail validation and policy checks. Manually entered/corrected fields must be distinguishable from detected fields.

## Data handling
Encrypt OAuth tokens, IMAP passwords, and stored evidence at rest with Laravel encryption; exclude credentials from serialization. Do not persist full email bodies or attachments. Fetch bodies transiently for classification, process bounded text, and discard them. Retain minimal application metadata and short evidence until user deletion. A disconnected mailbox retains history unless the user deletes it. Delete-data removes app records and queued-work eligibility, never provider mail. Backups need a documented retention/deletion policy before hosting.

Real-email classification stays disabled until the operator chooses and configures a provider whose processing policy is acceptable; tests use synthetic fixtures. The UI must explain which provider receives email text before enabling this feature. Provider name, model version, and prompt version are configuration, not business logic.

## Commands after scaffolding
These commands are now available after setup. Browser tests require the running local server and the explicitly enabled sample workspace.

```sh
composer install
npm ci
php artisan key:generate
php artisan migrate
composer run dev
php artisan test
./vendor/bin/pint --test
npm run typecheck
npm run build
npx playwright test
```

Target typecheck script: vue-tsc --noEmit. Browser tooling is added in the browser-test task. Separately, when the development helper does not include these processes, run `php artisan queue:work --tries=3` and `php artisan schedule:work` in separate terminals.

## Code style
Follow starter formatting and Laravel conventions; typed PHP signatures, backed enums, explicit nullable values; Vue Composition API with script setup and typed props. Example shape (contract, not implemented code):

```php
public function classify(EmailContent $email): ClassificationResult
{
    return $this->classifier->classify($email);
}
```

Return a validated ClassificationResult, never a raw model response to the frontend. Prefer named domain actions over generic repositories or a microservice architecture.

## Verification strategy
Pest/PHPUnit using the starter's framework for domain and HTTP tests; synthetic MIME fixtures and fake provider adapters; isolated test databases; browser tests for the full seeded journey. Critical cases: reversed chronology, duplicate ingestion, ambiguous roles, ATS senders, auto-acknowledgments, provider timeout, expired history, revoked tokens, hostile email markup, and cross-user access. Never use real mailbox contents as committed fixtures. Target fixture evaluation: at least 40 labeled examples, including at least 10 unrelated/ambiguous cases; >=90% correct event classification on unambiguous examples and zero false application records for the unrelated set. Report the sample size and failures, not a generalized accuracy claim.

## Boundaries
- Always: preserve source evidence, owner scoping, CSRF/session protections, explicit partial/error states, lockfiles, and focused tests for behavior changes.
- Resolve ordinary implementation choices within this scope without repeated permission requests.
- Resolve before live integration: provider/model, mailbox credentials, owner identity, deployed callback origin if hosting is requested.
- Never: send/delete/move provider mail or change read flags, log body/token content, treat email text as executable instructions, conceal provider failures with demo data, or weaken tests to pass.

## Definition of done
All checked tasks have evidence; tests, type checking, formatting, and build pass; browser journey verified on desktop and mobile; failures and reconnect paths work; setup guide reflects actual commands. The live Gmail path requires a separately recorded real-account smoke test after configuration. Passing fake-provider tests alone does not mean Gmail is connected.

## Mailbox-provider extension
See [SPEC-mailbox-connections.md](SPEC-mailbox-connections.md) for provider contracts, IMAP identity/cursors, connection security, and owner provisioning. One queue lock per connection, per-mailbox progress, and source-aware application records are required. Gmail thread/history semantics must not leak into generic domain types. Add live IMAP smoke tests alongside Gmail smoke tests.

## Primary sources checked during preparation
- [Laravel release requirements](https://laravel.com/docs/13.x/releases)
- [Official Vue starter](https://laravel.com/starter-kits)
- [Socialite](https://laravel.com/docs/13.x/socialite)
- [Google server OAuth](https://developers.google.com/identity/protocols/oauth2/web-server)
- [Gmail scopes](https://developers.google.com/workspace/gmail/api/auth/scopes)
- [Gmail synchronization](https://developers.google.com/workspace/gmail/api/guides/sync)

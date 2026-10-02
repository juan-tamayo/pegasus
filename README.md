# Pegasus

A Laravel + Vue web application that turns job-application emails from Gmail, Namecheap Private Email, and compatible IMAP mailboxes into an evidence-backed application tracker.

**State:** first working Laravel/Vue build. Private owner login, Google OAuth consent handling, encrypted Gmail/IMAP mailbox records, tested IMAP connection setup, searchable application dashboard, evidence timelines, corrections, and a local sample workspace are implemented. Automatic mailbox syncing, semantic classification, review processing, and live-provider verification are still pending. See [build status](docs/PROGRESS.md).

## Start here
1. [Product scope](docs/SCOPE.md)
2. [Capability map](CAPABILITY-MAP.md)
3. [Shared technical specification](SPEC.md)
4. Module specifications: [mailboxes](SPEC-mailbox-connections.md), [connection](SPEC-gmail-connection.md), [sync](SPEC-email-sync.md), [classification](SPEC-application-classification.md), [tracker](SPEC-application-tracker.md)
5. [Implementation plan](tasks/plan.md) and [coding checklist](tasks/todo.md)
6. [External setup](docs/SETUP.md)

User-required stack: Laravel and Vue. Planned stack: Laravel 13, PHP 8.3+, Vue 3 with TypeScript, Inertia, Vite, Tailwind, SQLite for personal-use development, and Laravel database queues.

## Run locally
```sh
composer install
npm ci --ignore-scripts
cp .env.example .env
php artisan key:generate
php artisan migrate
npm run build
php artisan pegasus:owner you@example.com
php artisan serve --host=127.0.0.1 --port=8000
```
The owner command prompts privately for a password of at least 12 characters. Password reset: `php artisan pegasus:owner you@example.com --reset`.

For a local fictional workspace, set `PEGASUS_DEMO_ENABLED=true` in `.env` and visit http://127.0.0.1:8000/demo. The demo login is disabled outside the local environment; sample accounts cannot connect real mailboxes. Set it back to false when using real data. Login and sample workspace use separate accounts.

For ongoing frontend editing use `composer run dev`. Google setup and custom IMAP host configuration are in [SETUP.md](docs/SETUP.md). Do not overwrite an existing `.env` when rerunning setup.

## Verify
```sh
php artisan test
./vendor/bin/pint --test
npm run typecheck
npm run build
npm audit
composer audit
npx playwright install chromium
npx playwright test
```
Browser tests require the server running locally on port 8000 with the sample workspace enabled. They modify only fictional records and restore the sample status after testing.

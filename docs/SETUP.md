# Setup prerequisites and implementation handoff

## Current environment
Observed during preparation: PHP 8.3.31, Composer 2.8.10, Node 22.21.1, npm present. Application dependencies are now installed and locked. Laravel 13 requires PHP 8.3 or later. Validate required PHP extensions and exact starter package requirements during scaffolding.

## Local project setup
The official Laravel Vue starter was merged while preserving the planning documents, then updated to Laravel 13. Choose TypeScript, Inertia, SQLite, and the starter's supported test runner. Resolve and lock package versions. Add Google Socialite during the connection slice; add browser-test dependencies during QA. Keep secrets in ignored .env; maintain a placeholder-only .env.example.

## Google setup (needed only for live Gmail testing)
1. Create/select a Google Cloud project and enable Gmail API.
2. Configure the OAuth consent screen and add the owner's account as a test user if using testing mode.
3. Create a Web application OAuth client. Register the exact identity callback and Gmail-consent callback URLs served by the app.
4. Put client ID and secret in local server-side configuration. Do not paste secrets into chat or commit them.
5. Configure the owner email, sign in, then grant Gmail read-only consent.
6. Confirm current Google restrictions for test-mode refresh-token lifetime before relying on unattended sync. Document any required reconnect behavior.

Suggested local origin: http://localhost:8000. Proposed callbacks: http://localhost:8000/auth/google/callback and http://localhost:8000/settings/gmail/callback. The final values must match the actual running origin exactly.

## Configuration contract
APP_NAME=Pegasus; APP_URL; APP_KEY; DB_CONNECTION=sqlite; QUEUE_CONNECTION=database; CACHE_STORE=database; SESSION_DRIVER=database; PEGASUS_OWNER_EMAIL; GOOGLE_CLIENT_ID; GOOGLE_CLIENT_SECRET; GOOGLE_REDIRECT_URI; GOOGLE_GMAIL_REDIRECT_URI; MAIL_CLASSIFIER_DRIVER=fixture; MAIL_CLASSIFIER_MODEL; MAIL_CLASSIFIER_API_KEY; MAIL_SYNC_INTERVAL_MINUTES=15; MAIL_INITIAL_MONTHS=6; IMAP_ALLOWED_HOSTS.

These names are proposed application configuration, not installed integrations. In live mode, a missing classifier must show Configuration required; it must never substitute fictional fixture classifications for real emails.

## Unresolved external choices
- Semantic classification provider/model and its email-data handling terms. Does not block fixture-based coding; blocks real email transfer to that provider.
- Google project/client credentials and owner identity. Does not block fake OAuth tests; blocks live consent.
- Production host/domain and backup policy. No deployment requested in this phase; select PHP-capable hosting with persistent data, queues, scheduling, and outbound TLS IMAP connectivity when requested.

Gmail read-only is a restricted scope. Public distribution may require verification and additional requirements depending on processing/storage; review [Google's scope documentation](https://developers.google.com/workspace/gmail/api/auth/scopes) before expanding beyond personal use.

## Namecheap Private Email and custom IMAP
Namecheap preset: mail.privateemail.com, port 993, TLS enabled, full email address as username. Use that mailbox's password or supported app password, not the Namecheap account login. App-password support can vary with the account's configuration; verify the current provider instructions at setup. Never put mailbox passwords in project files or chat; enter them in Pegasus's protected connection form once implemented.

For a different host, obtain its IMAP hostname, TLS port, username, and supported credential from the mail provider. Domain registration at Namecheap alone does not imply Private Email hosting. No SMTP connection is needed.

Google setup is optional for an IMAP-only installation. Provision the owner with `php artisan pegasus:owner you@example.com`; use `--reset` for a private password reset. Configure permitted IMAP hosts on the server before connecting custom hosts. Mailbox-specific passwords are encrypted database fields, not shared environment variables.

Live verification adds: fetch known messages without changing unread flags, incremental arrival detection, certificate validation, bad-password handling, disconnect, and independent Gmail/IMAP scans. Review current Namecheap [server settings](https://www.namecheap.com/support/knowledgebase/article.aspx/1179/2175/general-private-email-configuration-for-mail-clients-and-mobile-devices/) and [app-password instructions](https://www.namecheap.com/support/knowledgebase/article.aspx/10816/2178/how-to-use-app-passwords-for-private-email/).

## Current connection implementation
The local owner login, Google identity and Gmail consent routes, Namecheap/custom IMAP form, encrypted credential storage, and disconnect controls are implemented. IMAP setup uses Webklex PHP-IMAP 6.2 with verified TLS and a pinned public IPv4 address; IPv6-only mail servers are not supported by this first adapter. Allowed hosts default to mail.privateemail.com; add your provider's hostname to IMAP_ALLOWED_HOSTS for custom IMAP. It tests a read-only INBOX open before saving. The username and password are entered in the web form, not .env.

Live connections have not been tested with the owner's credentials. Automatic import/classification is not yet implemented, so a connected mailbox will still show Not scanned yet. Gmail disconnect currently erases local tokens and prevents local access; remote Google token revocation is pending. See PROGRESS.md for exact remaining work.

If an owner was provisioned with email/password, Google identity linking is pending. Google sign-in can create the initial owner only when no real owner exists and PEGASUS_OWNER_EMAIL matches the verified Google address. Additional Gmail mailbox consent works after signing in with the existing owner password.

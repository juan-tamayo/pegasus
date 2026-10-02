# Module: mailbox-connections

Inherits shared conventions, commands, and testing from SPEC.md. Owns common mailbox lifecycle; gmail-connection remains the stable Google-specific module ID.

## Authentication and ownership
One owner may connect multiple mailboxes. Google identity login is optional. Provide a private owner-provisioning command with a hidden password prompt, framework password hashing, login throttling, CSRF protection, and session regeneration. Never seed a default password or enable public registration. Keep app login credentials separate from mailbox credentials. Recovery is through a server-side owner password-reset command for MVP; public email recovery is out of scope. Linking Google identity to an existing owner requires an authenticated session and must not rely on matching email strings alone.

## Provider-neutral contract
MailboxProvider supports testConnection, listFolders, scanBatch, fetchMessage, and disconnect. Scan batches return message references, a provider-specific checkpoint, and completion state; checkpoints only become authoritative after durable processing. NormalizedEmail carries connection ID, provider message key, optional thread ID, sender, subject, received timestamp, bounded decoded text, truncation flag, and optional validated source URL. Never expose secrets through these objects. Generic code does not assume Gmail IDs, labels, history, or URLs.

## Providers
- Gmail/Google Workspace: OAuth through SPEC-gmail-connection.md.
- Namecheap Private Email: preset mail.privateemail.com, port 993, implicit TLS with certificate/hostname verification, username = full mailbox email address. Use the mailbox credential or app password supported by that account; never ask for the Namecheap registrar-account password.
- Custom IMAP: administrator-allowed hostname, port 993 implicit TLS or explicitly configured 143 with mandatory STARTTLS, username and password/app password. Fail closed on failed TLS negotiation. Generic password IMAP does not promise support for OAuth-only hosts.

Connection state: disconnected, connected, reconnect_required, error. Report authentication, certificate, timeout, and unsupported-server errors distinctly without revealing credentials. All authenticated owners' routes and jobs enforce ownership and connection generation.

## IMAP read-only behavior and connection safety
Use EXAMINE/read-only folder selection and BODY.PEEK or equivalent non-marking fetch. Never STORE, COPY, MOVE, EXPUNGE, APPEND, or connect to SMTP. IMAP credentials may grant write access despite these application restrictions; explain this accurately in connection setup.

A custom host is an outbound network target: allow only administrator-configured public mail hosts and approved ports; resolve and reject loopback/private/link-local/metadata targets for IPv4 and IPv6; enforce the restriction at connection time against DNS rebinding while retaining TLS hostname verification. Use bounded timeouts, message sizes, concurrency, and retries. Do not accept arbitrary URI schemes or disabled certificate checks. Select a maintained PHP IMAP client after verifying PHP/runtime compatibility and support for these requirements; do not pin an unresearched package in this plan.

## Routes and disconnect
GET /settings/mailboxes; POST /settings/mailboxes/imap/test; POST /settings/mailboxes/imap; PATCH /settings/mailboxes/{connection}; DELETE /settings/mailboxes/{connection}. Test and save are owner-authenticated, CSRF-protected, rate-limited actions. Validate credentials transiently before saving encrypted values; replacement passwords are write-only and never echoed. Duplicate provider account connections are rejected for the owner.

Disconnect disables the connection, increments generation, stops queued work, and removes local secrets. For IMAP, explain that credential revocation/rotation is performed in the mail provider's settings; local disconnect cannot revoke a shared password. Deleting one connection does not disconnect others. Retained evidence keeps its source identity.

## Acceptance
- An IMAP-only owner can sign in and connect Namecheap without configuring Google.
- Gmail and IMAP run concurrently without shared cursors, credential leakage, or cross-connection record collisions.
- IMAP reading leaves read/unread flags unchanged; invalid TLS, internal-network targets, stale queued jobs, and wrong-owner access are rejected.

## Sources
- [Namecheap connection settings](https://www.namecheap.com/support/knowledgebase/article.aspx/1179/2175/general-private-email-configuration-for-mail-clients-and-mobile-devices/)
- [Namecheap app-password guidance](https://www.namecheap.com/support/knowledgebase/article.aspx/10816/2178/how-to-use-app-passwords-for-private-email/)

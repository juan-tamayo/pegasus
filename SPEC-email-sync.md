# Module: email-sync

Inherits shared conventions from SPEC.md; depends on mailbox-connections and its gmail-connection adapter.

## Responsibility and provider contract
Queue and retrieve received messages for the configured scope. Emit NormalizedEmail values containing connection ID, provider message key, optional thread ID, sender, subject, received timestamp, and bounded plain text. Classification receives text in memory; job payloads carry database IDs only.

## Gmail initial import
- Default last six calendar months through scan start; default label INBOX, with an explicit option to include archived received mail. Exclude SPAM, TRASH, SENT, and DRAFT as message types. Record the actual range and mode in each run.
- Enumerate all received-message IDs within the chosen coverage instead of relying only on English job keywords. Fetch MIME text in bounded batches; do not download attachments. Filter relevance during processing and retain only minimal IDs/outcomes needed for deduplication of unrelated messages.
- Capture a history cursor before listing, paginate completely, then reconcile changes since that cursor. Commit the resulting cursor only after changed messages are safely persisted or durably queued.
- Decode base64url, recursively handle multipart text, fall back to sanitized HTML-to-text. Enforce decoded-size and message-length bounds; truncation/unsupported content is visible to classification as uncertainty.

## Gmail incremental import
Use history.list with the saved cursor; paginate, deduplicate, apply the selected coverage rules and inbox-label additions. A history 404 triggers a bounded full re-scan, not silent success. Use a rolling overlap to include arrivals during scan transitions. Store cursors as strings. Source deletion marks source unavailable without inventing a hiring-status change.

## Execution and routes
POST /mailboxes/{connection}/sync returns a run identifier; GET /mailboxes/{connection}/sync/{run} returns owner-scoped safe progress. Manual refresh is rate limited. A scheduler dispatches every 15 minutes when enabled, with one active run per connection and no overlap. Retry transient 429/5xx errors with bounded exponential backoff and Retry-After where supplied; preserve checkpoints. Report fetched, processed, failed, and review counts separately. Fetch completion and classification completion are distinct states.

## Acceptance and tests
- Multi-page initial scan and repeated incremental runs create exactly one stored record per provider message.
- Interrupted jobs resume; two simultaneous triggers do not duplicate events or advance cursors past lost work.
- History expiry, rate limiting, malformed MIME, oversized text, revoked access, and partial classification remain visible and recoverable.

## IMAP import and incremental synchronization
Use the common contract in SPEC-mailbox-connections.md. Default INBOX; explicitly selected extra received-mail folders are recorded in coverage. Use read-only folder selection and non-mutating body fetches. Decode MIME transfer encodings, including base64 and quoted-printable, using a maintained parser.

Identify a message by connection + folder + UIDVALIDITY + UID, never sequence number. Initial import searches the configured date window and persists batches; subsequent runs fetch unseen UIDs and reconcile a bounded recent window. Maintain a contiguous durably processed checkpoint, not simply the largest fetched UID. UIDVALIDITY changes trigger a bounded rescan; reuse prior records only with verified message identity/content evidence, otherwise surface potential duplicates for review. Message-ID alone is not globally unique. A folder copy must not silently duplicate an application or change its status. Removed messages preserve prior evidence and show source unavailable when detected.

Test UID reuse, UIDVALIDITY reset, folder copies, out-of-order jobs, connection timeout, invalid credentials, and certificate failure. One mailbox failure must leave other mailbox runs operational. IMAP polling does not claim Gmail history-level completeness about deletions outside the reconciliation window.

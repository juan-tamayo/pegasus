# Module: application-classification

Inherits shared conventions from SPEC.md; depends on email-sync.

## Contract
Classifier accepts NormalizedEmail with bounded text and limited, same-owner application context. Returns a validated structure: relevance (relevant/irrelevant/uncertain), event_type (confirmation/response/interview/offer/rejection/unknown), company nullable, role nullable, requisition_id nullable, application_date nullable, evidence_excerpt, review_reason nullable, classifier_version. Confidence may be retained for evaluation but must not be treated as a calibrated probability.

An adapter can use a configured semantic model; a fixture adapter supports development. Keyword rules alone are not sufficient to claim production semantic classification. No external model is called until its configuration and disclosure are complete.

## Processing
- Treat all email content as untrusted data. Classifier has no tools or mailbox credentials and cannot follow instructions embedded in messages.
- Validate enum/length/date/schema values and require a supporting excerpt actually present in decoded source text. Missing evidence, truncated context, unsupported language, conflicting outcomes, or malformed response becomes review-required.
- Exclude advertisements, newsletters, recruiter cold outreach, and job alerts unless there is evidence of an actual application. A generic acknowledgment is confirmation only when it specifically confirms an application.
- Use current message text; quoted historical replies/signatures must not override the new content. ATS sender domains are not necessarily employer names.
- Match by explicit application/requisition identifiers first, then provider thread (when available) plus consistent company/role within the same mailbox. Company alone is insufficient. Ambiguous association and repeat application cycles go to review.
- Project events chronologically using provider received timestamps; an older message cannot overwrite a newer decision. Later acknowledgments never regress a substantive status. Conflicting terminal outcomes require review unless an explicit reopening/new cycle is established.
- Preserve manual overrides on resync and reclassification. Store detected status separately, signal new conflicting evidence, and offer an explicit reset-to-detected action.

## Review contract
PATCH /reviews/{review} with confirmed event type, company/role, optional existing application ID, or dismiss action. Validate ownership and fields; perform assignment and projection in one transaction. Retrying a resolved review cannot duplicate events.

## Acceptance and tests
- Confirmation → interview → rejection yields rejected with three evidence events even when delivered to the job queue out of order.
- Two company roles are separate; unknown company/role stays unknown; one message discussing multiple roles is reviewable.
- Prompt injection, quoted rejection in a newer interview message, unrelated promotional mail, provider timeout, and schema errors cannot fabricate a confirmed outcome.

Cross-mailbox candidates require explicit review before association; default application ownership includes the source connection. IMAP References/In-Reply-To are hints, not trusted global thread IDs.

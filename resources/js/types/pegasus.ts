export type Status = { value: string; label: string };
export type Mailbox = {
    id: number;
    provider: string;
    address: string;
    state: string;
    last_success_at: string | null;
    settings?: { host?: string; port?: number };
};
export type Application = {
    id: number;
    company: string | null;
    role: string | null;
    status: string;
    detected_status: string;
    manual_status: string | null;
    is_demo: boolean;
    review_required: boolean;
    applied_at: string | null;
    last_message_at: string | null;
    waiting_days: number | null;
    mailbox: Pick<Mailbox, 'id' | 'address' | 'provider'> | null;
};
export const labels: Record<string, string> = {
    awaiting_response: 'Awaiting response',
    response_received: 'Response received',
    interview: 'Interview',
    offer: 'Offer',
    rejected: 'Rejected',
    needs_review: 'Needs review',
};
export function date(value: string | null) {
    return value ? new Intl.DateTimeFormat(undefined, { month: 'short', day: 'numeric' }).format(new Date(value)) : 'Unknown';
}

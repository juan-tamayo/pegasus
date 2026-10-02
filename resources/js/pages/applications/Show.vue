<script setup lang="ts">
import StatusBadge from '@/components/StatusBadge.vue';
import PegasusLayout from '@/layouts/PegasusLayout.vue';
import { date, type Application, type Status } from '@/types/pegasus';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Check } from 'lucide-vue-next';
const props = defineProps<{
    application: Application;
    events: { id: number; event_type: string; subject: string; evidence: string; occurred_at: string }[];
    statuses: Status[];
}>();
const form = useForm({
    company: props.application.company || '',
    role: props.application.role || '',
    manual_status: props.application.manual_status || '',
});
function save() {
    form.transform((d) => ({ ...d, manual_status: d.manual_status || null })).patch(`/applications/${props.application.id}`, {
        preserveScroll: true,
    });
}
</script>
<template>
    <Head :title="application.company || 'Application'" /><PegasusLayout>
        <Link href="/applications" class="back-link"><ArrowLeft :size="16" /> All applications</Link>
        <header class="page-heading">
            <div>
                <p class="eyebrow">THE STORY SO FAR</p>
                <h1>{{ application.company || 'Company unknown' }}</h1>
                <p class="subtitle">{{ application.role || 'Role unknown' }} · {{ application.mailbox?.address || 'Mailbox not linked' }}</p>
            </div>
            <StatusBadge :status="application.status" />
        </header>
        <div v-if="application.is_demo" class="demo-banner"><span>Sample application</span> Fictional emails for exploring the tracker.</div>
        <div class="detail-grid">
            <section class="panel">
                <h2>Email timeline</h2>
                <p class="subtitle">The evidence behind the latest status.</p>
                <div v-if="!events.length" class="empty-state"><p>No supporting messages yet.</p></div>
                <article v-for="event in events" :key="event.id" class="timeline-event">
                    <div class="event-dot"><Check :size="13" /></div>
                    <div>
                        <div class="event-meta">
                            <StatusBadge
                                :status="
                                    event.event_type === 'confirmation'
                                        ? 'awaiting_response'
                                        : event.event_type === 'rejection'
                                          ? 'rejected'
                                          : event.event_type === 'response'
                                            ? 'response_received'
                                            : event.event_type
                                "
                            /><time>{{ date(event.occurred_at) }}</time>
                        </div>
                        <h3>{{ event.subject }}</h3>
                        <p class="evidence">{{ event.evidence }}</p>
                    </div>
                </article>
            </section>
            <section class="panel correction-panel">
                <h2>Keep the details right</h2>
                <p class="subtitle">Your corrections take priority over detected values.</p>
                <form @submit.prevent="save">
                    <label for="company">Company<input id="company" v-model="form.company" maxlength="255" /></label>
                    <p v-if="form.errors.company" class="field-error">{{ form.errors.company }}</p>
                    <label for="role">Role<input id="role" v-model="form.role" maxlength="255" /></label>
                    <p v-if="form.errors.role" class="field-error">{{ form.errors.role }}</p>
                    <label for="status"
                        >Status<select id="status" v-model="form.manual_status">
                            <option value="">Use detected status</option>
                            <option v-for="s in statuses" :key="s.value" :value="s.value">{{ s.label }}</option>
                        </select></label
                    >
                    <p v-if="form.errors.manual_status" class="field-error">{{ form.errors.manual_status }}</p>
                    <button class="button primary" :disabled="form.processing">{{ form.processing ? 'Saving…' : 'Save correction' }}</button>
                </form>
                <p class="fine-print">
                    Application date: {{ date(application.applied_at) }}<br />No response found only describes emails within the scan coverage.
                </p>
            </section>
        </div>
    </PegasusLayout>
</template>

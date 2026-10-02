<script setup lang="ts">
import StatusBadge from '@/components/StatusBadge.vue';
import PegasusLayout from '@/layouts/PegasusLayout.vue';
import { date, type Application, type Mailbox, type Status } from '@/types/pegasus';
import { Head, Link, router } from '@inertiajs/vue3';
import { ArrowRight, ArrowUpRight, Mail, Plus, Search, SlidersHorizontal } from 'lucide-vue-next';
import { computed, ref } from 'vue';
const props = defineProps<{
    applications: { data: Application[]; total: number; links: { url: string | null; label: string; active: boolean }[] };
    filters: { search?: string; status?: string; mailbox?: number };
    statuses: Status[];
    counts: Record<string, number>;
    mailboxes: Mailbox[];
}>();
const search = ref(props.filters.search || '');
const status = ref(props.filters.status || '');
const mailbox = ref(props.filters.mailbox || '');
const total = computed(() => Object.values(props.counts).reduce((sum, n) => sum + Number(n), 0));
const demo = computed(() => props.applications.data.some((a) => a.is_demo));
function filter() {
    router.get(
        '/applications',
        { search: search.value || undefined, status: status.value || undefined, mailbox: mailbox.value || undefined },
        { preserveState: true, preserveScroll: true },
    );
}
</script>

<template>
    <Head title="Applications" />
    <PegasusLayout>
        <header class="page-heading">
            <div>
                <p class="eyebrow">EVERY APPLICATION. ONE PLACE.</p>
                <h1>Your next chapter starts here.</h1>
                <p class="subtitle">Keep track of the possibilities. We’ll help you see where things stand.</p>
            </div>
            <Link href="/settings/mailboxes" class="button primary"><Plus :size="17" /> Connect mailbox</Link>
        </header>
        <div v-if="demo" class="demo-banner">
            <span>Sample workspace</span> These are fictional applications for trying Pegasus. Your inbox has not been scanned.
        </div>
        <section class="metrics" aria-label="Application summary">
            <div class="metric">
                <span>Total applications</span><strong>{{ total }}<span class="metric-marker neutral"></span></strong
                ><small>Every possibility, accounted for</small>
            </div>
            <div class="metric">
                <span>Awaiting response</span><strong>{{ counts.awaiting_response || 0 }}<span class="metric-marker amber"></span></strong
                ><small>No further response found</small>
            </div>
            <div class="metric">
                <span>Interviews</span><strong>{{ counts.interview || 0 }}<span class="metric-marker blue"></span></strong
                ><small>Conversations moving forward</small>
            </div>
            <div class="metric">
                <span>Offers</span><strong>{{ counts.offer || 0 }}<span class="metric-marker green"></span></strong
                ><small>A new beginning in sight</small>
            </div>
        </section>
        <section class="application-section">
            <div class="section-title">
                <div>
                    <h2>
                        Application activity <span>{{ applications.total }}</span>
                    </h2>
                    <p>The latest on your search, straight from your inbox.</p>
                </div>
                <span class="section-caption">ONE STEP AT A TIME <ArrowUpRight :size="17" /></span>
            </div>
            <form class="filters" @submit.prevent="filter">
                <div class="search-input">
                    <Search :size="17" /><input v-model="search" aria-label="Search company or role" placeholder="Search company or role…" /><button
                        type="submit"
                        aria-label="Search"
                    >
                        <ArrowRight :size="16" />
                    </button>
                </div>
                <label class="select-control"
                    ><SlidersHorizontal :size="16" /><select v-model="status" aria-label="Filter by status" @change="filter">
                        <option value="">All statuses</option>
                        <option v-for="item in statuses" :key="item.value" :value="item.value">{{ item.label }}</option>
                    </select></label
                ><label class="select-control"
                    ><Mail :size="16" /><select v-model="mailbox" aria-label="Filter by mailbox" @change="filter">
                        <option value="">All mailboxes</option>
                        <option v-for="m in mailboxes" :key="m.id" :value="m.id">{{ m.address }}</option>
                    </select></label
                >
            </form>
            <div v-if="applications.data.length" class="table-scroll">
                <table class="applications-table">
                    <thead>
                        <tr>
                            <th>Company & role</th>
                            <th>Status</th>
                            <th>Applied</th>
                            <th>Waiting</th>
                            <th>Mailbox</th>
                            <th><span class="sr-only">Details</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="a in applications.data" :key="a.id">
                            <td>
                                <Link :href="`/applications/${a.id}`" class="company-cell"
                                    ><span class="company-avatar">{{ (a.company || '?').slice(0, 1) }}</span
                                    ><span
                                        ><strong>{{ a.company || 'Company unknown' }}</strong
                                        ><small>{{ a.role || 'Role unknown' }}</small></span
                                    ></Link
                                >
                            </td>
                            <td><StatusBadge :status="a.status" /><small v-if="a.review_required" class="review-label">Needs review</small></td>
                            <td>{{ date(a.applied_at) }}</td>
                            <td>{{ a.waiting_days === null ? '—' : `${a.waiting_days} days` }}</td>
                            <td class="mailbox-cell">{{ a.mailbox?.address || 'Not linked' }}</td>
                            <td>
                                <Link :href="`/applications/${a.id}`" :aria-label="`View ${a.company || 'application'}`" class="row-arrow"
                                    ><ArrowUpRight :size="18"
                                /></Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div v-if="applications.data.length" class="mobile-applications">
                <Link v-for="a in applications.data" :key="a.id" :href="`/applications/${a.id}`" class="mobile-application"
                    ><div class="mobile-company">
                        <span class="company-avatar">{{ (a.company || '?').slice(0, 1) }}</span>
                        <div>
                            <strong>{{ a.company || 'Company unknown' }}</strong>
                            <p>{{ a.role || 'Role unknown' }}</p>
                        </div>
                        <ArrowUpRight :size="17" />
                    </div>
                    <div class="mobile-meta">
                        <StatusBadge :status="a.status" /><span>{{
                            a.waiting_days === null ? date(a.applied_at) : `${a.waiting_days} days waiting`
                        }}</span>
                    </div>
                    <small>{{ a.mailbox?.address || 'Not linked' }}</small></Link
                >
            </div>
            <div v-if="!applications.data.length" class="empty-state">
                <Mail :size="32" />
                <h3>{{ total ? 'No applications match these filters' : 'A fresh start for your search' }}</h3>
                <p>{{ total ? 'Try another company, role, or status.' : 'Connect a mailbox to bring your application emails together.' }}</p>
                <Link v-if="!total" href="/settings/mailboxes" class="button primary">Connect your first mailbox <ArrowRight :size="16" /></Link>
            </div>
            <div class="table-footer">
                <span>{{ applications.total }} applications · Latest updates first</span>
                <nav v-if="applications.links.length > 3" aria-label="Pagination">
                    <template v-for="(link, index) in applications.links" :key="index"
                        ><Link v-if="link.url" :href="link.url" :class="{ selected: link.active }">{{
                            index === 0 ? 'Previous' : index === applications.links.length - 1 ? 'Next' : link.label
                        }}</Link></template
                    >
                </nav>
            </div>
        </section>
        <section class="coverage">
            <Mail :size="20" />
            <div>
                <h3>Your inbox, with context.</h3>
                <p v-if="!mailboxes.length">Gmail, Namecheap Private Email, and custom-domain mailboxes belong here.</p>
                <p v-else>
                    {{ mailboxes.length }} mailbox{{ mailboxes.length === 1 ? '' : 'es' }} · Default coverage: the last six months of received inbox
                    mail.
                </p>
                <p v-for="m in mailboxes" :key="m.id" class="sync-line">
                    {{ m.address }} · {{ m.state.replaceAll('_', ' ') }} ·
                    {{ m.last_success_at ? `Last scan ${date(m.last_success_at)}` : 'Not scanned yet' }}
                </p>
            </div>
            <Link href="/settings/mailboxes">Manage mailboxes <ArrowUpRight :size="17" /></Link>
        </section>
        <footer class="page-footer">A confirmation is a start. Silence is never a rejection.<span>PEGASUS / YOUR NEXT MOVE</span></footer>
    </PegasusLayout>
</template>

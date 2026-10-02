<script setup lang="ts">
import PegasusLayout from '@/layouts/PegasusLayout.vue';
import { date, type Mailbox } from '@/types/pegasus';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ArrowUpRight, Mail, ShieldCheck } from 'lucide-vue-next';
import { ref } from 'vue';
defineProps<{ mailboxes: Mailbox[]; googleConfigured: boolean; isDemo: boolean }>();
const connecting = ref(false);
const form = useForm({ provider: 'namecheap', address: '', username: '', password: '', host: 'mail.privateemail.com', port: 993, encryption: 'ssl' });
function open(provider: string) {
    form.reset();
    form.provider = provider;
    form.host = provider === 'namecheap' ? 'mail.privateemail.com' : '';
    connecting.value = true;
}
function connect() {
    if (form.provider === 'namecheap') form.username = form.address;
    form.encryption = Number(form.port) === 143 ? 'tls' : 'ssl';
    form.post('/settings/mailboxes/imap', {
        onSuccess: () => {
            connecting.value = false;
            form.reset('password');
        },
        onError: () => form.reset('password'),
        preserveScroll: true,
    });
}
function disconnect(id: number) {
    if (window.confirm('Disconnect this mailbox? Your application history will remain.')) router.delete(`/settings/mailboxes/${id}`);
}
</script>
<template>
    <Head title="Mailboxes" /><PegasusLayout>
        <header class="page-heading">
            <div>
                <p class="eyebrow">BRING YOUR INBOXES TOGETHER</p>
                <h1>A home for every mailbox.</h1>
                <p class="subtitle">Your applications can arrive anywhere. Keep them in one place.</p>
            </div>
        </header>
        <div v-if="isDemo" class="demo-banner"><span>Sample workspace</span> Sign in with your owner account to connect a real mailbox.</div>
        <div class="provider-grid">
            <section class="panel">
                <span class="provider-icon gmail-icon">G</span>
                <h2>Gmail</h2>
                <p>Connect your Gmail or Google Workspace account with Google’s permission screen.</p>
                <a v-if="googleConfigured && !isDemo" href="/settings/mailboxes/google/connect" class="button primary"
                    >Connect with Google <ArrowUpRight :size="16"
                /></a>
                <p v-else class="setup-note">Google connection setup is pending.</p>
            </section>
            <section class="panel">
                <span class="provider-icon namecheap-icon">N</span>
                <h2>Namecheap Private Email</h2>
                <p>Your custom-domain mailbox, connected securely through IMAP.</p>
                <button class="button primary" :disabled="isDemo" @click="open('namecheap')">
                    Connect Private Email <ArrowUpRight :size="16" />
                </button>
            </section>
            <section class="panel">
                <span class="provider-icon"><Mail :size="24" /></span>
                <h2>Custom mailbox</h2>
                <p>Bring an email address from another provider that supports IMAP.</p>
                <button class="button secondary" :disabled="isDemo" @click="open('imap')">Connect custom mailbox <ArrowUpRight :size="16" /></button>
            </section>
        </div>
        <section v-if="connecting" class="panel imap-form-panel">
            <h2>{{ form.provider === 'namecheap' ? 'Connect Namecheap Private Email' : 'Connect an IMAP mailbox' }}</h2>
            <p>Enter your mailbox credentials. Pegasus will verify the connection before saving them securely.</p>
            <form @submit.prevent="connect">
                <div class="imap-form-grid">
                    <label>Email address<input v-model="form.address" type="email" required autocomplete="off" /></label
                    ><label
                        >Mailbox password or app password<input v-model="form.password" type="password" required autocomplete="new-password" /></label
                    ><template v-if="form.provider === 'imap'"
                        ><label>IMAP server<input v-model="form.host" required placeholder="imap.your-provider.com" /></label
                        ><label>Username<input v-model="form.username" required /></label
                        ><label
                            >Encrypted connection<select v-model="form.port">
                                <option :value="993">Port 993 · TLS</option>
                                <option :value="143">Port 143 · STARTTLS</option>
                            </select></label
                        ></template
                    >
                </div>
                <p v-for="(error, field) in form.errors" :key="field" class="field-error" role="alert">{{ error }}</p>
                <p class="fine-print">
                    Use your mailbox password, not your domain registrar login. IMAP credentials may allow changes at the provider; Pegasus only uses
                    read-only access. Custom mail servers must be enabled in server settings.
                </p>
                <div class="form-actions">
                    <button class="button primary" :disabled="form.processing">
                        {{ form.processing ? 'Verifying connection…' : 'Verify and connect' }}</button
                    ><button
                        type="button"
                        class="button secondary"
                        @click="
                            connecting = false;
                            form.reset('password');
                        "
                    >
                        Cancel
                    </button>
                </div>
            </form>
        </section>
        <section class="panel connected-panel">
            <h2>Your mailboxes</h2>
            <div v-if="!mailboxes.length" class="empty-state">
                <Mail :size="28" />
                <h3>No mailboxes connected yet</h3>
                <p>Each connection will show its scan coverage and last successful update here.</p>
            </div>
            <div v-for="m in mailboxes" :key="m.id" class="mailbox-row">
                <Mail :size="21" />
                <div>
                    <strong>{{ m.address }}</strong>
                    <p>
                        {{ m.provider }} · {{ m.state.replaceAll('_', ' ') }} ·
                        {{ m.last_success_at ? `Last scan ${date(m.last_success_at)}` : 'Not scanned yet' }}
                    </p>
                </div>
                <button v-if="m.state !== 'disconnected'" class="button secondary" @click="disconnect(m.id)">Disconnect</button
                ><span v-else class="muted-label">Disconnected</span>
            </div>
        </section>
        <div class="privacy-note">
            <ShieldCheck :size="20" />
            <p>
                Pegasus reads mail to track your applications. IMAP connections will use read-only operations without changing read flags. Mailbox
                secrets stay on the server.
            </p>
        </div>
    </PegasusLayout>
</template>

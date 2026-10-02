<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { ArrowRight, Check, Feather, Mail } from 'lucide-vue-next';
defineProps<{ status?: string; googleConfigured?: boolean }>();
const form = useForm({ email: '', password: '', remember: false });
function submit() {
    form.post('/login', { onFinish: () => form.reset('password') });
}
</script>
<template>
    <Head title="Welcome" />
    <div class="login-shell">
        <section class="login-story">
            <div class="brand">
                <span class="brand-mark"><Feather :size="22" /></span> pegasus.
            </div>
            <div>
                <p class="eyebrow">A LITTLE CLARITY FOR YOUR NEXT BIG MOVE</p>
                <h1>Your job search.<br />A clearer picture.</h1>
                <p>From “application received” to what comes next. Bring your emails together and know where every opportunity stands.</p>
                <div class="login-example">
                    <Mail :size="20" /><span
                        ><strong>One inbox. Many possibilities.</strong><small>Confirmations, conversations, and decisions.</small></span
                    ><Check :size="20" />
                </div>
            </div>
            <span class="login-footnote">YOUR NEXT CHAPTER STARTS HERE.</span>
        </section>
        <section class="login-form">
            <div>
                <p class="eyebrow">YOUR PRIVATE WORKSPACE</p>
                <h2>Welcome to Pegasus.</h2>
                <p>Sign in to keep your next move in view.</p>
                <form @submit.prevent="submit">
                    <label>Email address<input v-model="form.email" type="email" autocomplete="username" required autofocus /></label>
                    <p v-if="form.errors.email" class="field-error" role="alert">{{ form.errors.email }}</p>
                    <label>Password<input v-model="form.password" type="password" autocomplete="current-password" required /></label
                    ><label class="remember"><input v-model="form.remember" type="checkbox" /> Keep me signed in</label
                    ><button class="button primary" :disabled="form.processing">
                        {{ form.processing ? 'Signing in…' : 'Sign in' }} <ArrowRight :size="18" />
                    </button>
                </form>
                <a v-if="googleConfigured" href="/auth/google/redirect" class="button secondary google-login">Sign in with Google</a>
                <p class="fine-print">Private access. Your owner account is created during setup.</p>
                <p v-if="status" role="status">{{ status }}</p>
            </div>
        </section>
    </div>
</template>

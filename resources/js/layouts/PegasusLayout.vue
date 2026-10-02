<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { ArrowUpRight, BriefcaseBusiness, LogOut, Mail } from 'lucide-vue-next';
import { computed } from 'vue';
const page = usePage();
const message = computed(() => (page.props.flash as { message?: string })?.message);
</script>

<template>
    <div class="pegasus-shell">
        <aside class="pegasus-sidebar">
            <Link href="/applications" class="brand"
                ><span class="brand-mark"><AppLogoIcon width="28" height="28" /></span> pegasus<span class="brand-dot">.</span></Link
            >
            <div class="workspace-label">YOUR JOB SEARCH</div>
            <nav aria-label="Main navigation">
                <Link href="/applications" :class="{ active: page.url.startsWith('/applications') }"
                    ><BriefcaseBusiness :size="18" /> Applications</Link
                >
                <Link href="/settings/mailboxes" :class="{ active: page.url.startsWith('/settings/mailboxes') }"><Mail :size="18" /> Mailboxes</Link>
            </nav>
            <div class="sidebar-note">
                <span class="little-dot"></span> A little clarity.<br /><span>For your next big move.</span><ArrowUpRight :size="18" />
            </div>
            <button aria-label="Sign out" class="sign-out" @click="router.post('/logout')"><LogOut :size="16" /> Sign out</button>
        </aside>
        <main class="pegasus-main">
            <div class="topline">
                <span>JOB SEARCH, ORGANIZED</span><span class="private-label"><span class="little-dot"></span> Your private workspace</span>
            </div>
            <div v-if="message" class="flash-message" role="status">{{ message }}</div>
            <slot />
        </main>
    </div>
</template>

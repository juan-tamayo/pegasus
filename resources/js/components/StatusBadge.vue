<script setup lang="ts">
import { labels } from '@/types/pegasus';
defineProps<{ status: string }>();
</script>
<template>
    <span class="status-badge" :class="status"><span></span>{{ labels[status] || status }}</span>
</template>

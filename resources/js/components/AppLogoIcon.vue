<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({ inheritAttrs: false });
defineProps<{ className?: HTMLAttributes['class'] }>();
</script>

<template>
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64" :class="className" v-bind="$attrs" aria-hidden="true">
        <!-- The complete Pegasus in flight, seen from the side. -->
        <path fill="currentColor" opacity="0.55" d="M37 34C32 25 31 14 35 5c7 8 12 18 9 28l-7 1Z" />
        <path fill="currentColor" d="M18 35C11 29 9 38 3 34c1 8 8 9 15 5l1-4Z" />
        <path
            fill="currentColor"
            d="M17 34c7-4 15-2 22-1 4-3 4-11 9-15v-6l4 4h3l7 6-3 5-6-3c-2 4 0 10-4 15l6 4 5 10-4 2-6-9-7-3 1 7-4 10-4-2 3-9-3-6c-4 2-9 1-13-1l-4 7-8 7-3-3 7-8 1-5-5 6-5-2 7-8 2-4Z"
        />
        <path fill="currentColor" d="M39 35C39 22 25 9 9 4c3 10 6 16 14 23l-10-6c4 8 8 11 15 14l-8-3c6 8 13 8 19 3Z" />
    </svg>
</template>

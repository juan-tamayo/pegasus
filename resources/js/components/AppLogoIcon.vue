<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({ inheritAttrs: false });
defineProps<{ className?: HTMLAttributes['class'] }>();
</script>

<template>
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64" :class="className" v-bind="$attrs" aria-hidden="true">
        <!-- A side profile cropped to the horse's torso and two lifted wings. -->
        <path fill="currentColor" opacity="0.55" d="M35 43C29 33 28 21 35 7c1 10 3 16 8 23l-1-15c7 10 10 20 7 29Z" />
        <path fill="currentColor" d="M39 44C24 40 13 27 7 8c10 4 18 10 26 20C25 15 20 8 18 3c15 7 26 22 28 37l-7 4Z" />
        <path
            fill="none"
            stroke="var(--pg-accent, #295943)"
            stroke-width="1.8"
            stroke-linecap="round"
            d="M14 17c7 6 15 15 21 23M23 14c6 7 13 17 16 25"
        />
        <path fill="currentColor" d="M8 43c7-3 13-4 20-3l12 3c7 1 11-3 15-8l4 3c-3 5-4 10-3 15-6-2-10-2-16 0-9 3-15 1-23-2-4-2-7-3-11-3l2-5Z" />
    </svg>
</template>

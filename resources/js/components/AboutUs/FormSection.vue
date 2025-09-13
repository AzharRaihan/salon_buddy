<script setup>
defineProps({
    title: {
        type: String,
        required: true
    },
    subtitle: {
        type: String,
        default: ''
    }
})
</script>

<template>
    <VCol cols="12" class="form-section">
        <div class="section-header mb-6">
            <h4 class="text-h4 section-title">{{ title }}</h4>
            <p v-if="subtitle" class="text-body-1 text-medium-emphasis mt-1">
                {{ subtitle }}
            </p>
        </div>
        <slot />
    </VCol>
</template>

<style scoped>
</style> 
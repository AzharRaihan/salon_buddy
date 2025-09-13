<template>
    <div v-if="showDebug" class="debug-panel">
        <VCard title="API Response Debug" class="mb-4">
            <VCardText>
                <VBtn @click="showDebug = false" variant="text" size="small" class="mb-2">
                    Close Debug
                </VBtn>
                <pre class="debug-content">{{ JSON.stringify(responseData, null, 2) }}</pre>
            </VCardText>
        </VCard>
    </div>
</template>

<script setup>
import { ref } from 'vue'

const props = defineProps({
    responseData: {
        type: Object,
        default: () => ({})
    }
})

const showDebug = ref(true)
</script>

<style scoped>
.debug-panel {
    position: fixed;
    top: 20px;
    right: 20px;
    width: 400px;
    max-height: 600px;
    z-index: 9999;
    background: white;
    border: 1px solid #ddd;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.debug-content {
    font-size: 12px;
    overflow: auto;
    max-height: 400px;
    background: #f5f5f5;
    padding: 8px;
    border-radius: 4px;
    white-space: pre-wrap;
    word-break: break-all;
}
</style> 
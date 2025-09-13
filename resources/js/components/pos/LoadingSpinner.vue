<template>
    <div class="loading-spinner-container" :class="containerClass">
        <div class="loading-spinner" :class="spinnerClass">
            <div class="spinner"></div>
        </div>
        <div v-if="message" class="loading-message">
            {{ message }}
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
    size: {
        type: String,
        default: 'medium',
        validator: (value) => ['small', 'medium', 'large'].includes(value)
    },
    message: {
        type: String,
        default: ''
    },
    overlay: {
        type: Boolean,
        default: false
    },
    color: {
        type: String,
        default: 'primary'
    }
})

const containerClass = computed(() => ({
    'loading-overlay': props.overlay,
    [`loading-${props.size}`]: true
}))

const spinnerClass = computed(() => ({
    [`spinner-${props.color}`]: true,
    [`spinner-${props.size}`]: true
}))
</script>

<style scoped>
.loading-spinner-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

.loading-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.8);
    z-index: 1000;
    backdrop-filter: blur(2px);
}

.loading-spinner {
    display: flex;
    align-items: center;
    justify-content: center;
}

.spinner {
    border-radius: 50%;
    border-style: solid;
    animation: spin 1s linear infinite;
}

/* Size variants */
.spinner-small .spinner {
    width: 20px;
    height: 20px;
    border-width: 2px;
}

.spinner-medium .spinner {
    width: 32px;
    height: 32px;
    border-width: 3px;
}

.spinner-large .spinner {
    width: 48px;
    height: 48px;
    border-width: 4px;
}

/* Color variants */
.spinner-primary .spinner {
    border-color: #3078F6 transparent #3078F6 transparent;
}

.spinner-secondary .spinner {
    border-color: #6c757d transparent #6c757d transparent;
}

.spinner-success .spinner {
    border-color: #28a745 transparent #28a745 transparent;
}

.spinner-danger .spinner {
    border-color: #dc3545 transparent #dc3545 transparent;
}

.spinner-warning .spinner {
    border-color: #ffc107 transparent #ffc107 transparent;
}

.loading-message {
    font-size: 14px;
    color: #666;
    text-align: center;
}

.loading-small .loading-message {
    font-size: 12px;
}

.loading-large .loading-message {
    font-size: 16px;
}

@keyframes spin {
    0% {
        transform: rotate(0deg);
    }

    100% {
        transform: rotate(360deg);
    }
}
</style>

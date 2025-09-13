<template>
    <div v-if="show" class="confirmation-modal select-modal show" @mousedown.self="emit('close')">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ title }}</h5>
                <button class="close-modal" @click="emit('close')">
                    <VIcon icon="tabler-x" />
                </button>
            </div>
            <div class="modal-body">
                <div class="confirmation-content">
                    <div class="icon-wrapper">
                        <VIcon :icon="icon" size="48" :class="iconClass" />
                    </div>
                    <p class="message">{{ message }}</p>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" @click="emit('close')">
                    {{ cancelText }}
                </button>
                <button class="btn btn-danger" @click="handleConfirm">
                    {{ confirmText }}
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
// Define props
const props = defineProps({
    show: {
        type: Boolean,
        default: false
    },
    title: {
        type: String,
        default: 'Confirm Action'
    },
    message: {
        type: String,
        default: 'Are you sure you want to proceed?'
    },
    icon: {
        type: String,
        default: 'tabler-alert-triangle'
    },
    iconClass: {
        type: String,
        default: 'text-warning'
    },
    confirmText: {
        type: String,
        default: 'Yes, Proceed'
    },
    cancelText: {
        type: String,
        default: 'Cancel'
    }
})

const emit = defineEmits(['confirm', 'close'])

const handleConfirm = () => {
    emit('confirm')
    emit('close')
}
</script>

<style scoped>
.confirmation-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1050;
}

.modal-content {
    background: white;
    border-radius: 8px;
    max-width: 400px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #dee2e6;
}

.modal-title {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 600;
}

.close-modal {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    padding: 0;
    color: #6c757d;
}

.close-modal:hover {
    color: #343a40;
}

.modal-body {
    padding: 1.5rem;
}

.confirmation-content {
    text-align: center;
}

.icon-wrapper {
    margin-bottom: 1rem;
}

.message {
    margin: 0;
    font-size: 1rem;
    color: #6c757d;
    line-height: 1.5;
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 0.5rem;
    padding: 1rem 1.5rem;
    border-top: 1px solid #dee2e6;
}

.btn {
    padding: 0.5rem 1rem;
    border-radius: 4px;
    border: 1px solid transparent;
    cursor: pointer;
    font-size: 0.875rem;
    transition: all 0.2s;
}

.btn-secondary {
    background-color: #6c757d;
    border-color: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background-color: #5a6268;
    border-color: #545b62;
}

.btn-danger {
    background-color: #dc3545;
    border-color: #dc3545;
    color: white;
}

.btn-danger:hover {
    background-color: #c82333;
    border-color: #bd2130;
}
</style>

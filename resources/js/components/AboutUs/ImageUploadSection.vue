<script setup>
import { ref } from 'vue'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

const props = defineProps({
    fieldName: {
        type: String,
        required: true
    },
    label: {
        type: String,
        required: true
    },
    previewImage: {
        type: String,
        required: true
    },
    disabled: {
        type: Boolean,
        default: false
    },
    errorMessage: {
        type: String,
        default: ''
    }
})

const emit = defineEmits(['upload', 'reset'])

const fileInputRef = ref()

const handleFileChange = (event) => {
    emit('upload', event, props.fieldName)
}

const handleReset = () => {
    emit('reset', props.fieldName, fileInputRef)
}

const triggerFileInput = () => {
    fileInputRef.value?.click()
}
</script>

<template>
    <VCol cols="12" md="6" lg="4" class="mb-4">
        <VCard 
            variant="outlined" 
            :class="{ 'border-error': errorMessage }"
            class="image-upload-card"
        >
            <VCardText class="d-flex flex-column align-center pa-4">
                <!-- Image Preview -->
                <VAvatar 
                    rounded 
                    size="120" 
                    class="mb-4 image-preview-avatar"
                    :image="previewImage"
                />
                
                <!-- Upload Label -->
                <h6 class="text-h6 mb-2 text-center">{{ label }}</h6>
                
                <!-- Action Buttons -->
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <VBtn 
                        color="primary" 
                        size="small" 
                        :disabled="disabled"
                        @click="triggerFileInput"
                    >
                        <VIcon icon="tabler-cloud-upload" class="me-1" />
                        {{ t('Upload Image') }}
                    </VBtn>

                    <VBtn 
                        size="small" 
                        color="secondary" 
                        variant="tonal" 
                        :disabled="disabled"
                        @click="handleReset"
                    >
                        <VIcon icon="tabler-refresh" class="me-1" />
                        {{ t('Reset') }}
                    </VBtn>
                </div>

                <!-- File Input (Hidden) -->
                <input 
                    ref="fileInputRef"
                    type="file" 
                    accept=".jpeg,.png,.jpg,.gif" 
                    hidden 
                    @change="handleFileChange"
                >

                <!-- Helper Text -->
                <p class="text-caption text-center text-medium-emphasis mb-2">
                    {{ t('Allowed JPG, GIF or PNG. Max size of 2 MB') }}
                </p>

                <!-- Error Message -->
                <VAlert
                    v-if="errorMessage"
                    type="error"
                    variant="tonal"
                    density="compact"
                    class="mt-2 w-100"
                >
                    {{ errorMessage }}
                </VAlert>
            </VCardText>
        </VCard>
    </VCol>
</template>

<style scoped>
.image-upload-card {
    transition: all 0.2s ease-in-out;
}

.image-upload-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.image-preview-avatar {
    border: 2px solid rgba(var(--v-theme-primary), 0.1);
    transition: border-color 0.2s ease-in-out;
}

.image-upload-card:hover .image-preview-avatar {
    border-color: rgba(var(--v-theme-primary), 0.3);
}

.border-error {
    border-color: rgb(var(--v-theme-error)) !important;
}
</style> 
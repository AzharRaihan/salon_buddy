<script setup>
import { onMounted, watch } from 'vue'
import { useAboutUsForm } from '@/composables/useAboutUsForm'
import { useImageUpload } from '@/composables/useImageUpload'
import FormSection from '@/components/AboutUs/FormSection.vue'
import ImageUploadSection from '@/components/AboutUs/ImageUploadSection.vue'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

// Composables
const {
    form,
    loadings,
    fetchAboutUsData,
    submitForm,
    resetForm,
    getFieldError
} = useAboutUsForm()

const {
    handleImageChange,
    resetImage,
    getPreviewImage,
    setOriginalImages
} = useImageUpload()

// Enhanced fetch function that also sets original images
const loadAboutUsData = async () => {
    try {
        const data = await fetchAboutUsData()
        if (data) {
            // Set original images for preview
            setOriginalImages(data)
        }
    } catch (error) {
        console.error('Error loading About Us data:', error)
    }
}

// Handle image upload with proper form integration
const handleImageUpload = (event, fieldName) => {
    handleImageChange(event, fieldName, form)
}

// Handle image reset with proper form integration
const handleImageReset = (fieldName, inputRef) => {
    resetImage(fieldName, form, inputRef)
}

// Initialize data on component mount
onMounted(() => {
    loadAboutUsData()
})

// Watch for form processing changes
watch(() => form.processing, (processing) => {
    loadings.value = processing
})
</script>

<template>
    <VRow>
        <VCol cols="12">
            <!-- Main Card -->
            <VCard class="about-us-form-card">
                <VCardItem>
                    <VCardTitle class="text-h3 d-flex align-center">
                        <VIcon icon="tabler-info-circle" class="me-3" />
                        {{ t('About Us Content Management') }}
                    </VCardTitle>
                    <VCardSubtitle class="text-body-1 mt-2">
                        {{ t('Manage and customize your website\'s About Us page content') }}
                    </VCardSubtitle>
                </VCardItem>

                <VDivider />

                <VCardText>
                    <VForm @submit.prevent="submitForm">
                        <VRow>
                            <!-- Section 1: Hero Section -->
                            <FormSection 
                                :title="t('Hero Section')" 
                                :subtitle="t('Main banner section that appears at the top of your About Us page')"
                            >
                                <VRow>
                                    <!-- Section 1 Heading -->
                                    <VCol cols="12" md="6">
                                        <AppTextField
                                            v-model="form.section_1_heading"
                                            :label="t('Hero Heading')"
                                            :placeholder="t('Enter main heading for hero section')"
                                            :error-messages="getFieldError('section_1_heading')"
                                            :disabled="loadings"
                                            counter="100"
                                            maxlength="100"
                                            :required="true"
                                        />
                                    </VCol>

                                    <!-- Section 1 Experience -->
                                    <VCol cols="12" md="6">
                                        <AppTextField
                                            v-model="form.section_1_experience"
                                            :label="t('Experience Text')"
                                            :placeholder="t('e.g., \'10+ Years Experience\'')"
                                            :error-messages="getFieldError('section_1_experience')"
                                            :disabled="loadings"
                                            counter="100"
                                            maxlength="100"
                                            :required="true"
                                        />
                                    </VCol>

                                    <!-- Section 1 Description -->
                                    <VCol cols="12">
                                        <AppTextarea
                                            v-model="form.section_1_description"
                                            :label="t('Hero Description')"
                                            :placeholder="t('Enter detailed description for hero section')"
                                            :error-messages="getFieldError('section_1_description')"
                                            :disabled="loadings"
                                            rows="4"
                                            counter="250"
                                            maxlength="250"
                                            :required="true"
                                        />
                                    </VCol>

                                    

                                    <!-- Section 1 Image Upload -->
                                    <ImageUploadSection
                                        field-name="section_1_image"
                                        :label="t('Hero Section Image')"
                                        :preview-image="getPreviewImage('section_1_image')"
                                        :disabled="loadings"
                                        :error-message="getFieldError('section_1_image')"
                                        @upload="handleImageUpload"
                                        @reset="handleImageReset"
                                    />
                                    <ImageUploadSection
                                        field-name="section_1_image_2"
                                        :label="t('Hero Section Image 2')"
                                        :preview-image="getPreviewImage('section_1_image_2')"
                                        :disabled="loadings"
                                        :error-message="getFieldError('section_1_image_2')"
                                        @upload="handleImageUpload"
                                        @reset="handleImageReset"
                                    />

                                    <!-- Section 1 Button Link -->
                                    <VCol cols="12" md="6">
                                        <AppTextField
                                            v-model="form.section_1_btn_link"
                                            :label="t('Button Link')"
                                            :placeholder="t('Enter URL for hero button')"
                                            :error-messages="getFieldError('section_1_btn_link')"
                                            :disabled="loadings"
                                            type="url"
                                            :required="true"
                                        />
                                    </VCol>
                                </VRow>
                            </FormSection>

                            <!-- Section 2: Video/Play Section -->
                            <FormSection 
                                :title="t('Video Section')" 
                                :subtitle="t('Video or promotional content section')"
                            >
                                <VRow>
                                    <!-- Section Play Title -->
                                    <VCol cols="12" md="6">
                                        <AppTextField
                                            v-model="form.section_play_title"
                                            :label="t('Video Section Title')"
                                            :placeholder="t('Enter title for video section')"
                                            :error-messages="getFieldError('section_play_title')"
                                            :disabled="loadings"
                                            counter="100"
                                            maxlength="100"
                                            :required="true"
                                        />
                                    </VCol>

                                    <!-- Section Play Link -->
                                    <VCol cols="12" md="6">
                                        <AppTextField
                                            v-model="form.section_play_link"
                                            :label="t('Video/Link URL')"
                                            :placeholder="t('Enter video or content URL')"
                                            :error-messages="getFieldError('section_play_link')"
                                            :disabled="loadings"
                                            type="url"
                                            :required="true"
                                        />
                                    </VCol>

                                    <!-- Section Play Image Upload -->
                                    <ImageUploadSection
                                        field-name="section_play_image"
                                        :label="t('Video Section Image')"
                                        :preview-image="getPreviewImage('section_play_image')"
                                        :disabled="loadings"
                                        :error-message="getFieldError('section_play_image')"
                                        @upload="handleImageUpload"
                                        @reset="handleImageReset"
                                    />
                                </VRow>
                            </FormSection>

                            <!-- Section 3: Discover Section -->
                            <FormSection 
                                :title="t('Discover Section')" 
                                :subtitle="t('Main content section with background and featured content')"
                            >
                                <VRow>
                                    <!-- Section Discover Heading -->
                                    <VCol cols="12" md="6">
                                        <AppTextField
                                            v-model="form.section_discover_heading"
                                            :label="t('Discover Heading')"
                                            :placeholder="t('Enter heading for discover section')"
                                            :error-messages="getFieldError('section_discover_heading')"
                                            :disabled="loadings"
                                            counter="100"
                                            maxlength="100"
                                            :required="true"
                                        />
                                    </VCol>

                                    <!-- Section Discover Description -->
                                    <VCol cols="12" md="6">
                                        <AppTextarea
                                            v-model="form.section_discover_description"
                                            :label="t('Discover Description')"
                                            :placeholder="t('Enter description for discover section')"
                                            :error-messages="getFieldError('section_discover_description')"
                                            :disabled="loadings"
                                            rows="3"
                                            counter="250"
                                            maxlength="250"
                                            :required="true"
                                        />
                                    </VCol>

                                    <!-- Section Discover Images -->
                                    <ImageUploadSection
                                        field-name="section_discover_bg_image"
                                        :label="t('Discover Background Image')"
                                        :preview-image="getPreviewImage('section_discover_bg_image')"
                                        :disabled="loadings"
                                        :error-message="getFieldError('section_discover_bg_image')"
                                        @upload="handleImageUpload"
                                        @reset="handleImageReset"
                                    />

                                    <ImageUploadSection
                                        field-name="section_discover_front_image"
                                        :label="t('Discover Front Image')"
                                        :preview-image="getPreviewImage('section_discover_front_image')"
                                        :disabled="loadings"
                                        :error-message="getFieldError('section_discover_front_image')"
                                        @upload="handleImageUpload"
                                        @reset="handleImageReset"
                                    />
                                </VRow>
                            </FormSection>

                            <!-- Section 4: Discover Items -->
                            <FormSection 
                                :title="t('Feature Items')" 
                                :subtitle="t('Three featured items showcasing your services or values')"
                            >
                                <VRow>
                                    <!-- Loop through 3 discover items -->
                                    <template v-for="itemIndex in 3" :key="`discover-item-${itemIndex}`">
                                        <VCol cols="12">
                                            <VCard variant="outlined" class="feature-item-card">
                                                <VCardItem>
                                                    <VCardTitle class="text-h6">
                                                        <VIcon icon="tabler-star" class="me-2" />
                                                        {{ t('Feature Item') }} {{ itemIndex }}
                                                    </VCardTitle>
                                                </VCardItem>
                                                
                                                <VCardText>
                                                    <VRow>
                                                        <!-- Item Heading -->
                                                        <VCol cols="12" md="6">
                                                            <AppTextField
                                                                v-model="form[`section_discover_item_${itemIndex}_heading`]"
                                                                :label="`${t('Item')} ${itemIndex} ${t('Heading')}`" :required="true"
                                                                :placeholder="`${t('Enter heading for item')} ${itemIndex}`"
                                                                :error-messages="getFieldError(`section_discover_item_${itemIndex}_heading`)"
                                                                :disabled="loadings"
                                                                counter="100"
                                                                maxlength="100"
                                                            />
                                                        </VCol>

                                                        <!-- Item Description -->
                                                        <VCol cols="12" md="6">
                                                            <AppTextarea
                                                                v-model="form[`section_discover_item_${itemIndex}_description`]"
                                                                :label="`${t('Item')} ${itemIndex} ${t('Description')}`"
                                                                :required="true"
                                                                :placeholder="`${t('Enter description for item')} ${itemIndex}`"
                                                                :error-messages="getFieldError(`section_discover_item_${itemIndex}_description`)"
                                                                :disabled="loadings"
                                                                rows="3"
                                                                counter="250"
                                                                maxlength="250"
                                                            />
                                                        </VCol>

                                                        <!-- Item Image -->
                                                        <ImageUploadSection
                                                            :field-name="`section_discover_item_${itemIndex}_image`"
                                                            :label="`${t('Item')} ${itemIndex} ${t('Image')}`"
                                                            :preview-image="getPreviewImage(`section_discover_item_${itemIndex}_image`)"
                                                            :disabled="loadings"
                                                            :error-message="getFieldError(`section_discover_item_${itemIndex}_image`)"
                                                            @upload="handleImageUpload"
                                                            @reset="handleImageReset"
                                                        />
                                                    </VRow>
                                                </VCardText>
                                            </VCard>
                                        </VCol>
                                    </template>
                                </VRow>
                            </FormSection>

                            <!-- Form Actions -->
                            <VCol cols="12">
                                <div class="d-flex flex-wrap gap-4">
                                    <VBtn 
                                        type="submit" 
                                        color="primary"
                                        :loading="loadings" 
                                        :disabled="loadings"
                                        class="px-8"
                                    >
                                        <VIcon start icon="tabler-checkbox" />
                                        {{ t('Update About Us Content') }}
                                    </VBtn>
                                </div>
                            </VCol>
                        </VRow>
                    </VForm>
                </VCardText>
            </VCard>
        </VCol>
    </VRow>
</template>

<style scoped>
/* .about-us-form-card {
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
    border-radius: 12px;
    overflow: hidden;
}

.feature-item-card {
    margin-bottom: 1.5rem;
    transition: all 0.2s ease-in-out;
}

.feature-item-card:hover {
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
}

.actions-card {
    background: linear-gradient(135deg, rgba(var(--v-theme-surface), 0.8), rgba(var(--v-theme-primary), 0.05));
    border: 1px solid rgba(var(--v-theme-primary), 0.1);
} */
</style>
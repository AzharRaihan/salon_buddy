import { ref, reactive } from 'vue'
import { toast } from 'vue3-toastify'
import defaultBanner from '@images/system-config/default-picture.png'
import { useI18n } from 'vue-i18n'

export function useAboutUsForm() {
    const { t } = useI18n() // Move inside function to ensure i18n is initialized
    
    const loadings = ref(false)
    
    // Initialize form data
    const form = reactive({
        section_1_heading: '',
        section_1_description: '',
        section_1_btn_link: '',
        section_1_image: null,
        section_1_image_2: null,
        section_1_experience: '',
        section_play_title: '',
        section_play_link: '',
        section_play_image: null,
        section_discover_heading: '',
        section_discover_description: '',
        section_discover_bg_image: null,
        section_discover_front_image: null,
        section_discover_item_1_heading: '',
        section_discover_item_1_description: '',
        section_discover_item_1_image: null,
        section_discover_item_2_heading: '',
        section_discover_item_2_description: '',
        section_discover_item_2_image: null,
        section_discover_item_3_heading: '',
        section_discover_item_3_description: '',
        section_discover_item_3_image: null,
        errors: {},
        clearErrors() {
            this.errors = {}
        },
        data() {
            const formData = { ...this }
            delete formData.errors
            delete formData.clearErrors
            delete formData.data
            return formData
        }
    })

    // Custom validation errors for better UX
    const customErrors = ref({})

    // Fetch About Us data from API
    const fetchAboutUsData = async () => {
        try {
            loadings.value = true
            const response = await $api('/website-about-us')

            if (response.success && response.data) {
                const data = response.data
                
                // Populate form with fetched data
                Object.keys(form.data()).forEach(key => {
                    if (key.includes('_image')) {
                        // For images, keep the form field as null (no file selected)
                        form[key] = null
                    } else {
                        form[key] = data[key] || ''
                    }
                })

                // Return data for image preview setup
                return data
            }
        } catch (err) {
            console.error('Error fetching About Us data:', err)
            toast(t('Error loading About Us data'), { type: 'error' })
        } finally {
            loadings.value = false
        }
    }

    // Validate form before submission
    const validateForm = () => {
        const errors = {}
        let isValid = true

        // Section 1 validation
        if (!form.section_1_heading?.trim()) {
            errors.section_1_heading = t('Section 1 heading is required')
            isValid = false
        } else if (form.section_1_heading.length > 100) {
            errors.section_1_heading = t('Section 1 heading must not exceed 100 characters')
            isValid = false
        }

        if (!form.section_1_description?.trim()) {
            errors.section_1_description = t('Section 1 description is required')
            isValid = false
        } else if (form.section_1_description.length > 250) {
            errors.section_1_description = t('Section 1 description must not exceed 250 characters')
            isValid = false
        }

        if (!form.section_1_btn_link?.trim()) {
            errors.section_1_btn_link = t('Section 1 button link is required')
            isValid = false
        }

        if (!form.section_1_experience?.trim()) {
            errors.section_1_experience = t('Section 1 experience is required')
            isValid = false
        }

        // Section Play validation
        if (!form.section_play_title?.trim()) {
            errors.section_play_title = t('Section play title is required')
            isValid = false
        }

        if (!form.section_play_link?.trim()) {
            errors.section_play_link = t('Section play link is required')
            isValid = false
        }

        // Section Discover validation
        if (!form.section_discover_heading?.trim()) {
            errors.section_discover_heading = t('Section discover heading is required')
            isValid = false
        }

        if (!form.section_discover_description?.trim()) {
            errors.section_discover_description = t('Section discover description is required')
            isValid = false
        }

        // Section Discover Items validation
        for (let i = 1; i <= 3; i++) {
            const headingField = `section_discover_item_${i}_heading`
            const descField = `section_discover_item_${i}_description`

            if (!form[headingField]?.trim()) {
                errors[headingField] = t(`Section discover item ${i} heading is required`)
                isValid = false
            }

            if (!form[descField]?.trim()) {
                errors[descField] = t(`Section discover item ${i} description is required`)
                isValid = false
            }
        }

        customErrors.value = errors
        return isValid
    }

    // Submit form data
    const submitForm = async () => {
        if (!validateForm()) {
            toast(t('Please fill all required fields correctly'), { type: 'error' })
            return
        }

        try {
            loadings.value = true
            
            // Create FormData for file uploads
            const formData = new FormData()
            
            // Append form fields
            Object.keys(form.data()).forEach(key => {
                const value = form[key]
                if (value !== null && value !== undefined) {
                    if (key.includes('_image') && value instanceof File) {
                        formData.append(key, value)
                    } else if (!key.includes('_image')) {
                        formData.append(key, value || '')
                    }
                }
            })

            const response = await $api('/website-about-us-update', {
                method: 'POST',
                body: formData,
                headers: {
                    'Accept': 'application/json',
                },
            })

            if (response.success) {
                toast(t('About Us content updated successfully'), { type: 'success' })
                customErrors.value = {}
                form.clearErrors()
                
                // Refresh data after successful update
                await fetchAboutUsData()
            } else {
                throw new Error(response.message || t('Failed to update About Us content'))
            }
        } catch (err) {
            console.error('Error updating About Us:', err)
            
            // Handle validation errors
            if (err.errors) {
                customErrors.value = { ...customErrors.value, ...err.errors }
                toast(t('Please check the form for errors'), { type: 'error' })
            } else {
                toast(err.message || t('Error updating About Us content'), { type: 'error' })
            }
        } finally {
            loadings.value = false
        }
    }

    // Reset form to original state
    const resetForm = () => {
        fetchAboutUsData()
        customErrors.value = {}
        form.clearErrors()
    }

    // Get combined errors (custom + server errors)
    const getFieldError = (fieldName) => {
        return customErrors.value[fieldName] || form.errors[fieldName] || ''
    }

    return {
        form,
        loadings,
        customErrors,
        fetchAboutUsData,
        validateForm,
        submitForm,
        resetForm,
        getFieldError
    }
}
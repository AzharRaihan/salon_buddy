import { ref, computed } from 'vue'
import { toast } from 'vue3-toastify'
import defaultBanner from '@images/system-config/default-picture.png'
import { useI18n } from 'vue-i18n'

export function useImageUpload() {

    const { t } = useI18n() // Move inside function to ensure i18n is initialized

    // Store preview images for each field
    const imagePreview = ref({
        section_1_image: defaultBanner,
        section_1_image_2: defaultBanner,
        section_play_image: defaultBanner,
        section_discover_bg_image: defaultBanner,
        section_discover_front_image: defaultBanner,
        section_discover_item_1_image: defaultBanner,
        section_discover_item_2_image: defaultBanner,
        section_discover_item_3_image: defaultBanner,
    })

    // Store original image URLs from API
    const originalImages = ref({})

    // Image validation configuration
    const imageValidation = {
        allowedTypes: ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'],
        maxSize: 2 * 1024 * 1024, // 2MB in bytes
        allowedExtensions: ['jpeg', 'png', 'jpg', 'gif']
    }

    // Validate image file
    const validateImageFile = (file) => {
        const errors = []

        if (!file) {
            return { isValid: false, errors: ['No file selected'] }
        }

        // Check file type
        if (!imageValidation.allowedTypes.includes(file.type)) {
            errors.push(t('Invalid file type. Only JPEG, PNG, JPG, and GIF files are allowed.'))
        }

        // Check file size
        if (file.size > imageValidation.maxSize) {
            errors.push(t('File size too large. Maximum allowed size is 2MB.'))
        }

        // Check file extension
        const fileExtension = file.name.split('.').pop().toLowerCase()
        if (!imageValidation.allowedExtensions.includes(fileExtension)) {
            errors.push(t('Invalid file extension. Only jpeg, png, jpg, and gif extensions are allowed.'))
        }

        return {
            isValid: errors.length === 0,
            errors
        }
    }

    // Handle image change
    const handleImageChange = (event, fieldName, form) => {
        const file = event.target.files[0]
        
        if (!file) {
            return
        }

        const validation = validateImageFile(file)

        if (!validation.isValid) {
            // Show validation errors
            validation.errors.forEach(error => {
                toast(error, { type: 'error' })
            })
            
            // Reset file input
            event.target.value = ''
            return
        }

        // Create preview
        const reader = new FileReader()
        reader.onload = (e) => {
            imagePreview.value[fieldName] = e.target.result
        }
        reader.readAsDataURL(file)

        // Set file in form
        if (form && form[fieldName] !== undefined) {
            form[fieldName] = file
        }

        toast(t('Image uploaded successfully'), { type: 'success' })
    }

    // Reset image to default
    const resetImage = (fieldName, form, inputRef) => {
        // Reset preview to default or original image
        imagePreview.value[fieldName] = originalImages.value[fieldName] || defaultBanner
        
        // Reset form field
        if (form && form[fieldName] !== undefined) {
            form[fieldName] = null
        }

        // Reset file input
        if (inputRef && inputRef.value) {
            inputRef.value.value = ''
        }

        toast(t('Image reset successfully'), { type: 'success' })
    }

    // Set original images from API data
    const setOriginalImages = (data) => {
        const imageFields = [
            'section_1_image',
            'section_1_image_2',
            'section_play_image', 
            'section_discover_bg_image',
            'section_discover_front_image',
            'section_discover_item_1_image',
            'section_discover_item_2_image',
            'section_discover_item_3_image'
        ]

        imageFields.forEach(field => {
            const imageUrl = data[`${field}_url`] || data[field]
            if (imageUrl) {
                originalImages.value[field] = imageUrl
                imagePreview.value[field] = imageUrl
            }
        })
    }

    // Get preview image for a field
    const getPreviewImage = (fieldName) => {
        return imagePreview.value[fieldName] || defaultBanner
    }

    // Check if image has been changed
    const hasImageChanged = (fieldName) => {
        const current = imagePreview.value[fieldName]
        const original = originalImages.value[fieldName] || defaultBanner
        return current !== original
    }

    // Reset all images to original state
    const resetAllImages = (form) => {
        Object.keys(imagePreview.value).forEach(fieldName => {
            imagePreview.value[fieldName] = originalImages.value[fieldName] || defaultBanner
            if (form && form[fieldName] !== undefined) {
                form[fieldName] = null
            }
        })
    }

    // Get image upload helper text
    const getImageUploadHelperText = () => {
        return t('Allowed JPG, GIF or PNG. Max size of 2 MB')
    }

    return {
        imagePreview,
        originalImages,
        imageValidation,
        validateImageFile,
        handleImageChange,
        resetImage,
        setOriginalImages,
        getPreviewImage,
        hasImageChanged,
        resetAllImages,
        getImageUploadHelperText
    }
} 
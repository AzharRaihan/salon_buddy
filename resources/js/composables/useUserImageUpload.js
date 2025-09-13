import { ref, computed } from 'vue'
import { toast } from 'vue3-toastify'
import { useI18n } from 'vue-i18n'
import defaultAvatar from '@images/system-config/default-picture.png'

export function useUserImageUpload() {
    const { t } = useI18n()

    // Image Cropper State
    const showCropperModal = ref(false)
    const imageSrc = ref(null)
    const croppedImage = ref(null)
    const cropPreview = ref(null)
    const refInputEl = ref()
    const previewImage = ref(defaultAvatar)
    let cropperRef = null

    // Image Validation Configuration
    const ALLOWED_TYPES = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp']
    const MAX_FILE_SIZE = 1 * 1024 * 1024 // 1MB
    const MIN_WIDTH = 65
    const MIN_HEIGHT = 65
    const RECOMMENDED_WIDTH = 420
    const RECOMMENDED_HEIGHT = 390

    // Validation Functions
    const validateImageFile = (file) => {
        if (!ALLOWED_TYPES.includes(file.type)) {
            toast(t('Only JPEG, JPG, PNG, and WEBP formats are allowed.'), { type: 'error' })
            return false
        }
        if (file.size > MAX_FILE_SIZE) {
            toast(t('File size must be less than or equal to 1 MB.'), { type: 'error' })
            return false
        }
        return true
    }

    const validateImageDimensions = (img) => {
        if (img.width < MIN_WIDTH || img.height < MIN_HEIGHT) {
            toast(t(`Image dimensions must be at least ${MIN_WIDTH}px × ${MIN_HEIGHT}px.`), { type: 'error' })
            return false
        }
        return true
    }

    // Cropper Functions
    const onCrop = ({ canvas }) => {
        cropperRef = canvas
        cropPreview.value = canvas.toDataURL()
    }

    const getCroppedImage = () => {
        if (cropperRef) {
            croppedImage.value = cropperRef.toDataURL()
            previewImage.value = croppedImage.value
            showCropperModal.value = false
            return convertDataURLtoFile(croppedImage.value, 'cropped.jpg')
        }
        return null
    }

    const convertDataURLtoFile = (dataURL, filename) => {
        const arr = dataURL.split(',')
        const mime = arr[0].match(/:(.*?);/)[1]
        const bstr = atob(arr[1])
        let n = bstr.length
        const u8arr = new Uint8Array(n)
        while (n--) {
            u8arr[n] = bstr.charCodeAt(n)
        }
        return new File([u8arr], filename, { type: mime })
    }

    const cancelCrop = () => {
        showCropperModal.value = false
        imageSrc.value = null
        cropPreview.value = null
        if (refInputEl.value) {
            refInputEl.value.value = ''
        }
    }

    // Image Change Handler
    const changeImage = (event, form) => {
        const file = event.target.files[0]
        if (!file) return

        if (!validateImageFile(file)) {
            event.target.value = ''
            return false
        }

        const reader = new FileReader()
        reader.onload = e => {
            const img = new Image()
            img.onload = () => {
                if (!validateImageDimensions(img)) {
                    event.target.value = ''
                    return false
                }
                imageSrc.value = e.target.result
                showCropperModal.value = true
            }
            img.onerror = () => {
                toast(t('Invalid image file.'), { type: 'error' })
                event.target.value = ''
                return false
            }
            img.src = e.target.result
        }
        reader.readAsDataURL(file)
        return true
    }

    // Reset Functions
    const resetImage = (form) => {
        if (form) {
            form.photo = null
        }
        previewImage.value = defaultAvatar
        if (refInputEl.value) {
            refInputEl.value.value = ''
        }
        imageSrc.value = null
        showCropperModal.value = false
    }

    const setPreviewImage = (imageUrl) => {
        if (imageUrl) {
            previewImage.value = imageUrl
        } else {
            previewImage.value = defaultAvatar
        }
    }

    // Get helper text
    const getHelperText = () => {
        return t('Allowed JPG, GIF or PNG. Max size of 1 MB Required')
    }

    const getRecommendedText = () => {
        return t('Recommended size: 420px x 390px') + ' - ' + t("Use the exact size for best results, but don't use less.")
    }

    return {
        // State
        showCropperModal,
        imageSrc,
        croppedImage,
        cropPreview,
        refInputEl,
        previewImage,
        
        // Constants
        ALLOWED_TYPES,
        MAX_FILE_SIZE,
        MIN_WIDTH,
        MIN_HEIGHT,
        RECOMMENDED_WIDTH,
        RECOMMENDED_HEIGHT,
        
        // Functions
        validateImageFile,
        validateImageDimensions,
        onCrop,
        getCroppedImage,
        convertDataURLtoFile,
        cancelCrop,
        changeImage,
        resetImage,
        setPreviewImage,
        getHelperText,
        getRecommendedText
    }
}

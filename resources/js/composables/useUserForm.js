import { ref, reactive } from 'vue'
import { toast } from 'vue3-toastify'
import { useI18n } from 'vue-i18n'

export function useUserForm() {
    const { t } = useI18n()
    const loadings = ref(false)

    const socialMediaPlatforms = [
        { name: 'Facebook' },
        { name: 'Twitter' },
        { name: 'Instagram' },
        { name: 'YouTube' },
        { name: 'TikTok' }
    ]

    // Form state
    const form = reactive({
        name: '',
        designation: '',
        email: '',
        phone: '',
        password: '',
        password_confirmation: '',
        role: null,
        branch_id: [],
        salary: '',
        service_id: [],
        commission: '',
        overtime_hour_rate: '',
        status: null,
        will_login: 'Yes',
        photo: null,
        social_media: socialMediaPlatforms.map(platform => ({
            name: platform.name,
            url: '',
            is_active: false
        })),
    })

    // Error states
    const errors = reactive({
        name: '',
        designation: '',
        email: '',
        phone: '',
        password: '',
        password_confirmation: '',
        role: '',
        branch_id: '',
        salary: '',
        service_id: '',
        social_media: Array(socialMediaPlatforms.length).fill(''),
    })

    // Data sources
    const roles = ref([])
    const branches = ref([])
    const services = ref([])

    // Validation functions
    const validateName = (name) => {
        if (!name) {
            errors.name = t('Name is required')
            return false
        }
        errors.name = ''
        return true
    }

    const validateDesignation = (designation) => {
        if (!designation) {
            errors.designation = t('Designation is required')
            return false
        }
        errors.designation = ''
        return true
    }

    const validateEmail = (email) => {
        if (!email) {
            errors.email = t('Email is required')
            return false
        }
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            errors.email = t('Please enter a valid email')
            return false
        }
        errors.email = ''
        return true
    }

    const validatePhone = (phone) => {
        if (!phone) {
            errors.phone = t('Phone is required')
            return false
        }
        errors.phone = ''
        return true
    }

    const validatePassword = (password, willLogin = 'Yes', existingPassword = null) => {
        if (!password && willLogin === 'Yes' && !existingPassword) {
            errors.password = t('Password is required')
            return false
        }
        if (password && password.length < 6 && willLogin === 'Yes') {
            errors.password = t('Password must be at least 6 characters')
            return false
        }
        errors.password = ''
        return true
    }

    const validatePasswordConfirmation = (passwordConfirmation) => {
        if (passwordConfirmation !== form.password && form.will_login === 'Yes') {
            errors.password_confirmation = t('Password confirmation does not match')
            return false
        }
        errors.password_confirmation = ''
        return true
    }

    const validateRole = (role) => {
        if (!role) {
            errors.role = t('Role is required')
            return false
        }
        errors.role = ''
        return true
    }

    const validateBranch = (branch) => {
        if (!branch || branch.length === 0) {
            errors.branch_id = t('At least one branch is required')
            return false
        }
        errors.branch_id = ''
        return true
    }

    const validateSalary = (salary) => {
        if (!salary) {
            errors.salary = t('Salary is required')
            return false
        }
        errors.salary = ''
        return true
    }

    const validateService = (service) => {
        if (!service || service.length === 0) {
            errors.service_id = t('At least one service is required')
            return false
        }
        errors.service_id = ''
        return true
    }

    const validateSocialMedia = () => {
        let isValid = true
        errors.social_media = Array(socialMediaPlatforms.length).fill('')
        
        form.social_media.forEach((social, index) => {
            if (social.is_active && !social.url.trim()) {
                errors.social_media[index] = `${social.name} ${t('URL is required when active')}`
                isValid = false
            } else if (social.is_active && social.url.trim()) {
                const urlPattern = /^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/
                if (!urlPattern.test(social.url.trim())) {
                    errors.social_media[index] = t('Please enter a valid') + ' ' + social.name + ' ' + t('URL')
                    isValid = false
                }
            }
        })
        
        return isValid
    }

    // API functions
    const fetchRoles = async () => {
        try {
            const response = await $api('/roles')
            roles.value = response.data.roles.map(role => ({
                title: role.name,
                value: role.id
            }))
        } catch (error) {
            console.error('Error fetching roles:', error)
            toast(t('Failed to load roles'), { type: 'error' })
        }
    }

    const fetchBranches = async () => {
        try {
            const response = await $api('/get-branch-list')
            branches.value = response.data.map(branch => ({
                title: branch.branch_name,
                value: branch.id
            }))
        } catch (error) {
            console.error('Error fetching branches:', error)
            toast(t('Failed to load branches'), { type: 'error' })
        }
    }

    const fetchServices = async () => {
        try {
            const response = await $api('/get-service-type-item-list')
            services.value = response.data.map(service => ({
                title: service.name,
                value: service.id
            }))
        } catch (error) {
            console.error('Error fetching services:', error)
            toast(t('Failed to load services'), { type: 'error' })
        }
    }

    // Form validation
    const validateForm = (existingPassword = null) => {
        const isNameValid = validateName(form.name)
        const isDesignationValid = validateDesignation(form.designation)
        const isEmailValid = validateEmail(form.email)
        const isPhoneValid = validatePhone(form.phone)
        const isPasswordValid = validatePassword(form.password, form.will_login, existingPassword)
        const isPasswordConfirmationValid = validatePasswordConfirmation(form.password_confirmation)
        const isRoleValid = validateRole(form.role)
        const isBranchValid = validateBranch(form.branch_id)
        const isSalaryValid = validateSalary(form.salary)
        const isServiceValid = validateService(form.service_id)
        const isSocialMediaValid = validateSocialMedia()

        return isNameValid && isEmailValid && isPhoneValid && isPasswordValid && 
               isPasswordConfirmationValid && isRoleValid && isBranchValid &&
               isSalaryValid && isServiceValid && isSocialMediaValid
    }

    // Reset form
    const resetForm = () => {
        Object.assign(form, {
            name: '',
            designation: '',
            email: '',
            phone: '',
            password: '',
            password_confirmation: '',
            role: '',
            branch_id: [],
            salary: '',
            service_id: [],
            commission: '',
            overtime_hour_rate: '',
            status: '',
            will_login: 'Yes',
            photo: null,
            social_media: socialMediaPlatforms.map(platform => ({
                name: platform.name,
                url: '',
                is_active: false
            })),
        })

        // Reset all errors
        Object.keys(errors).forEach(key => {
            if (Array.isArray(errors[key])) {
                errors[key] = Array(socialMediaPlatforms.length).fill('')
            } else {
                errors[key] = ''
            }
        })
    }

    // Initialize form with user data (for edit)
    const initializeForm = (userData) => {
        let savedSocialMedia = []
        try {
            savedSocialMedia = typeof userData.social_media === 'string' 
                ? JSON.parse(userData.social_media) 
                : (userData.social_media || [])
        } catch (e) {
            console.error('Error parsing social media:', e)
            savedSocialMedia = []
        }

        Object.assign(form, {
            name: userData.name,
            designation: userData.designation,
            email: userData.email,
            phone: userData.phone || '',
            password: '',
            password_confirmation: '',
            role: userData.role,
            branch_id: userData.branch_id?.split(',').map(Number) || [],
            salary: userData.salary || '',
            service_id: userData.service_id?.split(',').map(Number) || [],
            commission: userData.commission || '',
            overtime_hour_rate: userData.overtime_hour_rate || '',
            status: userData.status,
            will_login: userData.will_login === 'Yes' ? 'Yes' : 'No',
            photo: null,
            social_media: socialMediaPlatforms.map(platform => {
                const saved = savedSocialMedia.find(sm => sm.name === platform.name)
                return {
                    name: platform.name,
                    url: saved?.url || '',
                    is_active: saved?.is_active || false
                }
            }),
        })
    }

    return {
        // State
        form,
        errors,
        loadings,
        roles,
        branches,
        services,
        socialMediaPlatforms,

        // Functions
        validateName,
        validateDesignation,
        validateEmail,
        validatePhone,
        validatePassword,
        validatePasswordConfirmation,
        validateRole,
        validateBranch,
        validateSalary,
        validateService,
        validateSocialMedia,
        validateForm,
        fetchRoles,
        fetchBranches,
        fetchServices,
        resetForm,
        initializeForm
    }
}

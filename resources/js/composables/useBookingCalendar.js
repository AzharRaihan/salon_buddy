import { ref, computed, watch } from 'vue'
import { toast } from 'vue3-toastify'
import { useI18n } from 'vue-i18n'

export function useBookingCalendar() {
    const { t } = useI18n()

    // Reactive state
    const events = ref([])
    const loadings = ref(false)
    const showAddModal = ref(false)
    const showEditModal = ref(false)
    const selectedEvent = ref(null)
    const selectedDate = ref(null)

    // Form data
    const customers = ref([])
    const branches = ref([])
    const servicePackages = ref([])
    const serviceSellers = ref([])

    const bookingForm = ref({
        reference_no: '',
        customer_id: null,
        branch_id: null,
        date: null,
        note: '',
        status: 'Pending',
        booking_details: [],
    })

    const errors = ref({
        reference_no: '',
        customer_id: null,
        branch_id: null,
        date: null,
        status: null,
        booking_details: []
    })

    // Filter states
    const checkAll = ref(true)
    const selectedCalendars = ref(['Pending', 'Accepted', 'Rejected', 'Completed'])

    const availableCalendars = ref([
        { color: 'warning', name: 'Pending' },
        { color: 'info', name: 'Accepted' },
        { color: 'error', name: 'Rejected' },
        { color: 'success', name: 'Completed' },
    ])

    // Computed properties
    const isCompletedBooking = computed(() => {
        return selectedEvent.value?.status === 'Completed'
    })

    const filteredEvents = computed(() => {
        if (checkAll.value) {
            selectedCalendars.value = ['Pending', 'Accepted', 'Rejected', 'Completed']
            return events.value
        }
        return events.value.filter(event => selectedCalendars.value.includes(event.status))
    })

    // Watch for checkAll changes
    watch(checkAll, (newValue) => {
        if (newValue) {
            selectedCalendars.value = ['Pending', 'Accepted', 'Rejected', 'Completed']
        } else if (newValue === false && selectedCalendars.value.length === 4) {
            selectedCalendars.value = []
        }
    })

    // Watch for selectedCalendars changes
    watch(selectedCalendars, (newValue) => {
        const allCalendars = ['Pending', 'Accepted', 'Rejected', 'Completed']
        
        if (newValue.length === allCalendars.length && allCalendars.every(cal => newValue.includes(cal))) {
            checkAll.value = true
        } else {
            checkAll.value = false
        }
    }, { deep: true })

    // Methods
    const resetForm = () => {
        bookingForm.value = {
            reference_no: '',
            customer_id: null,
            branch_id: null,
            date: null,
            note: '',
            status: 'Pending',
            booking_details: [],
        }
        errors.value = {
            reference_no: '',
            customer_id: null,
            branch_id: null,
            date: null,
            status: null,
            booking_details: []
        }
    }

    const resetFormForAdd = (selectedDate = null) => {
        bookingForm.value = {
            reference_no: '',
            customer_id: null,
            branch_id: null,
            date: selectedDate,
            note: '',
            status: 'Pending',
            booking_details: [],
        }
        errors.value = {
            reference_no: '',
            customer_id: null,
            branch_id: null,
            date: null,
            status: null,
            booking_details: []
        }
    }

    const openAddModal = async () => {
        const today = new Date()
        const todayFormatted = today.toISOString().split('T')[0];
        resetFormForAdd(todayFormatted)
        try {
            const refResponse = await $api('/generate-booking-reference-no')
            if (refResponse.data) {
                bookingForm.value.reference_no = refResponse.data
            }
        } catch (err) {
            console.error('Error generating reference number:', err)
        }
        showAddModal.value = true
    }

    const closeModal = (modalType) => {
        if (modalType === 'add') {
            showAddModal.value = false
        } else {
            showEditModal.value = false
            selectedEvent.value = null
        }
    }

    const fetchInitialData = async () => {
        try {
            const [customersResponse, branchesResponse, packagesResponse, sellersResponse] = await Promise.all([
                $api('/get-all-customers'),
                $api('/get-branch-list'),
                $api('/get-service-type-item-list'),
                $api('/get-all-users')
            ])

            customers.value = customersResponse.data
            branches.value = branchesResponse.data
            servicePackages.value = packagesResponse.data
            serviceSellers.value = sellersResponse.data

        } catch (err) {
            console.error('Error fetching initial data:', err)
            toast('Error loading initial data', { type: 'error' })
        }
    }

    const fetchEvents = async () => {
        try {
            const res = await $api('/get-booking-list')
            if (!Array.isArray(res.data)) {
                throw new Error('Invalid response format')
            }
            
            const formattedEvents = res.data.map(booking => ({
                id: booking.id,
                title: `${booking.reference_no} - ${booking.customer.name}`,
                start: booking.date,
                end: booking.date,
                note: booking.note,
                status: booking.status,
                customer_name: booking.customer.name,
                customer_phone: booking.customer.phone,
                reference_no: booking.reference_no,
                extendedProps: {
                    status: booking.status,
                    customer_name: booking.customer.name,
                    customer_phone: booking.customer.phone,
                    reference_no: booking.reference_no
                },
                className: `status-${booking.status.toLowerCase()}`
            }))
            
            events.value = formattedEvents
        } catch (err) {
            console.error('Error fetching events:', err)
            toast('Error loading bookings', { type: 'error', message: err.message })
        }
    }

    const validateForm = () => {
        let isValid = true
        errors.value = {
            reference_no: '',
            customer_id: null,
            branch_id: null,
            date: null,
            status: null,
            booking_details: []
        }

        // Reset booking details errors
        bookingForm.value.booking_details.forEach(() => {
            errors.value.booking_details.push({
                item_id: '',
                start_time: '',
                end_time: '',
                quantity: '',
                service_seller_id: ''
            })
        })


        if (!bookingForm.value.reference_no) {
            errors.value.reference_no = t('Reference number is required')
            isValid = false
        }
        if (!bookingForm.value.customer_id) {
            errors.value.customer_id = t('Customer is required')
            isValid = false
        }
        if (!bookingForm.value.branch_id) {
            errors.value.branch_id = t('Branch is required')
            isValid = false
        }
        if (!bookingForm.value.date) {
            errors.value.date = t('Date is required')
            isValid = false
        }
        if (!bookingForm.value.status) {
            errors.value.status = t('Status is required')
            isValid = false
        }

        // Validate booking details
        if (bookingForm.value.booking_details.length === 0) {
            toast(t('Please add at least one booking detail'), {
                type: 'error'
            })
            isValid = false
        } else {
            bookingForm.value.booking_details.forEach((detail, index) => {
                if (!detail.item_id) {
                    errors.value.booking_details[index].item_id = 'Service is required'
                    isValid = false
                }
                if (!detail.start_time) {
                    errors.value.booking_details[index].start_time = t('Start time is required')
                    isValid = false
                }
                if (!detail.end_time) {
                    errors.value.booking_details[index].end_time = t('End time is required')
                    isValid = false
                }
                if (!detail.quantity || detail.quantity < 1) {
                    errors.value.booking_details[index].quantity = t('Valid quantity is required')
                    isValid = false
                }
                if (!detail.service_seller_id) {
                    errors.value.booking_details[index].service_seller_id = t('Service seller is required')
                    isValid = false
                }

                // Validate time range
                if (detail.start_time && detail.end_time) {
                    const start = new Date(`2000/01/01 ${detail.start_time}`)
                    const end = new Date(`2000/01/01 ${detail.end_time}`)
                    
                    if (end <= start) {
                        errors.value.booking_details[index].end_time = t('End time must be after start time')
                        isValid = false
                    }
                }
            })
        }

        return isValid
    }

    const handleSubmit = async (extraOptions) => {
        const { send_sms, send_email, send_whatsapp } = extraOptions

        loadings.value = true
    
        if (!validateForm()) {
            loadings.value = false
            return
        }
        

        try {
            const res = await $api('/bookings', {
                method: 'POST',
                body: {
                    ...bookingForm.value,
                    send_sms,
                    send_email,
                    send_whatsapp
                },
                onResponseError({ response }) {
                    toast(response._data.message, {
                        type: 'error',
                    })
                    loadings.value = false
                    return Promise.reject(response._data)
                }
            })

            const { status, message } = res

            if (status === 'error') {
                toast(message, { type: 'error' })
                loadings.value = false
                return
            }
            
            await fetchEvents()
            toast(message, { type: 'success' })
            showAddModal.value = false
            loadings.value = false
            

        } catch (err) {
            if (err.errors) {
                // Show each validation error as a toast
                for (const [field, messages] of Object.entries(err.errors)) {
                    messages.forEach(msg => {
                        toast(msg, { type: 'error' })
                    })
                }
            } else {
                // Show general error if no field-specific errors
                toast(message, {
                    type: 'error',
                })
            }
            loadings.value = false
            return
        }
    }

    const handleUpdate = async (extraOptions) => {
        const { send_sms, send_email, send_whatsapp } = extraOptions
        
        if (!validateForm()) {
            return
        }

        loadings.value = true

        if (selectedEvent.value) {
            try {
                const res = await $api(`/bookings/${selectedEvent.value.id}`, {
                    method: 'PUT',
                    body: {
                        ...bookingForm.value,
                        send_sms,
                        send_email,
                        send_whatsapp
                    },
                    onResponseError({ response }) {
                        toast(response._data.message, { type: 'error' })
                        return Promise.reject(response._data)
                    }
                })

                const { status, message } = res

                if (status === 'error') {
                    toast(message, { type: 'error' })
                    return
                }
                
                await fetchEvents()
                toast(message, { type: 'success' })
                showEditModal.value = false
                selectedEvent.value = null

            } catch (err) {
                if (err.errors) {
                    // Show each validation error as a toast
                    for (const [field, messages] of Object.entries(err.errors)) {
                        messages.forEach(msg => {
                            toast(msg, { type: 'error' })
                        })
                    }
                } else {
                    toast('Error updating booking', { type: 'error' })
                }
            } finally {
                loadings.value = false
            }
        }
    }

    const addBookingDetail = () => {
        bookingForm.value.booking_details.push({
            item_id: null,
            start_time: null,
            end_time: null,
            quantity: 1,
            service_seller_id: null
        })

        errors.value.booking_details.push({
            item_id: '',
            start_time: '',
            end_time: '',
            quantity: '',
            service_seller_id: ''
        })
    }

    const removeBookingDetail = (index) => {
        bookingForm.value.booking_details.splice(index, 1)
        errors.value.booking_details.splice(index, 1)
    }

    const getStatusBadgeClass = (status) => {
        switch (status) {
            case 'Pending':
                return 'bg-light-warning'
            case 'Accepted':
                return 'bg-light-info'
            case 'Rejected':
                return 'bg-light-error'
            case 'Completed':
                return 'bg-light-success'
            default:
                return 'bg-light-secondary'
        }
    }

    return {
        // State
        events,
        loadings,
        showAddModal,
        showEditModal,
        selectedEvent,
        selectedDate,
        customers,
        branches,
        servicePackages,
        serviceSellers,
        bookingForm,
        errors,
        checkAll,
        selectedCalendars,
        availableCalendars,
        
        // Computed
        isCompletedBooking,
        filteredEvents,
        
        // Methods
        resetForm,
        resetFormForAdd,
        openAddModal,
        closeModal,
        fetchInitialData,
        fetchEvents,
        validateForm,
        handleSubmit,
        handleUpdate,
        addBookingDetail,
        removeBookingDetail,
        getStatusBadgeClass
    }
}
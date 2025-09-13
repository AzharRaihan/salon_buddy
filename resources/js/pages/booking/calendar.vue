<script setup>
import { useRouter } from 'vue-router';
import { toast } from 'vue3-toastify';
import { ref, onMounted, watch } from 'vue';
import FullCalendar from '@fullcalendar/vue3';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import listPlugin from '@fullcalendar/list';
import { useI18n } from 'vue-i18n';
import { useBookingCalendar } from '@/composables/useBookingCalendar';
import BookingModal from '@/components/booking/BookingModal.vue';

const { t } = useI18n()

const router = useRouter()
const calendarRef = ref(null)
const branch_info = useCookie("branch_info").value || 0;

// Use the booking calendar composable
const {
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
    isCompletedBooking,
    filteredEvents,
    resetForm,
    openAddModal,
    closeModal,
    fetchInitialData,
    fetchEvents,
    validateForm,
    handleSubmit,
    handleUpdate,
    addBookingDetail,
    removeBookingDetail,
    getStatusBadgeClass,
    resetFormForAdd
} = useBookingCalendar()

const calendarOptions = ref({
    plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin, listPlugin],
    initialView: 'dayGridMonth',
    headerToolbar: {
        left: 'prev,next today',
        center: 'title',
        right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
    },
    editable: true,
    selectable: true,
    selectMirror: true,
    dayMaxEvents: true,
    weekends: true,
    events: [], // Initialize empty, will be updated when events change
    select: handleDateSelect,
    eventClick: handleEventClick,
    eventDrop: handleEventDrop,
    eventsSet: handleEvents,
    eventContent: function(arg) {
        const status = arg.event.extendedProps.status
        const customerName = arg.event.extendedProps.customer_name
        const customerPhone = arg.event.extendedProps.customer_phone
        const referenceNo = arg.event.extendedProps.reference_no
        
        return {
            html: `
                <div class="fc-event-content">
                    <div class="d-flex align-start flex-column">
                        <div class="d-flex flex-column">
                          <div>
                            <span class="text-sm font-weight-medium text-wrap-auto">${referenceNo}</span>
                            <span class="text-sm font-weight-medium text-wrap-auto">(${status})</span>
                          </div>
                          <div>
                            <span class="text-xs text-wrap-auto">${customerName}</span>
                            <span class="text-xs text-wrap-auto">(${customerPhone})</span>
                          </div>
                        </div>
                    </div>
                </div>`
        }
    }
})

// Watch for filtered events changes
watch(filteredEvents, (newEvents) => {
    calendarOptions.value = {
        ...calendarOptions.value,
        events: newEvents
    }
}, { immediate: true })


async function handleDateSelect(selectInfo) {

  const selected = new Date(selectInfo.startStr)
  const today = new Date()

  // Remove time part for accurate date-only comparison
  selected.setHours(0, 0, 0, 0)
  today.setHours(0, 0, 0, 0)

  // Check if selected date is today or in the future
  if (selected >= today) {
    selectedDate.value = selectInfo.startStr
    resetFormForAdd(selectInfo.startStr)

    try {
      // Generate reference number
      const refResponse = await $api('/generate-booking-reference-no')
      if (refResponse.data) {
        bookingForm.value.reference_no = refResponse.data
      }
    } catch (err) {
      console.error('Error generating reference number:', err)
    }

    // Reset form with the selected date
    showAddModal.value = true
  } else {
    toast('Cannot create booking for a past date.', { type: 'error' })
  }
}

async function handleEventClick(clickInfo) {
    try {
        // Fetch complete booking data
        const bookingResponse = await $api(`/bookings/${clickInfo.event.id}`)
        const bookingData = bookingResponse.data.booking

        selectedEvent.value = bookingData
        
        // Populate edit form
        bookingForm.value = {
            reference_no: bookingData.reference_no,
            customer_id: bookingData.customer.id,
            branch_id: bookingData.branch.id,
            date: bookingData.date,
            note: bookingData.note || '',
            status: bookingData.status,
            booking_details: bookingData.booking_details.map(detail => ({
                item_id: detail.items?.id,
                start_time: detail.start_time,
                end_time: detail.end_time,
                quantity: detail.quantity,
                service_seller_id: detail.service_seller?.id
            }))
        }

        showEditModal.value = true
    } catch (err) {
        console.error('Error fetching booking details:', err)
        toast('Error loading booking details', { type: 'error' })
    }
}

async function handleEventDrop(info) {
  const bookingId = info.event.id
  const newDate = info.event.startStr // New date after drag
  const today = new Date()
  const todayFormatted = today.toISOString().split('T')[0];
  if(newDate < todayFormatted) {
    toast('Cannot update booking date to a past date.', { type: 'error' })
    info.revert()
    return
  }

  try {
    // Send update request to backend
    const response = await $api(`/update-booking-date/${bookingId}`, {
      method: 'PUT',
      body: {
        booking_id: bookingId,
        date: newDate,
      },
      headers: {
        'Accept': 'application/json',
        'X-HTTP-Method-Override': 'PUT'
      }
    })

    if (response.success) {
      toast('Booking date updated successfully!', { type: 'success' })
    } else {
      toast('Failed to update booking date', { type: 'error' })
      info.revert() // Revert if update failed
    }
  } catch (err) {
    console.error('Error updating booking date:', err)
    toast('Error updating booking date', { type: 'error' })
    info.revert()
  }
}

function handleEvents(events) {
  events.value = events
}

function createEventId() {
    return String(Date.now())
}

onMounted(() => {
  fetchInitialData()
  fetchEvents()
})
</script>

<template>
    <VCard>
        <VCardText>
            <VRow>
                <VCol cols="2">
                    <div class="pb-8 pt-4">
                        <VBtn class="w-100" color="primary" @click="openAddModal">
                          <VIcon icon="tabler-plus" class="me-2" />
                          {{ $t('Add Booking') }}
                        </VBtn>
                    </div>
                    <VDivider />
                    <h4 class="text-sm text-uppercase mb-3 pt-10">FILTER</h4>
                    <div class="d-flex flex-column calendars-checkbox">
                        <VCheckbox
                          v-model="checkAll"
                          label="View all"
                        />
                        <VCheckbox
                          v-for="calendar in availableCalendars"
                          :key="calendar.name"
                          v-model="selectedCalendars"
                          :value="calendar.name"
                          :color="calendar.color"
                          :label="calendar.name"
                        />
                    </div>
                </VCol>
                <VCol cols="10" class="border-start">
                    <FullCalendar 
                        ref="calendarRef"
                        class="demo-app-calendar"
                        :options="calendarOptions"
                    />
                </VCol>
            </VRow>
        </VCardText>
    </VCard>

    <!-- Add Booking Modal -->
    <BookingModal
        v-model="showAddModal"
        :title="$t('Add Booking')"
        :form="bookingForm"
        :errors="errors"
        :customers="customers"
        :branches="branches"
        :service-packages="servicePackages"
        :service-sellers="serviceSellers"
        :loading="loadings"
        :submit-button-text="'Submit'"
        @submit="handleSubmit"
        @add-detail="addBookingDetail"
        @remove-detail="removeBookingDetail"
    />

    <!-- Edit Booking Modal -->
    <BookingModal
        v-model="showEditModal"
        :title="$t('Edit Booking')"
        :form="bookingForm"
        :errors="errors"
        :customers="customers"
        :branches="branches"
        :service-packages="servicePackages"
        :service-sellers="serviceSellers"
        :is-completed="isCompletedBooking"
        :loading="loadings"
        :show-submit-button="!isCompletedBooking"
        :submit-button-text="'Update'"
        @submit="handleUpdate"
        @add-detail="addBookingDetail"
        @remove-detail="removeBookingDetail"
    />
</template>

<style lang="scss">
@use "@core-scss/template/libs/full-calendar";

.calendars-checkbox {
  .v-label {
    color: rgba(var(--v-theme-on-surface), var(--v-high-emphasis-opacity));
    opacity: var(--v-high-emphasis-opacity);
  }
}

.calendar-add-event-drawer {
  &.v-navigation-drawer {
    border-end-start-radius: 0.375rem;
    border-start-start-radius: 0.375rem;
  }
}

.calendar-date-picker {
  display: none;

  +.flatpickr-input {
    +.flatpickr-calendar.inline {
      border: none;
      box-shadow: none;

      .flatpickr-months {
        border-block-end: none;
      }
    }
  }
}
.v-layout {
  overflow: visible !important;

  .v-card {
    overflow: visible;
  }
}

// Custom styles for calendar events with status badges
.fc-event {
  &.status-pending {
    background-color: rgba(var(--v-theme-warning), 0.1) !important;
    border-color: rgb(var(--v-theme-warning)) !important;
    color: rgb(var(--v-theme-warning)) !important;
  }
  
  &.status-accepted {
    background-color: rgba(var(--v-theme-info), 0.1) !important;
    border-color: rgb(var(--v-theme-info)) !important;
    color: rgb(var(--v-theme-info)) !important;
  }
  
  &.status-rejected {
    background-color: rgba(var(--v-theme-error), 0.1) !important;
    border-color: rgb(var(--v-theme-error)) !important;
    color: rgb(var(--v-theme-error)) !important;
  }
  
  &.status-completed {
    background-color: rgba(var(--v-theme-success), 0.1) !important;
    border-color: rgb(var(--v-theme-success)) !important;
    color: rgb(var(--v-theme-success)) !important;
  }
  
  .fc-event-title {
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 500;
    
    .status-badge {
      padding: 2px 6px;
      border-radius: 4px;
      font-size: 10px;
      font-weight: 500;
      text-transform: uppercase;
    }
  }
}
</style>

<style scoped>
.border-start {
    border-left: 1px solid #e0e0e0;
}
.fc-event-content .common-badge {
    padding: 2px 5px !important;

}

.v-card-title {
    padding: 25px 25px 16px 25px !important;
    border-bottom: 1px solid #dbdbdb;
    padding-bottom: 10px;
}

</style>

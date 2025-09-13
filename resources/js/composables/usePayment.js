import { ref } from 'vue'
import { useApi } from './useApi'
import { $api } from '@/utils/api'
import { toast } from 'vue3-toastify'

export const usePayment = () => {
  const { execute: apiCall } = useApi()
  const isProcessingPayment = ref(false)
  const paymentError = ref(null)
  const bookingReference = ref(null)
  const successBookingId = ref(null)

  // Create booking function
  const createBooking = async (orderData, paymentMethodId, paymentStatus = 'pending') => {
    try {
      const bookingData = {
        customer_name: orderData.customer_name,
        customer_email: orderData.customer_email,
        customer_phone: orderData.customer_phone,
        customer_address: orderData.customer_address,
        branch_id: orderData.branch_id,
        appointment_date: orderData.appointment_date,
        services: orderData.services,
        subtotal: orderData.subtotal,
        tax_amount: orderData.tax_amount,
        total_amount: orderData.total_amount,
        payment_method: paymentMethodId, // Use payment method ID instead of name
        payment_status: paymentStatus
      }

      console.log('Creating booking with data:', bookingData)

      const response = await $api('/create-booking', {
        method: 'POST',
        body: bookingData,
        onResponseError({ response }) {
          toast(response._data.message, {
            type: 'error',
          })
          return Promise.reject(response._data)
        },
      })

      if (response.data) {
        bookingReference.value = response.data
        return response.data
      }
      
      throw new Error('Invalid response from server')
    } catch (error) {
      console.error('Booking creation error:', error)
      throw error
    }
  }

  // Razorpay Integration - No page reload
  const processRazorpayPayment = async (amount, orderData, paymentMethodId) => {
    try {
      // Create booking first
      const bookingId = await createBooking(orderData, paymentMethodId, 'pending')

      // Create Razorpay order
      const orderResponse = await $api('/create-razorpay-order', {
        method: 'POST',
        body: {
          amount: amount * 100, // Convert to paisa
          booking_id: bookingId,
          ...orderData
        }
      })

      const options = {
        key: import.meta.env.VITE_RAZORPAY_KEY,
        amount: amount * 100,
        currency: 'INR',
        name: 'Salon Buddy',
        description: 'Booking Payment',
        order_id: orderResponse.data?.order_id,
        handler: async function (response) {
          // Payment successful - verify and set success booking
          const verifiedBookingId = await verifyPayment('razorpay', {
            razorpay_payment_id: response.razorpay_payment_id,
            razorpay_order_id: response.razorpay_order_id,
            razorpay_signature: response.razorpay_signature,
            booking_id: bookingId
          })
          if (verifiedBookingId) {
            successBookingId.value = verifiedBookingId
          }
        },
        prefill: {
          name: orderData.customer_name,
          email: orderData.customer_email,
          contact: orderData.customer_phone
        },
        theme: {
          color: '#3399cc'
        },
        modal: {
          ondismiss: function() {
            isProcessingPayment.value = false
            paymentError.value = 'Payment cancelled by user'
          }
        }
      }

      const rzp = new window.Razorpay(options)
      rzp.open()

      rzp.on('payment.failed', function (response) {
        paymentError.value = 'Payment failed. Please try again.'
        isProcessingPayment.value = false
      })

    } catch (error) {
      paymentError.value = 'Failed to initialize payment. Please try again.'
      isProcessingPayment.value = false
      throw error
    }
  }

  // PayPal Integration - Use popup instead of redirect
  const processPayPalPayment = async (amount, orderData, paymentMethodId) => {
    try {
      // Create booking first
      const bookingId = await createBooking(orderData, paymentMethodId, 'pending')

      // Create PayPal order
      const orderResponse = await $api('/create-paypal-order', {
        method: 'POST',
        body: {
          amount: amount,
          currency: 'USD',
          booking_id: bookingId,
          ...orderData
        }
      })

      // Open PayPal in a popup window instead of redirect
      const paypalWindow = window.open(
        orderResponse.data?.approval_url,
        'PayPal',
        'width=600,height=700,scrollbars=yes,resizable=yes'
      )

      // Monitor the popup window
      const checkClosed = setInterval(() => {
        if (paypalWindow.closed) {
          clearInterval(checkClosed)
          // Check payment status
          verifyPayment('paypal', { booking_id: bookingId })
        }
      }, 1000)

    } catch (error) {
      paymentError.value = 'Failed to initialize PayPal payment. Please try again.'
      isProcessingPayment.value = false
      throw error
    }
  }

  // Stripe Integration - Use Stripe Elements instead of redirect
  const processStripePayment = async (amount, orderData, paymentMethodId) => {
    try {
      // Create booking first
      const bookingId = await createBooking(orderData, paymentMethodId, 'pending')

      // Create Stripe payment intent
      const intentResponse = await $api('/create-stripe-session', {
        method: 'POST',
        body: {
          amount: amount * 100, // Convert to cents
          currency: 'usd',
          booking_id: bookingId,
          ...orderData
        }
      })

      const stripe = window.Stripe(import.meta.env.VITE_STRIPE_PUBLIC_KEY)
      
      // Use Stripe's confirm payment method instead of redirect
      const { error, paymentIntent } = await stripe.confirmCardPayment(
        intentResponse.data?.client_secret, {
          payment_method: {
            card: {
              // This would need to be implemented with Stripe Elements
              // For now, redirect to checkout as fallback
            }
          }
        }
      )

      if (error) {
        paymentError.value = error.message
        isProcessingPayment.value = false
      } else {
        // Payment successful
        verifyPayment('stripe', { 
          payment_intent_id: paymentIntent.id,
          booking_id: bookingId 
        })
      }

    } catch (error) {
      paymentError.value = 'Failed to initialize Stripe payment. Please try again.'
      isProcessingPayment.value = false
      throw error
    }
  }

  // Paytm Integration - Use popup instead of redirect
  const processPaytmPayment = async (amount, orderData, paymentMethodId) => {
    try {
      // Create booking first
      const bookingId = await createBooking(orderData, paymentMethodId, 'pending')

      // Create Paytm order
      const orderResponse = await $api('/create-paytm-order', {
        method: 'POST',
        body: {
          amount: amount,
          booking_id: bookingId,
          ...orderData
        }
      })

      // Open Paytm in a popup window
      const paytmWindow = window.open(
        orderResponse.data?.payment_url,
        'Paytm',
        'width=600,height=700,scrollbars=yes,resizable=yes'
      )

      // Monitor the popup window
      const checkClosed = setInterval(() => {
        if (paytmWindow.closed) {
          clearInterval(checkClosed)
          // Check payment status
          verifyPayment('paytm', { booking_id: bookingId })
        }
      }, 1000)

    } catch (error) {
      paymentError.value = 'Failed to initialize Paytm payment. Please try again.'
      isProcessingPayment.value = false
      throw error
    }
  }
  // Paytm Integration - Use popup instead of redirect
  const processPaystackPayment = async (amount, orderData, paymentMethodId) => {
    try {
      // Create booking first
      const bookingId = await createBooking(orderData, paymentMethodId, 'pending')

      // Create Paytm order
      const orderResponse = await $api('/create-paystack-order', {
        method: 'POST',
        body: {
          amount: amount,
          booking_id: bookingId,
          ...orderData
        }
      })

      // Open Paytm in a popup window
      const paytmWindow = window.open(
        orderResponse.data?.payment_url,
        'Paystack',
        'width=600,height=700,scrollbars=yes,resizable=yes'
      )

      // Monitor the popup window
      const checkClosed = setInterval(() => {
        if (paytmWindow.closed) {
          clearInterval(checkClosed)
          // Check payment status
          verifyPayment('paystack', { booking_id: bookingId })
        }
      }, 1000)

    } catch (error) {
      paymentError.value = 'Failed to initialize Paystack payment. Please try again.'
      isProcessingPayment.value = false
      throw error
    }
  }

  // Cash Payment - Direct booking creation
  const processCashPayment = async (orderData, paymentMethodId) => {
    try {
      const bookingId = await createBooking(orderData, paymentMethodId, 'pending')
      
      // For cash payments, booking is created successfully
      // Return booking ID instead of redirecting
      isProcessingPayment.value = false
      return bookingId
      
    } catch (error) {
      paymentError.value = 'Failed to create booking. Please try again.'
      isProcessingPayment.value = false
      throw error
    }
  }

  // Navigate to success page
  const navigateToSuccess = (bookingId) => {
    isProcessingPayment.value = false
    // Instead of redirecting, we'll return the booking ID
    // The component will handle moving to step 4
    return bookingId
  }

  // Verify payment after successful payment
  const verifyPayment = async (paymentMethod, paymentData) => {
    try {
      const response = await $api('/verify-payment', {
        method: 'POST',
        body: {
          payment_method: paymentMethod,
          ...paymentData
        }
      })

      if (response.data?.success) {
        // Return booking ID instead of redirecting
        const bookingId = response.data?.booking_id || paymentData.booking_id
        isProcessingPayment.value = false
        return bookingId
      } else {
        paymentError.value = 'Payment verification failed. Please contact support.'
        isProcessingPayment.value = false
      }

    } catch (error) {
      paymentError.value = 'Payment verification failed. Please contact support.'
      isProcessingPayment.value = false
    }
  }

  // Main payment processor
  const processPayment = async (paymentMethodId, amount, orderData) => {
    isProcessingPayment.value = true
    paymentError.value = null
    successBookingId.value = null

    try {
      // Find the payment method to determine the type
      const paymentMethod = orderData.paymentMethods?.find(pm => pm.id === paymentMethodId)
      
      if (!paymentMethod) {
        throw new Error('Invalid payment method selected')
      }

      const methodType = paymentMethod.account_type?.toLowerCase() || paymentMethod.name?.toLowerCase()

      let bookingId
      switch (methodType) {
        case 'Razorpay':
          await processRazorpayPayment(amount, orderData, paymentMethodId)
          // For Razorpay, success is handled in the handler callback
          break
        case 'Paypal':
          await processPayPalPayment(amount, orderData, paymentMethodId)
          // For PayPal, success is handled in popup callback
          break
        case 'Stripe':
          await processStripePayment(amount, orderData, paymentMethodId)
          // For Stripe, success is handled in confirm callback
          break
        case 'Paytm':
          await processPaytmPayment(amount, orderData, paymentMethodId)
          // For Paytm, success is handled in popup callback
        case 'Paystack':
          await processPaystackPayment(amount, orderData, paymentMethodId)
          // For Paystack, success is handled in popup callback
          break
        case 'Cash':
        default:
          bookingId = await processCashPayment(orderData, paymentMethodId)
          if (bookingId) {
            successBookingId.value = bookingId
          }
          break
      }
      
      return bookingId
    } catch (error) {
      console.error('Payment processing error:', error)
      if (!paymentError.value) {
        paymentError.value = 'Payment processing failed. Please try again.'
      }
      isProcessingPayment.value = false
      throw error
    }
  }

  return {
    isProcessingPayment,
    paymentError,
    bookingReference,
    successBookingId,
    processPayment,
    verifyPayment,
    createBooking
  }
}
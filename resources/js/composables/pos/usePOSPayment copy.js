import { ref } from 'vue'
import { $api } from '@/utils/api'
import { toast } from 'vue3-toastify'

export const usePOSPayment = () => {
    const isProcessingPayment = ref(false)
    const paymentError = ref(null)
    const paymentSuccess = ref(false)
    const userData = useCookie("userData").value;

    // Process payment for POS orders
    const processPOSPayment = async (paymentMethodId, amount, orderData) => {
        isProcessingPayment.value = true
        paymentError.value = null
        paymentSuccess.value = false
        let paymentMethodResponse = null

        try {

            if(userData){
                // Get payment method details
                paymentMethodResponse = await $api('/get-all-payment-methods-pos', {
                    method: 'GET'
                })
            } else  {
                // Get payment method details
                paymentMethodResponse = await $api('/get-all-payment-getway-frontend', {
                    method: 'GET'
                })
            }

            if (!paymentMethodResponse.success) {
                throw new Error('Failed to fetch payment methods')
            }

            const paymentMethod = paymentMethodResponse.data.find(pm => pm.id == paymentMethodId)
            
            if (!paymentMethod) {
                throw new Error('Invalid payment method selected')
            }

            const methodType = paymentMethod.account_type?.toLowerCase() || paymentMethod.account_type?.toLowerCase()

            let paymentResult
            switch (methodType) {
                case 'razorpay':
                    paymentResult = await processRazorpayPayment(amount, orderData, paymentMethodId)
                    break
                case 'stripe':
                    paymentResult = await processStripePayment(amount, orderData, paymentMethodId)
                    break
                case 'paypal':
                    paymentResult = await processPayPalPayment(amount, orderData, paymentMethodId)
                    break
                case 'paytm':
                    paymentResult = await processPaytmPayment(amount, orderData, paymentMethodId)
                    break
                case 'paystack':
                    paymentResult = await processPaystackPayment(amount, orderData, paymentMethodId)
                    break
                case 'cash':
                default:
                    paymentResult = await processCashPayment(amount, orderData, paymentMethodId)
                    break
            }

            if (paymentResult.success) {
                paymentSuccess.value = true
                toast('Payment processed successfully', { type: 'success' })
                return paymentResult
            } else {
                throw new Error(paymentResult.message || 'Payment processing failed')
            }

        } catch (error) {
            console.error('Payment processing error:', error)
            paymentError.value = error.message || 'Payment processing failed. Please try again.'
            toast(paymentError.value, { type: 'error' })
            throw error
        } finally {
            isProcessingPayment.value = false
        }
    }

    // Razorpay Integration - Already working with popup
    const processRazorpayPayment = async (amount, orderData, paymentMethodId) => {
        try {
            // Create Razorpay order
            const orderResponse = await $api('/create-razorpay-order', {
                method: 'POST',
                body: {
                    amount: amount * 100,
                    currency: 'INR',
                    ...orderData
                }
            })

            if (!orderResponse.success) {
                throw new Error(orderResponse.message || 'Failed to create Razorpay order')
            }

            return await new Promise((resolve, reject) => {
                const options = {
                    key: orderResponse.data?.rk,
                    amount: amount * 100,
                    currency: 'INR',
                    name: 'Salon Buddy',
                    description: 'POS Payment',
                    order_id: orderResponse.data?.order_id,
                    handler: async function (response) {
                        // Payment successful - verify payment
                        const verifiedResult = await verifyPayment('razorpay', {
                            razorpay_payment_id: response.razorpay_payment_id,
                            razorpay_order_id: response.razorpay_order_id,
                            razorpay_signature: response.razorpay_signature
                        })
                        if (verifiedResult.success) {
                            resolve({ success: true, message: 'Razorpay payment successful', data: verifiedResult.data, transaction_id: response.razorpay_payment_id });
                        } else {
                            reject({ success: false, message: 'Payment verification failed', error: verifiedResult.error });
                        }
                    },
                    prefill: {
                        name: 'POS Customer',
                        email: 'pos@salonbuddy.com',
                        contact: '1234567890'
                    },
                    theme: {
                        color: '#3399cc'
                    },
                    modal: {
                        ondismiss: function() {
                            isProcessingPayment.value = false
                            paymentError.value = 'Payment cancelled by user'
                            toast(paymentError.value, { type: 'error' })
                            reject({ success: false, message: 'Payment cancelled by user' });
                        }
                    }
                };
    
                const rzp = new window.Razorpay(options);
                rzp.open();
                rzp.on('payment.failed', function (response) {
                    paymentError.value = 'Payment failed. Please try again.'
                    isProcessingPayment.value = false
                    reject({ success: false, message: 'Payment failed', error: response });
                });
            });

        } catch (error) {
            paymentError.value = 'Failed to initialize payment. Please try again.'
            isProcessingPayment.value = false
            return { success: false, message: error.message }
        }
    }

    // Stripe Integration - Using embedded form instead of redirect
    const processStripePayment = async (amount, orderData, paymentMethodId) => {
        try {
            // Create Stripe payment intent
            const intentResponse = await $api('/create-stripe-payment-intent', {
                method: 'POST',
                body: {
                    amount: amount * 100, // Convert to cents
                    currency: 'usd',
                    ...orderData
                }
            })

            if (!intentResponse.success) {
                throw new Error(intentResponse.message || 'Failed to create Stripe payment intent')
            }

            const stripe = window.Stripe(intentResponse.data?.window_open_id)
            
            // Use embedded payment form instead of redirect
            return await new Promise((resolve, reject) => {
                const elements = stripe.elements();
                const card = elements.create('card', {
                    style: {
                        base: {
                            fontSize: '16px',
                            color: '#424770',
                            '::placeholder': {
                                color: '#aab7c4',
                            },
                        },
                        invalid: {
                            color: '#9e2146',
                        },
                    },
                });

                // Create a container for the card element
                const cardContainer = document.createElement('div');
                cardContainer.id = 'stripe-card-container';
                cardContainer.style.cssText = `
                    position: fixed;
                    top: 50%;
                    left: 50%;
                    transform: translate(-50%, -50%);
                    background: white;
                    padding: 20px;
                    border-radius: 8px;
                    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                    z-index: 10000;
                    min-width: 400px;
                `;
                
                const overlay = document.createElement('div');
                overlay.style.cssText = `
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(0, 0, 0, 0.5);
                    z-index: 9999;
                `;

                const title = document.createElement('h3');
                title.textContent = 'Enter Payment Details';
                title.style.marginBottom = '20px';
                title.style.fontSize = '20px';
                title.style.textAlign = 'center';

                const cardDiv = document.createElement('div');
                cardDiv.style.marginBottom = '20px';

                const buttonContainer = document.createElement('div');
                buttonContainer.style.display = 'flex';
                buttonContainer.style.gap = '10px';
                buttonContainer.style.justifyContent = 'center';

                const payButton = document.createElement('button');
                payButton.textContent = 'Pay $' + amount;
                payButton.style.cssText = `
                    padding: 10px 20px;
                    background: #6772e5;
                    color: white;
                    border: none;
                    border-radius: 4px;
                    cursor: pointer;
                `;

                const cancelButton = document.createElement('button');
                cancelButton.textContent = 'Cancel';
                cancelButton.style.cssText = `
                    padding: 10px 20px;
                    background: #6c757d;
                    color: white;
                    border: none;
                    border-radius: 4px;
                    cursor: pointer;
                `;

                cardContainer.appendChild(title);
                cardContainer.appendChild(cardDiv);
                buttonContainer.appendChild(payButton);
                buttonContainer.appendChild(cancelButton);
                cardContainer.appendChild(buttonContainer);

                document.body.appendChild(overlay);
                document.body.appendChild(cardContainer);
                card.mount(cardDiv);

                payButton.addEventListener('click', async () => {
                    payButton.disabled = true;
                    payButton.textContent = 'Processing...';

                    const { error, paymentIntent } = await stripe.confirmCardPayment(
                        intentResponse.data?.client_secret,
                        {
                            payment_method: {
                                card: card,
                                billing_details: {
                                    name: 'POS Customer',
                                },
                            }
                        }
                    );

                    if (error) {
                        reject({ success: false, message: error.message });
                    } else if (paymentIntent.status === 'succeeded') {
                        const verifiedResult = await verifyPayment('stripe', {
                            payment_intent_id: paymentIntent.id,
                            session_id: intentResponse.data?.session_id
                        });
                        
                        if (verifiedResult.success) {
                            resolve({ success: true, message: 'Stripe payment successful', data: verifiedResult.data, transaction_id: paymentIntent.id });
                        } else {
                            reject({ success: false, message: 'Payment verification failed', error: verifiedResult.error });
                        }
                    }

                    // Clean up
                    document.body.removeChild(overlay);
                    document.body.removeChild(cardContainer);
                });

                cancelButton.addEventListener('click', () => {
                    document.body.removeChild(overlay);
                    document.body.removeChild(cardContainer);
                    reject({ success: false, message: 'Payment cancelled by user' });
                });
            });

        } catch (error) {
            let message = 'Something went wrong';
            if (error.data?.message) {
                message = error.data.message; // backend error (clean message)
            } else if (error.message) {
                message = error.message; // fallback
            }
            return { success: false, message };
        }
    }

    // PayPal Integration - Using popup window
    const processPayPalPayment = async (amount, orderData, paymentMethodId) => {
        try {
            // Create PayPal order
            const orderResponse = await $api('/create-paypal-order', {
                method: 'POST',
                body: {
                    amount: amount,
                    currency: 'USD',
                    ...orderData
                }
            })

            if (!orderResponse.success) {
                throw new Error(orderResponse.message || 'Failed to create PayPal order')
            }

            // Open PayPal in popup window
            return await new Promise((resolve, reject) => {
                const popup = window.open(
                    orderResponse.data?.payment_url,
                    'paypal_payment',
                    'width=500,height=600,scrollbars=yes,resizable=yes'
                );

                const checkClosed = setInterval(() => {
                    if (popup.closed) {
                        clearInterval(checkClosed);
                        // Check payment status
                        checkPayPalPaymentStatus(orderData.order_id || 'temp_order_id')
                            .then(result => {
                                if (result.success) {
                                    resolve({ success: true, message: 'PayPal payment successful', data: result.data, transaction_id: orderData.order_id });
                                } else {
                                    reject({ success: false, message: 'Payment failed or cancelled' });
                                }
                            })
                            .catch(error => {
                                reject({ success: false, message: error.message });
                            });
                    }
                }, 1000);
            });

        } catch (error) {
            let message = 'Something went wrong';
            if (error.data?.message) {
                message = error.data.message; // backend error (clean message)
            } else if (error.message) {
                message = error.message; // fallback
            }
            return { success: false, message };
        }
    }

    // Check PayPal payment status
    const checkPayPalPaymentStatus = async (orderId) => {
        try {
            const response = await $api('/check-paypal-payment-status', {
                method: 'POST',
                body: { order_id: orderId }
            });
            return response;
        } catch (error) {
            return { success: false, message: error.message };
        }
    }

    // Paytm Integration - Using embedded form
    const processPaytmPayment = async (amount, orderData, paymentMethodId) => {
        try {
            // Create Paytm order
            const orderResponse = await $api('/create-paytm-order', {
                method: 'POST',
                body: {
                    amount: amount,
                    ...orderData
                }
            })

            if (!orderResponse.success) {
                throw new Error(orderResponse.message || 'Failed to create Paytm order')
            }

            // Create embedded form for Paytm
            return await new Promise((resolve, reject) => {
                const formContainer = document.createElement('div');
                formContainer.id = 'paytm-form-container';
                formContainer.style.cssText = `
                    position: fixed;
                    top: 50%;
                    left: 50%;
                    transform: translate(-50%, -50%);
                    background: white;
                    padding: 20px;
                    border-radius: 8px;
                    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                    z-index: 10000;
                    min-width: 400px;
                `;

                const overlay = document.createElement('div');
                overlay.style.cssText = `
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(0, 0, 0, 0.5);
                    z-index: 9999;
                `;

                const title = document.createElement('h3');
                title.textContent = 'Paytm Payment';
                title.style.marginBottom = '20px';
                title.style.textAlign = 'center';

                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'https://securegw-stage.paytm.in/order/process'; // Use production URL for live
                form.target = 'paytm_iframe';

                // Add form fields
                const paymentData = orderResponse.data?.payment_data;
                for (const [key, value] of Object.entries(paymentData)) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = key;
                    input.value = value;
                    form.appendChild(input);
                }

                const iframe = document.createElement('iframe');
                iframe.name = 'paytm_iframe';
                iframe.style.cssText = `
                    width: 100%;
                    height: 400px;
                    border: 1px solid #ddd;
                    border-radius: 4px;
                `;

                const closeButton = document.createElement('button');
                closeButton.textContent = 'Close';
                closeButton.style.cssText = `
                    padding: 10px 20px;
                    background: #6c757d;
                    color: white;
                    border: none;
                    border-radius: 4px;
                    cursor: pointer;
                    margin-top: 10px;
                `;

                formContainer.appendChild(title);
                formContainer.appendChild(form);
                formContainer.appendChild(iframe);
                formContainer.appendChild(closeButton);

                document.body.appendChild(overlay);
                document.body.appendChild(formContainer);

                // Submit form
                form.submit();

                // Listen for iframe load to check payment status
                iframe.onload = async () => {
                    try {
                        // Check payment status after a delay
                        setTimeout(async () => {
                            const statusResult = await checkPaytmPaymentStatus(orderResponse.data?.order_id);
                            if (statusResult.success) {
                                document.body.removeChild(overlay);
                                document.body.removeChild(formContainer);
                                resolve({ success: true, message: 'Paytm payment successful', data: statusResult.data });
                            }
                        }, 3000);
                    } catch (error) {
                        reject({ success: false, message: error.message });
                    }
                };

                closeButton.addEventListener('click', () => {
                    document.body.removeChild(overlay);
                    document.body.removeChild(formContainer);
                    reject({ success: false, message: 'Payment cancelled by user' });
                });
            });

        } catch (error) {
            let message = 'Something went wrong';
            if (error.data?.message) {
                message = error.data.message; // backend error (clean message)
            } else if (error.message) {
                message = error.message; // fallback
            }
            return { success: false, message };
        }
    }

    // Check Paytm payment status
    const checkPaytmPaymentStatus = async (orderId) => {
        try {
            const response = await $api('/check-paytm-payment-status', {
                method: 'POST',
                body: { order_id: orderId }
            });
            return response;
        } catch (error) {
            let message = 'Something went wrong';
            if (error.data?.message) {
                message = error.data.message; // backend error (clean message)
            } else if (error.message) {
                message = error.message; // fallback
            }
            return { success: false, message };
        }
    }

    // Paystack Integration - Using embedded form
    const processPaystackPayment = async (amount, orderData, paymentMethodId) => {
        try {
            // Create Paystack order
            const orderResponse = await $api('/create-paystack-order', {
                method: 'POST',
                body: {
                    amount: amount,
                    email: orderData.customer_email || 'customer@example.com',
                    ...orderData
                }
            })

            if (!orderResponse.success) {
                throw new Error(orderResponse.message || 'Failed to create Paystack order')
            }

            // Use Paystack redirect method (more reliable)
            if (orderResponse.data?.authorization_url) {
                // Store the order reference for verification when user returns
                localStorage.setItem('paystack_order_reference', orderResponse.data.reference);
                localStorage.setItem('paystack_order_data', JSON.stringify(orderData));
                localStorage.setItem('paystack_payment_method_id', paymentMethodId);
                localStorage.setItem('paystack_amount', amount);
                
                // Redirect to Paystack payment page
                window.location.href = orderResponse.data.authorization_url;
                
                // Return a promise that will be rejected to prevent order saving
                // The order will only be saved after successful payment verification
                return new Promise((resolve, reject) => {
                    reject({ 
                        success: false, 
                        message: 'Redirecting to Paystack for payment...', 
                        redirect_url: orderResponse.data.authorization_url,
                        reference: orderResponse.data.reference,
                        is_redirect: true
                    });
                });
            } else {
                // Fallback to inline method if authorization_url is not available
                return await new Promise((resolve, reject) => {
                    // Check if PaystackPop is available
                    if (typeof window.PaystackPop === 'undefined') {
                        reject({ success: false, message: 'Paystack library not loaded. Please include Paystack script.' });
                        return;
                    }

                    const handler = window.PaystackPop.setup({
                        key: orderResponse.data?.p_k || orderResponse.data?.public_key,
                        email: orderData.customer_email || 'customer@example.com',
                        amount: amount * 100, // Convert to kobo
                        currency: 'NGN',
                        ref: orderResponse.data?.reference,
                        callback: async function(response) {
                            try {
                                // Verify payment
                                const verifiedResult = await verifyPayment('paystack', {
                                    reference: response.reference
                                });
                                
                                if (verifiedResult.success) {
                                    resolve({ success: true, message: 'Paystack payment successful', data: verifiedResult.data, transaction_id: response.reference });
                                } else {
                                    reject({ success: false, message: 'Payment verification failed', error: verifiedResult.error });
                                }
                            } catch (error) {
                                reject({ success: false, message: 'Payment verification error: ' + error.message });
                            }
                        },
                        onClose: function() {
                            reject({ success: false, message: 'Payment cancelled by user' });
                        }
                    });
                    
                    handler.openIframe();
                });
            }

        } catch (error) {
            let message = 'Something went wrong';
            if (error.data?.message) {
                message = error.data.message; // backend error (clean message)
            } else if (error.message) {
                message = error.message; // fallback
            }
            return { success: false, message };
        }
    }

    // Cash Payment
    const processCashPayment = async (amount, orderData, paymentMethodId) => {
        try {
            // For cash payments, we just return success
            // The actual order saving is handled by the POS controller
            return { 
                success: true, 
                message: 'Cash payment received',
                payment_status: 'completed'
            }
        } catch (error) {
            let message = 'Something went wrong';
            if (error.data?.message) {
                message = error.data.message; // backend error (clean message)
            } else if (error.message) {
                message = error.message; // fallback
            }
            return { success: false, message };
        }
    }

    // Verify payment
    const verifyPayment = async (gateway, paymentData) => {
        try {
            let endpoint = ''
            let body = paymentData

            switch (gateway) {
                case 'razorpay':
                    endpoint = '/verify-razorpay-payment'
                    break
                case 'stripe':
                    endpoint = '/verify-stripe-payment'
                    break
                case 'paypal':
                    endpoint = '/verify-paypal-payment'
                    break
                case 'paytm':
                    endpoint = '/verify-paytm-payment'
                    break
                case 'paystack':
                    endpoint = '/verify-paystack-payment'
                    break
                default:
                    throw new Error('Unsupported payment gateway')
            }

            const response = await $api(endpoint, {
                method: 'POST',
                body: body
            })

            if (response.success) {
                paymentSuccess.value = true
                toast('Payment verified successfully', { type: 'success' })
                return { success: true, data: response.data }
            } else {
                throw new Error(response.message || 'Payment verification failed')
            }
        } catch (error) {
            paymentError.value = error.message
            toast(paymentError.value, { type: 'error' })
            return { success: false, error: error.message }
        }
    }

    // Reset payment state
    const resetPaymentState = () => {
        isProcessingPayment.value = false
        paymentError.value = null
        paymentSuccess.value = false
    }

    return {
        isProcessingPayment,
        paymentError,
        paymentSuccess,
        processPOSPayment,
        verifyPayment,
        resetPaymentState
    }
} 
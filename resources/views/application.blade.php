<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <link rel="icon" href="{{ favicon_url() }}" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ setting('site_title') ?? 'Salon Buddy' }}</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('loader.css') }}" />
    @vite(['resources/js/main.js'])
    
    <!-- Paystack Script -->
    <script src="https://js.paystack.co/v1/inline.js"></script>
    
    <!-- Payment Success Handler -->
    <script>
        // // Handle Paystack payment success
        // document.addEventListener('DOMContentLoaded', function() {
        //     // Check if user returned from Paystack
        //     const paystackReference = localStorage.getItem('paystack_order_reference');
        //     if (paystackReference) {
        //         // Get the stored order data
        //         const orderData = JSON.parse(localStorage.getItem('paystack_order_data') || '{}');
        //         const paymentMethodId = localStorage.getItem('paystack_payment_method_id');
        //         const amount = localStorage.getItem('paystack_amount');
                
        //         // Verify payment and complete order
        //         verifyAndCompletePaystackOrder(paystackReference, orderData, paymentMethodId, amount);
        //     }
        // });
        
        // async function verifyAndCompletePaystackOrder(reference, orderData, paymentMethodId, amount) {
        //     try {
        //         // Verify payment with backend
        //         const response = await fetch('/api/verify-paystack-payment', {
        //             method: 'POST',
        //             headers: {
        //                 'Content-Type': 'application/json',
        //                 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        //                 'Authorization': 'Bearer ' + (localStorage.getItem('token') || '')
        //             },
        //             body: JSON.stringify({ reference: reference })
        //         });
                
        //         const result = await response.json();
                
        //         if (result.success) {
        //             // Add transaction_id to orderData
        //             orderData.transaction_id = reference;
                    
        //             // Save order to backend
        //             const saveResponse = await fetch('/api/save-order', {
        //                 method: 'POST',
        //                 headers: {
        //                     'Content-Type': 'application/json',
        //                     'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        //                     'Authorization': 'Bearer ' + (localStorage.getItem('token') || '')
        //                 },
        //                 body: JSON.stringify(orderData)
        //             });
                    
        //             const saveResult = await saveResponse.json();
                    
        //             if (saveResult.success) {
        //                 // Show success message
        //                 if (typeof toast !== 'undefined') {
        //                     toast('Payment successful! Order completed.', { type: 'success' });
        //                 }
                        
        //                 // Redirect to POS
        //                 window.location.href = '/pos';
        //             } else {
        //                 throw new Error(saveResult.message || 'Failed to save order');
        //             }
        //         } else {
        //             throw new Error(result.message || 'Payment verification failed');
        //         }
        //     } catch (error) {
        //         console.error('Paystack payment completion error:', error);
        //         if (typeof toast !== 'undefined') {
        //             toast('Payment verification failed: ' + error.message, { type: 'error' });
        //         }
        //     } finally {
        //         // Clear stored data
        //         localStorage.removeItem('paystack_order_reference');
        //         localStorage.removeItem('paystack_order_data');
        //         localStorage.removeItem('paystack_payment_method_id');
        //         localStorage.removeItem('paystack_amount');
        //     }
        // }
    </script>

    <style>
    /* CUSTOM CURSOR ELEMENTS */
    .cursor-dot, .cursor-ring {
      position: fixed;
      top: 0;
      left: 0;
      pointer-events: none;
      transform: translate3d(-50%,-50%,0);
      transition: opacity 200ms linear, transform 150ms ease-out;
      z-index: 999999;
      will-change: transform;
      mix-blend-mode: normal;
    }
    .cursor-dot {
      width: 12px;
      height: 12px;
      border-radius: 50%;
      background:rgba(9, 141, 156, 0.52);
      box-shadow: 0 2px 6px rgba(15,23,42,0.25);
      opacity: 1;
      transition: background 160ms ease, width 160ms ease, height 160ms ease;
    }
    .cursor-ring {
      width: 25px;
      height: 25px;
      border-radius: 50%;
      border: 1px solid rgb(5, 120, 132);
      background: transparent;
      transform-origin: center;
      opacity: 0.95;
      transition: border-color 160ms ease, transform 160ms ease, opacity 200ms ease;
      backdrop-filter: blur(0px);
    }
    @media (hover: none), (pointer: coarse) {
      body { cursor: auto; }
      .cursor-dot, .cursor-ring { display: none; }
    }
  </style>

</head>

<body>
    <div id="app">
        <div id="loading-bg">
            <div class="loading-logo">
                <!-- SVG Logo -->
                <img src="{{ favicon_url() }}" width="60" height="60" alt="Logo" />
            </div>
            <!-- <div class=" loading">
              <div class="effect-1 effects"></div>
              <div class="effect-2 effects"></div>
              <div class="effect-3 effects"></div>
            </div> -->
        </div>
    </div>
    <div aria-hidden="true" class="cursor-dot" id="cursorDot"></div>
    <div aria-hidden="true" class="cursor-ring" id="cursorRing"></div>

    <script>
        const loaderColor = localStorage.getItem('vuexy-initial-loader-bg') || '#FFFFFF'
        const primaryColor = localStorage.getItem('vuexy-initial-loader-color') || '#7367F0'

        if (loaderColor)
            document.documentElement.style.setProperty('--initial-loader-bg', loaderColor)
        if (loaderColor)
            document.documentElement.style.setProperty('--initial-loader-bg', loaderColor)

        if (primaryColor)
            document.documentElement.style.setProperty('--initial-loader-color', primaryColor)
    </script>
    <!-- Payment Gateway Scripts -->
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <script src="https://js.stripe.com/v3/"></script>
    <script src="https://www.paypal.com/sdk/js?client-id={{ config('services.paypal.client_id') }}&currency=USD"></script>
    <script src="https://js.paystack.co/v1/inline.js"></script>

    <script>
    (function () {
      // Elements
      const dot = document.getElementById('cursorDot');
      const ring = document.getElementById('cursorRing');
      const body = document.body;

      // State
      let mouseX = window.innerWidth / 2;
      let mouseY = window.innerHeight / 2;
      let ringX = mouseX;
      let ringY = mouseY;
      let isHovering = false;
      let isDown = false;
      let visible = true;

      // Sensible easing factors (ring lags)
      const ease = 0.18; // lower = more lag
      const dotEase = 0.6; // dot follows more tightly

      // Utility: check if target should trigger hover style
      function isInteractive(el) {
        if (!el) return false;
        const tag = el.tagName && el.tagName.toLowerCase();
        if (tag === 'a' || tag === 'button' || el.hasAttribute('role') && el.getAttribute('role').includes('button')) return true;
        if (el.matches && (el.matches('input, textarea, select, [contenteditable="true"], .btn, .interactive'))) return true;
        return false;
      }

      // Mouse move
      window.addEventListener('mousemove', (e) => {
        mouseX = e.clientX;
        mouseY = e.clientY;
        // show when moved
        if (!visible) {
          visible = true;
          dot.style.opacity = 1;
          ring.style.opacity = 1;
        }
        // detect element under pointer (for hover state)
        const el = document.elementFromPoint(mouseX, mouseY);
        const hoverNow = isInteractive(el);
      }, { passive: true });

      // Mouse down / up for press effect
      window.addEventListener('mousedown', () => {
        isDown = true;
        body.classList.add('cursor--down');
      });
      window.addEventListener('mouseup', () => {
        isDown = false;
        body.classList.remove('cursor--down');
      });

      // Hide when leaving window
      window.addEventListener('mouseleave', () => {
        visible = false;
        body.classList.add('cursor--hidden');
        dot.style.opacity = 0;
        ring.style.opacity = 0;
      });
      window.addEventListener('mouseenter', () => {
        visible = true;
        body.classList.remove('cursor--hidden');
        dot.style.opacity = 1;
        ring.style.opacity = 1;
      });

      // Resize handling (avoid weird positions)
      window.addEventListener('resize', () => {
        // clamp positions into viewport
        mouseX = Math.min(window.innerWidth - 2, Math.max(2, mouseX));
        mouseY = Math.min(window.innerHeight - 2, Math.max(2, mouseY));
        ringX = mouseX;
        ringY = mouseY;
      });

      // Animation loop - move dot & ring
      function animate() {
        // dot moves quickly toward mouse
        const dx = mouseX - parseFloat(dot.style.left || mouseX) ;
        const dy = mouseY - parseFloat(dot.style.top || mouseY) ;
        const dotNextX = (parseFloat(dot.style.left || mouseX) || mouseX) + dx * dotEase;
        const dotNextY = (parseFloat(dot.style.top || mouseY) || mouseY) + dy * dotEase;

        dot.style.left = dotNextX + 'px';
        dot.style.top = dotNextY + 'px';

        // ring lags more (smooth trailing)
        ringX += (mouseX - ringX) * ease;
        ringY += (mouseY - ringY) * ease;
        ring.style.left = ringX + 'px';
        ring.style.top = ringY + 'px';

        requestAnimationFrame(animate);
      }
      // initialize positions so no jump
      dot.style.left = mouseX + 'px';
      dot.style.top = mouseY + 'px';
      ring.style.left = mouseX + 'px';
      ring.style.top = mouseY + 'px';

      requestAnimationFrame(animate);

      // Optional: keyboard accessibility - show focus ring when focusing interactive elements via keyboard
      document.addEventListener('focusin', (e) => {
        if (isInteractive(e.target)) {
          body.classList.add('cursor--hover');
        }
      });
      document.addEventListener('focusout', (e) => {
        if (isInteractive(e.target)) {
          body.classList.remove('cursor--hover');
        }
      });

      // OPTIONAL: expose API to enable/disable cursor (example)
      window.customCursor = {
        enable() {
          body.style.cursor = 'none';
          dot.style.display = 'block';
          ring.style.display = 'block';
        },
        disable() {
          body.style.cursor = 'auto';
          dot.style.display = 'none';
          ring.style.display = 'none';
        }
      };

    })();
  </script>

</body>

</html>

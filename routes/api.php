<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\POSController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\BannerController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\DamageController;
use App\Http\Controllers\HelperController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SalaryController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\HolidayController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\FrontendController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\VacationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PortfolioController;
use App\Http\Controllers\PromotionController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\SocialAuthController;
use App\Http\Controllers\CustomerAuthController;
use App\Http\Controllers\DeliveryAreaController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\ProductUsagesController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\PaymentGatewayController;
use App\Http\Controllers\WebsiteSettingController;
use App\Http\Controllers\WorkingProcessController;
use App\Http\Controllers\CustomerReceiveController;
use App\Http\Controllers\DeliveryPartnerController;
use App\Http\Controllers\ExpenseCategoryController;
use App\Http\Controllers\SupplierPaymentController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Admin/Staff Authentication Routes
Route::group(['prefix' => 'auth'], function () {
    Route::post('login', [AuthController::class, 'login'])->name('login');
    Route::post('register', [AuthController::class, 'register'])->name('register');

    Route::group(['middleware' => 'auth:sanctum'], function () {
        Route::get('logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('user', [AuthController::class, 'user'])->name('user');
    });
});

// Customer Authentication Routes
Route::group(['prefix' => 'customer'], function () {
    Route::post('login', [CustomerAuthController::class, 'login'])->name('customer.login');
    Route::post('register', [CustomerAuthController::class, 'register'])->name('customer.register');
    Route::post('forgot-password', [CustomerAuthController::class, 'forgotPassword'])->name('customer.forgot-password');
    Route::get('social-auth-urls', [SocialAuthController::class, 'getCustomerSocialAuthUrls'])->name('customer.social-auth-urls');

    Route::group(['middleware' => 'auth:sanctum'], function () {
        Route::post('logout', [CustomerAuthController::class, 'logout'])->name('customer.logout');
        Route::get('profile', [CustomerAuthController::class, 'getCustomer'])->name('customer.profile');
        Route::put('profile', [CustomerAuthController::class, 'updateProfile'])->name('customer.profile.update');
        Route::post('change-password', [CustomerAuthController::class, 'changePassword'])->name('customer.change-password');
        
        // Customer Dashboard API
        Route::get('dashboard', [FrontendController::class, 'dashboardFrontend'])->name('customer.dashboard');
        
        // Customer Service Orders API
        Route::get('service-orders', [FrontendController::class, 'getCustomerServiceOrders'])->name('customer.service-orders');
        Route::get('service-orders/{id}', [FrontendController::class, 'getServiceOrderDetails'])->name('customer.service-orders.details');
        
        // Customer Product Orders API
        Route::get('product-orders', [FrontendController::class, 'getCustomerProductOrders'])->name('customer.product-orders');
        Route::get('product-orders/{id}', [FrontendController::class, 'getProductOrderDetails'])->name('customer.product-orders.details');
        Route::get('product-orders/{id}/invoice', [FrontendController::class, 'downloadProductOrderInvoice'])->name('customer.product-orders.invoice');
        Route::get('product-orders/test/route', [FrontendController::class, 'testRoute'])->name('customer.product-orders.test');
        Route::get('product-orders/test/pdf', [FrontendController::class, 'testPdfGeneration'])->name('customer.product-orders.test-pdf');
        
        // Customer Transaction History API
        Route::get('transaction-history', [FrontendController::class, 'getCustomerTransactionHistory'])->name('customer.transaction-history');
        Route::get('transaction-history/{id}', [FrontendController::class, 'getTransactionDetails'])->name('customer.transaction-history.details');
        
        // Customer Package Orders API
        Route::get('package-orders', [FrontendController::class, 'getCustomerPackageOrders'])->name('customer.package-orders');
        Route::get('package-orders/{id}', [FrontendController::class, 'getPackageOrderDetails'])->name('customer.package-orders.details');
        
        // Debug route
        Route::get('debug-data', [FrontendController::class, 'debugCustomerData'])->name('customer.debug');
    });
});

// Customer Social Authentication Routes (requires session for OAuth flow)
Route::group(['prefix' => 'customer/auth', 'as' => 'customer.auth.', 'middleware' => ['web']], function () {
    // Google OAuth
    Route::get('google/redirect', [SocialAuthController::class, 'redirectToGoogle'])->name('google.redirect');
    Route::get('google/callback', [SocialAuthController::class, 'handleGoogleCallback'])->name('google.callback');
    
    // Facebook OAuth
    Route::get('facebook/redirect', [SocialAuthController::class, 'redirectToFacebook'])->name('facebook.redirect');
    Route::get('facebook/callback', [SocialAuthController::class, 'handleFacebookCallback'])->name('facebook.callback');
    
    // GitHub OAuth
    Route::get('github/redirect', [SocialAuthController::class, 'redirectToGithub'])->name('github.redirect');
    Route::get('github/callback', [SocialAuthController::class, 'handleGithubCallback'])->name('github.callback');
});

/**
 * Forgot password
 */
Route::group(['prefix' => 'forgot-password'], function () {
    Route::post('step-1', [ForgotPasswordController::class, 'step1']);
    Route::post('step-2', [ForgotPasswordController::class, 'step2']);
    Route::post('step-3', [ForgotPasswordController::class, 'step3']);
});

Route::group(['middleware' => 'auth:sanctum'], function () {
    // Profile routes
    Route::controller(ProfileController::class)->group(function () {
        Route::post('/set-security-question', 'setSecurityQuestion');
        Route::post('/update-profile', 'updateProfile');
        Route::post('/change-password', 'changePassword');
    });

    //API Routes
    Route::apiResource('roles', RoleController::class);
    Route::get('permissions', [RoleController::class, 'permissions']);
    Route::apiResource('users', UserController::class);
    Route::apiResource('sales', SaleController::class);
    Route::get('sales-by-user', [SaleController::class, 'saleByUser']);
    Route::apiResource('items', ItemController::class);
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('units', UnitController::class);
    Route::apiResource('delivery-areas', DeliveryAreaController::class);
    Route::apiResource('delivery-partners', DeliveryPartnerController::class);
    Route::apiResource('branches', BranchController::class);
    Route::apiResource('customers', CustomerController::class);
    Route::apiResource('expense-categories', ExpenseCategoryController::class);
    Route::apiResource('expenses', ExpenseController::class);
    Route::apiResource('suppliers', SupplierController::class);
    Route::apiResource('payment-methods', PaymentMethodController::class);
    Route::apiResource('attendances', AttendanceController::class);
    Route::apiResource('promotions', PromotionController::class);
    Route::apiResource('purchases', PurchaseController::class);
    Route::apiResource('damages', DamageController::class);
    Route::apiResource('customer-receives', CustomerReceiveController::class);
    Route::apiResource('supplier-payments', SupplierPaymentController::class);
    Route::apiResource('bookings', BookingController::class);
    Route::apiResource('vacations', VacationController::class);
    Route::apiResource('holidays', HolidayController::class);
    Route::apiResource('salaries', SalaryController::class);
    Route::apiResource('banners', BannerController::class);
    Route::apiResource('working-processes', WorkingProcessController::class);
    Route::apiResource('portfolios', PortfolioController::class);
    Route::apiResource('faqs', FaqController::class);
    Route::apiResource('stock', StockController::class);
    Route::apiResource('product-usages', ProductUsagesController::class);

    // Stock routes
    Route::controller(StockController::class)->group(function () {
        Route::get('stock-summary', 'summary');
        Route::get('alert-stock', 'alertStockList');
        Route::get('product-stock/{id}', 'calculateItemStock');
    });

    // Product usage routes
    Route::controller(ProductUsagesController::class)->group(function () {
        Route::get('product-usages/products', 'getProducts');
    });

    // Report routes
    Route::controller(ReportController::class)->group(function () {
        Route::get('expense-report', 'expenseReport');
        Route::get('expense-report-filters', 'expenseReportFilters');
        Route::get('attendance-report', 'attendanceReport');
        Route::get('attendance-report-filters', 'attendanceReportFilters');
        Route::get('purchase-report', 'purchaseReport');
        Route::get('purchase-report-filters', 'purchaseReportFilters');
        Route::get('damage-report', 'damageReport');
        Route::get('damage-report-filters', 'damageReportFilters');
        Route::get('sale-report-filters', 'saleReportFilters');
        Route::get('sales-report', 'salesReport');
        Route::get('sales-report-filters', 'salesReportFilters');
        Route::get('employee-commission-report', 'employeeCommissionReport');
        Route::get('employee-commission-report-filters', 'employeeCommissionReportFilters');
        Route::get('profit-loss-report', 'profitLossReport');
        Route::get('profit-loss-report-filters', 'profitLossReportFilters');
        Route::get('daily-summary-report', 'dailySummaryReport');
        Route::get('daily-summary-report-filters', 'dailySummaryReportFilters');
        Route::get('stock-report', 'stockReport');
        Route::get('stock-report-filters', 'stockReportFilters');
        Route::get('salary-report-filters', 'salaryReportFilters');
        Route::get('salary-report', 'salaryReport');
    });

    //  POS routes
    Route::controller(POSController::class)->group(function () {
        Route::get('all-items', 'allItems');
        Route::get('item/{id}', 'getItemById');
        Route::post('save-order', 'saveOrder');
        Route::get('getOrderDetails/{order_id}', 'getOrderDetails');
        Route::get('get-booking-list-pos', 'getBookingListForPOS');
        Route::get('get-sale-for-edit/{sale_id}', 'getSaleForEdit');
        Route::put('update-order/{sale_id}', 'updateOrder');
    });

    // Booking routes for POS
    Route::controller(BookingController::class)->group(function () {
        Route::get('booking-details-pos/{id}', 'getBookingDetailsForPOS');
        Route::put('update-booking-date/{id}', 'updateBookingDate');
    });
    
    // Dashboard routes
    Route::controller(DashboardController::class)->group(function () {
        Route::get('dashboard/statistics', 'statistics');
        Route::get('dashboard/total-profit', 'totalProfit');
        Route::get('dashboard/total-expenses', 'totalExpenses');
        Route::get('dashboard/revenue-report', 'revenueReport');
        Route::get('dashboard/earning-reports', 'earningReports');
        Route::get('dashboard/popular-products', 'popularProducts');
    });

    // Helper routes
    Route::controller(HelperController::class)->group(function () {
        Route::get('generate-branch-code', 'generateBranchCode');
        Route::get('generate-item-code', 'generateItemCode');
        Route::get('generate-expense-reference-no', 'generateExpenseReferenceNo');
        Route::get('get-unit-list', 'getUnitList');
        Route::get('get-category-list', 'getCategoryList');
        Route::get('get-service-category-list', 'getServiceCategoryList');
        Route::get('get-product-category-list', 'getProductCategoryList');
        Route::get('get-all-users', 'getAllUsers');
        Route::get('get-all-customers', 'getAllCustomers');
        Route::get('get-all-customers-pos', 'getAllCustomersPos');
        Route::get('get-customers-except-walking-customer', 'getCustomersExceptWalkingCustomer');
        Route::get('set-branch-data/{id}', 'setBranchData');
        Route::get('get-branch-list', 'getBranchList');
        Route::get('get-item-list', 'getItemList');
        Route::get('generate-purchase-reference-no', 'generatePurchaseReferenceNo');
        Route::get('generate-damage-reference-no', 'generateDamageReferenceNo');
        Route::get('get-all-suppliers', 'getAllSuppliers');
        Route::get('get-all-payment-methods', 'getAllPaymentMethods');
        Route::get('get-all-payment-methods-pos', 'getAllPaymentMethodsPos');
        Route::get('get-all-employees', 'getAllEmployees');
        Route::get('customer-receive-reference-no', 'customerReceiveReferenceNo');
        Route::get('supplier-payment-reference-no', 'supplierPaymentReferenceNo');
        Route::get('get-expense-category-list', 'getExpenseCategoryList');
        Route::get('get-user-list', 'getUserList');
        Route::get('get-timezone-list', 'getTimezoneList');
        Route::get('get-tax-details', 'getTaxDetails');
        Route::get('get-product-type-item-list', 'getProductTypeItemList');
        Route::get('get-service-type-item-list', 'getServiceTypeItemList');
        Route::get('get-package-type-item-list', 'getPackageTypeItemList');
        Route::get('get-service-package-list', 'getServicePackageList');
        Route::get('get-item-and-service-list', 'getItemAndServiceList');
        Route::get('generate-booking-reference-no', 'generateBookingReferenceNo');
        Route::get('get-booking-list', 'getBookingList');
        Route::get('customer-due/{customerId}', 'getCustomerDue');
        Route::get('get-notifications', 'getNotifications');
        Route::delete('delete-notification/{id}', 'deleteNotification');
        Route::get('notification-count', 'notificationCount');
        Route::post('mark-notification-read', 'markNotificationAsRead');
        Route::post('mark-notification-unread', 'markNotificationAsUnread');
        Route::get('get-company-info', 'getCompanyInfo');
        Route::get('get-all-type-item-list', 'getAllTypeItemList');
        Route::get('supplier-payment/{supplierId}', 'getSupplierPayment');
        Route::get('customer-loyalty-points/{customerId}', 'getCustomerLoyaltyPoints');
        Route::get('company-loyalty-settings', 'getCompanyLoyaltySettings');
        Route::post('calculate-loyalty-points-needed', 'calculateLoyaltyPointsNeeded');
    });


    // Settings routes
    Route::controller(SettingController::class)->group(function () {
        Route::get('company-info', 'companyInfo');
        Route::post('company-info', 'companyInfoPost');
        Route::get('white-label', 'whiteLabel');
        Route::post('white-label', 'whiteLabelPost');
        Route::get('mail-settings', 'mailSettings');
        Route::post('mail-settings', 'mailSettingsPost');
        Route::get('sms-settings', 'smsSettings');
        Route::post('sms-settings', 'smsSettingsPost');
        Route::get('payment-settings', 'paymentSettings');
        Route::post('payment-settings', 'paymentSettingsPost');
        Route::get('tax-settings', 'taxSettings');
        Route::post('tax-settings', 'taxSettingsPost');
        
        // Test routes for email and SMS
        Route::post('test-email', 'testEmail');
        Route::post('test-sms', 'testSms');
    });

    // Website settings routes
    Route::controller(WebsiteSettingController::class)->group(function () {
        Route::get('website-settings', 'websiteSettings');
        Route::post('website-settings-update', 'websiteSettingsUpdate');
        Route::get('website-about-us', 'websiteAboutUs');
        Route::post('website-about-us-update', 'websiteAboutUsUpdate');
    });
});

// All Settings
Route::get('all-settings', [SettingController::class, 'allSettings']);
Route::controller(HelperController::class)->group(function () {
    Route::get('get-company-info', 'getCompanyInfo');
    Route::get('get-all-type-item-list', 'getAllTypeItemList');
});
// Website settings routes
Route::controller(FrontendController::class)->group(function () {
    Route::get('get-all-branches', 'getAllBranches');
    Route::get('get-all-categories', 'getAllCategories');
    Route::get('get-service-list', 'getServiceList');
    Route::post('send-contact-us-message', 'sendContactUsMessage');
    Route::get('get-all-banner', 'getAllBanner');
    Route::get('get-all-faq', 'getAllFaq');
    Route::get('get-package-type-item-list-frontend', 'getPackageTypeItemList');
    Route::get('get-all-payment-getway-frontend', 'getAllPaymentGetwayFrontend');

    // api for package with patination
    Route::get('get-package-type-item-list-paginated', 'getPackageTypeItemListPaginated');
    
    // Products API
    Route::get('get-products', 'getProducts');
    Route::get('get-products-paginated', 'getProductsPaginated');

    // Services API for Featured Services
    Route::get('get-service-categories', 'getServiceCategories');
    Route::get('get-featured-services', 'getFeaturedServices');
    Route::get('get-featured-services-paginated', 'getFeaturedServicesPaginated');

    // About us API
    Route::get('get-about-us', 'getAboutUs');
    Route::get('get-service-counter', 'getServiceCounter');
    Route::get('get-staff-counter', 'getStaffCounter');
    Route::get('get-customer-counter', 'getCustomerCounter');
    Route::get('get-done-service-counter', 'getDoneServiceCounter');
    Route::get('get-all-employees-frontend', 'getAllEmployeesFrontend');


    // Booking API
    Route::get('get-services-by-category', 'getServicesByCategory');
    Route::post('create-booking', 'createBooking');
    Route::get('booking-details/{bookingId}', 'getBookingDetails');

    // Get payment methods
    Route::get('get-payment-methods', 'getPaymentMethods');

    
   

    // Order creation API
    Route::post('create-order', 'createOrder');
    Route::get('website-settings-frontend', 'websiteSettingsFrontend');

    // Team details API
    Route::get('get-member-details/{id}', 'getMemberDetails');

    // Testimonials API
    Route::get('get-testimonials', 'getTestimonials');

    // Working process API
    Route::get('get-working-process', 'getWorkingProcess');

    // Gallery API
    Route::get('get-gallery', 'getGallery');

    // Delivery area API
    Route::get('get-delivery-areas', 'getDeliveryAreas');
 
    // Payment success API
    Route::get('payment-success', 'paymentSuccess');

});

// Package routes for sold packages and usage
Route::middleware('auth:sanctum')->group(function () {
    Route::get('packages', [\App\Http\Controllers\PackageController::class, 'index']);
    Route::get('packages/{id}', [\App\Http\Controllers\PackageController::class, 'show']);
    Route::post('packages/add-usage', [\App\Http\Controllers\PackageController::class, 'addUsage']);
});



// Payment Gateway Routes
Route::controller(PaymentGatewayController::class)->group(function () {
    // Razorpay
    Route::post('create-razorpay-order', 'createRazorpayOrder');
    Route::post('verify-razorpay-payment', 'verifyRazorpayPayment');
    
    // Stripe
    Route::post('create-stripe-session', 'createStripeSession');
    Route::post('create-stripe-payment-intent', 'createStripePaymentIntent');
    Route::post('verify-stripe-payment', 'verifyStripePayment');
    
    // PayPal
    Route::post('create-paypal-order', 'createPaypalOrder');
    Route::post('verify-paypal-payment', 'verifyPaypalPayment');
    Route::post('check-paypal-payment-status', 'checkPaypalPaymentStatus');
    // Route::get('/payment-success', 'paymentSuccess');
    // Route::get('debug-paypal-config', 'debugPaypalConfig');
    
    // Paytm
    Route::post('create-paytm-order', 'createPaytmOrder');
    Route::post('verify-paytm-payment', 'verifyPaytmPayment');
    Route::post('check-paytm-payment-status', 'checkPaytmPaymentStatus');
    Route::post('paytm-callback', 'verifyPaytmPayment');
    
    // Paystack
    Route::post('create-paystack-order', 'createPaystackOrder');
    Route::post('verify-paystack-payment', 'verifyPaystackPayment');
});

// Route::get('/paytm-initiate', [PaytmController::class, 'initiatePayment']);
// Route::post('/paytm-callback', [PaytmController::class, 'paymentCallback']);


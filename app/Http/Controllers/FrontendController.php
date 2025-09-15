<?php
namespace App\Http\Controllers;
use DateTime;
use App\Models\Faq;
use App\Models\Item;
use App\Models\Sale;
use App\Models\User;
use App\Models\Banner;
use App\Models\Branch;
use App\Models\Booking;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Ratting;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Portfolio;
use App\Models\ItemDetail;
use App\Models\SaleDetail;
use App\Models\AboutusPage;
use App\Traits\ApiResponse;
use Illuminate\Support\Str;
use App\Models\DeliveryArea;
use Illuminate\Http\Request;
use App\Models\PaymentMethod;
use App\Models\WebsiteSetting;
use App\Models\WorkingProcess;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\PackageUsagesSummary;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Validator;

class FrontendController extends Controller
{
    use ApiResponse;
    
    public function getAllBranches()
    {
        $branches = Branch::where('active_status', 'Active')->get();
        return $this->successResponse($branches, 'Branches fetched successfully');
    }

    public function dashboardFrontend2()
    {
        $dashboard = Customer::where('id', 1)->first();
        return $this->successResponse($dashboard, 'Dashboard fetched successfully');
    }

    public function websiteSettingsFrontend()
    {
        $websiteSettings = WebsiteSetting::where('company_id', 1)->first();
        $websiteSettings->company = Company::where('id', 1)->select('currency', 'tax_is_gst')->first();
        return $this->successResponse($websiteSettings, 'Website settings fetched successfully');
    }

    public function getAllEmployeesFrontend()
    {
        $listEmployees = User::where('company_id', 1)
                    ->where('status', 'Active')
                    ->where('del_status', 'Live')
                    ->where('id', '!=', 1)
                    ->get();
        return $this->successResponse($listEmployees, 'Employees fetched successfully');
    }

    public function getPaymentMethods()
    {
        $paymentMethods = PaymentMethod::where('status', 'Enable')
            ->where('use_in_website', 'Yes')
            ->where('status', 'Enable')
            ->where('company_id', 1)
            ->where('account_type', '!=', 'Loyalty Point')
            ->where('del_status', 'Live')
            ->orderBy('sort_id', 'ASC')
            ->get();
        return $this->successResponse($paymentMethods, 'Payment methods fetched successfully');
    }

    public function getAllCategories()
    {
        $categories = Category::where('company_id', 1)->where('del_status', 'Live')->get();
        return $this->successResponse($categories, 'Categories fetched successfully');
    }

    public function getAboutUs()
    {
        $aboutUs = AboutusPage::where('company_id', 1)->where('del_status', 'Live')->first();
        return $this->successResponse($aboutUs, 'About us fetched successfully');
    }

    public function getServiceCounter()
    {
        $serviceCounter = Item::where('company_id', 1)
        ->where('del_status', 'Live')
        ->where('type', 'Service')
        ->count();
        return $this->successResponse($serviceCounter, 'Service counter fetched successfully');
    }
    public function getServiceList()
    {
        $services = Item::where('company_id', 1)
            ->where('del_status', 'Live')
            ->where('status', 'Enable')
            ->where('type', 'Service')
            ->get()
            ->map(function ($service) {
                return [
                    'id' => $service->id,
                    'name' => $service->name,
                    'description' => $service->description,
                    'price' => $service->sale_price,
                    'sale_price' => $service->sale_price,
                    'duration' => $service->duration ?? '',
                    'duration_type' => $service->duration_type ?? '',
                    'category_id' => $service->category_id,
                    'category' => $service->category,
                    'image' => $service->photo_url,
                ];
            });
        
        return $this->successResponse($services, 'Services fetched successfully');
    }

    public function getStaffCounter()
    {
        $staffCounter = User::where('company_id', 1)->where('del_status', 'Live')->count();
        return $this->successResponse($staffCounter, 'Staff counter fetched successfully');
    }

    public function getCustomerCounter()
    {
        $customerCounter = Customer::where('company_id', 1)->where('del_status', 'Live')->count();
        return $this->successResponse($customerCounter, 'Customer counter fetched successfully');
    }

    public function getDoneServiceCounter()
    {
        $doneServiceCounter = Sale::count();
        return $this->successResponse($doneServiceCounter, 'Done service counter fetched successfully');
    }

    public function getAllBanner()
    {
        $banners = Banner::where('status', 'Enabled')
            ->where('company_id', 1)
            ->where('del_status', 'Live')
            ->first();

        if (!$banners) {
            return $this->successResponse([], 'No banners found', 200);
        }

        $banners->customer = Customer::whereNotNull('photo')
            ->where('company_id', 1)
            ->where('del_status', 'Live')
            ->limit(3)
            ->select('photo')
            ->get();

        // attach rating count
        $banners->ratting_count = Ratting::where('company_id', 1)
            ->where('del_status', 'Live')
            ->count();

        return $this->successResponse($banners, 'Banners fetched successfully');
    }

    public function getAllFaq()
    {
        $faqs = Faq::where('status', 'Enabled')->where('company_id', 1)->where('del_status', 'Live')->limit(5)->get();
        return $this->successResponse($faqs, 'FAQs fetched successfully');
    }

    public function getItemAndServiceList()
    {
        $itemList = Item::with('itemDetails')->where('company_id', 1)
                    ->where('del_status', 'Live')
                    ->where('status', 'Enable')
                    ->where('type', 'Package')
                    ->get();
        return $this->successResponse($itemList, 'Package fetched successfully');
    }

    public function getPackageTypeItemList()
    {
        $itemList = Item::where('company_id', 1)
            ->where('del_status', 'Live')
            ->where('status', 'Enable')
            ->where('type', 'Package')
            ->get();

        return $this->successResponse($itemList, 'Package fetched successfully');
    }

    public function getPackageTypeItemListPaginated(Request $request)
    {
        $perPage = $request->get('per_page', 6);
        $page = $request->get('page', 1);

        $itemList = Item::where('company_id', 1)
            ->where('del_status', 'Live')
            ->where('status', 'Enable')
            ->where('type', 'Package')
            ->paginate($perPage);

        return $this->successResponse($itemList, 'Package fetched successfully');
    }

    public function sendContactUsMessage(Request $request)
    {
        // Base validation rules
        $validationRules = [
            'name' => 'required|string|max:55',
            'email' => 'required|email|max:55',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ];

        $validator = Validator::make($request->all(), $validationRules);
        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }
        
        DB::beginTransaction();
        try {
            $validatedData = $validator->validated();
            $contact = Contact::create($validatedData);
            DB::commit();
            return $this->successResponse($contact, 'Contact created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage());
        }
    }

    public function registration(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:55',
            'last_name' => 'required|string|max:55',
            'email' => 'required|email|max:55|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required|string|min:8',
            // 'agree_policy' => 'required|accepted',
        ], [
            'password.confirmed' => 'Password confirmation does not match.',
            'agree_policy.accepted' => 'You must agree to the support policy.',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        DB::beginTransaction();
        try {
            $validatedData = $validator->validated();
            
            $user = User::create([
                'name' => $validatedData['first_name'] . ' ' . $validatedData['last_name'],
                'email' => $validatedData['email'],
                'password' => Hash::make($validatedData['password']),
            ]);

            // Create token for the user
            $token = $user->createToken('auth_token')->plainTextToken;

            DB::commit();
            
            return $this->successResponse([
                'user' => $user,
                'token' => $token,
            ], 'Registration successful');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Registration failed: ' . $e->getMessage());
        }
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:55',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $validatedData = $validator->validated();
        $user = User::where('email', $validatedData['email'])->first();

        if (!$user) {
            return $this->errorResponse('These credentials do not match our records.', 401);
        }

        if (!Hash::check($validatedData['password'], $user->password)) {
            return $this->errorResponse('These credentials do not match our records.', 401);
        }

        // Create token for the user
        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->successResponse([
            'user' => $user,
            'token' => $token,
        ], 'Login successful');
    }

    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:55',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $validatedData = $validator->validated();
        $user = User::where('email', $validatedData['email'])->first();

        if (!$user) {
            return $this->errorResponse('We can\'t find a user with that email address.');
        }

        try {
            // Send password reset link
            $status = Password::sendResetLink($request->only('email'));

            if ($status === Password::RESET_LINK_SENT) {
                return $this->successResponse(null, 'Password reset link sent to your email address.');
            }

            return $this->errorResponse('Unable to send password reset link.');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to send password reset email: ' . $e->getMessage());
        }
    }

    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required|string|min:8',
        ], [
            'password.confirmed' => 'Password confirmation does not match.',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            $status = Password::reset(
                $request->only('email', 'password', 'password_confirmation', 'token'),
                function (User $user, string $password) {
                    $user->forceFill([
                        'password' => Hash::make($password)
                    ])->setRememberToken(Str::random(60));

                    $user->save();

                    event(new PasswordReset($user));
                }
            );

            if ($status === Password::PASSWORD_RESET) {
                return $this->successResponse(null, 'Password has been reset successfully.');
            }

            return $this->errorResponse(__($status));
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to reset password: ' . $e->getMessage());
        }
    }

    public function logout(Request $request)
    {
        if ($request->user()) {
            $request->user()->currentAccessToken()->delete();
            return $this->successResponse(null, 'Logged out successfully');
        }
        return $this->errorResponse('User not authenticated');
    }

    public function getProducts()
    {
        $products = Item::where('company_id', 1)
            ->where('del_status', 'Live')
            ->where('status', 'Enable')
            ->where('type', 'Product')
            ->limit(8)
            ->get();

        return $this->successResponse($products, 'Products fetched successfully');
    }

    public function getProductsPaginated(Request $request)
    {
        $perPage = $request->get('per_page', 8);
        $page = $request->get('page', 1);
        $search = $request->get('search', '');

        $query = Item::where('company_id', 1)
            ->where('del_status', 'Live')
            ->where('status', 'Enable')
            ->where('type', 'Product');

        // Filter by search term if provided
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        $products = $query->paginate($perPage, ['*'], 'page', $page);

        $paginationData = [
            'data' => $products->items(),
            'current_page' => $products->currentPage(),
            'per_page' => $products->perPage(),
            'total' => $products->total(),
            'last_page' => $products->lastPage(),
            'from' => $products->firstItem(),
            'to' => $products->lastItem(),
        ];

        return $this->successResponse($paginationData, 'Products fetched successfully');
    }

    public function getServiceCategories()
    {
        $categories = Category::where('company_id', 1)
            ->where('del_status', 'Live')
            ->where('status', 'Enabled')
            ->where('type', 'Service')
            ->get();

        return $this->successResponse($categories, 'Service categories fetched successfully');
    }

    public function getFeaturedServices(Request $request)
    {
        $categoryId = $request->get('category_id');
        
        $query = Item::with(['category', 'ratings'])
            ->where('company_id', 1)
            ->where('del_status', 'Live')
            ->where('status', 'Enable')
            ->where('type', 'Service');

        // Filter by category if provided
        if ($categoryId && $categoryId !== 'all') {
            $query->where('category_id', $categoryId);
        }

        $services = $query->get()->map(function ($service) {
            return [
                'id' => $service->id,
                'name' => $service->name,
                'price' => $service->sale_price,
                'duration' => $service->duration ?? '',
                'duration_type' => $service->duration_type ?? '',
                'rating' => round($service->averageRating(), 1),
                'reviews' => $service->totalReviews(),
                'category_id' => $service->category_id,
                'category_name' => $service->category ? $service->category->name : '',
                'category_image' => $service->category ? $service->category->photo_url : asset('assets/images/system-config/default-picture.png'),
                'image' => $service->category ? $service->category->photo_url : asset('assets/images/system-config/default-picture.png'),
            ];
        });

        return $this->successResponse($services, 'Featured services fetched successfully');
    }

    public function getServicesByCategory(Request $request)
    {
        $branchId = $request->get('branch_id');
        $categoryId = $request->get('category_id');

        $query = Item::where('company_id', 1)
            ->where('del_status', 'Live')
            ->where('status', 'Enable')
            ->where('type', 'Service');

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        $services = $query->get()->map(function ($service) {
            return [
                'id' => $service->id,
                'name' => $service->name,
                'description' => $service->description,
                'price' => $service->sale_price,
                'duration' => $service->duration ?? '',
                'duration_type' => $service->duration_type ?? '',
                'image' => $service->photo_url ?? asset('assets/images/system-config/default-picture.png'),
                'category' => $service->category,
            ];
        });

        return $this->successResponse($services, 'Services fetched successfully');
    }

    public function createBooking(Request $request)
    {
        // dd($request->all());
        $validator = Validator::make($request->all(), [
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_address' => 'nullable|string',
            'branch_id' => 'required|exists:branches,id',
            'appointment_date' => 'required',
            'services' => 'required|array|min:1',
            'services.*.id' => 'required|exists:items,id',
            'services.*.price' => 'required|numeric|min:0',
            'services.*.duration' => 'required|numeric|min:0',
            'services.*.time' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        DB::beginTransaction();
        try {
            $validatedData = $validator->validated();

            $lastBooking = Booking::where('company_id', 1)->where('del_status', 'Live')->orderBy('id', 'desc')->first();
            $nextId = $lastBooking ? $lastBooking->id + 1 : 1;
            $referenceNo = str_pad($nextId, 6, '0', STR_PAD_LEFT);

            $customer = Customer::where('email', $validatedData['customer_email'])->first();
            if (!$customer) {
                $customer = Customer::create([
                    'name' => $validatedData['customer_name'],
                    'email' => $validatedData['customer_email'],
                    'phone' => $validatedData['customer_phone'],
                    'address' => $validatedData['customer_address'],
                    'company_id' => 1,
                    'del_status' => 'Live',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }else if($customer->phone == '') {
                $customer->update([
                    'phone' => $validatedData['customer_phone'],
                    'updated_at' => now(),
                ]);
            }
            
            // Create booking
            $booking = [
                'reference_no' => $referenceNo,
                'company_id' => 1,
                'user_id' => 1,
                'branch_id' => $validatedData['branch_id'],
                'customer_id' => $customer->id,
                'date' => $validatedData['appointment_date'],
                'status' => 'Pending',
                'created_at' => now(),
                'updated_at' => now(),
                
                // 'subtotal' => $validatedData['subtotal'],
                // 'tax_amount' => $validatedData['tax_amount'],
                // 'total_amount' => $validatedData['total_amount'],
                // 'payment_method' => $validatedData['payment_method'],
                // 'payment_status' => $validatedData['payment_method'] === 'cash' ? 'Pending' : 'Completed',
            ];

            $bookingId = DB::table('bookings')->insertGetId($booking);

            $bookingDetails = [];
            foreach ($validatedData['services'] as $service) {
                // Get service price from database to ensure data integrity
                $serviceItem = Item::find($service['id']);
                if (!$serviceItem) {
                    throw new \Exception('Service not found: ' . $service['id']);
                }

                // Convert to DateTime
                $start = DateTime::createFromFormat('H:i', $service['time']);
                // Clone and add duration
                $end = clone $start;
                $end->modify("+{$service['duration']} minutes");
                $start_time = $start->format('H:i');
                $end_time   = $end->format('H:i');

                $bookingDetails[] = [
                    'booking_id' => $bookingId,
                    'item_id' => $service['id'],
                    'quantity' => 1,
                    'start_time' => $start_time,
                    'end_time' => $end_time,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            DB::table('booking_details')->insert($bookingDetails);
            DB::commit();
            return $this->successResponse($referenceNo, 'Booking created successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Failed to create booking: ' . $e->getMessage());
        }
    }

    public function getBookingDetails($bookingId)
    {
        try {
            $booking = DB::table('bookings')
                ->leftJoin('branches', 'bookings.branch_id', '=', 'branches.id')
                ->where('bookings.reference_no', $bookingId)
                ->select('bookings.*', 'branches.name as branch_name')
                ->first();

            if (!$booking) {
                return $this->errorResponse('Booking not found', 404);
            }

            $services = DB::table('booking_services')
                ->where('booking_id', $booking->id)
                ->get();

            $bookingData = [
                'id' => $booking->id,
                'reference_no' => $booking->reference_no,
                'customer_name' => $booking->customer_name,
                'customer_email' => $booking->customer_email,
                'customer_phone' => $booking->customer_phone,
                'customer_address' => $booking->customer_address,
                'appointment_date' => $booking->appointment_date,
                'appointment_time' => $booking->appointment_time,
                'status' => $booking->status,
                'payment_method' => $booking->payment_method,
                'payment_status' => $booking->payment_status,
                'subtotal' => $booking->subtotal,
                'tax_amount' => $booking->tax_amount,
                'total_amount' => $booking->total_amount,
                'branch' => [
                    'id' => $booking->branch_id,
                    'name' => $booking->branch_name
                ],
                'services' => $services->map(function ($service) {
                    return [
                        'id' => $service->service_id,
                        'name' => $service->service_name,
                        'price' => $service->service_price,
                        'duration' => $service->service_duration,
                        'duration_type' => $service->service_duration_type,
                    ];
                })
            ];

            return $this->successResponse($bookingData, 'Booking details fetched successfully');

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch booking details: ' . $e->getMessage());
        }
    }


    



   
    public function createOrder(Request $request)
    {
        // dd($request->all());
        $company = Company::find(1);

        $validator = Validator::make($request->all(), [
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.type' => 'required|in:Product,Package',
            'subtotal' => 'required|numeric|min:0',
            'tax_amount' => 'required|numeric|min:0',
            'delivery_charge' => 'nullable|numeric|min:0',
            'delivery_area_id' => 'nullable|exists:delivery_areas,id',
            'total_amount' => 'required|numeric|min:0',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'customer_data' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        DB::beginTransaction();
        try {
            $validatedData = $validator->validated();

            // Handle customer data if provided
            $customerId = null;
            if (!empty($validatedData['customer_data'])) {
                $customerData = $validatedData['customer_data'];
                $customer = Customer::where('email', $customerData['email'])->first();
                
                if (!$customer) {
                    $customer = Customer::create([
                        'name' => ($customerData['f_name'] ?? '') . ' ' . ($customerData['l_name'] ?? ''),
                        'email' => $customerData['email'],
                        'phone' => $customerData['phone'],
                        'address' => $customerData['address'] ?? '',
                        'company_id' => 1,
                        'del_status' => 'Live',
                    ]);
                }
                $customerId = $customer->id;
            }

            // Generate reference number
            $lastSale = Sale::where('company_id', 1)->where('del_status', 'Live')->orderBy('id', 'desc')->first();
            $nextId = $lastSale ? $lastSale->id + 1 : 1;
            $referenceNo = 'SO' . date('Ymd') . str_pad($nextId, 4, '0', STR_PAD_LEFT);

            $tax_type = $company->tax_type;
            if($tax_type == 'Inclusive'){
                $grandtotal_with_tax_discount = $validatedData['subtotal'];
            }else if($tax_type == 'Exclusive'){
                $grandtotal_with_tax_discount = $validatedData['subtotal'] + $validatedData['tax_amount'];
            }


            // Create sale record
            $sale = Sale::create([
                'reference_no' => $referenceNo,
                'order_from' => 'Website',
                'order_date' => now()->format('Y-m-d'),
                'order_status' => 'Pending',
                'subtotal_without_tax_discount' => $validatedData['subtotal'],
                'grandtotal_with_tax_discount' => $grandtotal_with_tax_discount,
                'discount' => 0,
                'total_tax' => $validatedData['tax_amount'],
                'delivery_charge' => $validatedData['delivery_charge'] ?? 0,
                'delivery_area_id' => $validatedData['delivery_area_id'] ?? null,
                'total_payable' => $validatedData['total_amount'],
                'customer_id' => $customerId,
                'payment_method_id' => $validatedData['payment_method_id'],
                'branch_id' => 1,
                'user_id' => 1, // Default user, you can get from auth later
                'company_id' => 1,
                'del_status' => 'Live',
            ]);

            // Create sale details
            foreach ($validatedData['items'] as $item) {
                // Get item price from database to ensure data integrity
                $itemRecord = Item::find($item['id']);
                if (!$itemRecord) {
                    throw new \Exception('Item not found: ' . $item['id']);
                }

                $unitPrice = $item['price']; // Use the price from cart
                $quantity = $item['quantity'];
                $subtotal = $unitPrice * $quantity;
                
                // Calculate tax for this item (proportional to subtotal)
                $itemTax = ($subtotal / $validatedData['subtotal']) * $validatedData['tax_amount'];
                $itemTotal = $subtotal + $itemTax;

                SaleDetail::create([
                    'sale_id' => $sale->id,
                    'item_id' => $item['id'],
                    'unit_price' => $unitPrice,
                    'quantity' => $quantity,
                    'subtotal' => $subtotal,
                    'total_tax' => $itemTax,
                    'total_payable' => $itemTotal,
                    'branch_id' => 1,
                    'user_id' => 1,
                    'company_id' => 1,
                    'del_status' => 'Live',
                ]);
            }

            DB::commit();

            return $this->successResponse([
                'order_id' => $sale->id,
                'reference_no' => $referenceNo,
                'total_amount' => $validatedData['total_amount']
            ], 'Order created successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Failed to create order: ' . $e->getMessage());
        }
    }

    public function verifyPayment(Request $request)
    {
        // This is a placeholder - implement actual payment verification
        // In a real scenario, you'd verify the payment with the respective gateway
        
        DB::beginTransaction();
        try {
            // Update booking payment status
            DB::table('bookings')
                ->where('reference_no', $request->booking_reference ?? 'BK' . date('Ymd') . strtoupper(Str::random(6)))
                ->update([
                    'payment_status' => 'completed',
                    'updated_at' => now()
                ]);

            DB::commit();

            return $this->successResponse([
                'success' => true,
                'booking_id' => $request->booking_reference ?? 'BK' . date('Ymd') . strtoupper(Str::random(6))
            ], 'Payment verified successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Payment verification failed: ' . $e->getMessage());
        }
    }

    public function dashboardFrontend(Request $request)
    {
        // Check if customer is authenticated
        if (!$request->user()) {
            return $this->errorResponse('Customer authentication required', 401);
        }

        $customer = $request->user();
        $customerId = $customer->id;

        try {
            $service_bookings = Booking::where('customer_id', $customerId)
                ->where('del_status', 'Live')
                ->count();

            $complete_orders = Sale::where('customer_id', $customerId)
                ->where('del_status', 'Live')
                ->where('order_status', 'Completed')
                ->count();

            $total_buy_package = DB::table('sales')
                ->join('sale_details', 'sales.id', '=', 'sale_details.sale_id')
                ->join('items', 'sale_details.item_id', '=', 'items.id')
                ->where('sales.customer_id', $customerId)
                ->where('sales.del_status', 'Live')
                ->where('sale_details.del_status', 'Live')
                ->where('items.type', 'Package')
                ->count();

            // Latest 10 orders from sales where order_from is 'Website'
            $latestOrders = Sale::where('customer_id', $customerId)
                ->where('del_status', 'Live')
                ->where('order_from', 'Website')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($sale) {
                    return [
                        'id' => $sale->id,
                        'order_id' => $sale->reference_no,
                        'date' => $sale->order_date ?? $sale->created_at->format('Y-m-d'),
                        'total_payable' => $sale->total_payable ?? 0,
                        'status' => $sale->order_status ?? 'Pending'
                    ];
                });

            $dashboardData = [
                'stats' => [
                    'complete_order' => $complete_orders,
                    'service_booking' => $service_bookings,
                    'total_buy_package' => $total_buy_package
                ],
                'orderHistory' => $latestOrders
            ];

            return $this->successResponse($dashboardData, 'Dashboard data fetched successfully');

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch dashboard data: ' . $e->getMessage());
        }
    }

    public function getCustomerServiceOrders(Request $request)
    {
        // Check if customer is authenticated
        if (!$request->user()) {
            return $this->errorResponse('Customer authentication required', 401);
        }

        $customer = $request->user();
        $customerId = $customer->id;

        try {
            $perPage = $request->get('per_page', 10);
            $search = $request->get('search', '');

            // Debug: Check if customer has any bookings
            $totalBookings = Booking::where('customer_id', $customerId)->count();
            
            // Build query for bookings - start simple
            $query = Booking::where('customer_id', $customerId)
                ->where('del_status', 'Live');

            // Apply search filter only if search is not empty
            if (!empty($search)) {
                $query->where(function($q) use ($search) {
                    $q->where('reference_no', 'LIKE', "%{$search}%")
                      ->orWhere('status', 'LIKE', "%{$search}%");
                });
            }

            // Get paginated results
            $serviceOrders = $query->orderBy('created_at', 'desc')
                ->paginate($perPage);

            // Transform the data with safe handling
            $serviceOrders->getCollection()->transform(function ($booking) {
                // Get branch name safely
                $branchName = 'N/A';
                try {
                    $branch = Branch::find($booking->branch_id);
                    $branchName = $branch ? $branch->branch_name : 'N/A';
                } catch (\Exception $e) {
                    // Continue with N/A if branch fetch fails
                }

                return [
                    'id' => $booking->id,
                    'order_id' => $booking->reference_no ?? 'N/A',
                    'branch' => $branchName,
                    'date' => $booking->date,
                    'status' => $booking->status ?? 'Pending',
                    'created_at' => $booking->created_at
                ];
            });

            return $this->successResponse([
                'data' => $serviceOrders->items(),
                'current_page' => $serviceOrders->currentPage(),
                'per_page' => $serviceOrders->perPage(),
                'total' => $serviceOrders->total(),
                'last_page' => $serviceOrders->lastPage(),
                'from' => $serviceOrders->firstItem(),
                'to' => $serviceOrders->lastItem(),
            ], 'Service orders fetched successfully');

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch service orders: ' . $e->getMessage() . ' Line: ' . $e->getLine());
        }
    }

    public function getServiceOrderDetails(Request $request, $id)
    {
        // Check if customer is authenticated
        if (!$request->user()) {
            return $this->errorResponse('Customer authentication required', 401);
        }

        $customer = $request->user();
        $customerId = $customer->id;

        try {
            // Get booking with related data
            $booking = Booking::with(['branch:id,branch_name'])
                ->where('id', $id)
                ->where('customer_id', $customerId)
                ->where('del_status', 'Live')
                ->first();

            if (!$booking) {
                return $this->errorResponse('Service order not found', 404);
            }

            // Get booking details (services)
            $bookingDetails = DB::table('booking_details')
                ->join('items', 'booking_details.item_id', '=', 'items.id')
                ->where('booking_details.booking_id', $booking->id)
                ->where('booking_details.del_status', 'Live')
                ->select(
                    'items.id as service_id',
                    'items.name as service_name',
                    'items.description as service_description',
                    'booking_details.quantity',
                    'booking_details.start_time',
                    'booking_details.end_time'
                )
                ->get();

            // Prepare response data
            $serviceOrderData = [
                'id' => $booking->id,
                'order_id' => $booking->reference_no,
                'branch' => [
                    'id' => $booking->branch_id,
                    'name' => $booking->branch ? $booking->branch->branch_name : 'N/A'
                ],
                'date' => $booking->date ?? $booking->created_at->format('Y-m-d'),
                'status' => $booking->status,
                'payment_status' => $booking->payment_status,
                'note' => $booking->note,
                'created_at' => $booking->created_at->format('Y-m-d H:i:s'),
                'services' => $bookingDetails->map(function ($detail) {
                    return [
                        'id' => $detail->service_id,
                        'name' => $detail->service_name,
                        'description' => $detail->service_description,
                        'quantity' => $detail->quantity,
                        'start_time' => $detail->start_time,
                        'end_time' => $detail->end_time
                    ];
                })
            ];

            return $this->successResponse($serviceOrderData, 'Service order details fetched successfully');

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch service order details: ' . $e->getMessage());
        }
    }

    public function getCustomerProductOrders(Request $request)
    {
        // Check if customer is authenticated
        if (!$request->user()) {
            return $this->errorResponse('Customer authentication required', 401);
        }

        $customer = $request->user();
        $customerId = $customer->id;

        try {
            $perPage = $request->get('per_page', 10);
            $search = $request->get('search', '');

            // Build query for sales (product orders)
            $query = Sale::where('customer_id', $customerId)
                ->where('del_status', 'Live')
                ->where('order_from', 'Website');

            // Apply search filter only if search is not empty
            if (!empty($search)) {
                $query->where(function($q) use ($search) {
                    $q->where('reference_no', 'LIKE', "%{$search}%")
                      ->orWhere('order_status', 'LIKE', "%{$search}%");
                });
            }

            // Get paginated results
            $productOrders = $query->orderBy('created_at', 'desc')
                ->paginate($perPage);

            // Transform the data
            $productOrders->getCollection()->transform(function ($sale) {
                return [
                    'id' => $sale->id,
                    'order_id' => $sale->reference_no ?? 'N/A',
                    'date' => $sale->order_date,
                    'total_amount' => $sale->total_payable ?? 0,
                    'status' => $sale->order_status ?? 'Pending',
                    'created_at' => $sale->created_at
                ];
            });

            return $this->successResponse([
                'data' => $productOrders->items(),
                'current_page' => $productOrders->currentPage(),
                'per_page' => $productOrders->perPage(),
                'total' => $productOrders->total(),
                'last_page' => $productOrders->lastPage(),
                'from' => $productOrders->firstItem(),
                'to' => $productOrders->lastItem(),
            ], 'Product orders fetched successfully');

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch product orders: ' . $e->getMessage() . ' Line: ' . $e->getLine());
        }
    }

    public function getProductOrderDetails(Request $request, $id)
    {
        // Check if customer is authenticated
        if (!$request->user()) {
            return $this->errorResponse('Customer authentication required', 401);
        }

        $customer = $request->user();
        $customerId = $customer->id;

        try {
            // Get sale with related data
            $sale = Sale::where('id', $id)
                ->where('customer_id', $customerId)
                ->where('del_status', 'Live')
                ->where('order_from', 'Website')
                ->first();

            if (!$sale) {
                return $this->errorResponse('Product order not found', 404);
            }

            // Get sale details (products)
            $saleDetails = DB::table('sale_details')
                ->join('items', 'sale_details.item_id', '=', 'items.id')
                ->where('sale_details.sale_id', $sale->id)
                ->where('sale_details.del_status', 'Live')
                ->select(
                    'items.id as product_id',
                    'items.name as product_name',
                    'items.description as product_description',
                    'items.photo as product_image',
                    'sale_details.quantity',
                    'sale_details.unit_price',
                    'sale_details.subtotal',
                    'sale_details.total_tax',
                    'sale_details.total_payable'
                )
                ->get();

            // Get payment method
            $paymentMethod = PaymentMethod::find($sale->payment_method_id);

            // Prepare response data
            $productOrderData = [
                'id' => $sale->id,
                'order_id' => $sale->reference_no,
                'date' => $sale->order_date,
                'status' => $sale->order_status ?? 'Pending',
                'payment_method' => $paymentMethod ? $paymentMethod->name : 'N/A',
                'total_tax' => $sale->total_tax ?? 0,
                'subtotal_without_tax_discount' => $sale->subtotal_without_tax_discount ?? 0,
                'total_payable' => $sale->total_payable ?? 0,
                'created_at' => $sale->created_at->format('Y-m-d H:i:s'),
                'products' => $saleDetails->map(function ($detail) {
                    return [
                        'id' => $detail->product_id,
                        'name' => $detail->product_name,
                        'description' => $detail->product_description,
                        'image' => $detail->product_image ?? asset('assets/images/system-config/default-picture.png'),
                        'quantity' => $detail->quantity,
                        'unit_price' => $detail->unit_price,
                        'subtotal' => $detail->subtotal,
                        'total_tax' => $detail->total_tax,
                        'total_payable' => $detail->total_payable
                    ];
                })
            ];

            return $this->successResponse($productOrderData, 'Product order details fetched successfully');

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch product order details: ' . $e->getMessage());
        }
    }

    public function downloadProductOrderInvoice(Request $request, $id)
    {
        // Check if customer is authenticated
        if (!$request->user()) {
            return $this->errorResponse('Customer authentication required', 401);
        }

        $authenticatedCustomer = $request->user();
        $customerId = $authenticatedCustomer->id;

        try {
            // Debug: Log the start of the process
            Log::info('PDF Generation Started', [
                'customer_id' => $customerId,
                'order_id' => $id
            ]);

            // Get sale with related data
            $sale = Sale::where('id', $id)
                ->where('customer_id', $customerId)
                ->where('del_status', 'Live')
                ->where('order_from', 'Website')
                ->first();

            if (!$sale) {
                \Log::error('Sale not found', [
                    'customer_id' => $customerId,
                    'order_id' => $id
                ]);
                return $this->errorResponse('Product order not found', 404);
            }

            \Log::info('Sale found', ['sale_id' => $sale->id, 'reference_no' => $sale->reference_no]);

            // Get customer details
            $customer = Customer::find($customerId);

            if (!$customer) {
                \Log::error('Customer not found', ['customer_id' => $customerId]);
                return $this->errorResponse('Customer not found', 404);
            }

            \Log::info('Customer found', ['customer_name' => $customer->name]);

            // Get sale details (products)
            $saleDetails = DB::table('sale_details')
                ->join('items', 'sale_details.item_id', '=', 'items.id')
                ->where('sale_details.sale_id', $sale->id)
                ->where('sale_details.del_status', 'Live')
                ->select(
                    'items.id as product_id',
                    'items.name as product_name',
                    'items.description as product_description',
                    'sale_details.quantity',
                    'sale_details.unit_price',
                    'sale_details.subtotal',
                    'sale_details.total_tax',
                    'sale_details.total_payable'
                )
                ->get();

            \Log::info('Sale details found', ['product_count' => $saleDetails->count()]);

            // Get payment method
            $paymentMethod = PaymentMethod::find($sale->payment_method_id);

            // Prepare invoice data
            $invoiceData = [
                'order' => [
                    'id' => $sale->id,
                    'order_id' => $sale->reference_no,
                    'date' => $sale->order_date ?? $sale->created_at->format('Y-m-d'),
                    'status' => $sale->order_status ?? 'Pending',
                    'payment_method' => $paymentMethod ? $paymentMethod->name : 'N/A',
                    'total_tax' => $sale->total_tax ?? 0,
                    'total_payable' => $sale->total_payable ?? 0,
                    'created_at' => $sale->created_at->format('Y-m-d H:i:s'),
                ],
                'customer' => [
                    'name' => $customer->name ?? 'N/A',
                    'email' => $customer->email ?? 'N/A',
                    'phone' => $customer->phone ?? null,
                    'address' => $customer->address ?? null,
                ],
                'products' => $saleDetails->map(function ($detail) {
                    return [
                        'name' => $detail->product_name,
                        'description' => $detail->product_description,
                        'quantity' => $detail->quantity,
                        'unit_price' => $detail->unit_price,
                        'subtotal' => $detail->subtotal,
                        'total_tax' => $detail->total_tax,
                        'total_payable' => $detail->total_payable
                    ];
                }),
                'invoice_date' => now()->format('Y-m-d'),
                'invoice_number' => 'INV-' . $sale->reference_no,
            ];

            \Log::info('Invoice data prepared', [
                'order_id' => $invoiceData['order']['order_id'],
                'customer_name' => $invoiceData['customer']['name'],
                'product_count' => count($invoiceData['products'])
            ]);

            // Check if view exists
            if (!view()->exists('invoice.product-order')) {
                \Log::error('View not found: invoice.product-order');
                return $this->errorResponse('Invoice template not found', 500);
            }

            \Log::info('View exists, starting PDF generation');

            // Generate PDF with error handling
            try {
                $pdf = Pdf::loadView('invoice.product-order', $invoiceData);
                \Log::info('PDF loaded successfully');
                
                // Set paper size and orientation
                $pdf->setPaper('A4', 'portrait');
                \Log::info('PDF paper size set');
                
                // Set response headers to ensure PDF content type
                $filename = 'invoice-' . $sale->reference_no . '.pdf';
                
                \Log::info('Starting PDF download', ['filename' => $filename]);
                
                // Return PDF response with proper headers
                return response($pdf->output(), 200, [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                    'Cache-Control' => 'no-cache, no-store, must-revalidate',
                    'Pragma' => 'no-cache',
                    'Expires' => '0'
                ]);
                
            } catch (\Exception $pdfError) {
                \Log::error('PDF Generation Error', [
                    'error' => $pdfError->getMessage(),
                    'trace' => $pdfError->getTraceAsString()
                ]);
                return $this->errorResponse('PDF generation failed: ' . $pdfError->getMessage(), 500);
            }

        } catch (\Exception $e) {
            \Log::error('General Error in PDF generation', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'customer_id' => $customerId ?? null,
                'order_id' => $id
            ]);
            return $this->errorResponse('Failed to generate invoice: ' . $e->getMessage(), 500);
        }
    }

    public function testRoute(Request $request)
    {
        // Simple test to verify authentication and route
        if (!$request->user()) {
            return $this->errorResponse('Customer authentication required', 401);
        }

        return $this->successResponse([
            'message' => 'Test route works!',
            'customer_id' => $request->user()->id,
            'url' => $request->fullUrl(),
            'base_url' => url('/'),
            'api_url' => url('/api'),
        ], 'Test successful');
    }

    public function testPdfGeneration(Request $request)
    {
        // Simple test to verify PDF generation works
        if (!$request->user()) {
            return $this->errorResponse('Customer authentication required', 401);
        }

        try {
            // Create simple test data
            $testData = [
                'order' => [
                    'id' => 1,
                    'order_id' => 'TEST-ORDER-123',
                    'date' => now()->format('Y-m-d'),
                    'status' => 'Test',
                    'payment_method' => 'Test Payment',
                    'total_tax' => 10.00,
                    'total_payable' => 110.00,
                    'created_at' => now()->format('Y-m-d H:i:s'),
                ],
                'customer' => [
                    'name' => 'Test Customer',
                    'email' => 'test@example.com',
                    'phone' => '123-456-7890',
                    'address' => '123 Test Street',
                ],
                'products' => [
                    [
                        'name' => 'Test Product 1',
                        'description' => 'Test Description 1',
                        'quantity' => 2,
                        'unit_price' => 50.00,
                        'subtotal' => 100.00,
                        'total_tax' => 10.00,
                        'total_payable' => 110.00
                    ]
                ],
                'invoice_date' => now()->format('Y-m-d'),
                'invoice_number' => 'INV-TEST-123',
            ];

            // Check if view exists
            if (!view()->exists('invoice.product-order')) {
                return $this->errorResponse('Invoice template not found', 500);
            }

            // Test PDF generation
            $pdf = Pdf::loadView('invoice.product-order', $testData);
            $pdf->setPaper('A4', 'portrait');
            
            // Return PDF response
            return response($pdf->output(), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="test-invoice.pdf"',
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0'
            ]);

        } catch (\Exception $e) {
            return $this->errorResponse('PDF test failed: ' . $e->getMessage(), 500);
        }
    }

    public function debugCustomerData(Request $request)
    {
        try {
            $customer = $request->user();
            $customerId = $customer ? $customer->id : null;

            $data = [
                'customer_authenticated' => !!$customer,
                'customer_id' => $customerId,
                'customer_data' => $customer,
                'total_customers' => Customer::count(),
                'total_bookings' => Booking::count(),
                'total_branches' => Branch::count(),
                'customer_bookings' => $customerId ? Booking::where('customer_id', $customerId)->count() : 0,
                'sample_bookings' => Booking::limit(3)->get(),
                'sample_customers' => Customer::limit(3)->get(),
                'sample_branches' => Branch::limit(3)->get()
            ];

            return $this->successResponse($data, 'Debug data fetched successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Debug failed: ' . $e->getMessage());
        }
    }

    public function getCustomerTransactionHistory(Request $request)
    {
        // Check if customer is authenticated
        if (!$request->user()) {
            return $this->errorResponse('Customer authentication required', 401);
        }

        $customer = $request->user();
        $customerId = $customer->id;

        try {
            $perPage = $request->get('per_page', 10);
            $search = $request->get('search', '');
            $page = $request->get('page', 1);

            // Get bookings data (Service transactions)
            $bookingsQuery = DB::table('bookings')
                ->leftJoin('payment_methods', 'bookings.payment_method', '=', 'payment_methods.id')
                ->where('bookings.customer_id', $customerId)
                ->where('bookings.del_status', 'Live')
                ->select(
                    'bookings.id',
                    'bookings.reference_no as transaction_id',
                    'bookings.total_amount as amount',
                    'bookings.date as transaction_date',
                    'payment_methods.name as payment_method',
                    'bookings.status',
                    'bookings.created_at',
                    DB::raw("'Service' as type"),
                    DB::raw("'booking' as source_type")
                );

            // Get sales data (Product transactions)
            $salesQuery = DB::table('sales')
                ->leftJoin('payment_methods', 'sales.payment_method_id', '=', 'payment_methods.id')
                ->where('sales.customer_id', $customerId)
                ->where('sales.del_status', 'Live')
                ->where('sales.order_from', 'Website')
                ->select(
                    'sales.id',
                    'sales.reference_no as transaction_id',
                    'sales.total_payable as amount',
                    DB::raw('COALESCE(sales.order_date, DATE(sales.created_at)) as transaction_date'),
                    'payment_methods.name as payment_method',
                    DB::raw('COALESCE(sales.order_status, "Pending") as status'),
                    'sales.created_at',
                    DB::raw("'Product' as type"),
                    DB::raw("'sale' as source_type")
                );

            // Apply search filter if provided
            if (!empty($search)) {
                $bookingsQuery->where(function($q) use ($search) {
                    $q->where('bookings.reference_no', 'LIKE', "%{$search}%")
                      ->orWhere('bookings.status', 'LIKE', "%{$search}%")
                      ->orWhere('payment_methods.name', 'LIKE', "%{$search}%");
                });

                $salesQuery->where(function($q) use ($search) {
                    $q->where('sales.reference_no', 'LIKE', "%{$search}%")
                      ->orWhere('sales.order_status', 'LIKE', "%{$search}%")
                      ->orWhere('payment_methods.name', 'LIKE', "%{$search}%");
                });
            }

            // Union both queries
            $transactionsQuery = $bookingsQuery->union($salesQuery);

            // Get total count for pagination
            $totalCount = DB::table(DB::raw("({$transactionsQuery->toSql()}) as combined_transactions"))
                ->mergeBindings($transactionsQuery)
                ->count();

            // Apply pagination and ordering
            $transactions = DB::table(DB::raw("({$transactionsQuery->toSql()}) as combined_transactions"))
                ->mergeBindings($transactionsQuery)
                ->orderBy('created_at', 'desc')
                ->offset(($page - 1) * $perPage)
                ->limit($perPage)
                ->get();

            // Transform the data
            $transformedTransactions = $transactions->map(function($transaction) {
                return [
                    'id' => $transaction->id,
                    'transaction_id' => $transaction->transaction_id,
                    'amount' => $transaction->amount,
                    'date' => $transaction->transaction_date,
                    'payment_method' => $transaction->payment_method ?? 'N/A',
                    'status' => $transaction->status,
                    'type' => $transaction->type,
                    'source_type' => $transaction->source_type,
                    'created_at' => $transaction->created_at
                ];
            });

            // Calculate pagination data
            $totalPages = ceil($totalCount / $perPage);
            $currentPage = (int) $page;
            $from = (($currentPage - 1) * $perPage) + 1;
            $to = min($currentPage * $perPage, $totalCount);

            return $this->successResponse([
                'data' => $transformedTransactions,
                'current_page' => $currentPage,
                'per_page' => $perPage,
                'total' => $totalCount,
                'last_page' => $totalPages,
                'from' => $from,
                'to' => $to,
            ], 'Transaction history fetched successfully');

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch transaction history: ' . $e->getMessage());
        }
    }

    public function getTransactionDetails(Request $request, $id)
    {
        // Check if customer is authenticated
        if (!$request->user()) {
            return $this->errorResponse('Customer authentication required', 401);
        }

        $customer = $request->user();
        $customerId = $customer->id;

        try {
            // Get the transaction type and source from the request
            $sourceType = $request->get('source_type', 'booking'); // Default to booking
            $type = $request->get('type', 'Service'); // Default to Service

            if ($sourceType === 'booking') {
                // Get booking details
                $booking = Booking::with(['branch:id,branch_name'])
                    ->where('id', $id)
                    ->where('customer_id', $customerId)
                    ->where('del_status', 'Live')
                    ->first();

                if (!$booking) {
                    return $this->errorResponse('Transaction not found', 404);
                }

                // Get booking details (services)
                $bookingDetails = DB::table('booking_details')
                    ->join('items', 'booking_details.item_id', '=', 'items.id')
                    ->where('booking_details.booking_id', $booking->id)
                    ->where('booking_details.del_status', 'Live')
                    ->select(
                        'items.id as item_id',
                        'items.name as item_name',
                        'items.description as item_description',
                        'booking_details.quantity',
                        'booking_details.price as item_price',
                        'booking_details.total_price',
                        'booking_details.start_time',
                        'booking_details.end_time'
                    )
                    ->get();

                // Get payment method
                $paymentMethod = PaymentMethod::find($booking->payment_method);

                return $this->successResponse([
                    'id' => $booking->id,
                    'transaction_id' => $booking->reference_no,
                    'type' => 'Service',
                    'date' => $booking->date ?? $booking->created_at->format('Y-m-d'),
                    'status' => $booking->status,
                    'payment_method' => $paymentMethod ? $paymentMethod->name : 'N/A',
                    'payment_status' => $booking->payment_status,
                    'subtotal' => $booking->subtotal ?? 0,
                    'tax_amount' => $booking->tax_amount ?? 0,
                    'total_amount' => $booking->total_amount ?? 0,
                    'branch' => [
                        'id' => $booking->branch_id,
                        'name' => $booking->branch ? $booking->branch->branch_name : 'N/A'
                    ],
                    'note' => $booking->note,
                    'created_at' => $booking->created_at->format('Y-m-d H:i:s'),
                    'items' => $bookingDetails->map(function ($detail) {
                        return [
                            'id' => $detail->item_id,
                            'name' => $detail->item_name,
                            'description' => $detail->item_description,
                            'quantity' => $detail->quantity,
                            'price' => $detail->item_price,
                            'total_price' => $detail->total_price,
                            'start_time' => $detail->start_time,
                            'end_time' => $detail->end_time
                        ];
                    })
                ], 'Transaction details fetched successfully');

            } else {
                // Get sale details
                $sale = Sale::where('id', $id)
                    ->where('customer_id', $customerId)
                    ->where('del_status', 'Live')
                    ->where('order_from', 'Website')
                    ->first();

                if (!$sale) {
                    return $this->errorResponse('Transaction not found', 404);
                }

                // Get sale details (products)
                $saleDetails = DB::table('sale_details')
                    ->join('items', 'sale_details.item_id', '=', 'items.id')
                    ->where('sale_details.sale_id', $sale->id)
                    ->where('sale_details.del_status', 'Live')
                    ->select(
                        'items.id as item_id',
                        'items.name as item_name',
                        'items.description as item_description',
                        'items.photo as item_image',
                        'sale_details.quantity',
                        'sale_details.unit_price',
                        'sale_details.subtotal',
                        'sale_details.total_tax',
                        'sale_details.total_payable'
                    )
                    ->get();

                // Get payment method
                $paymentMethod = PaymentMethod::find($sale->payment_method_id);

                return $this->successResponse([
                    'id' => $sale->id,
                    'transaction_id' => $sale->reference_no,
                    'type' => 'Product',
                    'date' => $sale->order_date ?? $sale->created_at->format('Y-m-d'),
                    'status' => $sale->order_status ?? 'Pending',
                    'payment_method' => $paymentMethod ? $paymentMethod->name : 'N/A',
                    'total_tax' => $sale->total_tax ?? 0,
                    'total_payable' => $sale->total_payable ?? 0,
                    'created_at' => $sale->created_at->format('Y-m-d H:i:s'),
                    'items' => $saleDetails->map(function ($detail) {
                        return [
                            'id' => $detail->item_id,
                            'name' => $detail->item_name,
                            'description' => $detail->item_description,
                            'image' => $detail->item_image ?? asset('assets/images/system-config/default-picture.png'),
                            'quantity' => $detail->quantity,
                            'unit_price' => $detail->unit_price,
                            'subtotal' => $detail->subtotal,
                            'total_tax' => $detail->total_tax,
                            'total_payable' => $detail->total_payable
                        ];
                    })
                ], 'Transaction details fetched successfully');
            }

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch transaction details: ' . $e->getMessage());
        }
    }

    public function getCustomerPackageOrders(Request $request)
    {
        // Check if customer is authenticated
        if (!$request->user()) {
            return $this->errorResponse('Customer authentication required', 401);
        }

        $customer = $request->user();
        $customerId = $customer->id;

        try {
            $perPage = $request->get('per_page', 10);
            $search = $request->get('search', '');

            // Build query for sales (package orders) - joining with items to filter by package type
            $query = Sale::select(
                    'sales.id',
                    'sales.reference_no',
                    'sales.total_payable',
                    'sales.created_at',
                    'sales.order_status'
                )
                ->join('sale_details', 'sales.id', '=', 'sale_details.sale_id')
                ->join('items', 'sale_details.item_id', '=', 'items.id')
                ->where('sales.customer_id', $customerId)
                ->where('sales.del_status', 'Live')
                ->where('items.type', 'Package')
                ->where('items.del_status', 'Live')
                ->where('sale_details.del_status', 'Live')
                ->groupBy('sales.id', 'sales.reference_no', 'sales.total_payable', 'sales.created_at', 'sales.order_status');

            // Apply search filter only if search is not empty
            if (!empty($search)) {
                $query->where(function($q) use ($search) {
                    $q->where('sales.reference_no', 'LIKE', "%{$search}%")
                      ->orWhere('sales.order_status', 'LIKE', "%{$search}%")
                      ->orWhere('items.name', 'LIKE', "%{$search}%");
                });
            }

            // Get paginated results
            $packageOrders = $query->orderBy('sales.created_at', 'desc')
                ->paginate($perPage);

            // Transform the data with package information
            $packageOrders->getCollection()->transform(function ($sale) {
                // Get the first package from this sale for display
                $packageItem = DB::table('sale_details')
                    ->join('items', 'sale_details.item_id', '=', 'items.id')
                    ->join('branches', 'sale_details.branch_id', '=', 'branches.id')
                    ->where('sale_details.sale_id', $sale->id)
                    ->where('items.type', 'Package')
                    ->where('items.del_status', 'Live')
                    ->where('sale_details.del_status', 'Live')
                    ->select('items.name as package_name', 'items.description', 'items.duration', 'items.duration_type', 'branches.branch_name')
                    ->first();

                // Calculate end date (assuming 3 months package duration)
                $startDate = $sale->created_at;
                if($packageItem->duration_type == 'Hour'){
                    $endDate = $startDate->copy()->addHours((int)$packageItem->duration);
                }else if($packageItem->duration_type == 'Day'){
                    $endDate = $startDate->copy()->addDays((int)$packageItem->duration);
                }else if($packageItem->duration_type == 'Minute'){
                    $endDate = $startDate->copy()->addMinutes((int)$packageItem->duration);
                }

                return [
                    'id' => $sale->id,
                    'order_id' => $sale->reference_no ?? 'N/A',
                    'package_name' => $packageItem ? $packageItem->package_name : 'N/A',
                    'duration' => $packageItem ? $packageItem->duration : 'N/A',
                    'duration_type' => $packageItem ? $packageItem->duration_type : 'N/A',
                    'end_date' => $endDate,
                    'amount' => $sale->total_payable ?? 0,
                    'branch' => $packageItem ? $packageItem->branch_name : 'N/A', // Default branch name, can be customized
                    'status' => $this->getPackageStatus($sale, $endDate),
                    'created_at' => $sale->created_at
                ];
            });

            return $this->successResponse([
                'data' => $packageOrders->items(),
                'current_page' => $packageOrders->currentPage(),
                'per_page' => $packageOrders->perPage(),
                'total' => $packageOrders->total(),
                'last_page' => $packageOrders->lastPage(),
                'from' => $packageOrders->firstItem(),
                'to' => $packageOrders->lastItem(),
            ], 'Package orders fetched successfully');

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch package orders: ' . $e->getMessage() . ' Line: ' . $e->getLine());
        }
    }

    public function getPackageOrderDetails(Request $request, $id)
    {
        // Check if customer is authenticated
        if (!$request->user()) {
            return $this->errorResponse('Customer authentication required', 401);
        }

        $customer = $request->user();
        $customerId = $customer->id;

        try {


            // Get sale with package items
            $sale = Sale::with('branch:id,branch_name')
                ->where('id', $id)
                ->where('customer_id', $customerId)
                ->where('del_status', 'Live')
                ->first();

            if (!$sale) {
                return $this->errorResponse('Package order not found', 404);
            }

            // Verify this sale contains package items
            $hasPackageItems = DB::table('sale_details')
                ->join('items', 'sale_details.item_id', '=', 'items.id')
                ->where('sale_details.sale_id', $sale->id)
                ->where('items.type', 'Package')
                ->where('items.del_status', 'Live')
                ->where('sale_details.del_status', 'Live')
                ->exists();

            if (!$hasPackageItems) {
                return $this->errorResponse('Package order not found', 404);
            }

            // Get package details from sale_details
            $saleDetail = SaleDetail::where('sale_id', $sale->id)
                ->whereHas('item', function($q) { $q->where('type', 'Package'); })
                ->first();

            if (!$saleDetail) {
                return $this->errorResponse('Package not found for this sale.', 404);
            }

            $mainPackage = Item::find($saleDetail->item_id);
            if (!$mainPackage) {
                return $this->errorResponse('Package details not found', 404);
            }


            // Calculate package duration and dates
            $startDate = $sale->created_at;
            if($mainPackage->duration_type == 'Hour'){
                $endDate = $startDate->copy()->addHours((int)$mainPackage->duration);
            }else if($mainPackage->duration_type == 'Day'){
                $endDate = $startDate->copy()->addDays((int)$mainPackage->duration);
            }else if($mainPackage->duration_type == 'Minute'){
                $endDate = $startDate->copy()->addMinutes((int)$mainPackage->duration);
            }
            
            // Get included items/services in the package with proper usage calculation
            $includedItems = ItemDetail::where('item_relation_id', $mainPackage->id)
                ->with('item')
                ->get();

            // For each included item, get usage and remaining
            $services = $includedItems->map(function($detail) use ($sale, $mainPackage) {
                $usedQty = PackageUsagesSummary::where('customer_id', $sale->customer_id)
                    ->where('package_id', $mainPackage->id)
                    ->where('sale_id', $sale->id)
                    ->where('package_item_id', $detail->item_id)
                    ->sum('usages_qty');
                $remaining = ($detail->quantity ?? 0) - $usedQty;
                return [
                    'id' => $detail->item_id,
                    'name' => $detail->item->name ?? 'Service',
                    'quantity' => $detail->quantity ?? 0,
                    'used' => $usedQty,
                    'remaining' => $remaining,
                ];
            });
            

            // Usage summary (all usages for this customer/package)
            $usages = PackageUsagesSummary::where('customer_id', $sale->customer_id)
                        ->where('package_id', $mainPackage->id)
                        ->where('sale_id', $sale->id)
                        ->with('item')
                        ->where('del_status', 'Live')
                        ->orderBy('usages_date', 'desc')
                        ->get()
                        ->map(function($usage) {
                            return [
                                'service_item' => $usage->item->name ?? '',
                                'usages_date' => $usage->usages_date,
                                'usages_time' => $usage->usages_time,
                                'taken_qty' => $usage->usages_qty,
                            ];
                        });

            // Prepare response data
            $packageOrderData = [
                'id' => $sale->id,
                'order_id' => $sale->reference_no,
                'package_name' => $mainPackage->name,
                'package_code' => $mainPackage->code ?? 'N/A',
                'package_price' => $mainPackage->sale_price,
                'duration' => $mainPackage->duration ?? '',
                'duration_type' => $mainPackage->duration_type ?? '',
                'branch' => $sale->branch->branch_name ?? 'N/A', 
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'status' => $this->getPackageStatus($sale, $endDate),
                'created_at' => $sale->created_at->format('Y-m-d H:i:s'),
                'items' => $services,
                'usages' => $usages
            ];

            return $this->successResponse($packageOrderData, 'Package order details fetched successfully');

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch package order details: ' . $e->getMessage());
        }
    }

    /**
     * Helper method to determine package status
     */
    private function getPackageStatus($sale, $endDate)
    {
        $now = now();
        $orderStatus = $sale->order_status ?? 'Pending';
        
        // If order is cancelled, return cancelled
        if (strtolower($orderStatus) === 'cancelled') {
            return 'Cancelled';
        }
        
        // If end date has passed, return expired
        if ($now->greaterThan($endDate)) {
            return 'Expired';
        }
        
        // If order is completed or confirmed, return active
        if (in_array(strtolower($orderStatus), ['completed', 'confirmed'])) {
            return 'Active';
        }
        
        // Default to pending
        return 'Pending';
    }



    // Fetch Testimonials Query
    public function getTestimonials()
    {
        try {
            // Get testimonials with customer data using DB join query (max 10 records)
            $testimonials = DB::table('rattings')
                ->join('customers', 'rattings.customer_id', '=', 'customers.id')
                ->where('rattings.company_id', 1)
                ->where('rattings.del_status', 'Live')
                ->where('customers.del_status', 'Live')
                ->whereNotNull('rattings.comment')
                ->where('rattings.comment', '!=', '')
                ->select(
                    'rattings.id',
                    'rattings.rating',
                    'rattings.comment as review',
                    'rattings.created_at',
                    'customers.name',
                    'customers.photo',
                    'customers.email'
                )
                ->orderBy('rattings.created_at', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($testimonial) {
                    return [
                        'id' => $testimonial->id,
                        'name' => $testimonial->name ?? 'Anonymous',
                        'rating' => (float) $testimonial->rating,
                        'review' => $testimonial->review,
                        'date' => \Carbon\Carbon::parse($testimonial->created_at)->format('M d, Y'),
                        'photo_url' => $testimonial->photo 
                            ? asset('assets/images/' . $testimonial->photo)
                            : asset('assets/images/system-config/default-picture.png'),
                        'location' => 'Customer' // Default location, can be customized later
                    ];
                });

            return $this->successResponse($testimonials, 'Testimonials fetched successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch testimonials: ' . $e->getMessage());
        }
    }

    public function getMemberDetails($id)
    {
        $member = User::find($id);
        $member->service_done = SaleDetail::where('employee_id', $member->id)->where('del_status', 'Live')->count();
        $member->happy_customers = Ratting::where('employee_id', $member->id)
        ->whereNotNull('rating')
        ->whereNotNull('customer_id')
        ->where('del_status', 'Live')
        ->count();
        $member->customer_rattings = Ratting::where('employee_id', $member->id)->where('del_status', 'Live')->avg('rating');
        return $this->successResponse($member, 'Member details fetched successfully');
    }

    public function getWorkingProcess()
    {
        $workingProcess = WorkingProcess::where('del_status', 'Live')
        ->where('company_id', 1)
        ->orderBy('position', 'asc')
        ->limit(3)->get();
        
        return $this->successResponse($workingProcess, 'Working process fetched successfully');
    }

    public function getGallery()
    {
        $gallery = Portfolio::where('del_status', 'Live')
        ->where('company_id', 1)
        ->orderBy('position', 'asc')
        ->limit(5)->get();
        return $this->successResponse($gallery, 'Gallery fetched successfully');
    }

    public function getDeliveryAreas()
    {
        $deliveryAreas = DeliveryArea::where('del_status', 'Live')
        ->where('company_id', 1)
        ->get();
        return $this->successResponse($deliveryAreas, 'Delivery areas fetched successfully');
    }
}

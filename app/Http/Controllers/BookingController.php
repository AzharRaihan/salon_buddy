<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Booking;
use App\Models\Customer;
use App\Services\NotificationService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Models\BookingDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class BookingController extends Controller
{
    use ApiResponse;

    protected $notificationService;

    public function __construct()
    {
        $this->notificationService = new NotificationService();
    }

    public function updateBookingDate(Request $request)
    {
        $booking = Booking::findOrFail($request->booking_id);
        if (!$booking) {
            return $this->errorResponse('Booking not found', 404);
        }
        $booking->date = $request->date;
        $booking->save();
        return $this->successResponse($booking, 'Booking date updated successfully');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Booking::query();

        $query->join('customers', 'bookings.customer_id', '=', 'customers.id')
            ->join('branches', 'bookings.branch_id', '=', 'branches.id')
            ->select('bookings.*', 'customers.name as customer_name', 'customers.phone as customer_phone', 'branches.branch_name')
            ->where('bookings.company_id', Auth::user()->company_id)
            ->where('bookings.del_status', 'Live');

        // Search functionality
        if ($request->has('q') && !empty($request->q)) {
            $query->where(function ($q) use ($request) {
                $q->where('bookings.reference_no', 'like', '%' . $request->q . '%')
                ->orWhere('customers.name', 'like', '%' . $request->q . '%')
                ->orWhere('branches.branch_name', 'like', '%' . $request->q . '%');
            });
        }

        // Sorting
        if (!empty($request->sortBy)) {
            $direction = $request->orderBy === 'asc' ? 'asc' : 'desc';
            $query->orderBy($request->sortBy, $direction);
        } else {
            $query->orderBy('bookings.id', 'desc');
        }

        // Pagination
        $perPage = $request->itemsPerPage ?? 10;
        $bookings = $query->paginate($perPage);

        return $this->successResponse([
            'bookings' => $bookings->items(),
            'total' => $bookings->total(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            // Validate request
            $validationRules = [
                'reference_no' => 'required|string|unique:bookings,reference_no',
                'customer_id' => 'required|exists:customers,id',
                'branch_id' => 'required|exists:branches,id',
                'date' => 'required|date',
                'note' => 'nullable|string',
                'status' => 'required|in:Pending,Accepted,Completed,Rejected',
                'booking_details' => 'required|array|min:1',
                'booking_details.*.item_id' => 'required|exists:items,id',
                'booking_details.*.start_time' => 'required',
                'booking_details.*.end_time' => 'required',
                'booking_details.*.quantity' => 'required|integer|min:1',
                'booking_details.*.service_seller_id' => 'required|exists:users,id',
            ];

            $validator = Validator::make($request->all(), $validationRules);
            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $validatedData = $validator->validated();

            // Create booking
            $booking = Booking::create([
                'reference_no' => $request->reference_no,
                'customer_id' => $request->customer_id,
                'branch_id' => $request->branch_id,
                'date' => $request->date,
                'note' => $request->note,
                'status' => $request->status,
                'user_id' => Auth::id(),
                'company_id' => Auth::user()->company_id,
            ]);

            // Create booking details
            foreach ($request->booking_details as $detail) {
                BookingDetail::create([
                    'booking_id' => $booking->id,
                    'item_id' => $detail['item_id'],
                    'start_time' => $detail['start_time'],
                    'end_time' => $detail['end_time'],
                    'quantity' => $detail['quantity'],
                    'service_seller_id' => $detail['service_seller_id'],
                    'user_id' => Auth::id(),
                    'company_id' => Auth::user()->company_id,
                ]);
            }

            DB::commit();

            // Send notifications
            $notificationData = [
                'send_sms' => $request->send_sms ?? false,
                'send_email' => $request->send_email ?? false,
                'send_whatsapp' => $request->send_whatsapp ?? false,
                'booking_id' => $booking->id,
            ];

            $notificationResult = $this->notificationService->sendNotifications($notificationData, 'Booking');

            if ($notificationResult['status'] === 'Success') {
                return $this->successResponse($booking, 'Booking created successfully');
            } else {
                return $this->successResponse($booking, 'Booking created successfully but ' . $notificationResult['message']);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create booking',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Booking $booking)
    {
        // $booking = Booking::with(['customer:id,name', 'branch:id,branch_name', 'bookingDetails.servicePackage:id,name', 'bookingDetails.serviceSeller:id,name'])
        //     ->findOrFail($booking);
        
        // if (!$booking) {
        //     return $this->errorResponse('Booking not found', 404);
        // }
        
        return $this->successResponse([
            'booking' => $booking->load('customer:id,name', 'branch:id,branch_name',  'bookingDetails.items:id,name,code', 'bookingDetails.serviceSeller:id,name'),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            DB::beginTransaction();
            
            $booking = Booking::findOrFail($id);
            
            // Validate request
            $validationRules = [
                'customer_id' => 'required|exists:customers,id',
                'branch_id' => 'required|exists:branches,id',
                'date' => 'required|date',
                'note' => 'nullable|string',
                'status' => 'required|in:Pending,Accepted,Completed,Rejected',
                'booking_details' => 'required|array|min:1',
                'booking_details.*.item_id' => 'required|exists:items,id',
                'booking_details.*.start_time' => 'required',
                'booking_details.*.end_time' => 'required',
                'booking_details.*.quantity' => 'required|integer|min:1',
                'booking_details.*.service_seller_id' => 'required|exists:users,id',
            ];

            $validator = Validator::make($request->all(), $validationRules);
            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            // Update booking
            $booking->update([
                'customer_id' => $request->customer_id,
                'branch_id' => $request->branch_id,
                'date' => $request->date,
                'note' => $request->note,
                'status' => $request->status,
                'updated_at' => now(),
                'user_id' => Auth::id(),
                'company_id' => Auth::user()->company_id,
            ]);

            // Delete existing booking details
            $booking->bookingDetails()->delete();

            // Create new booking details
            foreach ($request->booking_details as $detail) {
                BookingDetail::create([
                    'booking_id' => $booking->id,
                    'item_id' => $detail['item_id'],
                    'start_time' => $detail['start_time'],
                    'end_time' => $detail['end_time'],
                    'quantity' => $detail['quantity'],
                    'service_seller_id' => $detail['service_seller_id'],
                    'user_id' => Auth::id(),
                    'company_id' => Auth::user()->company_id,
                ]);
            }

            DB::commit();

            // Send notifications
            $notificationData = [
                'send_sms' => $request->send_sms ?? false,
                'send_email' => $request->send_email ?? false,
                'send_whatsapp' => $request->send_whatsapp ?? false,
                'booking_id' => $booking->id,
            ];
            $this->notificationService->sendNotifications($notificationData, 'Booking');
            return $this->successResponse($booking, 'Booking updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Failed to update booking: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            DB::beginTransaction();

            $booking = Booking::findOrFail($id);
            
            // Soft delete booking and its details
            $booking->update(['del_status' => 'Deleted']);
            $booking->bookingDetails()->update(['del_status' => 'Deleted']);

            DB::commit();

            return $this->successResponse(null, 'Booking deleted successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete booking',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update booking status with notifications
     */
    public function updateStatus(Request $request, string $id)
    {
        try {
            $booking = Booking::findOrFail($id);
            
            // Validate request
            $validationRules = [
                'status' => 'required|in:Pending,Accepted,Completed,Rejected',
                'send_sms' => 'boolean',
                'send_email' => 'boolean',
                'send_whatsapp' => 'boolean',
            ];

            $validator = Validator::make($request->all(), $validationRules);
            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            // Update booking status
            $booking->update([
                'status' => $request->status,
                'updated_at' => now(),
            ]);

            // Send notifications if requested
            $notificationData = [
                'send_sms' => $request->send_sms ?? false,
                'send_email' => $request->send_email ?? false,
                'send_whatsapp' => $request->send_whatsapp ?? false,
                'booking_id' => $booking->id,
            ];

            $notificationResult = $this->notificationService->sendNotifications($notificationData, 'Booking');

            if ($notificationResult['status'] === 'Success') {
                return $this->successResponse($booking, 'Booking status updated successfully');
            } else {
                return $this->successResponse($booking, 'Booking status updated successfully but ' . $notificationResult['message']);
            }

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update booking status: ' . $e->getMessage());
        }
    }

    /**
     * Get booking details for POS system
     */
    public function getBookingDetailsForPOS(string $id)
    {
        try {
            $booking = Booking::with(['customer:id,name,phone,email,address', 'branch:id,branch_name'])
                ->where('id', $id)
                ->where('company_id', Auth::user()->company_id)
                ->where('del_status', 'Live')
                ->first();

            if (!$booking) {
                return $this->errorResponse('Booking not found', 404);
            }

            // Get booking details with item information
            $bookingDetails = DB::table('booking_details')
                ->join('items', 'booking_details.item_id', '=', 'items.id')
                ->where('booking_details.booking_id', $booking->id)
                ->where('booking_details.del_status', 'Live')
                ->select(
                    'booking_details.id',
                    'booking_details.item_id',
                    'booking_details.quantity',
                    'booking_details.start_time',
                    'booking_details.end_time',
                    'items.name as service_name',
                    'items.sale_price as service_price',
                    'items.description as service_description'
                )
                ->get();

            // Calculate totals
            $subtotal = $bookingDetails->sum(function ($detail) {
                return $detail->service_price * $detail->quantity;
            });

            // Get tax rate from settings (you may need to adjust this based on your tax settings)
            $taxRate = 0.05; // 5% tax rate - you should get this from your settings
            $tax = $subtotal * $taxRate;
            $grandTotal = $subtotal + $tax;

            $bookingData = [
                'id' => $booking->id,
                'reference_no' => $booking->reference_no,
                'date' => $booking->date,
                'status' => $booking->status,
                'note' => $booking->note,
                'customer' => [
                    'id' => $booking->customer->id,
                    'name' => $booking->customer->name,
                    'phone' => $booking->customer->phone,
                    'email' => $booking->customer->email,
                    'address' => $booking->customer->address,
                ],
                'branch' => [
                    'id' => $booking->branch->id,
                    'name' => $booking->branch->branch_name,
                ],
                'services' => $bookingDetails->map(function ($detail) {
                    return [
                        'id' => $detail->item_id,
                        'name' => $detail->service_name,
                        'price' => $detail->service_price,
                        'quantity' => $detail->quantity,
                        'subtotal' => $detail->service_price * $detail->quantity,
                        'start_time' => $detail->start_time,
                        'end_time' => $detail->end_time,
                    ];
                }),
            ];

            return $this->successResponse($bookingData, 'Booking details fetched successfully');

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch booking details: ' . $e->getMessage());
        }
    }


}

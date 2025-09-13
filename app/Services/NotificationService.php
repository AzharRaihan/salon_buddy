<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\Booking;
use App\Models\PackageUsagesSummary;
use App\Models\Customer;
use App\Models\Branch;
use App\Models\Company;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    protected $emailService;
    protected $smsService;
    protected $whatsappService;

    public function __construct()
    {
        $this->emailService = new EmailService();
        $this->smsService = new SMSService();
        $this->whatsappService = new WhatsappService();
    }

    /**
     * Send notifications for any entity type
     *
     * @param array $notificationData
     * @param string $entityType
     * @return array
     */
    public function sendNotifications($notificationData, $entityType)
    {
        try {
            $entity = $this->getEntity($notificationData, $entityType);
            
            if (!$entity) {
                return [
                    'status' => 'Error',
                    'message' => 'Entity not found'
                ];
            }

            $customer = $entity->customer;
            $branch = $entity->branch;

            if (!$customer || !$branch) {
                return [
                    'status' => 'Error',
                    'message' => 'Customer or branch information not found'
                ];
            }

            $message = $this->buildNotificationMessage($entity, $customer, $branch, $entityType);
            $responses = [];

            // Send WhatsApp message
            if ($notificationData['send_whatsapp'] && $customer->phone) {
                $responses['whatsapp'] = $this->whatsappService->sendWhatsapp($customer->phone, $message);
            }

            // Send Email
            if ($notificationData['send_email'] && $customer->email) {
                $emailSubject = $this->buildEmailSubject($entity, $entityType);
                $responses['email'] = $this->emailService->sendEmail(
                    $customer->email,
                    $emailSubject,
                    $entity->id,
                    $entityType
                );
            }

            // Send SMS
            if ($notificationData['send_sms'] && $customer->phone) {
                $responses['sms'] = $this->smsService->sendSMS($customer->phone, $message);
            }

            // Return overall status
            return $this->processNotificationResponses($responses);

        } catch (\Exception $e) {
            Log::error('Notification sending failed', [
                'entity_type' => $entityType,
                'exception' => $e->getMessage()
            ]);
            return [
                'status' => 'Error',
                'message' => 'Notification sending failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get entity based on type and ID
     *
     * @param array $notificationData
     * @param string $entityType
     * @return mixed
     */
    private function getEntity($notificationData, $entityType)
    {
        $idField = $this->getIdField($entityType);
        $entityId = $notificationData[$idField] ?? null;

        if (!$entityId) {
            return null;
        }

        switch ($entityType) {
            case 'Booking':
                return Booking::with(['customer', 'branch'])->find($entityId);
            case 'Sale':
                return Sale::with(['customer', 'branch'])->find($entityId);
            case 'Package Usage':
                return PackageUsagesSummary::with(['customer', 'branch'])->find($entityId);
            default:
                return null;
        }
    }

    /**
     * Get the ID field name for the entity type
     *
     * @param string $entityType
     * @return string
     */
    private function getIdField($entityType)
    {
        switch ($entityType) {
            case 'Booking':
                return 'booking_id';
            case 'Sale':
                return 'sale_id';
            case 'Package Usage':
                return 'usage_id';
            default:
                return 'id';
        }
    }

    /**
     * Build notification message based on entity type
     *
     * @param mixed $entity
     * @param Customer $customer
     * @param Branch $branch
     * @param string $entityType
     * @return string
     */
    private function buildNotificationMessage($entity, $customer, $branch, $entityType)
    {
        switch ($entityType) {
            case 'Booking':
                return $this->buildBookingMessage($entity, $customer, $branch);
            case 'Sale':
                return $this->buildSaleMessage($entity, $customer, $branch);
            case 'Package Usage':
                return $this->buildPackageUsageMessage($entity, $customer, $branch);
            default:
                return "Dear {$customer->name}, your notification from {$branch->branch_name}.";
        }
    }

    /**
     * Build booking notification message
     *
     * @param Booking $booking
     * @param Customer $customer
     * @param Branch $branch
     * @return string
     */
    private function buildBookingMessage($booking, $customer, $branch)
    {
        $statusText = $this->getBookingStatusText($booking->status);
        
        $message = "Dear {$customer->name}, your booking at {$branch->branch_name} on {$booking->date} has been {$statusText}. Please find your booking details below:\n\n" .
                   "Booking Reference: {$booking->reference_no}\n" .
                   "Booking Date: {$booking->date}\n" .
                   "Booking Status: {$booking->status}\n";

        if ($booking->note) {
            $message .= "Note: {$booking->note}\n";
        }

        if ($booking->status === 'Rejected') {
            $message .= "\nIf you have any questions, feel free to contact us.\n";
        }

        $message .= "\nThank you for choosing us!";

        return $message;
    }

    /**
     * Build sale notification message
     *
     * @param Sale $sale
     * @param Customer $customer
     * @param Branch $branch
     * @return string
     */
    private function buildSaleMessage($sale, $customer, $branch)
    {
        $statusText = ucfirst($sale->order_status);

        $message = "Dear {$customer->name}, your purchase from {$branch->branch_name} has been {$statusText}. Please find your sale details below:\n\n" .
                "Invoice Reference: {$sale->reference_no}\n" .
                "Sale Date: {$sale->order_date}\n" .
                "Order Status: {$sale->order_status}\n" .
                "Total Amount: {$sale->total_payable}\n";

        $message .= "\nThank you for shopping with us!";

        return $message;
    }

    /**
     * Build package usage notification message
     *
     * @param PackageUsagesSummary $usage
     * @param Customer $customer
     * @param Branch $branch
     * @return string
     */
    private function buildPackageUsageMessage($usage, $customer, $branch)
    {
        $message = "Dear {$customer->name}, your package usage at {$branch->branch_name} on {$usage->usages_date} has been recorded. Please find your package usage details below:\n\n" .
                   "Package Usage Reference: {$usage->id}\n" .
                   "Package Usage Date: {$usage->usages_date}\n" .
                   "Package Usage Time: {$usage->usages_time}\n" .
                   "Usage Quantity: {$usage->usages_qty}\n";

        $message .= "\nThank you for choosing us!";
        return $message;
    }

    /**
     * Build email subject based on entity type
     *
     * @param mixed $entity
     * @param string $entityType
     * @return string
     */
    private function buildEmailSubject($entity, $entityType)
    {
        switch ($entityType) {
            case 'Booking':
                return "Booking {$entity->status} - {$entity->reference_no}";
            case 'Sale':
                return "Your Order ({$entity->reference_no}) - {$entity->order_status}";
            case 'Package Usage':
                return "Package Usage {$entity->usages_qty} - {$entity->id}";
            default:
                return "Notification from Salon Buddy";
        }
    }

    /**
     * Get status text for booking notifications
     *
     * @param string $status
     * @return string
     */
    private function getBookingStatusText($status)
    {
        switch ($status) {
            case 'Accepted':
                return 'confirmed';
            case 'Completed':
                return 'completed successfully';
            case 'Pending':
                return 'received and is currently pending confirmation';
            case 'Rejected':
                return 'rejected';
            default:
                return strtolower($status);
        }
    }

    /**
     * Process notification responses and return overall status
     *
     * @param array $responses
     * @return array
     */
    private function processNotificationResponses($responses)
    {
        if (empty($responses)) {
            return [
                'status' => 'Error',
                'message' => 'No notification methods selected'
            ];
        }

        $successCount = 0;
        $totalCount = count($responses);
        
        foreach ($responses as $response) {
            if (isset($response['status']) && $response['status'] === 'Success') {
                $successCount++;
            }
        }

        if ($successCount === $totalCount) {
            return [
                'status' => 'Success',
                'message' => 'All notifications sent successfully'
            ];
        } elseif ($successCount > 0) {
            return [
                'status' => 'Partial',
                'message' => "{$successCount} out of {$totalCount} notifications sent successfully"
            ];
        } else {
            return [
                'status' => 'Error',
                'message' => 'All notification methods failed'
            ];
        }
    }
}

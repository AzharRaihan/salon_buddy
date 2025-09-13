<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Company;
use App\Models\Item;
use Illuminate\Support\Facades\DB;

class LoyaltyPointService
{
    /**
     * Calculate loyalty points earned for a sale
     */
    public function calculateLoyaltyPointsEarned($items, $customerId)
    {
        // Check if customer is Walk-in Customer
        $customer = Customer::find($customerId);
        if (!$customer || $customer->name === 'Walk-in Customer' || $customer->name === 'Walking-in Customer') {
            return 0;
        }

        $totalPoints = 0;
        
        foreach ($items as $item) {
            $itemRecord = Item::find($item['id']);
            if ($itemRecord && $itemRecord->loyalty_point) {
                $points = $itemRecord->loyalty_point * $item['qty'];
                $totalPoints += $points;
            }
        }
        
        return $totalPoints;
    }

    /**
     * Calculate loyalty points needed to complete a sale
     */
    public function calculateLoyaltyPointsNeeded($totalAmount, $customerId)
    {
        // Check if customer is Walk-in Customer
        $customer = Customer::find($customerId);
        if (!$customer || $customer->name === 'Walk-in Customer' || $customer->name === 'Walking-in Customer') {
            return 0;
        }

        $company = Company::find($customer->company_id);
        if (!$company || !$company->loyalty_rate || $company->loyalty_rate <= 0) {
            return 0;
        }

        // Calculate points needed: total amount / loyalty rate
        return $totalAmount / $company->loyalty_rate;
    }

    /**
     * Check if customer has enough loyalty points
     */
    public function hasEnoughLoyaltyPoints($customerId, $pointsNeeded)
    {
        $customer = Customer::find($customerId);
        if (!$customer) {
            return false;
        }

        return $customer->loyalty_points >= $pointsNeeded;
    }

    /**
     * Get customer's current loyalty points
     */
    public function getCustomerLoyaltyPoints($customerId)
    {
        $customer = Customer::find($customerId);
        return $customer ? $customer->loyalty_points : 0;
    }

    /**
     * Get company loyalty settings
     */
    public function getCompanyLoyaltySettings($companyId)
    {
        $company = Company::find($companyId);
        return [
            'minimum_point_to_redeem' => $company->minimum_point_to_redeem ?? 0,
            'loyalty_rate' => $company->loyalty_rate ?? 0,
        ];
    }

    /**
     * Redeem loyalty points for a customer
     */
    public function redeemLoyaltyPoints($customerId, $pointsToRedeem)
    {
        $customer = Customer::find($customerId);
        if (!$customer) {
            throw new \Exception('Customer not found');
        }

        if ($customer->loyalty_points < $pointsToRedeem) {
            throw new \Exception('Insufficient loyalty points');
        }

        $customer->loyalty_points -= $pointsToRedeem;
        $customer->save();

        return $customer->loyalty_points;
    }

    /**
     * Add loyalty points to a customer
     */
    public function addLoyaltyPoints($customerId, $pointsToAdd)
    {
        $customer = Customer::find($customerId);
        if (!$customer) {
            throw new \Exception('Customer not found');
        }

        $customer->loyalty_points += $pointsToAdd;
        $customer->save();

        return $customer->loyalty_points;
    }

    /**
     * Calculate loyalty point value in currency
     */
    public function calculateLoyaltyPointValue($points, $companyId)
    {
        $company = Company::find($companyId);
        if (!$company || !$company->loyalty_rate || $company->loyalty_rate <= 0) {
            return 0;
        }

        return $points * $company->loyalty_rate;
    }

    /**
     * Check if loyalty points can be used for payment method
     */
    public function canUseLoyaltyPoints($paymentMethodId)
    {
        $paymentMethod = DB::table('payment_methods')->find($paymentMethodId);
        return $paymentMethod && $paymentMethod->account_type === 'Loyalty Point';
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CommissionReportController extends Controller
{
    /**
     * Display the commission report with optional filtering.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Order::with(['purchaser', 'purchaser.referrer', 'items.product']);

        // Apply filters
        if ($request->filled('distributor')) {
            $distributor = $request->input('distributor');
            $query->whereHas('purchaser.referrer', function($q) use ($distributor) {
                $q->where('id', $distributor)
                  ->orWhere('first_name', 'like', "%{$distributor}%")
                  ->orWhere('last_name', 'like', "%{$distributor}%");
            });
        }

        if ($request->filled('date_from')) {
            $query->where('order_date', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->where('order_date', '<=', $request->input('date_to'));
        }

        $orders = $query->get();

        // Process orders to add commission data
        $processedOrders = [];
        foreach ($orders as $order) {
            $purchaser = $order->purchaser;
            
            // Skip if purchaser doesn't exist
            if (!$purchaser) {
                continue;
            }
            
            $distributor = $purchaser->referrer;
            
            // Skip if purchaser has no referrer
            if (!$distributor) {
                continue;
            }
            
            // Check if referrer is a distributor
            $isDistributor = $distributor->isDistributor();
            
            // Check if purchaser is a customer
            $isCustomer = $purchaser->isCustomer();
            
            // Count referred distributors by the order date
            $referredDistributorsCount = $distributor->getReferredDistributorsCount($order->order_date);
            
            // Calculate commission percentage based on referred distributors
            $percentage = $this->getCommissionPercentage($referredDistributorsCount);
            
            // Calculate order total
            $orderTotal = $order->getOrderTotal();
            
            // Calculate commission - only if the referrer is a Distributor and the purchaser is a Customer
            $commission = ($isDistributor && $isCustomer) ? $order->calculateCommission($percentage) : 0;
            
            $processedOrders[] = [
                'invoice' => $order->invoice_number,
                'purchaser' => $purchaser->first_name . ' ' . $purchaser->last_name,
                'distributor' => $isDistributor ? $distributor->first_name . ' ' . $distributor->last_name : '',
                'referred_distributors' => $referredDistributorsCount,
                'order_date' => $order->order_date,
                'percentage' => $isDistributor && $isCustomer ? $percentage : 0,
                'order_total' => $orderTotal,
                'commission' => $commission,
                'order' => $order
            ];
        }

        return view('commission-report', [
            'orders' => $processedOrders,
            'request' => $request
        ]);
    }

    /**
     * Get the commission percentage based on the number of referred distributors.
     *
     * @param  int  $referredDistributorsCount
     * @return int
     */
    private function getCommissionPercentage($referredDistributorsCount)
    {
        // Based on the example in the requirements
        // John (with 8 referred distributors) earned 10% commission
        if ($referredDistributorsCount >= 10) {
            return 15;
        } elseif ($referredDistributorsCount >= 5) {
            return 10;
        } else {
            return 5;
        }
    }
} 
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

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('order_date', [
                $request->input('start_date'),
                $request->input('end_date')
            ]);
        }

        $orders = $query->get();

        $reportData = [];
        foreach ($orders as $order) {
            $purchaser = $order->purchaser;
            $referrer = $purchaser ? $purchaser->referrer : null;
            
            // Skip if purchaser has no referrer
            if (!$referrer) {
                continue;
            }
            
            // Check if referrer is a distributor
            $isDistributor = $referrer->isDistributor();
            
            // Check if purchaser is a customer
            $isCustomer = $purchaser->isCustomer();
            
            // Calculate order total
            $orderTotal = $order->getOrderTotal();
            
            // Calculate referred distributors count at the time of order
            $referredDistributorsCount = $referrer->countReferredDistributorsByDate($order->order_date);
            
            // Calculate commission percentage based on referred distributors count
            $percentage = 0;
            if ($isDistributor && $isCustomer) {
                if ($referredDistributorsCount >= 32) {
                    $percentage = 30;
                } elseif ($referredDistributorsCount >= 22) {
                    $percentage = 25;
                } elseif ($referredDistributorsCount >= 16) {
                    $percentage = 20;
                } elseif ($referredDistributorsCount >= 11) {
                    $percentage = 15;
                } elseif ($referredDistributorsCount >= 5) {
                    $percentage = 10;
                } elseif ($referredDistributorsCount >= 0) {
                    $percentage = 5;
                }
            }
            
            // Calculate commission amount
            $commission = ($percentage / 100) * $orderTotal;
            
            $reportData[] = [
                'invoice' => $order->invoice_number,
                'purchaser' => $purchaser->first_name . ' ' . $purchaser->last_name,
                'distributor' => $isDistributor ? $referrer->first_name . ' ' . $referrer->last_name : '',
                'referred_distributors' => $referredDistributorsCount,
                'order_date' => $order->order_date,
                'percentage' => $percentage,
                'order_total' => $orderTotal,
                'commission' => $commission,
                'items' => $order->items,
            ];
        }

        return view('commission-report', [
            'reportData' => $reportData,
            'distributor' => $request->input('distributor', ''),
            'start_date' => $request->input('start_date', ''),
            'end_date' => $request->input('end_date', '')
        ]);
    }
}

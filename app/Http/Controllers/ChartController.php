<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Models\Products;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Bill; // Assuming Employee model for company ID
use App\Models\Bill_payment;
use App\Models\Category;
use App\Models\PaymentMethod;
use App\Models\Service;  // Assuming Category model for categories
use App\Models\Shopping;
use Carbon\Carbon; // For date handling

class ChartController extends Controller
{
   public function chart(Request $request)
    {
        $query = DB::table('bill_details')
            ->join('products', 'products.id', '=', 'bill_details.id_product')
            ->select(
                DB::raw('SUM(bill_details.quantity) as total'),
                DB::raw('SUBSTRING(products.name, 1, 40) as name'), // Limit product name to 40 characters
                'products.code as code'
            )
            ->groupBy('products.id', 'products.name', 'products.code') // Group by id, original name, and code
            ->orderBy('total', 'desc')
            ->limit(10);

        $products = $query->get();

        return response()->json($products, 201);
    }

    // Method for Most Notable Clients
    public function chart2(Request $request)
    {
        $query = DB::table('bills')
            ->join('users', 'users.id', '=', 'bills.id_client')
            ->select(
                DB::raw('SUM(bills.net_amount) as total'), // Sum net_amount for client purchases
                'users.name as name'
            )
            ->where('bills.status', 1) // Assuming status 1 means completed bills, adjust if needed
            ->where('bills.type','!=' ,'PRESUPUESTO') // Assuming you only count sales, adjust or remove if services/other types are included
            ->groupBy('users.id', 'users.name')
            ->orderBy('total', 'desc')
            ->limit(10);

        $users = $query->get();

        return response()->json($users, 201);
    }
    public function monthlySummary(Request $request)
    {

  
        $endDate = Carbon::now();
        $startDate = Carbon::now()->subMonths(9)->startOfMonth(); // Últimos 10 meses (incluyendo el actual)

        $months = [];
        $currentMonth = $startDate->copy();
        while ($currentMonth->lte($endDate)) {
            $months[] = $currentMonth->format('M Y'); // Ej. "Ene 2023"
            $currentMonth->addMonth();
        }

        // Fetch Bill data (net_amount)
        $billsData = Bill::whereBetween('created_at', [$startDate, $endDate->endOfMonth()])
            ->select(
                DB::raw('DATE_FORMAT(created_at, "%b %Y") as month'),
                DB::raw('SUM(net_amount) as total_net_amount')
            )
            ->groupBy('month')
            ->orderBy('month') // Order by month string to ensure correct chronological order
            ->get()
            ->keyBy('month');

        // Fetch Bill Payments data (amount)
        $billPaymentsData = Bill_payment::whereBetween('created_at', [$startDate, $endDate->endOfMonth()])
            ->select(
                DB::raw('DATE_FORMAT(created_at, "%b %Y") as month'),
                DB::raw('SUM(amount) as total_amount_paid')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->keyBy('month');

        // Fetch Services data (price)
        $servicesData = Service::whereBetween('created_at', [$startDate, $endDate->endOfMonth()])
            ->select(
                DB::raw('DATE_FORMAT(created_at, "%b %Y") as month'),
                DB::raw('SUM(price) as total_service_price')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->keyBy('month');

        // Fetch Shoppings data (total)
        $shoppingsData = Shopping::whereBetween('created_at', [$startDate, $endDate->endOfMonth()])
            ->select(
                DB::raw('DATE_FORMAT(created_at, "%b %Y") as month'),
                DB::raw('SUM(total) as total_shopping_amount')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->keyBy('month');


        // Initialize data series with 0 for all months
        $netAmounts = array_fill_keys($months, 0);
        $amountsPaid = array_fill_keys($months, 0);
        $servicePrices = array_fill_keys($months, 0);
        $shoppingTotals = array_fill_keys($months, 0);

        // Populate data
        foreach ($months as $monthKey) {
            if ($billsData->has($monthKey)) {
                $netAmounts[$monthKey] = (float) $billsData[$monthKey]->total_net_amount;
            }
            if ($billPaymentsData->has($monthKey)) {
                $amountsPaid[$monthKey] = (float) $billPaymentsData[$monthKey]->total_amount_paid;
            }
            if ($servicesData->has($monthKey)) {
                $servicePrices[$monthKey] = (float) $servicesData[$monthKey]->total_service_price;
            }
             if ($shoppingsData->has($monthKey)) {
                $shoppingTotals[$monthKey] = (float) $shoppingsData[$monthKey]->total_shopping_amount;
            }
        }


        $response = [
            'chartTitle' => __('Monthly Sales Overview'),
            'xAxisLabel' => __('Month'),
            'yAxisTitle' => __('Amount'),
            'categories' => array_values($months),
            'series' => [
                [
                    'name' => __('Net Billed Amount'),
                    'data' => array_values($netAmounts),
                    'color' => '#03DAC6' // Color for Net Billed Amount
                ],
                [
                    'name' => __('Payments Received'),
                    'data' => array_values($amountsPaid),
                    'color' => '#6200EE' // Color for Payments Received
                ],
                [
                    'name' => __('Services Revenue'),
                    'data' => array_values($servicePrices),
                    'color' => '#FFC107' // Color for Services
                ],
                [
                    'name' => __('Purchases Cost'),
                    'data' => array_values($shoppingTotals),
                    'color' => '#F44336' // Color for Purchases
                ]
            ]
        ];

        return response()->json($response);
    }
 
        
}
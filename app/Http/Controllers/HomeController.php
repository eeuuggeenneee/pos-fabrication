<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Customer;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $orders = Order::with(['items', 'payments'])->get();
        $startDate = Carbon::now()->startOfMonth()->setTimezone('Asia/Manila');
        $endDate = Carbon::now()->endOfMonth()->setTimezone('Asia/Manila');

        $incomeData = $orders->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy(function ($order) {
                return $order->created_at->format('Y-m-d');
            })->map(function ($group) {
                return $group->sum(function ($order) {
                    return min($order->receivedAmount(), $order->total());
                });
            });

        $dates = [];
        $currentDate = $startDate->copy();

        while ($currentDate <= $endDate) {
            $dates[] = $currentDate->format('Y-m-d');
            $currentDate->addDay();
        }

        $filledIncomeData = collect($dates)->map(function ($date) use ($incomeData) {
            return $incomeData->has($date) ? $incomeData->get($date) : 0;
        });

        $chartData = [
            "labels" => $dates,
            "datasets" => [
                [
                    "label" => "Income",
                    "data" => $filledIncomeData->values()->toArray(),
                    "backgroundColor" => "rgba(255, 237, 74, 255)",
                    "borderColor" => "rgba(229,213,66,255)",
                    "borderWidth" => 4,
                ],
            ],
        ];

        $customers_count = Customer::count();

        $totalIncome = $filledIncomeData->sum();

        return view('home', [
            'orders_count' => $orders->count(),
            'income' => $orders->sum(function ($order) {
                return min($order->receivedAmount(), $order->total());
            }),
            'income_today' => $orders->where('created_at', '>=', now()->startOfDay())->sum(function ($order) {
                return min($order->receivedAmount(), $order->total());
            }),
            'customers_count' => $customers_count,
            'chartData' => $chartData,
            'totalIncome' => $totalIncome,
            'startDate' => $startDate,
        ]);
    }
}

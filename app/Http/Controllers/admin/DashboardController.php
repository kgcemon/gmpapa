<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Categorie;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use SebastianBergmann\CodeCoverage\Report\Html\Dashboard;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::limit(10)->orderBy('created_at', 'desc')->get();

        $dashboardData = Cache::remember('dashboardData', 60, function () use ($request) {
            $today = Carbon::today();
            $yesterday = Carbon::yesterday();

            // Handle custom date range (optional)
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');

            // Custom Range Query
            $customQuery = null;
            if ($startDate && $endDate) {
                $customQuery = Order::whereBetween('created_at', [$startDate, Carbon::parse($endDate)->endOfDay()]);
            }

            return [
                // TODAY
                'today' => [
                    'total_orders' => Order::whereDate('created_at', $today)->count(),
                    'completed_orders' => Order::whereDate('created_at', $today)->where('status', 'delivered')->count(),
                    'users' => User::count(),
                    'new_users' => User::whereDate('created_at', $today)->count(),
                    'sales' => Order::whereDate('created_at', $today)->where('status', 'delivered')->sum('total'),
                ],

                // YESTERDAY
                'yesterday' => [
                    'total_orders' => Order::whereDate('created_at', $yesterday)->count(),
                    'completed_orders' => Order::whereDate('created_at', $yesterday)->where('status', 'delivered')->count(),
                    'users' => User::count(),
                    'new_users' => User::whereDate('created_at', $yesterday)->count(),
                    'sales' => Order::whereDate('created_at', $yesterday)->where('status', 'delivered')->sum('total'),
                ],

                // CUSTOM RANGE (if provided)
                'custom' => $customQuery ? [
                    'total_orders' => (clone $customQuery)->count(),
                    'completed_orders' => (clone $customQuery)->where('status', 'delivered')->count(),
                    'users' => User::count(),
                    'new_users' => User::whereBetween('created_at', [$startDate, Carbon::parse($endDate)->endOfDay()])->count(),
                    'sales' => (clone $customQuery)->where('status', 'delivered')->sum('total'),
                ] : null,

                // ALL TIME
                'alltime' => [
                    'total_orders' => Order::count(),
                    'completed_orders' => Order::where('status', 'delivered')->count(),
                    'users' => User::count(),
                    'new_users' => User::count(), // all users counted here
                    'sales' => Order::where('status', 'delivered')->sum('total'),
                ],
            ];
        });

        return view('admin.dashboard', compact('dashboardData', 'orders'));
    }
}

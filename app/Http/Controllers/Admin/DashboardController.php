<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('admin/Dashboard', [
            'stats' => [
                'totalProducts' => Product::count(),
                'totalOrders' => Order::count(),
                'totalUsers' => User::where('role', 'guest')->count(),
                'totalRevenue' => Order::where('status', Order::STATUS_DELIVERED)->sum('total'),
                'pendingOrders' => Order::where('status', Order::STATUS_PENDING)->count(),
            ],
        ]);
    }
}

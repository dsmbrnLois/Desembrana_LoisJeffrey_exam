<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateOrderStatusRequest;
use App\Models\Order;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Order::with(['user', 'items.product']);

        if ($search = $request->input('search')) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $orders = $query->latest()->paginate(10)->withQueryString();

        return Inertia::render('admin/orders/Index', [
            'orders' => $orders,
            'filters' => [
                'search' => $search,
                'status' => $status,
            ],
            'statuses' => Order::STATUSES,
        ]);
    }

    public function show(Order $order): Response
    {
        $order->load(['user', 'items.product']);

        return Inertia::render('admin/orders/Show', [
            'order' => $order,
            'statuses' => Order::STATUSES,
        ]);
    }

    public function updateStatus(UpdateOrderStatusRequest $request, Order $order): RedirectResponse
    {
        $oldStatus = $order->status;
        $order->update(['status' => $request->validated('status')]);

        ActivityLogger::log(
            'order_status_updated',
            "Order #{$order->id} status changed from {$oldStatus} to {$order->status}",
            $order,
            ['old_status' => $oldStatus, 'new_status' => $order->status]
        );

        return back()->with('success', 'Order status updated successfully.');
    }

    public function destroy(Order $order): RedirectResponse
    {
        $orderId = $order->id;
        $order->delete();

        ActivityLogger::log('order_deleted', "Deleted order #{$orderId}");

        return back()->with('success', 'Order deleted successfully.');
    }
}

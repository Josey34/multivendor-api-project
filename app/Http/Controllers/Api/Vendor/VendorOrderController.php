<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderDetailResource;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class VendorOrderController extends Controller
{
    /**
     * Get all orders for vendor
     */
    public function index(Request $request)
    {
        $user = $request->user();

        if (!$user->isVendor() || !$user->vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Vendor account required.'
            ], 403);
        }

        $query = Order::where('vendor_id', $user->vendor->id)
            ->with('user')
            ->withCount('items');

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by payment status
        if ($request->has('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // Search by order number
        if ($request->has('search')) {
            $query->where('order_number', 'like', '%' . $request->search . '%');
        }

        $orders = $query->latest()->paginate(20);

        return response()->json([
            'success' => true,
            'data' => OrderResource::collection($orders),
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ]
        ], 200);
    }

    /**
     * Get single order details
     */
    public function show(Request $request, $orderNumber)
    {
        $user = $request->user();

        $order = Order::where('order_number', $orderNumber)
            ->where('vendor_id', $user->vendor->id)
            ->with(['items.product', 'shippingAddress', 'billingAddress', 'user'])
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => new OrderDetailResource($order)
        ], 200);
    }

    /**
     * Update order status (vendor can process, ship orders)
     */
    public function updateStatus(Request $request, $orderNumber)
    {
        $user = $request->user();

        $order = Order::where('order_number', $orderNumber)
            ->where('vendor_id', $user->vendor->id)
            ->firstOrFail();

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:processing,shipped,delivered',
            'tracking_number' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Validate status transition
        if ($order->status === 'cancelled' || $order->status === 'refunded') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot update cancelled or refunded orders'
            ], 422);
        }

        // Update order status
        switch ($request->status) {
            case 'processing':
                if ($order->status !== 'pending') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Can only set to processing from pending status'
                    ], 422);
                }
                $order->update(['status' => 'processing']);
                break;

            case 'shipped':
                if ($order->status !== 'processing') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Can only ship orders that are being processed'
                    ], 422);
                }
                $order->markAsShipped($request->tracking_number);
                break;

            case 'delivered':
                if ($order->status !== 'shipped') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Can only mark as delivered orders that have been shipped'
                    ], 422);
                }
                $order->markAsDelivered();
                break;
        }

        return response()->json([
            'success' => true,
            'message' => 'Order status updated successfully',
            'data' => new OrderDetailResource($order->fresh())
        ], 200);
    }

    /**
     * Get order statistics for vendor
     */
    public function statistics(Request $request)
    {
        $user = $request->user();

        if (!$user->isVendor() || !$user->vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Vendor account required.'
            ], 403);
        }

        $vendorId = $user->vendor->id;

        $stats = [
            'total_orders' => Order::where('vendor_id', $vendorId)->count(),
            'pending_orders' => Order::where('vendor_id', $vendorId)->pending()->count(),
            'processing_orders' => Order::where('vendor_id', $vendorId)->processing()->count(),
            'delivered_orders' => Order::where('vendor_id', $vendorId)->delivered()->count(),
            'total_revenue' => Order::where('vendor_id', $vendorId)
                ->where('payment_status', 'paid')
                ->sum('total'),
            'today_orders' => Order::where('vendor_id', $vendorId)
                ->whereDate('created_at', today())
                ->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ], 200);
    }
}

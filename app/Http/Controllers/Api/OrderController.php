<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderDetailResource;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Get all orders for authenticated user
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Order::where('user_id', $user->id)
            ->with('vendor')
            ->withCount('items');

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by payment status
        if ($request->has('payment_status')) {
            $query->where('payment_status', $request->payment_status);
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
            ->where('user_id', $user->id)
            ->with(['items.product', 'shippingAddress', 'billingAddress', 'vendor'])
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => new OrderDetailResource($order)
        ], 200);
    }

    /**
     * Cancel order (only if status is pending)
     */
    public function cancel(Request $request, $orderNumber)
    {
        $user = $request->user();

        $order = Order::where('order_number', $orderNumber)
            ->where('user_id', $user->id)
            ->firstOrFail();

        // Can only cancel pending orders
        if ($order->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot cancel order. Order is already ' . $order->status
            ], 422);
        }

        // Cancel order
        $order->cancel();

        // Restore stock
        foreach ($order->items as $item) {
            $item->product->increment('stock_quantity', $item->quantity);
            $item->product->decrement('total_sales', $item->quantity);

            // Update stock status
            $item->product->update(['stock_status' => 'in_stock']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Order cancelled successfully',
            'data' => new OrderDetailResource($order->fresh())
        ], 200);
    }
}

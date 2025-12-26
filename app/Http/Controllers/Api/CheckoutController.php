<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderDetailResource;
use App\Models\Address;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    /**
     * Process checkout and create order
     */
    public function process(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'shipping_address_id' => 'required|exists:addresses,id',
            'billing_address_id' => 'nullable|exists:addresses,id',
            'payment_method' => 'required|in:cod,bank_transfer,credit_card,e-wallet',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        // Get cart with items
        $cart = Cart::where('user_id', $user->id)
            ->with(['items.product', 'items.variant'])
            ->firstOrFail();

        // Check if cart has items
        if ($cart->items->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Cart is empty'
            ], 422);
        }

        // Verify addresses belong to user
        $shippingAddress = Address::where('id', $request->shipping_address_id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $billingAddress = $request->billing_address_id
            ? Address::where('id', $request->billing_address_id)->where('user_id', $user->id)->firstOrFail()
            : $shippingAddress;

        // Validate stock availability for all items
        foreach ($cart->items as $item) {
            if ($item->product->stock_quantity < $item->quantity) {
                return response()->json([
                    'success' => false,
                    'message' => "Insufficient stock for {$item->product->name}. Available: {$item->product->stock_quantity}"
                ], 422);
            }
        }

        // Group cart items by vendor
        $itemsByVendor = $cart->items->groupBy('product.vendor_id');

        DB::beginTransaction();

        try {
            $orders = [];

            // Create separate order for each vendor
            foreach ($itemsByVendor as $vendorId => $items) {
                // Calculate totals
                $subtotal = $items->sum(function ($item) {
                    return $item->price * $item->quantity;
                });

                $tax = $subtotal * 0.10; // 10% tax
                $shippingCost = 10.00; // Flat rate for now
                $total = $subtotal + $tax + $shippingCost;

                // Create order
                $order = Order::create([
                    'order_number' => $this->generateOrderNumber(),
                    'user_id' => $user->id,
                    'vendor_id' => $vendorId,
                    'shipping_address_id' => $shippingAddress->id,
                    'billing_address_id' => $billingAddress->id,
                    'status' => 'pending',
                    'payment_status' => $request->payment_method === 'cod' ? 'unpaid' : 'pending',
                    'payment_method' => $request->payment_method,
                    'subtotal' => $subtotal,
                    'tax' => $tax,
                    'shipping_cost' => $shippingCost,
                    'discount' => 0,
                    'total' => $total,
                    'notes' => $request->notes,
                ]);

                // Create order items
                foreach ($items as $item) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $item->product_id,
                        'product_variant_id' => $item->product_variant_id,
                        'product_name' => $item->product->name,
                        'product_sku' => $item->product->sku,
                        'variant_details' => $item->variant ? [
                            'variant_name' => $item->variant->getVariantName(),
                            'sku' => $item->variant->sku,
                        ] : null,
                        'quantity' => $item->quantity,
                        'price' => $item->price,
                        'subtotal' => $item->price * $item->quantity,
                    ]);

                    // Reduce stock
                    $item->product->decrement('stock_quantity', $item->quantity);
                    $item->product->increment('total_sales', $item->quantity);

                    // Update stock status
                    if ($item->product->stock_quantity <= 0) {
                        $item->product->update(['stock_status' => 'out_of_stock']);
                    } elseif ($item->product->stock_quantity <= $item->product->low_stock_threshold) {
                        $item->product->update(['stock_status' => 'low_stock']);
                    }
                }

                // Create payment record
                Payment::create([
                    'order_id' => $order->id,
                    'payment_gateway' => $request->payment_method,
                    'status' => $request->payment_method === 'cod' ? 'pending' : 'pending',
                    'amount' => $total,
                    'currency' => 'IDR',
                ]);

                $orders[] = $order;
            }

            // Clear cart
            $cart->clear();

            DB::commit();

            // Load relationships for response
            foreach ($orders as $order) {
                $order->load(['items', 'shippingAddress', 'billingAddress', 'vendor']);
            }

            return response()->json([
                'success' => true,
                'message' => 'Order placed successfully',
                'data' => [
                    'orders' => OrderDetailResource::collection($orders),
                    'total_orders' => count($orders),
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Checkout failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate unique order number
     */
    private function generateOrderNumber(): string
    {
        return 'ORD-' . date('Ymd') . '-' . strtoupper(Str::random(6));
    }
}

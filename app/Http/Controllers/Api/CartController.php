<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CartResource;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    /**
     * Get user's cart
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $cart = Cart::with(['items.product.primaryImage', 'items.variant'])
            ->firstOrCreate(['user_id' => $user->id]);

        return response()->json([
            'success' => true,
            'data' => new CartResource($cart)
        ], 200);
    }

    /**
     * Add item to cart
     */
    public function addItem(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'product_variant_id' => 'nullable|exists:product_variants,id',
            'quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $product = Product::findOrFail($request->product_id);

        // Check if product is active and in stock
        if (!$product->is_active || $product->stock_status === 'out_of_stock') {
            return response()->json([
                'success' => false,
                'message' => 'Product is not available'
            ], 422);
        }

        // Check stock quantity
        if ($product->stock_quantity < $request->quantity) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient stock. Available: ' . $product->stock_quantity
            ], 422);
        }

        // Get or create cart
        $cart = Cart::firstOrCreate(['user_id' => $user->id]);

        // Check if item already exists in cart
        $existingItem = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $product->id)
            ->where('product_variant_id', $request->product_variant_id)
            ->first();

        if ($existingItem) {
            // Update quantity
            $newQuantity = $existingItem->quantity + $request->quantity;

            if ($product->stock_quantity < $newQuantity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot add more. Available: ' . $product->stock_quantity
                ], 422);
            }

            $existingItem->update(['quantity' => $newQuantity]);
            $cartItem = $existingItem;
        } else {
            // Create new cart item
            $price = $product->getEffectivePrice();

            // If variant selected, use variant price
            if ($request->product_variant_id) {
                $variant = ProductVariant::find($request->product_variant_id);
                $price = $variant->getEffectivePrice();
            }

            $cartItem = CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $product->id,
                'product_variant_id' => $request->product_variant_id,
                'quantity' => $request->quantity,
                'price' => $price,
            ]);
        }

        $cart->load(['items.product.primaryImage', 'items.variant']);

        return response()->json([
            'success' => true,
            'message' => 'Item added to cart',
            'data' => new CartResource($cart)
        ], 201);
    }

    /**
     * Update cart item quantity
     */
    public function updateItem(Request $request, $itemId)
    {
        $validator = Validator::make($request->all(), [
            'quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $cart = Cart::where('user_id', $user->id)->firstOrFail();

        $cartItem = CartItem::where('id', $itemId)
            ->where('cart_id', $cart->id)
            ->with('product')
            ->firstOrFail();

        // Check stock
        if ($cartItem->product->stock_quantity < $request->quantity) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient stock. Available: ' . $cartItem->product->stock_quantity
            ], 422);
        }

        $cartItem->update(['quantity' => $request->quantity]);

        $cart->load(['items.product.primaryImage', 'items.variant']);

        return response()->json([
            'success' => true,
            'message' => 'Cart updated',
            'data' => new CartResource($cart)
        ], 200);
    }

    /**
     * Remove item from cart
     */
    public function removeItem(Request $request, $itemId)
    {
        $user = $request->user();
        $cart = Cart::where('user_id', $user->id)->firstOrFail();

        $deleted = CartItem::where('id', $itemId)
            ->where('cart_id', $cart->id)
            ->delete();

        if (!$deleted) {
            return response()->json([
                'success' => false,
                'message' => 'Item not found in cart'
            ], 404);
        }

        $cart->load(['items.product.primaryImage', 'items.variant']);

        return response()->json([
            'success' => true,
            'message' => 'Item removed from cart',
            'data' => new CartResource($cart)
        ], 200);
    }

    /**
     * Clear entire cart
     */
    public function clear(Request $request)
    {
        $user = $request->user();
        $cart = Cart::where('user_id', $user->id)->firstOrFail();

        $cart->clear();

        return response()->json([
            'success' => true,
            'message' => 'Cart cleared'
        ], 200);
    }
}

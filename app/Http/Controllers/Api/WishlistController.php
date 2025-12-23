<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    /**
     * Get user's wishlist
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $wishlist = Wishlist::where('user_id', $user->id)
            ->with(['product.category', 'product.brand', 'product.vendor', 'product.primaryImage'])
            ->latest()
            ->get();

        $products = $wishlist->pluck('product');

        return response()->json([
            'success' => true,
            'data' => ProductResource::collection($products),
            'meta' => [
                'total' => $products->count(),
            ]
        ], 200);
    }

    /**
     * Add product to wishlist
     */
    public function store(Request $request, $productId)
    {
        $user = $request->user();
        $product = Product::findOrFail($productId);

        // Check if already in wishlist
        $exists = Wishlist::where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Product already in wishlist'
            ], 422);
        }

        // Add to wishlist
        Wishlist::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Product added to wishlist'
        ], 201);
    }

    /**
     * Remove product from wishlist
     */
    public function destroy(Request $request, $productId)
    {
        $user = $request->user();

        $deleted = Wishlist::where('user_id', $user->id)
            ->where('product_id', $productId)
            ->delete();

        if (!$deleted) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found in wishlist'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Product removed from wishlist'
        ], 200);
    }

    /**
     * Check if product is in wishlist
     */
    public function check(Request $request, $productId)
    {
        $user = $request->user();

        $inWishlist = Wishlist::where('user_id', $user->id)
            ->where('product_id', $productId)
            ->exists();

        return response()->json([
            'success' => true,
            'data' => [
                'in_wishlist' => $inWishlist
            ]
        ], 200);
    }
}

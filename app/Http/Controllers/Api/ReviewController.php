<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReviewResource;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReviewController extends Controller
{
    /**
     * Get reviews for a product
     */
    public function index($productId)
    {
        $product = Product::findOrFail($productId);

        $reviews = Review::where('product_id', $product->id)
            ->with('user')
            ->approved()
            ->latest()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => ReviewResource::collection($reviews),
            'meta' => [
                'current_page' => $reviews->currentPage(),
                'last_page' => $reviews->lastPage(),
                'per_page' => $reviews->perPage(),
                'total' => $reviews->total(),
                'average_rating' => $product->rating,
                'total_reviews' => $product->total_reviews,
            ]
        ], 200);
    }

    /**
     * Submit a review (authenticated users only)
     */
    public function store(Request $request, $productId)
    {
        $product = Product::findOrFail($productId);
        $user = $request->user();

        // Check if user already reviewed this product
        $existingReview = Review::where('product_id', $product->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existingReview) {
            return response()->json([
                'success' => false,
                'message' => 'You have already reviewed this product'
            ], 422);
        }

        // Validate input
        $validator = Validator::make($request->all(), [
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|min:10|max:1000',
            'images' => 'nullable|array|max:5',
            'images.*' => 'string', // Assuming image URLs or paths
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Create review
        $review = Review::create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'rating' => $request->rating,
            'comment' => $request->comment,
            'images' => $request->images,
            'is_verified_purchase' => false, // TODO: Check if user bought this product
            'is_approved' => true, // Auto-approve or set to false for moderation
        ]);

        // Update product rating
        $this->updateProductRating($product);

        return response()->json([
            'success' => true,
            'message' => 'Review submitted successfully',
            'data' => new ReviewResource($review->load('user'))
        ], 201);
    }

    /**
     * Update product's average rating
     */
    private function updateProductRating(Product $product)
    {
        $avgRating = Review::where('product_id', $product->id)
            ->approved()
            ->avg('rating');

        $totalReviews = Review::where('product_id', $product->id)
            ->approved()
            ->count();

        $product->update([
            'rating' => round($avgRating, 2),
            'total_reviews' => $totalReviews,
        ]);
    }
}

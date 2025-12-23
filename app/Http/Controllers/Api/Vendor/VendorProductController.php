<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductDetailResource;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class VendorProductController extends Controller
{
    /**
     * Get vendor's own products
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Check if user is vendor
        if (!$user->isVendor() || !$user->vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Vendor account required.'
            ], 403);
        }

        $query = Product::where('vendor_id', $user->vendor->id)
            ->with(['category', 'brand', 'primaryImage']);

        // Filter by status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%");
        }

        $products = $query->latest()->paginate(20);

        return response()->json([
            'success' => true,
            'data' => ProductResource::collection($products),
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ]
        ], 200);
    }

    /**
     * Get single vendor product
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();

        $product = Product::where('id', $id)
            ->where('vendor_id', $user->vendor->id)
            ->with(['category', 'brand', 'images', 'variants'])
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => new ProductDetailResource($product)
        ], 200);
    }

    /**
     * Create new product
     */
    public function store(Request $request)
    {
        $user = $request->user();

        if (!$user->isVendor() || !$user->vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Vendor account required.'
            ], 403);
        }

        // Check if vendor is approved
        if (!$user->vendor->isApproved()) {
            return response()->json([
                'success' => false,
                'message' => 'Your vendor account is not approved yet.'
            ], 403);
        }

        // Validate input
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'short_description' => 'nullable|string|max:500',
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0|lt:price',
            'cost_price' => 'nullable|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'weight' => 'nullable|numeric|min:0',
            'dimensions' => 'nullable|array',
            'is_featured' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Generate slug
        $slug = Str::slug($request->name) . '-' . rand(1000, 9999);

        // Create product
        $product = Product::create([
            'vendor_id' => $user->vendor->id,
            'category_id' => $request->category_id,
            'brand_id' => $request->brand_id,
            'name' => $request->name,
            'slug' => $slug,
            'sku' => strtoupper(Str::random(3) . '-' . rand(10000, 99999)),
            'description' => $request->description,
            'short_description' => $request->short_description,
            'price' => $request->price,
            'sale_price' => $request->sale_price,
            'cost_price' => $request->cost_price,
            'stock_quantity' => $request->stock_quantity,
            'low_stock_threshold' => 5,
            'weight' => $request->weight,
            'dimensions' => $request->dimensions,
            'is_active' => true,
            'is_featured' => $request->is_featured ?? false,
            'stock_status' => $request->stock_quantity > 0 ? 'in_stock' : 'out_of_stock',
            'published_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Product created successfully',
            'data' => new ProductDetailResource($product)
        ], 201);
    }

    /**
     * Update product
     */
    public function update(Request $request, $id)
    {
        $user = $request->user();

        $product = Product::where('id', $id)
            ->where('vendor_id', $user->vendor->id)
            ->firstOrFail();

        // Validate input
        $validator = Validator::make($request->all(), [
            'category_id' => 'sometimes|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'short_description' => 'nullable|string|max:500',
            'price' => 'sometimes|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'stock_quantity' => 'sometimes|integer|min:0',
            'weight' => 'nullable|numeric|min:0',
            'dimensions' => 'nullable|array',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Update product
        $product->update($request->only([
            'category_id',
            'brand_id',
            'name',
            'description',
            'short_description',
            'price',
            'sale_price',
            'cost_price',
            'stock_quantity',
            'weight',
            'dimensions',
            'is_active',
            'is_featured',
        ]));

        // Update stock status
        if ($request->has('stock_quantity')) {
            $product->update([
                'stock_status' => $request->stock_quantity > 0 ? 'in_stock' : 'out_of_stock'
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully',
            'data' => new ProductDetailResource($product)
        ], 200);
    }

    /**
     * Delete product
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();

        $product = Product::where('id', $id)
            ->where('vendor_id', $user->vendor->id)
            ->firstOrFail();

        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully'
        ], 200);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductDetailResource;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Get all products with filters, search, and pagination
     */
    public function index(Request $request)
    {
        $query = Product::query()
            ->with(['category', 'brand', 'vendor', 'primaryImage'])
            ->active();

        // Search by name or description
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by category
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by brand
        if ($request->has('brand_id')) {
            $query->where('brand_id', $request->brand_id);
        }

        // Filter by vendor
        if ($request->has('vendor_id')) {
            $query->where('vendor_id', $request->vendor_id);
        }

        // Filter by price range
        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // Filter by stock status
        if ($request->has('stock_status')) {
            $query->where('stock_status', $request->stock_status);
        }

        // Filter by featured
        if ($request->has('is_featured') && $request->is_featured) {
            $query->featured();
        }

        // Filter by on sale
        if ($request->has('on_sale') && $request->on_sale) {
            $query->onSale();
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        switch ($sortBy) {
            case 'price':
                $query->orderBy('price', $sortOrder);
                break;
            case 'name':
                $query->orderBy('name', $sortOrder);
                break;
            case 'rating':
                $query->orderBy('rating', $sortOrder);
                break;
            case 'sales':
                $query->orderBy('total_sales', $sortOrder);
                break;
            default:
                $query->orderBy('created_at', $sortOrder);
        }

        // Pagination
        $perPage = $request->get('per_page', 20);
        $products = $query->paginate($perPage);

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
     * Get single product details
     */
    public function show($slug)
    {
        $product = Product::where('slug', $slug)
            ->with([
                'category',
                'brand',
                'vendor',
                'images' => function ($query) {
                    $query->ordered();
                },
                'variants',
                'reviews' => function ($query) {
                    $query->approved()->latest()->limit(10);
                },
                'reviews.user'
            ])
            ->active()
            ->firstOrFail();

        // Increment views
        $product->incrementViews();

        return response()->json([
            'success' => true,
            'data' => new ProductDetailResource($product)
        ], 200);
    }

    /**
     * Get featured products
     */
    public function featured()
    {
        $products = Product::with(['category', 'brand', 'vendor', 'primaryImage'])
            ->active()
            ->featured()
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => ProductResource::collection($products)
        ], 200);
    }

    /**
     * Get products on sale
     */
    public function onSale()
    {
        $products = Product::with(['category', 'brand', 'vendor', 'primaryImage'])
            ->active()
            ->onSale()
            ->limit(20)
            ->get();

        return response()->json([
            'success' => true,
            'data' => ProductResource::collection($products)
        ], 200);
    }
}

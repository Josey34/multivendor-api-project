<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BrandResource;
use App\Models\Brand;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    /**
     * Get all brands
     */
    public function index()
    {
        $brands = Brand::active()
            ->withCount('products')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => BrandResource::collection($brands)
        ], 200);
    }

    /**
     * Get single brand
     */
    public function show($slug)
    {
        $brand = Brand::where('slug', $slug)
            ->active()
            ->withCount('products')
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => new BrandResource($brand)
        ], 200);
    }
}

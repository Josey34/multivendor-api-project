<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Get all categories (nested tree structure)
     */
    public function index()
    {
        $categories = Category::with('children')
            ->whereNull('parent_id')
            ->active()
            ->ordered()
            ->get();

        return response()->json([
            'success' => true,
            'data' => CategoryResource::collection($categories)
        ], 200);
    }

    /**
     * Get single category with products count
     */
    public function show($slug)
    {
        $category = Category::where('slug', $slug)
            ->with('children')
            ->withCount('products')
            ->active()
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => new CategoryResource($category)
        ], 200);
    }

    /**
     * Get all parent categories only
     */
    public function parents()
    {
        $categories = Category::parents()
            ->active()
            ->ordered()
            ->withCount('products')
            ->get();

        return response()->json([
            'success' => true,
            'data' => CategoryResource::collection($categories)
        ], 200);
    }
}

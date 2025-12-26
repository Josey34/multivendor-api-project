<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\VendorResource;
use App\Models\Vendor;
use Illuminate\Http\Request;

class AdminVendorController extends Controller
{
    /**
     * Get all vendors
     */
    public function index(Request $request)
    {
        $query = Vendor::with('user');

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where('shop_name', 'like', "%{$search}%");
        }

        $vendors = $query->latest()->paginate(20);

        return response()->json([
            'success' => true,
            'data' => VendorResource::collection($vendors),
            'meta' => [
                'current_page' => $vendors->currentPage(),
                'last_page' => $vendors->lastPage(),
                'per_page' => $vendors->perPage(),
                'total' => $vendors->total(),
            ]
        ], 200);
    }

    /**
     * Approve vendor
     */
    public function approve($id)
    {
        $vendor = Vendor::findOrFail($id);

        if ($vendor->status === 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'Vendor is already approved'
            ], 422);
        }

        $vendor->update(['status' => 'approved']);

        // TODO: Send email notification to vendor

        return response()->json([
            'success' => true,
            'message' => 'Vendor approved successfully',
            'data' => new VendorResource($vendor)
        ], 200);
    }

    /**
     * Reject vendor
     */
    public function reject($id)
    {
        $vendor = Vendor::findOrFail($id);

        $vendor->update(['status' => 'rejected']);

        // TODO: Send email notification to vendor

        return response()->json([
            'success' => true,
            'message' => 'Vendor rejected',
            'data' => new VendorResource($vendor)
        ], 200);
    }

    /**
     * Suspend vendor
     */
    public function suspend($id)
    {
        $vendor = Vendor::findOrFail($id);

        $vendor->update(['status' => 'suspended']);

        // TODO: Send email notification to vendor

        return response()->json([
            'success' => true,
            'message' => 'Vendor suspended',
            'data' => new VendorResource($vendor)
        ], 200);
    }

    /**
     * Update vendor commission rate
     */
    public function updateCommission(Request $request, $id)
    {
        $request->validate([
            'commission_rate' => 'required|numeric|min:0|max:100',
        ]);

        $vendor = Vendor::findOrFail($id);

        $vendor->update([
            'commission_rate' => $request->commission_rate
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Commission rate updated',
            'data' => new VendorResource($vendor)
        ], 200);
    }
}

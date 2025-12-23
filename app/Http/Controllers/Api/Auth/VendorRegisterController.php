<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class VendorRegisterController extends Controller
{
    /**
     * Register a new vendor
     */
    public function register(Request $request)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'required|string|max:20',
            'shop_name' => 'required|string|max:255',
            'shop_description' => 'nullable|string',
            'shop_address' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Use database transaction
        DB::beginTransaction();

        try {
            // Create user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone' => $request->phone,
                'user_type' => 'vendor',
                'status' => 'active',
            ]);

            // Assign vendor role
            $user->assignRole('vendor');

            // Create vendor profile
            $vendor = Vendor::create([
                'user_id' => $user->id,
                'shop_name' => $request->shop_name,
                'slug' => Str::slug($request->shop_name) . '-' . rand(1000, 9999),
                'description' => $request->shop_description,
                'phone' => $request->phone,
                'address' => $request->shop_address,
                'commission_rate' => 10.00, // Default commission
                'status' => 'pending', // Needs admin approval
                'rating' => 0,
                'total_reviews' => 0,
            ]);

            // Create vendor settings with defaults
            VendorSetting::create([
                'vendor_id' => $vendor->id,
                'allow_cod' => true,
                'allow_returns' => true,
                'return_days' => 7,
                'min_order_amount' => 0,
            ]);

            // Create token
            $token = $user->createToken('auth_token')->plainTextToken;

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Vendor registered successfully. Your account is pending approval.',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'phone' => $user->phone,
                        'user_type' => $user->user_type,
                    ],
                    'vendor' => [
                        'id' => $vendor->id,
                        'shop_name' => $vendor->shop_name,
                        'slug' => $vendor->slug,
                        'status' => $vendor->status,
                    ],
                    'access_token' => $token,
                    'token_type' => 'Bearer',
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Registration failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

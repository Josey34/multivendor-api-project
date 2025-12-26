<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class UploadController extends Controller
{
    /**
     * Upload product image
     */
    public function uploadProductImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB max
        ]);

        if (!$request->hasFile('image')) {
            return response()->json([
                'success' => false,
                'message' => 'No image file provided'
            ], 422);
        }

        $image = $request->file('image');
        $filename = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();

        // Create directories if they don't exist
        if (!Storage::exists('public/products')) {
            Storage::makeDirectory('public/products');
        }
        if (!Storage::exists('public/products/thumbnails')) {
            Storage::makeDirectory('public/products/thumbnails');
        }

        // Store original image
        $path = $image->storeAs('public/products', $filename);

        // Create thumbnail
        $thumbnailPath = 'public/products/thumbnails/' . $filename;
        $manager = new ImageManager(new Driver());
        $img = $manager->read($image->getRealPath());
        $img->scale(300, 300);
        Storage::put($thumbnailPath, (string) $img->encode());

        return response()->json([
            'success' => true,
            'message' => 'Image uploaded successfully',
            'data' => [
                'image_path' => 'products/' . $filename,
                'thumbnail_path' => 'products/thumbnails/' . $filename,
                'full_url' => Storage::url('products/' . $filename),
                'thumbnail_url' => Storage::url('products/thumbnails/' . $filename),
            ]
        ], 201);
    }

    /**
     * Upload user avatar
     */
    public function uploadAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg|max:2048', // 2MB max
        ]);

        $user = $request->user();

        if (!$request->hasFile('avatar')) {
            return response()->json([
                'success' => false,
                'message' => 'No avatar file provided'
            ], 422);
        }

        $avatar = $request->file('avatar');
        $filename = 'avatar_' . $user->id . '_' . time() . '.' . $avatar->getClientOriginalExtension();

        // Create directory if it doesn't exist
        if (!Storage::exists('public/avatars')) {
            Storage::makeDirectory('public/avatars');
        }

        // Delete old avatar if exists
        if ($user->avatar) {
            Storage::delete('public/' . $user->avatar);
        }

        // Resize and store avatar
        $manager = new ImageManager(new Driver());
        $img = $manager->read($avatar->getRealPath());
        $img->cover(200, 200); // Square crop
        Storage::put('public/avatars/' . $filename, (string) $img->encode());

        // Update user avatar
        $user->update([
            'avatar' => 'avatars/' . $filename
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Avatar uploaded successfully',
            'data' => [
                'avatar_path' => 'avatars/' . $filename,
                'avatar_url' => Storage::url('avatars/' . $filename),
            ]
        ], 200);
    }

    /**
     * Delete image
     */
    public function deleteImage(Request $request)
    {
        $request->validate([
            'path' => 'required|string',
        ]);

        $path = 'public/' . $request->path;

        if (!Storage::exists($path)) {
            return response()->json([
                'success' => false,
                'message' => 'Image not found'
            ], 404);
        }

        Storage::delete($path);

        // Also delete thumbnail if it's a product image
        if (Str::contains($request->path, 'products/')) {
            $thumbnailPath = str_replace('products/', 'products/thumbnails/', $path);
            if (Storage::exists($thumbnailPath)) {
                Storage::delete($thumbnailPath);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Image deleted successfully'
        ], 200);
    }
}

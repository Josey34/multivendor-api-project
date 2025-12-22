<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Review;
use App\Models\Setting;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorSetting;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create Roles
        $adminRole = Role::create(['name' => 'admin']);
        $vendorRole = Role::create(['name' => 'vendor']);
        $customerRole = Role::create(['name' => 'customer']);

        // Create Permissions
        Permission::create(['name' => 'manage users']);
        Permission::create(['name' => 'manage vendors']);
        Permission::create(['name' => 'manage products']);
        Permission::create(['name' => 'manage orders']);
        Permission::create(['name' => 'manage categories']);
        Permission::create(['name' => 'manage settings']);

        // Assign permissions to roles
        $adminRole->givePermissionTo(Permission::all());
        $vendorRole->givePermissionTo(['manage products', 'manage orders']);

        // Create Admin User
        $admin = User::factory()->admin()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'status' => 'active',
        ]);
        $admin->assignRole('admin');

        // Create Customer Users
        $customers = User::factory(20)->create([
            'user_type' => 'customer',
            'status' => 'active',
        ]);
        foreach ($customers as $customer) {
            $customer->assignRole('customer');
        }

        // Create Vendor Users with Shops
        $vendors = User::factory(5)->vendor()->create([
            'status' => 'active',
        ]);

        foreach ($vendors as $vendorUser) {
            $vendorUser->assignRole('vendor');

            $vendor = Vendor::factory()->approved()->create([
                'user_id' => $vendorUser->id,
            ]);

            VendorSetting::factory()->create([
                'vendor_id' => $vendor->id,
            ]);
        }

        // Create Addresses for Customers
        foreach ($customers->random(10) as $customer) {
            Address::factory(2)->create([
                'user_id' => $customer->id,
            ]);
            Address::factory()->default()->create([
                'user_id' => $customer->id,
            ]);
        }

        // Create Categories
        $parentCategories = Category::factory(5)->create();

        // Create child categories
        foreach ($parentCategories as $parent) {
            Category::factory(3)->child($parent->id)->create();
        }

        // Create Brands
        $brands = Brand::factory(10)->create();

        // Create Attributes
        $colorAttribute = Attribute::factory()->create([
            'name' => 'Color',
            'slug' => 'color',
            'type' => 'color',
        ]);

        $sizeAttribute = Attribute::factory()->create([
            'name' => 'Size',
            'slug' => 'size',
            'type' => 'select',
        ]);

        // Create Attribute Values
        $colors = ['Red', 'Blue', 'Green', 'Black', 'White', 'Yellow'];
        $colorCodes = ['#FF0000', '#0000FF', '#00FF00', '#000000', '#FFFFFF', '#FFFF00'];

        foreach ($colors as $index => $color) {
            AttributeValue::factory()->create([
                'attribute_id' => $colorAttribute->id,
                'value' => $color,
                'color_code' => $colorCodes[$index],
            ]);
        }

        $sizes = ['XS', 'S', 'M', 'L', 'XL', 'XXL'];
        foreach ($sizes as $size) {
            AttributeValue::factory()->create([
                'attribute_id' => $sizeAttribute->id,
                'value' => $size,
                'color_code' => null,
            ]);
        }

        // Create Products
        $allVendors = Vendor::all();
        $allCategories = Category::all();
        $allBrands = Brand::all();

        foreach ($allVendors as $vendor) {
            // Each vendor gets 10-20 products
            $productCount = rand(10, 20);

            for ($i = 0; $i < $productCount; $i++) {
                $product = Product::factory()->create([
                    'vendor_id' => $vendor->id,
                    'category_id' => $allCategories->random()->id,
                    'brand_id' => $allBrands->random()->id,
                ]);

                // Add product images
                ProductImage::factory()->primary()->create([
                    'product_id' => $product->id,
                ]);

                ProductImage::factory(rand(2, 5))->create([
                    'product_id' => $product->id,
                ]);

                // Add reviews for some products
                if (rand(0, 100) > 30) { // 70% of products have reviews
                    Review::factory(rand(1, 10))->create([
                        'product_id' => $product->id,
                        'user_id' => $customers->random()->id,
                        'order_id' => null,
                    ]);
                }
            }
        }

        // Create Settings
        Setting::create(['key' => 'site_name', 'value' => 'MultiVendor Shop', 'type' => 'string', 'group' => 'general']);
        Setting::create(['key' => 'site_email', 'value' => 'contact@shop.com', 'type' => 'string', 'group' => 'general']);
        Setting::create(['key' => 'currency', 'value' => 'IDR', 'type' => 'string', 'group' => 'general']);
        Setting::create(['key' => 'tax_rate', 'value' => '10', 'type' => 'integer', 'group' => 'financial']);
        Setting::create(['key' => 'maintenance_mode', 'value' => '0', 'type' => 'boolean', 'group' => 'system']);
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\StockMovement;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed Users & Profiles (Persis data P4: admin123 & staff123)
        $admin = User::create([
            'username' => 'admin',
            'password_hash' => password_hash('admin123', PASSWORD_BCRYPT),
            'role' => 'admin',
        ]);
        UserProfile::create([
            'user_id' => $admin->id,
            'full_name' => 'Administrator Utama',
            'phone' => '081234567890',
        ]);

        $staff = User::create([
            'username' => 'staff',
            'password_hash' => password_hash('staff123', PASSWORD_BCRYPT),
            'role' => 'staff',
        ]);
        UserProfile::create([
            'user_id' => $staff->id,
            'full_name' => 'Staff Gudang',
            'phone' => '081298765432',
        ]);

        // 2. Seed Categories
        $cat1 = Category::create([
            'name' => 'Elektronik',
            'description' => 'Peralatan elektronik dan periferal komputer',
        ]);
        $cat2 = Category::create([
            'name' => 'Aksesori',
            'description' => 'Aksesori pendukung gadget dan komputer',
        ]);
        $cat3 = Category::create([
            'name' => 'Alat Tulis Kantor',
            'description' => 'Perlengkapan dan peralatan kantor',
        ]);

        // 3. Seed Suppliers
        $sup1 = Supplier::create([
            'name' => 'PT Megah Utama Tech',
            'contact_name' => 'Budi Santoso',
            'phone' => '021-5551234',
            'email' => 'budi@megahtech.com',
            'address' => 'Jl. Industri No. 45, Jakarta',
        ]);
        $sup2 = Supplier::create([
            'name' => 'CV Aksesori Nusantara',
            'contact_name' => 'Siti Rahma',
            'phone' => '022-4445678',
            'email' => 'siti@aksa.co.id',
            'address' => 'Jl. Raya Bandung No. 12, Bandung',
        ]);

        // 4. Seed Products
        $p1 = Product::create([
            'category_id' => $cat1->id,
            'sku' => 'SKU-KB-001',
            'name' => 'Keyboard Mechanical RGB',
            'price' => 450000.00,
            'stock' => 25,
            'minimum_stock' => 5,
        ]);
        $p2 = Product::create([
            'category_id' => $cat1->id,
            'sku' => 'SKU-MS-002',
            'name' => 'Mouse Wireless Ergonomis',
            'price' => 175000.00,
            'stock' => 4,
            'minimum_stock' => 10,
        ]);
        $p3 = Product::create([
            'category_id' => $cat2->id,
            'sku' => 'SKU-MP-003',
            'name' => 'Mousepad Gaming XL',
            'price' => 85000.00,
            'stock' => 50,
            'minimum_stock' => 8,
        ]);
        $p4 = Product::create([
            'category_id' => $cat3->id,
            'sku' => 'SKU-KRT-004',
            'name' => 'Kertas A4 80gr Rim',
            'price' => 55000.00,
            'stock' => 2,
            'minimum_stock' => 15,
        ]);

        // 5. Seed Product Suppliers (Many-to-Many junction)
        $p1->suppliers()->attach($sup1->id, ['supplier_price' => 380000.00]);
        $p2->suppliers()->attach($sup1->id, ['supplier_price' => 140000.00]);
        $p3->suppliers()->attach($sup2->id, ['supplier_price' => 60000.00]);

        // 6. Seed Stock Movements
        StockMovement::create([
            'product_id' => $p1->id,
            'user_id' => $admin->id,
            'type' => 'IN',
            'quantity' => 25,
            'notes' => 'Stok awal gudang',
        ]);
        StockMovement::create([
            'product_id' => $p2->id,
            'user_id' => $admin->id,
            'type' => 'IN',
            'quantity' => 10,
            'notes' => 'Stok awal gudang',
        ]);
        StockMovement::create([
            'product_id' => $p2->id,
            'user_id' => $staff->id,
            'type' => 'OUT',
            'quantity' => 6,
            'notes' => 'Penjualan ke pelanggan',
        ]);
        StockMovement::create([
            'product_id' => $p3->id,
            'user_id' => $admin->id,
            'type' => 'IN',
            'quantity' => 50,
            'notes' => 'Stok awal gudang',
        ]);
        StockMovement::create([
            'product_id' => $p4->id,
            'user_id' => $admin->id,
            'type' => 'IN',
            'quantity' => 2,
            'notes' => 'Stok awal gudang',
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\Supplier;

class ProductSupplierController extends Controller
{
    public function index(Product $product)
    {
        $product->load('category');
        $attachedSuppliers = $product->suppliers()->orderBy('suppliers.name')->get();
        $allSuppliers      = Supplier::orderBy('name')->get();

        return view('products.suppliers', compact('product', 'attachedSuppliers', 'allSuppliers'));
    }

    public function attach(Request $request, Product $product)
    {
        $request->validate([
            'supplier_id'    => 'required|exists:suppliers,id',
            'supplier_price' => 'nullable|numeric|min:0',
        ], [
            'supplier_id.required' => 'Pilih supplier yang valid.',
            'supplier_id.exists'   => 'Supplier tidak ditemukan.',
            'supplier_price.numeric' => 'Harga dari supplier tidak boleh negatif.',
            'supplier_price.min'     => 'Harga dari supplier tidak boleh negatif.',
        ]);

        $product->suppliers()->syncWithoutDetaching([
            $request->supplier_id => ['supplier_price' => $request->supplier_price]
        ]);

        return redirect()->route('products.suppliers', $product->id)
            ->with('flash_success', 'Supplier berhasil dihubungkan ke produk.');
    }

    public function detach(Request $request, Product $product)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
        ]);

        $product->suppliers()->detach($request->supplier_id);

        return redirect()->route('products.suppliers', $product->id)
            ->with('flash_success', 'Relasi supplier berhasil dihapus.');
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;

class ProductController extends Controller
{
    // Allowed sort columns (allowlist - mirrors P4)
    private array $allowedSortColumns = [
        'id'    => 'products.id',
        'name'  => 'products.name',
        'price' => 'products.price',
        'stock' => 'products.stock',
        'sku'   => 'products.sku',
    ];

    public function index(Request $request)
    {
        $search     = trim($request->input('search', ''));
        $categoryId = $request->input('category_id') ? (int) $request->input('category_id') : null;
        $sortInput  = $request->input('sort', 'id');
        $sortDir    = strtoupper($request->input('dir', 'ASC')) === 'DESC' ? 'DESC' : 'ASC';
        $sortCol    = $this->allowedSortColumns[$sortInput] ?? 'products.id';

        $query = Product::with('category')
            ->select('products.*')
            ->join('categories', 'categories.id', '=', 'products.category_id')
            ->when($search, fn($q) => $q->where(fn($inner) =>
                $inner->where('products.name', 'like', '%' . $search . '%')
                      ->orWhere('products.sku', 'like', '%' . $search . '%')
            ))
            ->when($categoryId, fn($q) => $q->where('products.category_id', $categoryId))
            ->orderBy($sortCol, $sortDir);

        $products   = $query->paginate(10)->withQueryString();
        $categories = Category::orderBy('name')->get();

        return view('products.index', compact('products', 'categories', 'search', 'categoryId', 'sortInput', 'sortDir'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();
        return view('products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'sku'           => 'required|unique:products,sku',
            'name'          => 'required|string|max:150',
            'category_id'   => 'required|exists:categories,id',
            'price'         => 'required|numeric|min:0',
            'stock'         => 'required|integer|min:0',
            'minimum_stock' => 'required|integer|min:0',
        ], [
            'sku.required'         => 'SKU wajib diisi.',
            'sku.unique'           => 'SKU sudah digunakan. SKU harus unik.',
            'name.required'        => 'Nama produk wajib diisi.',
            'category_id.required' => 'Kategori wajib dipilih.',
            'category_id.exists'   => 'Kategori tidak valid.',
            'price.required'       => 'Harga wajib diisi.',
            'price.numeric'        => 'Harga harus berupa angka valid.',
            'price.min'            => 'Harga tidak boleh negatif.',
            'stock.required'       => 'Stok wajib diisi.',
            'stock.integer'        => 'Stok harus berupa bilangan bulat.',
            'stock.min'            => 'Stok tidak boleh negatif.',
            'minimum_stock.integer'=> 'Minimum stok harus berupa bilangan bulat.',
            'minimum_stock.min'    => 'Minimum stok tidak boleh negatif.',
        ]);

        $product = Product::create($request->only(['category_id', 'sku', 'name', 'price', 'stock', 'minimum_stock']));

        return redirect()->route('products.index')
            ->with('flash_success', 'Produk "' . $product->name . '" berhasil ditambahkan.');
    }

    public function edit(Product $product)
    {
        $categories = Category::orderBy('name')->get();
        return view('products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'sku'           => 'required|unique:products,sku,' . $product->id,
            'name'          => 'required|string|max:150',
            'category_id'   => 'required|exists:categories,id',
            'price'         => 'required|numeric|min:0',
            'minimum_stock' => 'required|integer|min:0',
        ], [
            'sku.required'         => 'SKU wajib diisi.',
            'sku.unique'           => 'SKU sudah digunakan oleh produk lain.',
            'name.required'        => 'Nama produk wajib diisi.',
            'category_id.required' => 'Kategori wajib dipilih.',
            'category_id.exists'   => 'Kategori tidak valid.',
            'price.required'       => 'Harga wajib diisi.',
            'price.numeric'        => 'Harga harus berupa angka valid.',
            'price.min'            => 'Harga tidak boleh negatif.',
            'minimum_stock.integer'=> 'Minimum stok harus berupa bilangan bulat.',
            'minimum_stock.min'    => 'Minimum stok tidak boleh negatif.',
        ]);

        $product->update($request->only(['category_id', 'sku', 'name', 'price', 'minimum_stock']));

        return redirect()->route('products.index')
            ->with('flash_success', 'Data produk "' . $product->name . '" berhasil diperbarui.');
    }

    public function destroy(Product $product)
    {
        $historyCount = $product->stockMovements()->count();
        if ($historyCount > 0) {
            return redirect()->route('products.index')
                ->with('flash_error', 'Gagal menghapus produk "' . $product->name . '". Produk ini telah memiliki ' . $historyCount . ' histori transaksi barang masuk/keluar (Referential Integrity Constraint).');
        }

        $name = $product->name;
        $product->delete();

        return redirect()->route('products.index')
            ->with('flash_success', 'Produk "' . $name . '" berhasil dihapus.');
    }
}

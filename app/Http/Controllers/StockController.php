<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\StockMovement;
use App\Models\Product;

class StockController extends Controller
{
    public function inForm(Request $request)
    {
        $products  = Product::orderBy('name')->get();
        $productId = $request->integer('product_id') ?: null;
        return view('stock.in', compact('products', 'productId'));
    }

    public function storeIn(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'required|integer|min:1',
            'notes'      => 'nullable|string|max:255',
        ], [
            'product_id.required' => 'Pilih produk yang valid.',
            'product_id.exists'   => 'Produk tidak ditemukan.',
            'quantity.required'   => 'Jumlah barang masuk wajib diisi.',
            'quantity.integer'    => 'Jumlah harus berupa bilangan bulat positif (> 0).',
            'quantity.min'        => 'Jumlah barang masuk harus berupa bilangan bulat positif (> 0).',
        ]);

        $userId = session('user.id');

        DB::transaction(function () use ($request, $userId) {
            Product::where('id', $request->product_id)
                ->increment('stock', $request->quantity);

            StockMovement::create([
                'product_id' => $request->product_id,
                'user_id'    => $userId,
                'type'       => 'IN',
                'quantity'   => $request->quantity,
                'notes'      => $request->notes ?: 'Penerimaan barang masuk',
            ]);
        });

        $product = Product::find($request->product_id);

        return redirect()->route('stock.history')
            ->with('flash_success', 'Transaksi barang masuk berhasil! Stok ' . $product->name . ' bertambah ' . $request->quantity . ' unit.');
    }

    public function outForm(Request $request)
    {
        $products  = Product::orderBy('name')->get();
        $productId = $request->integer('product_id') ?: null;
        return view('stock.out', compact('products', 'productId'));
    }

    public function storeOut(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'required|integer|min:1',
            'notes'      => 'nullable|string|max:255',
        ], [
            'product_id.required' => 'Pilih produk yang valid.',
            'product_id.exists'   => 'Produk tidak ditemukan.',
            'quantity.required'   => 'Jumlah barang keluar wajib diisi.',
            'quantity.integer'    => 'Jumlah harus berupa bilangan bulat positif (> 0).',
            'quantity.min'        => 'Jumlah barang keluar harus berupa bilangan bulat positif (> 0).',
        ]);

        $userId = session('user.id');

        try {
            DB::transaction(function () use ($request, $userId) {
                $product = Product::lockForUpdate()->find($request->product_id);

                if (!$product) {
                    throw new \Exception('Produk tidak ditemukan.');
                }

                if ($request->quantity > $product->stock) {
                    throw new \Exception('Jumlah barang keluar (' . $request->quantity . ' unit) melebihi stok yang tersedia (' . $product->stock . ' unit). Stok tidak boleh menjadi negatif.');
                }

                $product->decrement('stock', $request->quantity);

                StockMovement::create([
                    'product_id' => $request->product_id,
                    'user_id'    => $userId,
                    'type'       => 'OUT',
                    'quantity'   => $request->quantity,
                    'notes'      => $request->notes ?: 'Pengeluaran barang / penjualan',
                ]);
            });
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['quantity' => $e->getMessage()]);
        }

        $product      = Product::find($request->product_id);
        $remaining    = $product->stock;

        return redirect()->route('stock.history')
            ->with('flash_success', 'Transaksi barang keluar berhasil! Stok ' . $product->name . ' berkurang ' . $request->quantity . ' unit (Sisa stok: ' . $remaining . ').');
    }

    public function history(Request $request)
    {
        $typeFilter = $request->input('type', '');

        $movements = StockMovement::with(['product.category', 'user.profile'])
            ->when(in_array($typeFilter, ['IN', 'OUT']), fn($q) => $q->where('type', $typeFilter))
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        return view('stock.history', compact('movements', 'typeFilter'));
    }
}

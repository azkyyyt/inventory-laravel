<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Supplier;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::withCount('products')->orderBy('name')->get();
        return view('suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        return view('suppliers.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'         => 'required|string|max:150',
            'contact_name' => 'nullable|string|max:100',
            'phone'        => 'nullable|string|max:30',
            'email'        => 'nullable|email|max:100',
            'address'      => 'nullable|string',
        ], [
            'name.required' => 'Nama supplier wajib diisi.',
            'email.email'   => 'Format email tidak valid.',
        ]);

        $supplier = Supplier::create($request->only(['name', 'contact_name', 'phone', 'email', 'address']));

        return redirect()->route('suppliers.index')
            ->with('flash_success', 'Supplier "' . $supplier->name . '" berhasil ditambahkan.');
    }

    public function edit(Supplier $supplier)
    {
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $request->validate([
            'name'         => 'required|string|max:150',
            'contact_name' => 'nullable|string|max:100',
            'phone'        => 'nullable|string|max:30',
            'email'        => 'nullable|email|max:100',
            'address'      => 'nullable|string',
        ], [
            'name.required' => 'Nama supplier wajib diisi.',
            'email.email'   => 'Format email tidak valid.',
        ]);

        $supplier->update($request->only(['name', 'contact_name', 'phone', 'email', 'address']));

        return redirect()->route('suppliers.index')
            ->with('flash_success', 'Supplier "' . $supplier->name . '" berhasil diperbarui.');
    }

    public function destroy(Supplier $supplier)
    {
        $name = $supplier->name;
        $supplier->delete();
        return redirect()->route('suppliers.index')
            ->with('flash_success', 'Supplier "' . $name . '" berhasil dihapus.');
    }
}

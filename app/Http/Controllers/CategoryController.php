<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('products')->orderBy('name')->get();
        return view('categories.index', compact('categories'));
    }

    public function create()
    {
        return view('categories.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|unique:categories,name',
            'description' => 'nullable|string',
        ], [
            'name.required' => 'Nama kategori wajib diisi.',
            'name.unique'   => 'Kategori "' . $request->name . '" sudah ada.',
        ]);

        $category = Category::create($request->only(['name', 'description']));

        return redirect()->route('categories.index')
            ->with('flash_success', 'Kategori "' . $category->name . '" berhasil ditambahkan.');
    }

    public function edit(Category $category)
    {
        return view('categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name'        => 'required|unique:categories,name,' . $category->id,
            'description' => 'nullable|string',
        ], [
            'name.required' => 'Nama kategori wajib diisi.',
            'name.unique'   => 'Kategori "' . $request->name . '" sudah digunakan.',
        ]);

        $category->update($request->only(['name', 'description']));

        return redirect()->route('categories.index')
            ->with('flash_success', 'Kategori "' . $category->name . '" berhasil diperbarui.');
    }

    public function destroy(Category $category)
    {
        $name = $category->name;
        $category->delete();
        return redirect()->route('categories.index')
            ->with('flash_success', 'Kategori "' . $name . '" berhasil dihapus.');
    }
}

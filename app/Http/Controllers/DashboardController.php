<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $totalProducts   = Product::count();
        $inventoryValue  = Product::selectRaw('COALESCE(SUM(stock * price), 0) as total')->value('total') ?? 0;
        $lowStockCount   = Product::whereRaw('stock <= minimum_stock')->count();
        $totalIn         = StockMovement::where('type', 'IN')->sum('quantity');
        $totalOut        = StockMovement::where('type', 'OUT')->sum('quantity');

        $lowStockProducts = Product::with('category')
            ->whereRaw('stock <= minimum_stock')
            ->orderBy('stock', 'asc')
            ->get();

        $inventoryReports = Product::with('category')
            ->withCount([
                'stockMovements as total_in' => fn($q) => $q->where('type', 'IN')->select(DB::raw('COALESCE(SUM(quantity),0)')),
                'stockMovements as total_out' => fn($q) => $q->where('type', 'OUT')->select(DB::raw('COALESCE(SUM(quantity),0)')),
            ])
            ->orderBy('name', 'asc')
            ->get();

        return view('dashboard', compact(
            'totalProducts',
            'inventoryValue',
            'lowStockCount',
            'totalIn',
            'totalOut',
            'lowStockProducts',
            'inventoryReports'
        ));
    }
}

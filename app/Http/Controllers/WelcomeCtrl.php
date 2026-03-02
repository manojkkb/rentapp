<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Items;
use App\Models\Vendor;
use App\Models\Category;
use Illuminate\Support\Facades\DB;

class WelcomeCtrl extends Controller
{
    public function index()
    {
        // Get statistics
        $totalItems = Items::where('is_active', true)->count();
        $totalVendors = Vendor::count();
        $totalCategories = Category::count();
        
        // Get categories with items count
        $categories = Category::withCount(['items' => function($query) {
            $query->where('is_active', true);
        }])
        ->having('items_count', '>', 0)
        ->orderBy('items_count', 'desc')
        ->limit(12)
        ->get();
        
        // Get featured items (latest available items)
        $featuredItems = Items::with(['category', 'vendor'])
            ->where('is_active', true)
            ->where('is_available', true)
            ->orderBy('created_at', 'desc')
            ->limit(8)
            ->get();
        
        return view('welcome', compact('totalItems', 'totalVendors', 'totalCategories', 'categories', 'featuredItems'));
    }
}

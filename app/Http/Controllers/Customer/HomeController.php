<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Items;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Display the customer home page
     */
    public function index()
    {
        // Get featured items (you can add your own logic)
        $featuredItems = Items::where('is_active', true)
            ->latest()
            ->take(8)
            ->get();
        
        // Get all categories
        $categories = Category::where('is_active', true)
            ->withCount('items')
            ->get();
        
        return view('customer.home.index', compact('featuredItems', 'categories'));
    }
}

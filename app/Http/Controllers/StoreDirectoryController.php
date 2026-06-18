<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StoreDirectoryController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));

        $stores = Vendor::query()
            ->active()
            ->with([
                'businessCategory:id,name',
                'storeSettings.primaryLocation',
                'locations' => fn ($q) => $q
                    ->where('is_active', true)
                    ->orderByDesc('is_default')
                    ->orderBy('sort_order')
                    ->orderBy('name'),
            ])
            ->when($search !== '', function ($query) use ($search) {
                $term = '%'.addcslashes($search, '%_\\').'%';

                $query->where(function ($q) use ($term) {
                    $q->where('name', 'like', $term)
                        ->orWhere('city', 'like', $term)
                        ->orWhere('state', 'like', $term)
                        ->orWhereHas('businessCategory', fn ($cat) => $cat->where('name', 'like', $term))
                        ->orWhereHas(
                            'locations',
                            fn ($loc) => $loc->where('is_active', true)->where('name', 'like', $term),
                        );
                });
            })
            ->orderBy('name')
            ->paginate(24)
            ->withQueryString();

        return view('stores.index', compact('stores', 'search'));
    }
}

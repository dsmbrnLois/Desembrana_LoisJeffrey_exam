<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductListingController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Product::query();

        if ($search = $request->input('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        // Sort
        $sort = $request->input('sort', 'latest');
        match ($sort) {
            'price_asc' => $query->orderBy('price', 'asc'),
            'price_desc' => $query->orderBy('price', 'desc'),
            default => $query->latest(),
        };

        $products = $query->paginate(8)->withQueryString();

        return Inertia::render('storefront/ProductListing', [
            'products' => $products,
            'filters' => [
                'search' => $search,
                'sort' => $sort,
            ],
        ]);
    }

    public function show(Product $product): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'product' => $product,
        ]);
    }
}

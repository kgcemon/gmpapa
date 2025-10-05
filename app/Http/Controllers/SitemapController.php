<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Carbon\Carbon;

class SitemapController extends Controller
{
    public function index()
    {
        $urls = [];

        $urls[] = [
            'loc' => url('/'),
            'lastmod' => Carbon::now()->toAtomString(),
            'changefreq' => 'daily',
            'priority' => '1.0'
        ];

        $urls[] = [
            'loc' => url('/about'),
            'lastmod' => Carbon::now()->toAtomString(),
            'changefreq' => 'monthly',
            'priority' => '0.8'
        ];

        $products = Product::where('name', '!=', 'Wallet')->get();
        foreach ($products as $product) {
            $urls[] = [
                'loc' => url('/product/' . $product->slug),
                'lastmod' => optional($product->updated_at)->toAtomString() ?? now()->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => '1.0'
            ];
        }

        return response()->json([
           'status' => true,
           'urls' => $urls,
        ]);
}
}

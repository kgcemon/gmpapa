<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Carbon\Carbon;

class SitemapController extends Controller
{
    public function index()
    {
        $urls = [];

        $products = Product::where('name', '!=', 'Wallet')->get();
        foreach ($products as $product) {
            $urls[] = [
                'loc' => 'https://freefirebd.com/product/' . $product->slug,
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

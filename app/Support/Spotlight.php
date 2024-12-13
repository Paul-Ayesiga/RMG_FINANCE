<?php
namespace App\Support;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\Product;

class Spotlight
{
    public function search(Request $request)
{
    // Get the search term
    $searchTerm = $request->search;

    // Fetch products matching the search term
    $products = Product::query()
        ->where(function ($query) use ($searchTerm) {
            // Search by product name
            $query->where('name', 'ilike', "%$searchTerm%")
                ->orWhereHas('brand', function ($q) use ($searchTerm) {
                    // Search by brand name
                    $q->where('name', 'ilike', "%$searchTerm%");
                })
                ->orWhereHas('categories', function ($q) use ($searchTerm) {
                    // Search by category name
                    $q->where('name', 'ilike', "%$searchTerm%");
                });
        })
        ->take(5) // Limit results to 5 for better performance
        ->get()
        ->map(function (Product $product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'avatar' => $product->avatar ?? '/product.png', // Default image if not available
                'link' => url("/glowie/product/{$product->id}"), // Link to view the product
            ];
        });

    // Return the collection of products
    return response()->json($products);
}

}

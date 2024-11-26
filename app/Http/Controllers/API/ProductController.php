<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function all (Request $request) {
        $id = $request->input('id');
        $limit = $request->input('limit', 6);
        $name = $request->input('name');
        $description = $request->input('description');
        $tags = $request->input('tags');
        $categories = $request->input('categories');

        $price_from = $request->input('price_from');
        $price_to = $request->input('price_to');

        if($id) {
            $product = Product::with(['categories', 'galleries'])->find('id');

            if($product) {
                return ResponseFormatter::success(
                    $product, 
                    'Data produk berhasil diambil',
                );
            }
            else {
                return ResponseFormatter::error(
                    null, 
                    'Data produk tidak ada',
                    404
                );
            }
        }
        $product = Product::with(['category', 'galleries']);
        if ($name) {
            $product->where('name', 'like', '%' . $name . '%');
        }
        if ($description) {
            $product->where('description', 'like', '%' . $description . '%');
        }
        if ($tags) {
            $product->where('tags', 'like', '%' . $tags . '%');
        }
        if ($price_from) {
            $product->where('price', '>=', $price_from);
        }
        if ($price_to) {
            $product->where('price', '<=', $price_to);
        }
        if ($categories) {
            $product->where('categories', $categories);
        }

        return ResponseFormatter::success(
            $product->paginate($limit),
            'Data produk berhasil diambil'
        );
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validasi input
    $request->validate([
        'name' => 'required|string|max:255',
        'description' => 'required|string',
        'tags' => 'nullable|string',
        'categories_id' => 'required|exists:product_categories,id',
        'price' => 'required|numeric|min:0',
        'quantity' => 'required|integer|min:0',
    ]);

    // Buat data produk baru
    $product = Product::create([
        'name' => $request->name,
        'description' => $request->description,
        'tags' => $request->tags,
        'categories_id' => $request->categories_id,
        'price' => $request->price,
        'quantity' => $request->quantity,
    ]);

    // Kembalikan respons sukses
    return ResponseFormatter::success(
        $product,
        'Produk berhasil ditambahkan'
    );
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}

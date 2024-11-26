<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use Illuminate\Http\Request;

class ProductCategoryControlller extends Controller
{
    // Method untuk mendapatkan data kategori
    public function all (Request $request) {
        $id = $request->input('id');
        $limit = $request->input('limit');
        $name = $request->input('name');
        $show_product = $request->input('show_product');

        if($id) {
            $category = ProductCategory::with(['products'])->find('id');
            if($category) {
                return ResponseFormatter::success(
                    $category, 
                    'Data kategori berhasil diambil',
                );
            }
            else {
                return ResponseFormatter::error(
                    null, 
                    'Data kategori tidak ada',
                    404
                );
            }
        }

        $category = ProductCategory::query();
        if ($name) {
            $category->where('name', 'like', '%' . $name . '%');
        }
        if ($show_product) {
            $category->with('products');
        }
        return ResponseFormatter::success(
            $category->paginate($limit),
            'Data kategori berhasil diambil'
        );
    }

    // Method untuk menambahkan data kategori baru
    public function store(Request $request)
    {
        // Validasi data yang dikirim
        $request->validate([
            'name' => 'required|unique:product_categories',
        ]);

        // Buat kategori baru
        $category = ProductCategory::create([
            'name' => $request->name,
        ]);

        // Return respons sukses
        return ResponseFormatter::success(
            $category,
            'Kategori baru berhasil ditambahkan'
        );
    }
}

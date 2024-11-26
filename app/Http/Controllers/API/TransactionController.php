<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use GuzzleHttp\Psr7\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    public function all (Request $request) {
        $id = $request->input('id');
        $limit = $request->input('limit', 6);
        $status = $request->input('status');

        if($id) {
            $transaction = Transaction::with(['items.product'])->find('id');

            if($transaction) {
                return ResponseFormatter::success(
                    $transaction, 
                    'Data transaksi berhasil diambil',
                );
            }
            else {
                return ResponseFormatter::error(
                    null, 
                    'Data transaksi tidak ada',
                    404
                );
            }
        }

        $transaction = Transaction::with(['items.product'])->where('users_id', Auth::user()->id);
        if ($status) {
            $transaction->where('status', $status); 
        }

        return ResponseFormatter::success([
            $transaction->paginate($limit),
            'Data list transaksi berhasil diambil'
        ]);
    }

    public function checkout (Request $request) {
        // Validasi Input
        $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            // 'total_price' => 'required|numeric|min:0',
            'shipping_price' => 'required',
            'status' => 'required|in:PENDING,SUCCESS,CANCELLED,FAILED,SHIPPING,SHIPPED',
        ]);

        // Hitung total harga berdasarkan produk dan kuantitas
        $total_price = 0;
        // Iterasi setiap item di transaksi
        foreach ($request->items as $item) {
            $product = Product::find($item['id']);  // Ambil produk dari database
            if ($product) {
                $total_price += $product->price * $item['quantity'];
            } else {
                return ResponseFormatter::error(
                    null,
                    'Produk dengan ID ' . $item['id'] . ' tidak ditemukan.',
                    404
                );
            }
        }
        $total_price += $request->shipping_price; // Tambahkan biaya pengiriman

        // Simpan data transaksi
        $transaction = Transaction::create([
            'users_id' => Auth::user()->id,
            'address' => $request->address,
            'total_price' => $total_price,
            'shipping_price' => $request->shipping_price,
            'status' => $request->status,
        ]);

        // Tambahkan item transaksi
        foreach ($request->items as $product) {
            TransactionItem::create([
                'users_id' => Auth::user()->id,
                'products_id' => $product['id'],
                'transactions_id' => $transaction->id,
                'quantity' => $product['quantity']
            ]);
        }

        return ResponseFormatter::success([
            $transaction->load('items.product'),
            'Transaksi berhasil'
        ]);
    }
}

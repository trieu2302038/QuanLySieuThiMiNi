<?php

namespace App\Http\Controllers\Cashier;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\InvoiceDetail;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class InvoiceController extends Controller
{
    public function index()
    {
        $invoices = Invoice::with('user', 'details.product')
            ->orderBy('created_at', 'desc')
            ->get(); // Lấy toàn bộ danh sách hóa đơn

        return response()->json($invoices);
    }
    
    public function show($id)
    {
        return response()->json(
            Invoice::with(['user', 'details.product'])->findOrFail($id)
         );
    }


    public function store(Request $request)
    {
        try {
            $request->validate([
                'cart' => 'required|array',
                'cart.*.product_id' => 'required|exists:products,id',
                'cart.*.quantity' => 'required|integer|min:1',
                'payment_method' => 'required|string',
            ]);
    
            $totalAmount = 0;
            foreach ($request->cart as $item) {
                $product = Product::findOrFail($item['product_id']);
                if ($product->stock < $item['quantity']) {
                    return response()->json(['error' => 'Số lượng sản phẩm không đủ'], 400);
                }
                $totalAmount += $product->sale_price * $item['quantity'];
            }
    
            $invoice = Invoice::create([
                'user_id' => Auth::id(),
                'total_amount' => $totalAmount,
                'payment_method' => $request->payment_method,
            ]);
    
            foreach ($request->cart as $item) {
                $product = Product::findOrFail($item['product_id']);
                $product->decrement('stock', $item['quantity']);
                
                InvoiceDetail::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $product->sale_price,
                    'total' => $product->sale_price * $item['quantity'],
                ]);
            }
    
            return response()->json(['message' => 'Hóa đơn đã được tạo!', 'invoice' => $invoice]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Lỗi khi tạo hóa đơn'], 500);
        }
    }

    public function destroy($id)
    {
        $invoice = Invoice::findOrFail($id);
        $invoice->delete();

        return response()->json(['message' => 'Hóa đơn đã bị xóa']);
    }
}
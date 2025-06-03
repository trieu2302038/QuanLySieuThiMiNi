<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Cart;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class OrderController extends Controller
{

    // Lấy danh sách đơn hàng của người dùng, có thể lọc theo trạng thái và ngày
    public function index(Request $request)
    {
        $query = Order::where('user_id', Auth::id())->with('orderDetails.product');

        // Lọc theo trạng thái đơn hàng
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        // Lọc theo ngày (chuyển từ GMT+7 sang UTC để so sánh với DB)
        if ($request->has('start_date')) {
            $start = Carbon::parse($request->start_date)->startOfDay()->subHours(7);
            $query->where('created_at', '>=', $start);
        }

        if ($request->has('end_date')) {
            $end = Carbon::parse($request->end_date)->endOfDay()->subHours(7);
            $query->where('created_at', '<=', $end);
        }

        // Lấy các đơn hàng đã lọc
        $orders = $query->get();

        return response()->json($orders);
    }


    public function show($id)
    {
        $order = Order::where('user_id', Auth::id())->with('orderDetails.product')->findOrFail($id);
        return response()->json($order);
    }

    // Hiển thị trang chi tiết đơn hàng
    public function orderDetailPage($id)
    {
        return view('user.order-detail', compact('id'));
    }

    // Tạo đơn hàng từ giỏ hàng
    public function placeOrder(Request $request)
    {
        $user = Auth::user();
        $cartItems = Cart::where('user_id', $user->id)->with('product')->get();

        if ($cartItems->isEmpty()) {
            return response()->json(['message' => 'Giỏ hàng trống!'], 400);
        }

        // Tính tổng tiền
        $total = $cartItems->sum(function ($cart) {
            return $cart->product->sale_price * $cart->quantity;
        });

        // Tạo đơn hàng
        $order = Order::create([
            'user_id' => $user->id,
            'full_name' => $request->full_name,
            'phone' => $request->phone,
            'address' => $request->address,
            'payment_method' => $request->payment_method,
            'total' => $total,
            'status' => 'pending'
        ]);

        // Lưu chi tiết đơn hàng và trừ số lượng sản phẩm
        foreach ($cartItems as $cart) {
            $product = $cart->product;

            if ($product->stock < $cart->quantity) {
                return response()->json(['message' => "Sản phẩm {$product->name} không đủ hàng!"], 400);
            }

            OrderDetail::create([
                'order_id' => $order->id,
                'product_id' => $cart->product_id,
                'quantity' => $cart->quantity,
                'price' => $cart->product->sale_price
            ]);

            // Trừ số lượng tồn kho
            $product->stock -= $cart->quantity;
            $product->save();
        }

        // Xóa giỏ hàng sau khi đặt hàng thành công
        Cart::where('user_id', $user->id)->delete();

        return response()->json(['message' => 'Đặt hàng thành công!', 'order' => $order]);
    }

    public function cancelOrder($id) {
        $order = Order::where('id', $id)
            ->where('user_id', auth()->id())
            ->with('orderDetails.product') // Load chi tiết đơn hàng và sản phẩm
            ->first();
    
        if (!$order) {
            return response()->json(['message' => 'Đơn hàng không tồn tại hoặc không thuộc về bạn!'], 404);
        }
    
        if ($order->status !== 'pending') {
            return response()->json(['message' => 'Bạn chỉ có thể hủy đơn hàng khi trạng thái là Chờ xác nhận!'], 400);
        }
    
        // Hoàn lại số lượng sản phẩm
        foreach ($order->orderDetails as $detail) {
            if ($detail->product) {
                $detail->product->increment('stock', $detail->quantity); // Đảm bảo cập nhật đúng cột stock
            }
        }
    
        // Cập nhật trạng thái đơn hàng
        $order->update(['status' => 'canceled']);
    
        return response()->json(['message' => 'Đơn hàng đã được hủy thành công!']);
    }
    

    // Hiển thị trang danh sách đơn hàng
    public function ordersPage()
    {
        return view('user.orders');
    }
}

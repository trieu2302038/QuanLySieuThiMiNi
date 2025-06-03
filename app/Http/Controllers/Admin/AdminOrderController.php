<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderDetail;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AdminOrderController extends Controller
{
    // Lấy toàn bộ danh sách đơn hàng cho Admin
    public function index(Request $request)
    {
        $query = Order::with('user', 'orderDetails.product');
    
        // Lọc theo trạng thái đơn hàng
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }
    
        // Lọc theo ngày tạo đơn hàng (từ GMT+7 chuyển sang UTC để so sánh)
        if ($request->has('start_date')) {
            $start = \Carbon\Carbon::parse($request->start_date)->startOfDay()->subHours(7);
            $query->where('created_at', '>=', $start);
        }
    
        if ($request->has('end_date')) {
            $end = \Carbon\Carbon::parse($request->end_date)->endOfDay()->subHours(7);
            $query->where('created_at', '<=', $end);
        }
    
        $orders = $query->orderBy('created_at', 'desc')->get();
    
        return response()->json($orders);
    }
    
    // Cập nhật trạng thái đơn hàng
    public function update(Request $request, $id)
    {
        $order = Order::findOrFail($id);
        
        // Danh sách trạng thái theo thứ tự
        $statusOrder = ['pending', 'processing', 'shipped', 'completed', 'canceled'];
    
        // Kiểm tra trạng thái mới có hợp lệ không
        if (!in_array($request->status, $statusOrder)) {
            return response()->json(['message' => 'Trạng thái không hợp lệ!'], 400);
        }
    
        // Kiểm tra không cập nhật lùi trạng thái
        if (array_search($request->status, $statusOrder) < array_search($order->status, $statusOrder)) {
            return response()->json(['message' => 'Không thể cập nhật lùi trạng thái đơn hàng!'], 400);
        }
    
        $order->status = $request->status;
        $order->save();
    
        return response()->json(['message' => 'Cập nhật trạng thái thành công!']);
    }
    
    // Xóa đơn hàng
    public function destroy($id)
    {
        $order = Order::findOrFail($id);
        $order->delete();

        return response()->json(['message' => 'Xóa đơn hàng thành công!']);
    }

    // Lấy chi tiết đơn hàng theo ID
    public function show($id)
    {
        $order = Order::with('user', 'orderDetails.product')->findOrFail($id);
        return response()->json($order);
    }
}



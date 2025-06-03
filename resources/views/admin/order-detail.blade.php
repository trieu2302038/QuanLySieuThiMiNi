@extends('layouts.admin-layout')

@section('title', 'Chi Tiết Đơn Hàng')

@section('content')
<div class="container">
    <h1 class="text-center my-4">📝 Chi Tiết Đơn Hàng</h1>

    <div id="order-detail-content"></div>

    <div class="text-end mt-3">
        <a href="/admin/orders" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Quay Lại</a>
    </div>
</div>

<script>
    async function loadOrderDetail() {
        try {
            const response = await axios.get(`/api/admin/orders/{{ $id }}`, {
                headers: { 'Authorization': 'Bearer {{ auth()->user()->api_token }}' }
            });

            const order = response.data;

            // Ánh xạ trạng thái từ tiếng Anh sang tiếng Việt
            const statusMapping = {
                'pending': { text: 'Chờ xác nhận', class: 'bg-warning' },
                'processing': { text: 'Đang xử lý', class: 'bg-primary' },
                'shipped': { text: 'Đang giao hàng', class: 'bg-info' },
                'completed': { text: 'Hoàn thành', class: 'bg-success' },
                'canceled': { text: 'Đã hủy', class: 'bg-danger' }
            };

            let translatedStatus = statusMapping[order.status] || { text: 'Không xác định', class: 'bg-secondary' };

            // Chuyển đổi ngày giờ hiển thị theo định dạng DD/MM/YYYY HH:mm:ss
            let orderDate = new Date(order.created_at).toLocaleString('vi-VN', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });

            let html = `
                <div class="card shadow-lg p-3 mb-4">
                    <div class="card-header bg-dark text-white">
                        <h4><i class="fas fa-receipt"></i> Thông Tin Đơn Hàng</h4>
                    </div>
                    <div class="card-body">
                        <p><i class="fas fa-calendar-alt"></i> <strong>Ngày Đặt:</strong> ${orderDate}</p>
                        <p><i class="fas fa-user"></i> <strong>Họ & Tên:</strong> ${order.full_name}</p>
                        <p><i class="fas fa-phone"></i> <strong>Số Điện Thoại:</strong> ${order.phone}</p>
                        <p><i class="fas fa-map-marker-alt"></i> <strong>Địa Chỉ:</strong> ${order.address}</p>
                        <p><i class="fas fa-credit-card"></i> <strong>Phương Thức Thanh Toán:</strong> ${order.payment_method}</p>
                        <p>
                            <i class="fas fa-info-circle"></i> <strong>Trạng Thái:</strong>
                            <span class="badge ${translatedStatus.class}">${translatedStatus.text}</span>
                        </p>
                    </div>
                </div>

                <div class="card shadow-lg p-3">
                    <div class="card-header bg-secondary text-white">
                        <h4><i class="fas fa-box"></i> Sản Phẩm Đặt Mua</h4>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Hình Ảnh</th>
                                    <th>Sản Phẩm</th>
                                    <th>Giá</th>
                                    <th>Số Lượng</th>
                                    <th>Thành Tiền</th>
                                </tr>
                            </thead>
                            <tbody>
            `;

            order.order_details.forEach(item => {
                const imageUrl = item.product.image ? `/storage/${item.product.image}` : 'https://via.placeholder.com/100';
                const itemTotal = item.price * item.quantity;

                html += `
                    <tr>
                        <td><img src="${imageUrl}" width="80" height="80" class="rounded"></td>
                        <td>${item.product.name}</td>
                        <td>${new Intl.NumberFormat().format(item.price)} VNĐ</td>
                        <td>${item.quantity}</td>
                        <td>${new Intl.NumberFormat().format(itemTotal)} VNĐ</td>
                    </tr>
                `;
            });

            html += `</tbody></table>
                     <h4 class="text-end"><strong>Tổng Cộng: ${new Intl.NumberFormat().format(order.total)} VNĐ</strong></h4>
                    </div>
                </div>`;

            document.getElementById('order-detail-content').innerHTML = html;
        } catch (error) {
            console.error('Lỗi tải chi tiết đơn hàng:', error);
        }
    }

    document.addEventListener('DOMContentLoaded', loadOrderDetail);
</script>
@endsection

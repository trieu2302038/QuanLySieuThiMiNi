@extends('layouts.user-layout')

@section('title', 'Đơn Hàng Của Tôi')

@section('content')
<div class="container">
    <h1 class="text-center my-4">📦 Đơn Hàng Của Tôi</h1>

    <!-- Bộ lọc tìm kiếm -->
    <div class="row mb-4">
        <div class="col-md-4">
            <select id="status-filter" class="form-control">
                <option value="">Tất cả trạng thái</option>
                <option value="pending">Chờ xác nhận</option>
                <option value="processing">Đang xử lý</option>
                <option value="shipped">Đang giao hàng</option>
                <option value="completed">Hoàn thành</option>
                <option value="canceled">Đã hủy</option>
            </select>
        </div>
        <div class="col-md-4">
            <input type="date" id="start-date-filter" class="form-control" placeholder="Từ ngày">
        </div>
        <div class="col-md-4">
            <input type="date" id="end-date-filter" class="form-control" placeholder="Đến ngày">
        </div>
    </div>

    <!-- Nội dung đơn hàng -->
    <div id="order-content"></div>
</div>

<script>
    // Hàm tải đơn hàng với bộ lọc
    async function loadOrders() {
        const statusFilter = document.getElementById('status-filter').value;
        const startDateFilter = document.getElementById('start-date-filter').value;
        const endDateFilter = document.getElementById('end-date-filter').value;

        try {
            let url = '/api/orders';

            // Tạo các tham số truy vấn (query parameters) cho bộ lọc
            const params = new URLSearchParams();
            if (statusFilter) params.append('status', statusFilter);
            if (startDateFilter) params.append('start_date', startDateFilter);
            if (endDateFilter) params.append('end_date', endDateFilter);

            // Gửi yêu cầu API với các tham số truy vấn
            const response = await axios.get(url, {
                params: params,
                headers: { 'Authorization': 'Bearer {{ auth()->user()->api_token }}' }
            });

            let orders = response.data;

            // Sắp xếp đơn hàng mới nhất lên trên
            orders.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));

            // Ánh xạ trạng thái đơn hàng
            const statusMapping = {
                'pending': { text: 'Chờ xác nhận', class: 'bg-warning text-dark' },
                'processing': { text: 'Đang xử lý', class: 'bg-primary' },
                'shipped': { text: 'Đang giao hàng', class: 'bg-info' },
                'completed': { text: 'Hoàn thành', class: 'bg-success' },
                'canceled': { text: 'Đã hủy', class: 'bg-danger' }
            };

            let html = `
                <div class="card shadow-sm border-0 rounded-3">
                    <div class="card-header bg-light">
                        <h4 class="mb-0"><i class="fas fa-list"></i> Danh Sách Đơn Hàng</h4>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Ngày Đặt</th>
                                    <th>Tổng Tiền</th>
                                    <th>Trạng Thái</th>
                                    <th>Chi Tiết</th>
                                    <th>Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
            `;

            orders.forEach(order => {
                let translatedStatus = statusMapping[order.status] || { text: 'Không xác định', class: 'bg-secondary' };
                
                // Chuyển đổi ngày giờ hiển thị
                let orderDate = new Date(order.created_at).toLocaleString('vi-VN', {
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit'
                });

                // Hiển thị nút hủy đơn nếu trạng thái là "Chờ xác nhận"
                let cancelButton = order.status === 'pending' ? 
                    `<button class="btn btn-sm btn-danger" onclick="cancelOrder(${order.id})">
                        <i class="fas fa-times"></i> Hủy Đơn
                    </button>` : 
                    '';

                html += `
                    <tr>
                        <td>${orderDate}</td>
                        <td>${new Intl.NumberFormat().format(order.total)} VNĐ</td>
                        <td>
                            <span class="badge ${translatedStatus.class} px-3 py-2">${translatedStatus.text}</span>
                        </td>
                        <td>
                            <button class="btn btn-outline-primary btn-sm" onclick="viewOrderDetails(${order.id})">
                                <i class="fas fa-eye"></i> Xem
                            </button>
                        </td>
                        <td>${cancelButton}</td>
                    </tr>
                `;
            });

            html += `</tbody></table></div></div>`;
            document.getElementById('order-content').innerHTML = html;
        } catch (error) {
            console.error('Lỗi tải đơn hàng:', error);
        }
    }

    // Hàm xử lý hủy đơn hàng
    async function cancelOrder(orderId) {
        if (!confirm("Bạn có chắc muốn hủy đơn hàng này?")) return;

        try {
            const response = await axios.post(`/api/orders/${orderId}/cancel`, {}, {
                headers: { 'Authorization': `Bearer {{ auth()->user()->api_token }}` }
            });

            alert(response.data.message);
            loadOrders(); // Load lại danh sách đơn hàng
        } catch (error) {
            console.error('Lỗi hủy đơn hàng:', error);
            alert("Hủy đơn hàng thất bại!");
        }
    }

    // Lắng nghe sự kiện thay đổi bộ lọc
    document.getElementById('status-filter').addEventListener('change', loadOrders);
    document.getElementById('start-date-filter').addEventListener('change', loadOrders);
    document.getElementById('end-date-filter').addEventListener('change', loadOrders);

    document.addEventListener('DOMContentLoaded', loadOrders);

    function viewOrderDetails(orderId) {
        window.location.href = `/orders/${orderId}`;
    }
</script>

@endsection

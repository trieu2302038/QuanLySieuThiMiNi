@extends('layouts.admin-layout')

@section('title', 'Quản Lý Đơn Hàng')

@section('content')
<div class="container">
    <h1 class="text-center my-4">📊 Quản Lý Đơn Hàng</h1>

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

    <div id="admin-order-content"></div>
</div>

<script>
    async function loadAdminOrders() {
        const statusFilter = document.getElementById('status-filter').value;
        const startDateFilter = document.getElementById('start-date-filter').value;
        const endDateFilter = document.getElementById('end-date-filter').value;

        try {
            let url = '/api/admin/orders';
            const params = new URLSearchParams();

            if (statusFilter) params.append('status', statusFilter);
            if (startDateFilter) params.append('start_date', startDateFilter);
            if (endDateFilter) params.append('end_date', endDateFilter);

            const response = await axios.get(url, {
                params: params,
                headers: { 'Authorization': 'Bearer {{ auth()->user()->api_token }}' }
            });

            let orders = response.data;
            orders.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));

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
                        <h4 class="mb-0"><i class="fas fa-box"></i> Danh Sách Đơn Hàng</h4>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Ngày Đặt</th>
                                    <th>Khách Hàng</th>
                                    <th>Tổng Tiền</th>
                                    <th>Trạng Thái</th>
                                    <th>Chi Tiết</th>
                                    <th>Hành Động</th>
                                </tr>
                            </thead>
                            <tbody>
            `;

            orders.forEach(order => {
                let translatedStatus = statusMapping[order.status] || { text: 'Không xác định', class: 'bg-secondary' };
                let orderDate = new Date(order.created_at).toLocaleString('vi-VN', {
                    day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit'
                });

                let statusOptions = Object.keys(statusMapping).map(status => {
                    return `<option value="${status}" ${order.status === status ? 'selected' : ''}>${statusMapping[status].text}</option>`;
                }).join('');

                html += `
                    <tr>
                        <td>${orderDate}</td>
                        <td>${order.full_name || 'N/A'}</td>
                        <td>${new Intl.NumberFormat().format(order.total)} VNĐ</td>
                        <td>
                            <select class="form-select form-select-sm" 
                                onchange="updateOrderStatus(${order.id}, this.value, this)" 
                                data-previous="${order.status}">
                                ${statusOptions}
                            </select>
                        </td>
                        <td>
                            <button class="btn btn-outline-primary btn-sm" onclick="viewOrderDetails(${order.id})">
                                <i class="fas fa-eye"></i> Xem
                            </button>
                        </td>
                        <td>
                            ${
                                (order.status === 'canceled' || order.status === 'completed') 
                                ? `<button class="btn btn-sm btn-danger" onclick="deleteOrder(${order.id})">
                                    <i class="fas fa-trash"></i> Xóa
                                </button>` 
                                : ''
                            }
                        </td>
                    </tr>
                `;
            });

            html += `</tbody></table></div></div>`;
            document.getElementById('admin-order-content').innerHTML = html;
        } catch (error) {
            console.error('Lỗi tải đơn hàng:', error);
        }
    }

    async function updateOrderStatus(orderId, status, selectElement) {
        try {
            const response = await axios.put(`/api/admin/orders/${orderId}`, { status }, {
                headers: { 'Authorization': 'Bearer {{ auth()->user()->api_token }}' }
            });

            alert('✅ Cập nhật trạng thái thành công!');
        } catch (error) {
            console.error('❌ Lỗi cập nhật:', error);
            alert("Lỗi cập nhật trạng thái!");
            selectElement.value = selectElement.getAttribute("data-previous"); // quay về giá trị cũ
        }
    }

    function viewOrderDetails(orderId) {
        window.location.href = `/admin/orders/${orderId}`;
    }

    async function deleteOrder(orderId) {
        if (!confirm("Bạn có chắc muốn xóa đơn hàng này?")) return;
        try {
            const response = await axios.delete(`/api/admin/orders/${orderId}`, {
                headers: { 'Authorization': 'Bearer {{ auth()->user()->api_token }}' }
            });
            alert("✅ Xóa đơn hàng thành công!");
            loadAdminOrders();
        } catch (error) {
            console.error('❌ Lỗi xóa:', error);
            alert("Không thể xóa đơn hàng!");
        }
    }

    // Lắng nghe sự kiện thay đổi bộ lọc
    document.getElementById('status-filter').addEventListener('change', loadAdminOrders);
    document.getElementById('start-date-filter').addEventListener('change', loadAdminOrders);
    document.getElementById('end-date-filter').addEventListener('change', loadAdminOrders);

    document.addEventListener('DOMContentLoaded', loadAdminOrders);
</script>
@endsection

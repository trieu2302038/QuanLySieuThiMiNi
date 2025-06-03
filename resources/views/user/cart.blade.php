@extends('layouts.user-layout')

@section('title', 'Giỏ Hàng')

@section('content')
<meta name="api-token" content="{{ auth()->user()->api_token }}">

<div class="container">
    <h1 class="text-center my-4">🛒 Giỏ Hàng</h1>

    <div id="cart-content"></div>

    <div class="text-end mt-3">
        <h4><strong>Tổng tiền: <span id="totalAmount">0</span> VNĐ</strong></h4>
        <button id="placeOrderBtn" class="btn btn-success btn-lg mt-2" data-bs-toggle="modal" data-bs-target="#orderModal" disabled>🛍️ Đặt Hàng</button>
    </div>
</div>

<!-- Modal Đặt Hàng -->
<div class="modal fade" id="orderModal" tabindex="-1" aria-labelledby="orderModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="orderModalLabel">📦 Thông Tin Đặt Hàng</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label>Họ & Tên</label>
                    <input type="text" id="fullName" class="form-control" placeholder="Nhập tên người nhận">
                </div>
                <div class="mb-3">
                    <label>Số Điện Thoại</label>
                   <input type="text" id="phone" class="form-control" placeholder="Nhập số điện thoại" 
                        maxlength="10" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                </div>
                <div class="mb-3">
                    <label>Địa Chỉ Nhận Hàng</label>
                    <textarea id="address" class="form-control" placeholder="Nhập địa chỉ nhận hàng"></textarea>
                </div>
                <div class="mb-3">
                    <label>Phương Thức Thanh Toán</label>
                    <select id="paymentMethod" class="form-select">
                        <option value="COD">Thanh toán khi nhận hàng (COD)</option>
                        <option value="MoMo">Ví MoMo</option>
                        <option value="VNPay">VNPay</option>
                        <option value="Bank">Chuyển khoản ngân hàng</option>
                        <option value="Visa">Thẻ Visa</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" onclick="placeOrder()">✅ Xác Nhận</button>
                <button class="btn btn-secondary" data-bs-dismiss="modal">❌ Hủy</button>
            </div>
        </div>
    </div>
</div>

<script>
    let totalAmount = 0;

    async function loadCart() {
        try {
            const response = await axios.get('/api/cart', {
                headers: { 'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]').content }
            });

            const cartItems = response.data;
            let html = `
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Hình Ảnh</th>
                            <th>Sản Phẩm</th>
                            <th>Giá</th>
                            <th>Số Lượng</th>
                            <th>Thành Tiền</th>
                            <th>Hành Động</th>
                        </tr>
                    </thead>
                    <tbody>
            `;

            totalAmount = 0;
            cartItems.forEach(item => {
                const imageUrl = item.product.image ? `/storage/${item.product.image}` : 'https://via.placeholder.com/100';
                const itemTotal = item.product.sale_price * item.quantity;
                totalAmount += itemTotal;

                html += `
                    <tr id="cart-item-${item.id}">
                        <td><img src="${imageUrl}" width="80" height="80" class="rounded"></td>
                        <td>${item.product.name}</td>
                        <td>${new Intl.NumberFormat().format(item.product.sale_price)} VNĐ</td>
                        <td>
                            <input type="number" value="${item.quantity}" min="1" max="${item.product.stock}" onchange="updateCart(${item.id}, this.value)">
                        </td>
                        <td class="item-total">${new Intl.NumberFormat().format(itemTotal)} VNĐ</td>
                        <td><button onclick="removeFromCart(${item.id})" class="btn btn-danger btn-sm">Xóa</button></td>
                    </tr>
                `;
            });

            html += '</tbody></table>';
            document.getElementById('cart-content').innerHTML = html;
            document.getElementById('totalAmount').innerText = new Intl.NumberFormat().format(totalAmount);

            document.getElementById('placeOrderBtn').disabled = cartItems.length === 0;

        } catch (error) {
            console.error('Lỗi tải giỏ hàng:', error);
        }
    }

    async function updateCart(cartId, quantity) {
        if (quantity < 1) {
            alert('Số lượng phải lớn hơn 0.');
            loadCart();
            return;
        }

        try {
            await axios.put(`/api/cart/update/${cartId}`, { quantity }, {
                headers: { 'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]').content }
            });

            loadCart(); // Load lại giỏ hàng sau khi cập nhật
        } catch (error) {
            console.error('Lỗi cập nhật giỏ hàng:', error.response.data);
            alert(error.response.data.message || 'Cập nhật số lượng thất bại!');
            loadCart(); // Load lại dữ liệu cũ nếu cập nhật không thành công
        }
    }

    async function removeFromCart(cartId) {
        try {
            await axios.delete(`/api/cart/remove/${cartId}`, {
                headers: { 'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]').content }
            });

            loadCart();
        } catch (error) {
            console.error('Lỗi xóa sản phẩm:', error);
            alert('Xóa sản phẩm thất bại!');
        }
    }
    async function placeOrder() {
        const fullName = document.getElementById('fullName').value.trim();
        const phone = document.getElementById('phone').value.trim();
        const address = document.getElementById('address').value.trim();
        const paymentMethod = document.getElementById('paymentMethod').value;

        // Kiểm tra số điện thoại 10 chữ số
        const phoneRegex = /^\d{10}$/;
        if (!phoneRegex.test(phone)) {
            alert('Số điện thoại phải gồm đúng 10 chữ số.');
            return;
        }

        const orderData = {
            full_name: fullName,
            phone: phone,
            address: address,
            payment_method: paymentMethod,
            total: totalAmount
        };

        try {
            const response = await axios.post('/api/orders', orderData, {
                headers: { 'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]').content }
            });

            alert(response.data.message);
            loadCart();
            var orderModal = bootstrap.Modal.getInstance(document.getElementById('orderModal'));
            orderModal.hide();
        } catch (error) {
            console.error('Lỗi khi đặt hàng:', error);
            alert(error.response?.data?.message || 'Đặt hàng thất bại!');
        }
    }

    async function loadUserInfo() {
        try {
            const response = await axios.get('/api/user', {
                headers: { 'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]').content }
            });

            const user = response.data;

            document.getElementById('fullName').value = user.full_name || '';
            document.getElementById('phone').value = user.phone_number || '';
            document.getElementById('address').value = user.address || '';

        } catch (error) {
            console.error('Lỗi tải thông tin người dùng:', error);
        }
    }

    document.getElementById('orderModal').addEventListener('show.bs.modal', loadUserInfo);
    document.addEventListener('DOMContentLoaded', loadCart);
</script>
@endsection

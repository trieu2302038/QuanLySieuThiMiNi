@extends('layouts.cashier-layout')

@section('title', 'Trang Thu Ngân')

@section('content')
<div class="container mt-4">
    <div class="row">
        <!-- Danh sách sản phẩm -->
        <div class="col-md-7">
            <h3 class="text-center mb-3">Danh sách sản phẩm</h3>
            <div class="mb-3">
                <input type="text" id="search-box" class="form-control" placeholder="Tìm kiếm sản phẩm...">
            </div>
            <div class="row" id="product-list">
                @foreach ($products as $product)
                    <div class="col-md-6 mb-3 product-item" data-name="{{ $product->name }}">
                        <div class="card shadow-sm border-0">
                            <div class="position-relative">
                                <img src="{{ $product->image_url }}" class="card-img-top" alt="{{ $product->name }}" style="height: 180px; object-fit: cover; border-radius: 10px;">
                                <span class="badge bg-success position-absolute top-0 start-0 m-2">{{ $product->category->name }}</span>
                            </div>
                            <div class="card-body text-center">
                                <h5 class="card-title font-weight-bold text-truncate" title="{{ $product->name }}">{{ $product->name }}</h5>
                                <p class="card-text"><strong>Giá bán:</strong> 
                                    <span class="text-danger">{{ number_format($product->sale_price, 0, ',', '.') }} VNĐ</span>
                                </p>
                                <p class="card-text"><small class="text-muted">Còn lại: {{ $product->stock }} sản phẩm</small></p>
                                <button class="btn {{ $product->stock > 0 ? 'btn-primary' : 'btn-secondary' }} w-100 add-to-cart"
                                    data-id="{{ $product->id }}" 
                                    data-name="{{ $product->name }}" 
                                    data-price="{{ $product->sale_price }}"
                                    {{ $product->stock > 0 ? '' : 'disabled' }}>
                                <i class="fas fa-cart-plus"></i> 
                                {{ $product->stock > 0 ? 'Thêm vào giỏ hàng' : 'Hết hàng' }}
                            </button>                            
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>             
        </div>

        <!-- Giỏ hàng -->
        <div class="col-md-5">
            <h3 class="text-center mb-3">Giỏ hàng</h3>
            <div class="table-responsive">
                <table class="table table-bordered text-center">
                    <thead class="thead-dark">
                        <tr>
                            <th>#</th>
                            <th>Sản phẩm</th>
                            <th>Giá</th>
                            <th>Số lượng</th>
                            <th>Tổng</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody id="cart-body"></tbody>
                </table>
            </div>

            <!-- Hiển thị tổng tiền -->
            <h4 class="text-right mt-3">Tổng tiền: 
                <span id="total-price" class="text-danger font-weight-bold">0 VNĐ</span>
            </h4>

            <!-- Nút tạo hóa đơn -->
            <button class="btn btn-success w-100 mt-2" id="create-order">
                <i class="fas fa-file-invoice"></i> Tạo Hóa Đơn
            </button>
        </div>
    </div>
</div>

<!-- Modal hiển thị hóa đơn -->
<div class="modal fade" id="invoiceModal" tabindex="-1" aria-labelledby="invoiceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="invoiceModalLabel">Chi tiết hóa đơn</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <table class="table text-center">
                    <thead>
                        <tr>
                            <th>STT</th>
                            <th>Sản phẩm</th>
                            <th>Giá</th>
                            <th>Số lượng</th>
                            <th>Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody id="invoiceTable"></tbody>
                </table>
                <h4 class="text-right mt-3" id="invoiceTotal">Tổng tiền: 0 VNĐ</h4>

                <div class="form-group">
                    <label for="paymentMethod">Chọn phương thức thanh toán:</label>
                    <select id="paymentMethod" class="form-control">
                        <option value="cash">Tiền mặt</option>
                        <option value="credit_card">Thẻ tín dụng</option>
                        <option value="bank_transfer">Chuyển khoản</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-success" onclick="confirmPayment()">Xác nhận thanh toán</button>
            </div>
        </div>
    </div>
</div>

<script>
let cart = [];

// 🔍 Chức năng tìm kiếm sản phẩm
document.getElementById("search-box").addEventListener("keyup", function () {
    let keyword = this.value.toLowerCase();
    document.querySelectorAll(".product-item").forEach(item => {
        let name = item.getAttribute("data-name").toLowerCase();
        item.style.display = name.includes(keyword) ? "block" : "none";
    });
});

// 🛒 Cập nhật giỏ hàng
function updateCart() {
    let cartBody = document.getElementById("cart-body");
    let totalPrice = document.getElementById("total-price");
    cartBody.innerHTML = "";
    let total = 0;

    cart.forEach((item, index) => {
        let row = `
            <tr>
                <td>${index + 1}</td>
                <td>${item.name}</td>
                <td class="text-danger">${item.price.toLocaleString()} VNĐ</td>
                <td>
                    <input type="number" min="1" value="${item.quantity}" data-index="${index}" class="cart-quantity form-control text-center" style="width: 60px;">
                </td>
                <td class="text-success">${(item.price * item.quantity).toLocaleString()} VNĐ</td>
                <td>
                    <button class="btn btn-sm btn-danger remove-item" data-index="${index}">Xóa</button>
                </td>
            </tr>
        `;
        total += item.price * item.quantity;
        cartBody.innerHTML += row;
    });

    totalPrice.textContent = total.toLocaleString() + " VNĐ";

    document.querySelectorAll(".cart-quantity").forEach(input => {
        input.addEventListener("change", function () {
            let index = this.getAttribute("data-index");
            cart[index].quantity = parseInt(this.value);
            updateCart();
        });
    });

    document.querySelectorAll(".remove-item").forEach(button => {
        button.addEventListener("click", function () {
            let index = this.getAttribute("data-index");
            cart.splice(index, 1);
            updateCart();
        });
    });
}

// 🛒 Thêm sản phẩm vào giỏ hàng (có kiểm tra tồn kho)
document.querySelectorAll(".add-to-cart").forEach(button => {
    button.addEventListener("click", function () {
        let id = this.getAttribute("data-id");
        let name = this.getAttribute("data-name");
        let price = parseFloat(this.getAttribute("data-price"));
        let stock = parseInt(this.closest(".product-item").querySelector(".text-muted").textContent.match(/\d+/)[0]);

        if (stock <= 0) {
            alert("Sản phẩm này đã hết hàng!");
            return;
        }

        let existing = cart.find(item => item.id === id);
        if (existing) {
            if (existing.quantity >= stock) {
                alert("Số lượng sản phẩm trong giỏ đã đạt tối đa số lượng tồn kho!");
                return;
            }
            existing.quantity++;
        } else {
            cart.push({ id, name, price, quantity: 1, stock });
        }
        updateCart();
    });
});

// 🛒 Cập nhật giỏ hàng (có kiểm tra tồn kho)
function updateCart() {
    let cartBody = document.getElementById("cart-body");
    let totalPrice = document.getElementById("total-price");
    cartBody.innerHTML = "";
    let total = 0;

    cart.forEach((item, index) => {
        let row = `
            <tr>
                <td>${index + 1}</td>
                <td>${item.name}</td>
                <td class="text-danger">${item.price.toLocaleString()} VNĐ</td>
                <td>
                    <input type="number" min="1" max="${item.stock}" value="${item.quantity}" data-index="${index}" class="cart-quantity form-control text-center" style="width: 60px;">
                </td>
                <td class="text-success">${(item.price * item.quantity).toLocaleString()} VNĐ</td>
                <td>
                    <button class="btn btn-sm btn-danger remove-item" data-index="${index}">Xóa</button>
                </td>
            </tr>
        `;
        total += item.price * item.quantity;
        cartBody.innerHTML += row;
    });

    totalPrice.textContent = total.toLocaleString() + " VNĐ";

    document.querySelectorAll(".cart-quantity").forEach(input => {
        input.addEventListener("change", function () {
            let index = this.getAttribute("data-index");
            let newQuantity = parseInt(this.value);
            if (newQuantity > cart[index].stock) {
                alert("Số lượng vượt quá tồn kho!");
                this.value = cart[index].stock;
                return;
            }
            cart[index].quantity = newQuantity;
            updateCart();
        });
    });

    document.querySelectorAll(".remove-item").forEach(button => {
        button.addEventListener("click", function () {
            let index = this.getAttribute("data-index");
            cart.splice(index, 1);
            updateCart();
        });
    });
}


// 📜 Hiển thị hóa đơn khi nhấn "Tạo Hóa Đơn"
document.getElementById("create-order").addEventListener("click", function () {
    if (cart.length === 0) {
        alert("Giỏ hàng trống!");
        return;
    }

    let invoiceTable = document.getElementById("invoiceTable");
    let invoiceTotal = document.getElementById("invoiceTotal");
    invoiceTable.innerHTML = "";
    let total = 0; // 🛠️ Đảm bảo tổng tiền khởi tạo đúng

    cart.forEach((item, index) => {
        let row = `
            <tr>
                <td>${index + 1}</td>
                <td>${item.name}</td>
                <td class="text-danger">${item.price.toLocaleString()} VNĐ</td>
                <td>${item.quantity}</td>
                <td class="text-success">${(item.price * item.quantity).toLocaleString()} VNĐ</td>
            </tr>
        `;
        total += item.price * item.quantity; // 🛠️ Tính tổng tiền
        invoiceTable.innerHTML += row;
    });

    // 🛠️ Cập nhật tổng tiền vào modal
    invoiceTotal.innerHTML = `Tổng tiền: <span class="text-danger">${total.toLocaleString()} VNĐ</span>`;

    // 🛠️ Hiển thị modal hóa đơn
    let invoiceModal = new bootstrap.Modal(document.getElementById("invoiceModal"));
    invoiceModal.show();
});

function confirmPayment() {
    let paymentMethod = document.getElementById("paymentMethod").value;

    if (cart.length === 0) {
        alert("Giỏ hàng trống!");
        return;
    }

    let orderData = {
        cart: cart.map(item => ({
            product_id: item.id,
            quantity: item.quantity
        })),
        payment_method: paymentMethod
    };

    axios.post('/api/invoices', orderData, {
        headers: {
            'Content-Type': 'application/json',
            'Authorization': 'Bearer YOUR_ACCESS_TOKEN'  
        }
    })
    .then(response => {
        alert("Hóa đơn đã được tạo thành công!");
        
        // ✅ Cập nhật số lượng tồn kho ngay lập tức
        cart.forEach(item => {
            let productElement = document.querySelector(`.product-item[data-name="${item.name}"]`);
            if (productElement) {
                let stockElement = productElement.querySelector(".text-muted");
                let currentStock = parseInt(stockElement.textContent.match(/\d+/)[0]);
                let newStock = currentStock - item.quantity;
                stockElement.textContent = `Còn lại: ${newStock} sản phẩm`;

                // ✅ Nếu hết hàng thì disable nút "Thêm vào giỏ"
                let addToCartBtn = productElement.querySelector(".add-to-cart");
                if (newStock <= 0) {
                    addToCartBtn.classList.remove("btn-primary");
                    addToCartBtn.classList.add("btn-secondary");
                    addToCartBtn.textContent = "Hết hàng";
                    addToCartBtn.disabled = true;
                }
            }
        });

        // Xóa giỏ hàng
        cart = [];  
        updateCart();

        // Đóng modal hóa đơn
        let invoiceModal = bootstrap.Modal.getInstance(document.getElementById("invoiceModal"));
        invoiceModal.hide();
    })
    .catch(error => {
        console.error("Lỗi API:", error);
        if (error.response) {
            alert("Lỗi: " + error.response.data.error);
        } else {
            alert("Đã xảy ra lỗi khi kết nối đến máy chủ. Vui lòng thử lại sau.");
        }
    });
}

</script>
@endsection

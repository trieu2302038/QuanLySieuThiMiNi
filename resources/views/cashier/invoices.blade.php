@extends(auth()->user()->role === 'admin' ? 'layouts.admin-layout' : 'layouts.cashier-layout')

@section('title', 'Danh Sách Hóa Đơn')

@section('content')
<div class="container mt-4">
    <h2 class="text-center mb-4">Danh Sách Hóa Đơn</h2>

        <!-- Thanh tìm kiếm -->
        <div class="row mb-3">
            <div class="col-md-3">
                <input type="text" id="searchInput" class="form-control" placeholder="Nhập từ khóa tìm kiếm..." oninput="filterInvoices()">
            </div>
            <div class="col-md-3">
                <input type="date" id="searchDate" class="form-control" onchange="filterInvoices()">
            </div>
            <div class="col-md-3">
                <select id="paymentMethodFilter" class="form-control" onchange="filterInvoices()">
                    <option value="">-- Chọn phương thức thanh toán --</option>
                    <option value="cash">Tiền mặt</option>
                    <option value="credit_card">Thẻ tín dụng</option>
                    <option value="bank_transfer">Chuyển khoản ngân hàng</option>
                </select>
            </div>
            <div class="col-md-3 d-flex">
                <button class="btn btn-secondary w-50" onclick="resetFilter()">Làm mới</button>
            </div>
        </div>

        <!-- Bảng danh sách hóa đơn -->
        <div class="table-responsive">
            <table class="table table-bordered text-center">
                <thead class="thead-dark">
                    <tr>
                        <th>#</th>
                        <th>Người tạo</th>
                        <th>Tổng tiền</th>
                        <th>Phương thức thanh toán</th>
                        <th>Ngày tạo</th>
                        <th>Chi tiết</th>
                        @if(auth()->user()->role === 'admin')
                        <th>Xóa</th>
                        @endif
                    </tr>
                </thead>
                <tbody id="invoice-list"></tbody>
            </table>
        </div>
    </div>

    <!-- Modal hiển thị chi tiết hóa đơn -->
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
                                <th>#</th>
                                <th>Sản phẩm</th>
                                <th>Giá</th>
                                <th>Số lượng</th>
                                <th>Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody id="invoiceDetailTable"></tbody>
                    </table>
                    <h4 class="text-right mt-3" id="invoiceTotal">Tổng tiền: 0 VNĐ</h4>
                </div>
            </div>
        </div>    
    </div>

    <script>
    let invoicesData = []; // Lưu danh sách hóa đơn để tìm kiếm
    let filteredInvoices = []; // Lưu danh sách sau khi lọc

    document.addEventListener("DOMContentLoaded", function () {
        fetchInvoices();
    });

    function fetchInvoices() {
        axios.get(`/api/invoices?sortBy=created_at&order=desc`)
            .then(response => {
                invoicesData = response.data; // Lưu toàn bộ dữ liệu
                filteredInvoices = invoicesData; // Mặc định hiển thị toàn bộ
                renderInvoices(filteredInvoices); // Hiển thị danh sách hóa đơn
            })
            .catch(error => {
                console.error("Lỗi API:", error);
                alert("Không thể tải danh sách hóa đơn!");
            });
    }

    // Sắp xếp giảm dần 
    function fetchInvoices() {
        axios.get(`/api/invoices?sortBy=created_at&order=desc`)
            .then(response => {
                invoicesData = response.data; // Lấy toàn bộ dữ liệu không phân trang
                renderInvoices(invoicesData); // Hiển thị danh sách hóa đơn
            })
            .catch(error => {
                console.error("Lỗi API:", error);
                alert("Không thể tải danh sách hóa đơn!");
            });
    }

    // Định dạng ngày thành dd/MM/yyyy HH:mm:ss
    function formatDateTime(dateString) {
        let date = new Date(dateString);
        let day = date.getDate().toString().padStart(2, '0');
        let month = (date.getMonth() + 1).toString().padStart(2, '0');
        let year = date.getFullYear();
        let hours = date.getHours().toString().padStart(2, '0');
        let minutes = date.getMinutes().toString().padStart(2, '0');
        let seconds = date.getSeconds().toString().padStart(2, '0');
        return `${day}/${month}/${year} ${hours}:${minutes}:${seconds}`;
    }

    // Chuyển đổi ngày sang dd/MM/yyyy (bỏ giờ phút giây)
    function formatDateOnly(dateString) {
        let date = new Date(dateString);
        let day = date.getDate().toString().padStart(2, '0');
        let month = (date.getMonth() + 1).toString().padStart(2, '0');
        let year = date.getFullYear();
        return `${day}/${month}/${year}`;
    }

    // Hiển thị danh sách hóa đơn
    function renderInvoices(invoices) {
        let invoiceList = document.getElementById("invoice-list");
        invoiceList.innerHTML = "";

        invoices.forEach((invoice, index) => {
            let paymentMethod = getPaymentMethod(invoice.payment_method);
            let userName = invoice.user ? invoice.user.full_name : 'Không xác định';
            let formattedDateTime = formatDateTime(invoice.created_at);

            let row = `
                <tr>
                    <td>${index + 1}</td>
                    <td>${userName}</td>
                    <td class="text-danger">${invoice.total_amount.toLocaleString()} VNĐ</td>
                    <td>${paymentMethod}</td>
                    <td>${formattedDateTime}</td>
                    <td>
                        <button class="btn btn-info btn-sm" onclick="showInvoiceDetails(${invoice.id})">
                            Xem chi tiết
                        </button>
                    </td>
            `;

            // Nếu người dùng là admin thì hiển thị nút Xóa
            @if(auth()->user()->role === 'admin')
            row += `
                <td>
                    <button class="btn btn-danger btn-sm" onclick="deleteInvoice(${invoice.id})">
                        Xóa
                    </button>
                </td>
            `;
            @endif

            row += `</tr>`;

            invoiceList.innerHTML += row;
        });
    }

    // Hàm lọc hóa đơn theo từ khóa, ngày tạo và phương thức thanh toán
    function filterInvoices() {
        let keyword = document.getElementById("searchInput").value.trim().toLowerCase();
        let selectedDate = document.getElementById("searchDate").value;
        let paymentMethod = document.getElementById("paymentMethodFilter").value;

        filteredInvoices = invoicesData.filter(invoice => {
            let matchesKeyword = keyword === "" || invoice.user?.full_name.toLowerCase().includes(keyword);
            let matchesDate = selectedDate === "" || formatDateOnly(invoice.created_at) === formatDateOnly(selectedDate);
            let matchesPayment = paymentMethod === "" || invoice.payment_method === paymentMethod;

            return matchesKeyword && matchesDate && matchesPayment;
        });

        renderInvoices(filteredInvoices);
    }

    // Reset bộ lọc về mặc định
    function resetFilter() {
        document.getElementById("searchInput").value = "";
        document.getElementById("searchDate").value = "";
        document.getElementById("paymentMethodFilter").value = "";

        filteredInvoices = invoicesData; // Reset về danh sách gốc
        renderInvoices(filteredInvoices);
    }

    // Chuyển đổi phương thức thanh toán sang tiếng Việt
    function getPaymentMethod(method) {
        switch (method) {
            case "cash": return "Tiền mặt";
            case "credit_card": return "Thẻ tín dụng";
            case "bank_transfer": return "Chuyển khoản ngân hàng";
            default: return "Không xác định";
        }
    }

    // Hiển thị chi tiết hóa đơn
    function showInvoiceDetails(invoiceId) {
        axios.get(`/api/invoices/${invoiceId}`)
        .then(response => {
            let invoice = response.data;
            let invoiceDetailTable = document.getElementById("invoiceDetailTable");
            let invoiceTotal = document.getElementById("invoiceTotal");

            invoiceDetailTable.innerHTML = "";
            let total = 0;

            invoice.details.forEach((detail, index) => {
                let row = `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${detail.product.name}</td>
                        <td class="text-danger">${detail.price.toLocaleString()} VNĐ</td>
                        <td>${detail.quantity}</td>
                        <td class="text-success">${(detail.price * detail.quantity).toLocaleString()} VNĐ</td>
                    </tr>
                `;
                total += detail.price * detail.quantity;
                invoiceDetailTable.innerHTML += row;
            });

            invoiceTotal.innerHTML = `Tổng tiền: <span class="text-danger">${total.toLocaleString()} VNĐ</span>`;

            let invoiceModal = new bootstrap.Modal(document.getElementById("invoiceModal"));
            invoiceModal.show();
        })
        .catch(error => {
            console.error("Lỗi API:", error);
            alert("Không thể tải chi tiết hóa đơn!");
        });
    }

    function deleteInvoice(invoiceId) {
        if (!confirm("Bạn có chắc chắn muốn xóa hóa đơn này không?")) return;

        axios.delete(`/api/invoices/${invoiceId}`)
        .then(response => {
            alert("Hóa đơn đã được xóa thành công!");

            // Cập nhật danh sách hóa đơn sau khi xóa
            invoicesData = invoicesData.filter(invoice => invoice.id !== invoiceId);
            filteredInvoices = filteredInvoices.filter(invoice => invoice.id !== invoiceId);

            // Nếu danh sách sau khi lọc trống, hiển thị danh sách đầy đủ
            if (filteredInvoices.length === 0) {
                filteredInvoices = invoicesData;
            }

            // Hiển thị lại danh sách hóa đơn
            renderInvoices(filteredInvoices);
        })
        .catch(error => {
            console.error("Lỗi khi xóa hóa đơn:", error);
            alert("Không thể xóa hóa đơn!");
        });
    }

</script>
@endsection  

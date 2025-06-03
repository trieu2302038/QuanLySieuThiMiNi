@extends('layouts.user-layout')
@section('title', 'Trang Chủ')

@section('content')
@include('layouts.banner-slider')

<!-- Thanh tìm kiếm & Bộ lọc -->
<div class="row mb-3">
    <div class="col-md-3">
        <input type="text" id="searchInput" class="form-control" placeholder="🔍 Tìm kiếm sản phẩm..." oninput="filterProducts()">
    </div>
    <div class="col-md-3">
        <select id="categoryFilter" class="form-select" onchange="filterProducts()">
            <option value="">📂 Tất cả loại hàng</option>
        </select>
    </div>
    <div class="col-md-3">
        <select id="brandFilter" class="form-select" onchange="filterProducts()">
            <option value="">🏷️ Tất cả thương hiệu</option>
        </select>
    </div>
    <div class="col-md-3 text-end">
        <button class="btn btn-warning" onclick="resetFilters()">🔄 Reset</button>
    </div>
</div>

<!-- Danh sách sản phẩm -->
<div class="row" id="productList"></div>

<!-- Toast thông báo -->
<div class="toast-container position-fixed bottom-0 end-0 p-3">
    <div id="cartToast" class="toast align-items-center text-white bg-success border-0" role="alert">
        <div class="d-flex">
            <div class="toast-body"></div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

<script>
    const apiUrl = '/api/products';
    let allProducts = [];
    let isAddingToCart = false;
    const userRole = @json(auth()->check() ? auth()->user()->role : null);
    const apiToken = @json(auth()->user()->api_token ?? null);

    async function loadProducts() {
        try {
            const response = await axios.get(apiUrl);
            allProducts = response.data;
            populateFilters(allProducts);
            renderProducts(allProducts);
        } catch (error) {
            showToast("❌ Lỗi khi tải sản phẩm!");
        }
    }

    function populateFilters(products) {
        const categoryFilter = document.getElementById('categoryFilter');
        const brandFilter = document.getElementById('brandFilter');
        const categories = new Set();
        const brands = new Set();

        products.forEach(p => {
            if (p.category) categories.add(p.category.name);
            if (p.brand) brands.add(p.brand.name);
        });

        categoryFilter.innerHTML = '<option value="">📂 Tất cả loại hàng</option>';
        categories.forEach(cat => {
            categoryFilter.innerHTML += `<option value="${cat}">${cat}</option>`;
        });

        brandFilter.innerHTML = '<option value="">🏷️ Tất cả thương hiệu</option>';
        brands.forEach(brand => {
            brandFilter.innerHTML += `<option value="${brand}">${brand}</option>`;
        });
    }

    function renderProducts(products) {
        const productList = document.getElementById('productList');
        productList.innerHTML = '';

        products.forEach(product => {
            const imageUrl = product.image ? `/storage/${product.image}` : 'https://via.placeholder.com/150';
            const category = product.category?.name || 'Chưa có';
            const brand = product.brand?.name || 'Chưa có';
            const isOutOfStock = product.stock === 0;

            let buttonHtml = '';
            if (isOutOfStock) {
                buttonHtml = `<button class="btn btn-sm btn-secondary mt-auto" disabled>⛔ Hết hàng</button>`;
            } else if (!userRole || userRole === 'guest') {
                buttonHtml = `<a href="/login" class="btn btn-sm btn-warning mt-auto">🔒 Đăng nhập để mua</a>`;
            } else {
                buttonHtml = `<button class="btn btn-sm btn-danger mt-auto add-to-cart" data-id="${product.id}">🛒 Thêm vào giỏ</button>`;
            }

            productList.innerHTML += `
                <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                    <div class="card h-100 shadow-sm border-0">
                        <img src="${imageUrl}" class="card-img-top p-2" style="height: 180px; object-fit: contain;" alt="${product.name}">
                        <div class="card-body d-flex flex-column">
                            <h6 class="card-title text-truncate">${product.name}</h6>
                            <p class="small text-muted">${product.description || 'Không có mô tả'}</p>
                            <p class="small"><strong>Danh Mục:</strong> ${category}</p>
                            <p class="small"><strong>Thương Hiệu:</strong> ${brand}</p>
                            <p class="small"><strong>Còn lại:</strong> ${product.stock}</p>
                            <p class="fw-bold text-danger mb-1">${new Intl.NumberFormat().format(product.sale_price)} VND</p>
                            ${buttonHtml}
                        </div>
                    </div>
                </div>
            `;
        });

        document.querySelectorAll('.add-to-cart').forEach(button => {
            button.addEventListener('click', () => addToCart(button.dataset.id));
        });
    }

    function filterProducts() {
        const keyword = document.getElementById('searchInput').value.toLowerCase();
        const selectedCategory = document.getElementById('categoryFilter').value;
        const selectedBrand = document.getElementById('brandFilter').value;

        const filtered = allProducts.filter(p => {
            const nameMatch = p.name.toLowerCase().includes(keyword);
            const categoryMatch = selectedCategory ? p.category?.name === selectedCategory : true;
            const brandMatch = selectedBrand ? p.brand?.name === selectedBrand : true;
            return nameMatch && categoryMatch && brandMatch;
        });

        renderProducts(filtered);
    }

    function resetFilters() {
        document.getElementById('searchInput').value = '';
        document.getElementById('categoryFilter').value = '';
        document.getElementById('brandFilter').value = '';
        renderProducts(allProducts);
    }

    async function addToCart(productId) {
        if (isAddingToCart) return;
        isAddingToCart = true;

        try {
            const product = allProducts.find(p => p.id == productId);
            if (!product) return showToast("❌ Sản phẩm không tồn tại!");

            const cart = await axios.get('/api/cart', {
                headers: { 'Authorization': `Bearer ${apiToken}` }
            });

            const cartItem = cart.data.find(i => i.product_id == productId);
            if ((cartItem?.quantity || 0) >= product.stock) {
                return showToast("⚠️ Bạn đã thêm tối đa số lượng tồn kho!");
            }

            const res = await axios.post('/api/cart/add', { product_id: productId }, {
                headers: { 'Authorization': `Bearer ${apiToken}` }
            });

            showToast("🛒 " + res.data.message);
        } catch {
            showToast("❌ Lỗi khi thêm vào giỏ hàng!");
        } finally {
            isAddingToCart = false;
        }
    }

    function showToast(message) {
        const toastEl = document.getElementById('cartToast');
        toastEl.querySelector('.toast-body').textContent = message;
        new bootstrap.Toast(toastEl).show();
    }

    loadProducts();
</script>

<!-- Swiper slider -->
<script>
    const swiper = new Swiper(".mySwiper", {
        loop: true,
        autoplay: { delay: 3000, disableOnInteraction: false },
        pagination: { el: ".swiper-pagination", clickable: true },
        navigation: {
            nextEl: ".swiper-button-next",
            prevEl: ".swiper-button-prev",
        },
    });
</script>
@endsection

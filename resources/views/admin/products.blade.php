@extends('layouts.admin-layout')

@section('title', 'Quản Lý Sản Phẩm')

@section('content')
    <h1 class="text-center text-primary my-4 fs-1">Quản Lý Sản Phẩm</h1>
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="text-center text-primary my-4">Thêm / Sửa Sản Phẩm</h5>
                    <div id="error-message" class="alert alert-danger d-none"></div>
                    <form id="productForm">
                        <input type="hidden" id="productId">
                        <div class="mb-3">
                            <label for="name" class="form-label">Tên Sản Phẩm</label>
                            <input type="text" class="form-control" id="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="import_price" class="form-label">Giá Nhập</label>
                            <input type="number" class="form-control" id="import_price" required>
                        </div>
                        <div class="mb-3">
                            <label for="sale_price" class="form-label">Giá Bán</label>
                            <input type="number" class="form-control" id="sale_price" required>
                        </div>
                        <div class="mb-3">
                            <label for="stock" class="form-label">Số Lượng</label>
                            <input type="number" class="form-control" id="stock" required>
                        </div>
                        <div class="mb-3">
                            <label for="category_id" class="form-label">Loại Hàng</label>
                            <select class="form-control" id="category_id"></select>
                        </div>
                        <div class="mb-3">
                            <label for="brand_id" class="form-label">Thương Hiệu</label>
                            <select class="form-control" id="brand_id"></select>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Mô Tả</label>
                            <textarea class="form-control" id="description"></textarea>
                        </div><div class="mb-3">
                            <label for="image" class="form-label">Hình Ảnh</label>
                            <input type="file" class="form-control" id="image">
                        </div>
                        <img id="previewImage" src="" class="img-fluid mt-2" style="max-width: 150px; display: none;">                        
                        <button type="submit" class="btn btn-primary">Lưu</button>
                        <button type="button" class="btn btn-secondary d-none" id="cancelEdit">Hủy</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="mb-3">
                <input type="text" id="searchInput" class="form-control" placeholder="🔍 Tìm kiếm sản phẩm..." oninput="searchProduct()">
            </div>
            <div class="card">
                <div class="card-body">
                    <table class="table table-bordered table-hover">
                        <thead class="table-primary">
                            <tr>
                                <th>ID</th>
                                <th>Tên Sản Phẩm</th>
                                <th>Giá Nhập</th>
                                <th>Giá Bán</th>
                                <th>Số Lượng</th>
                                <th>Loại Hàng</th>
                                <th>Thương Hiệu</th>
                                <th>Mô tả</th>
                                <th>Hình ảnh</th>
                                <th>Hành Động</th>
                            </tr>
                        </thead>
                        <tbody id="productTable"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        const apiUrl = '/api/products';
        const categoryUrl = '/api/categories';
        const brandUrl = '/api/brands';

        async function loadProducts() {
            try {
                const response = await axios.get(apiUrl);
                renderTable(response.data);
            } catch (error) {
                console.error('Lỗi tải sản phẩm:', error);
            }
        }

        async function loadCategoriesAndBrands() {
            try {
                const categories = await axios.get(categoryUrl);
                const brands = await axios.get(brandUrl);

                document.getElementById('category_id').innerHTML = categories.data.map(c => `<option value="${c.id}">${c.name}</option>`).join('');
                document.getElementById('brand_id').innerHTML = brands.data.map(b => `<option value="${b.id}">${b.name}</option>`).join('');
            } catch (error) {
                console.error('Lỗi tải dữ liệu:', error);
            }
        }

        function renderTable(data) {
            const tableBody = document.getElementById('productTable');
            tableBody.innerHTML = '';
            data.forEach(item => {
                const imageUrl = item.image ? `/storage/${item.image}` : '';

                tableBody.innerHTML += `
                    <tr>
                        <td>${item.id}</td>
                        <td>${item.name}</td>
                        <td>${item.import_price}</td>
                        <td>${item.sale_price}</td>
                        <td>${item.stock}</td>
                        <td>${item.category ? item.category.name : 'N/A'}</td>
                        <td>${item.brand ? item.brand.name : 'N/A'}</td>
                        <td>${item.description ? item.description : 'Không có mô tả'}</td>
                        <td>
                            ${imageUrl ? `<img src="${imageUrl}" class="img-fluid" style="max-width: 50px;">` : ''}
                        </td>
                        <td>
                            <button class="btn btn-sm btn-warning" onclick="editProduct(${item.id}, '${item.name}', ${item.import_price}, ${item.sale_price}, ${item.stock}, ${item.category_id}, ${item.brand_id}, '${item.description}', '${imageUrl}')">Sửa</button>
                            <button class="btn btn-sm btn-danger" onclick="deleteProduct(${item.id})">Xóa</button>
                        </td>
                    </tr>
                `;
            });
        }

        document.getElementById('image').addEventListener('change', function (event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    document.getElementById('previewImage').src = e.target.result;
                    document.getElementById('previewImage').style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });

        document.getElementById('productForm').addEventListener('submit', async function (e) {
            e.preventDefault();
            const id = document.getElementById('productId').value;
            const formData = new FormData();
            formData.append('name', document.getElementById('name').value);
            formData.append('import_price', document.getElementById('import_price').value);
            formData.append('sale_price', document.getElementById('sale_price').value);
            formData.append('stock', document.getElementById('stock').value);
            formData.append('category_id', document.getElementById('category_id').value);
            formData.append('brand_id', document.getElementById('brand_id').value);
            formData.append('description', document.getElementById('description').value);

            // Kiểm tra xem có thay đổi ảnh hay không
            const imageInput = document.getElementById('image');
            if (imageInput.files.length > 0) {
                formData.append('image', imageInput.files[0]);
            }

            try {
                if (id) {
                    await axios.post(`${apiUrl}/${id}?_method=PUT`, formData, {
                        headers: { 'Content-Type': 'multipart/form-data' }
                    });
                } else {
                    await axios.post(apiUrl, formData, {
                        headers: { 'Content-Type': 'multipart/form-data' }
                    });
                }
                resetForm();
            } catch (error) {
                console.error('Lỗi khi lưu sản phẩm:', error.response?.data || error);
            }
        });

        function editProduct(id, name, import_price, sale_price, stock, category_id, brand_id, description, imageUrl) {
            document.getElementById('productId').value = id;
            document.getElementById('name').value = name;
            document.getElementById('import_price').value = import_price;
            document.getElementById('sale_price').value = sale_price;
            document.getElementById('stock').value = stock;
            document.getElementById('category_id').value = category_id;
            document.getElementById('brand_id').value = brand_id;
            document.getElementById('description').value = description;

            // Hiển thị ảnh nếu có
            const previewImage = document.getElementById('previewImage');
            if (imageUrl && imageUrl !== 'null') {
                previewImage.src = imageUrl;
                previewImage.style.display = 'block';
            } else {
                previewImage.style.display = 'none';
            }

            document.getElementById('cancelEdit').classList.remove('d-none');
        }

        function resetForm() {
            document.getElementById('productForm').reset();
            document.getElementById('productId').value = '';
            document.getElementById('previewImage').style.display = 'none'; // Ẩn ảnh khi reset form
            document.getElementById('cancelEdit').classList.add('d-none');
            loadProducts();
        }

        async function deleteProduct(id) {
            if (confirm('Bạn có chắc chắn muốn xóa?')) {
                await axios.delete(`${apiUrl}/${id}`);
                loadProducts();
            }
        }

        document.getElementById('cancelEdit').addEventListener('click', resetForm);
        loadProducts();
        loadCategoriesAndBrands();

        function searchProduct() {
            const keyword = document.getElementById('searchInput').value.toLowerCase();

            document.querySelectorAll("#productTable tr").forEach(row => {
                const productName = row.children[1].textContent.toLowerCase(); // Tên sản phẩm
                const categoryName = row.children[5].textContent.toLowerCase(); // Loại hàng
                const brandName = row.children[6].textContent.toLowerCase(); // Thương hiệu

                if (productName.includes(keyword) || categoryName.includes(keyword) || brandName.includes(keyword)) {
                    row.style.display = "";
                } else {
                    row.style.display = "none";
                }
            });
        }

    </script>
@endsection

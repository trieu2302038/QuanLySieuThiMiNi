@extends('layouts.admin-layout')

@section('title', 'Quản Lý Danh Mục')

@section('content')
<h1 class="text-center text-primary my-4 fs-1">Quản Lý Danh Mục</h1>

    <div class="row">
        <!-- Cột Thêm/Sửa Danh Mục -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="text-center text-primary my-4">Thêm / Sửa Danh Mục</h5>
                    <form id="categoryForm">
                        <input type="hidden" id="categoryId">
                        <div class="mb-3">
                            <label for="name" class="form-label">Tên Danh Mục</label>
                            <input type="text" class="form-control" id="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Mô Tả</label>
                            <textarea class="form-control" id="description"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Lưu</button>
                        <button type="button" class="btn btn-secondary d-none" id="cancelEdit">Hủy</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Cột Danh Sách Danh Mục -->
        <div class="col-md-8">
            <div class="mb-3">
                <input type="text" id="searchInput" class="form-control" placeholder="🔍 Tìm kiếm danh mục..." oninput="searchCategory()">
            </div>

            <div class="card">
                <div class="card-body">
                    <table class="table table-bordered table-hover">
                        <thead class="table-primary">
                            <tr>
                                <th>ID</th>
                                <th>Tên Danh Mục</th>
                                <th>Mô Tả</th>
                                <th>Hành Động</th>
                            </tr>
                        </thead>
                        <tbody id="categoryTable">
                            <!-- Dữ liệu sẽ được render bằng JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        const apiUrl = '/api/categories';
    
        async function loadCategories() {
            try {
                const response = await axios.get(apiUrl);
                renderTable(response.data);
            } catch (error) {
                console.error('Lỗi tải danh mục:', error);
            }
        }
    
        function renderTable(data) {
            const tableBody = document.getElementById('categoryTable');
            tableBody.innerHTML = '';
            data.forEach(item => {
                tableBody.innerHTML += `
                    <tr>
                        <td>${item.id}</td>
                        <td>${item.name}</td>
                        <td>${item.description || ''}</td>
                        <td>
                            <button class="btn btn-sm btn-warning" onclick="editCategory(${item.id}, '${item.name}', '${item.description || ''}')">Sửa</button>
                            <button class="btn btn-sm btn-danger" onclick="deleteCategory(${item.id})">Xóa</button>
                        </td>
                    </tr>
                `;
            });
        }
    
        document.getElementById('categoryForm').addEventListener('submit', async function (e) {
            e.preventDefault();
            const id = document.getElementById('categoryId').value;
            const name = document.getElementById('name').value;
            const description = document.getElementById('description').value;
    
            try {
                if (id) {
                    await axios.put(`${apiUrl}/${id}`, { name, description });
                } else {
                    await axios.post(apiUrl, { name, description });
                }
                await loadCategories();
                resetForm();
            } catch (error) {
                if (error.response && error.response.data && error.response.data.errors) {
                    const errors = error.response.data.errors;
                    let errorMessages = '';
                    for (let field in errors) {
                        errorMessages += errors[field].join(', ') + '\n';
                    }
                    alert(errorMessages);
                } else {
                    console.error('Lỗi khi lưu danh mục:', error);
                    alert('Đã xảy ra lỗi khi lưu danh mục.');
                }
            }

        });
    
        function editCategory(id, name, description) {
            document.getElementById('categoryId').value = id;
            document.getElementById('name').value = name;
            document.getElementById('description').value = description;
            document.getElementById('formTitle').innerText = 'Chỉnh Sửa Danh Mục';
            document.querySelector('button[type="submit"]').innerText = 'Cập Nhật';
            document.getElementById('cancelEdit').classList.remove('d-none');
        }
    
        function resetForm() {
            document.getElementById('categoryForm').reset();
            document.getElementById('categoryId').value = '';
            document.getElementById('formTitle').innerText = 'Thêm Danh Mục';
            document.querySelector('button[type="submit"]').innerText = 'Lưu';
            document.getElementById('cancelEdit').classList.add('d-none');
            loadCategories();
        }
    
        async function deleteCategory(id) {
            if (confirm('Bạn có chắc chắn muốn xóa?')) {
                try {
                    await axios.delete(`${apiUrl}/${id}`);
                    loadCategories();
                } catch (error) {
                    console.error('Lỗi khi xóa danh mục:', error);
                }
            }
        }
    
        async function searchCategory() {
            const keyword = document.getElementById('searchInput').value.toLowerCase();
            try {
                const response = await axios.get(apiUrl);
                const filteredData = response.data.filter(item =>
                    item.name.toLowerCase().includes(keyword) ||
                    (item.description && item.description.toLowerCase().includes(keyword))
                );
                renderTable(filteredData);
            } catch (error) {
                console.error('Lỗi khi tìm kiếm:', error);
            }
        }
    
        document.getElementById('cancelEdit').addEventListener('click', resetForm);
    
        loadCategories();
    </script>
    
@endsection

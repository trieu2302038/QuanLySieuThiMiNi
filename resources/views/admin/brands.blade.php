@extends('layouts.admin-layout')

@section('title', 'Quản Lý Thương Hiệu')

@section('content')
    <h1 class="text-center text-primary my-4 fs-1">Quản Lý Thương Hiệu</h1>

    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="text-center text-primary my-4">Thêm / Sửa Thương Hiệu</h5>
                    <form id="brandForm">
                        <input type="hidden" id="brandId">
                        <div class="mb-3">
                            <label for="name" class="form-label">Tên Thương Hiệu</label>
                            <input type="text" class="form-control" id="name" required>
                            <div id="nameError" class="text-danger mt-1"></div>
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

        <div class="col-md-8">
            <div class="mb-3">
                <input type="text" id="searchInput" class="form-control" placeholder="🔍 Tìm kiếm thương hiệu..." oninput="searchBrand()">
            </div>
            <div class="card">
                <div class="card-body">
                    <table class="table table-bordered table-hover">
                        <thead class="table-primary">
                            <tr>
                                <th>ID</th>
                                <th>Tên Thương Hiệu</th>
                                <th>Mô Tả</th>
                                <th>Hành Động</th>
                            </tr>
                        </thead>
                        <tbody id="brandTable"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        const apiUrl = '/api/brands';

        async function loadBrands() {
            try {
                const response = await axios.get(apiUrl);
                renderTable(response.data);
            } catch (error) {
                console.error('Lỗi tải thương hiệu:', error);
            }
        }

        function renderTable(data) {
            const tableBody = document.getElementById('brandTable');
            tableBody.innerHTML = '';
            data.forEach(item => {
                tableBody.innerHTML += `
                    <tr>
                        <td>${item.id}</td>
                        <td>${item.name}</td>
                        <td>${item.description || ''}</td>
                        <td>
                            <button class="btn btn-sm btn-warning" onclick="editBrand(${item.id}, '${item.name}', '${item.description || ''}')">Sửa</button>
                            <button class="btn btn-sm btn-danger" onclick="deleteBrand(${item.id})">Xóa</button>
                        </td>
                    </tr>
                `;
            });
        }

        document.getElementById('brandForm').addEventListener('submit', async function (e) {
            e.preventDefault();
            document.getElementById('nameError').textContent = ''; // Xóa lỗi cũ

            const id = document.getElementById('brandId').value;
            const name = document.getElementById('name').value;
            const description = document.getElementById('description').value;

            try {
                if (id) {
                    await axios.put(`${apiUrl}/${id}`, { name, description });
                } else {
                    await axios.post(apiUrl, { name, description });
                }
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
                    console.error('Lỗi khi lưu thương hiệu:', error);
                    alert('Đã xảy ra lỗi khi lưu thương hiệu.');
                }
            }
   });

        function editBrand(id, name, description) {
            document.getElementById('brandId').value = id;
            document.getElementById('name').value = name;
            document.getElementById('description').value = description;
            document.getElementById('cancelEdit').classList.remove('d-none');
        }

        function resetForm() {
            document.getElementById('brandForm').reset();
            document.getElementById('brandId').value = '';
            document.getElementById('cancelEdit').classList.add('d-none');
            loadBrands();
        }

        async function deleteBrand(id) {
            if (confirm('Bạn có chắc chắn muốn xóa?')) {
                try {
                    await axios.delete(`${apiUrl}/${id}`);
                    loadBrands();
                } catch (error) {
                    console.error('Lỗi khi xóa thương hiệu:', error);
                }
            }
        }

        async function searchBrand() {
            const keyword = document.getElementById('searchInput').value.toLowerCase();
            try {
                const response = await axios.get(apiUrl);
                const filteredData = response.data.filter(item =>
                    item.name.toLowerCase().includes(keyword)
                );
                renderTable(filteredData);
            } catch (error) {
                console.error('Lỗi khi tìm kiếm:', error);
            }
        }

        document.getElementById('cancelEdit').addEventListener('click', resetForm);
        loadBrands();
    </script>
@endsection

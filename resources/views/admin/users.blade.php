@extends('layouts.admin-layout')

@section('title', 'Quản Lý Người Dùng')

@section('content')
<h1 class="text-center text-primary my-4 fs-1">Quản Lý Người Dùng</h1>

    <!-- Form Thêm Người Dùng -->
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title text-center">Thêm Người Dùng</h5>
            <form id="addUserForm">
                <div class="row">
                    <div class="col-md-4 mb-2">
                        <label>Tên Đăng Nhập</label>
                        <input type="text" id="username" class="form-control" required>
                    </div>
                    <div class="col-md-4 mb-2">
                        <label>Mật Khẩu</label>
                        <input type="password" id="password" class="form-control" required>
                    </div>
                    <div class="col-md-4 mb-2">
                        <label>Họ và Tên</label>
                        <input type="text" id="full_name" class="form-control" required>
                    </div>
                    <div class="col-md-4 mb-2">
                        <label>Ngày Sinh</label>
                        <input type="date" id="birth_date" class="form-control">
                    </div>
                    <div class="col-md-4 mb-2">
                        <label>Email</label>
                        <input type="email" id="email" class="form-control" required>
                    </div>
                    <div class="col-md-4 mb-2">
                        <label>Địa Chỉ</label>
                        <input type="text" id="address" class="form-control">
                    </div>
                    <div class="col-md-4 mb-2">
                        <label>Số Điện Thoại</label>
                        <input type="text" id="phone_number" class="form-control">
                    </div>
                    <div class="col-md-4 mb-2">
                        <label>Vai Trò</label>
                        <select id="role" class="form-select">
                            <option value="user">Người Dùng</option>
                            <option value="cashier">Thu Ngân</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn btn-success w-100 mt-3">Thêm Người Dùng</button>
            </form>
        </div>
    </div>

    <!-- Tìm kiếm và lọc -->
    <div class="row mb-3">
        <div class="col-md-6">
            <input type="text" id="searchInput" class="form-control" placeholder="Tìm kiếm người dùng...">
        </div>
        <div class="col-md-6">
            <select id="roleFilter" class="form-select">
                <option value="">Tất cả vai trò</option>
                <option value="admin">Admin</option>
                <option value="cashier">Thu Ngân</option>
                <option value="user">Người Dùng</option>
            </select>
        </div>
    </div>

    <!-- Danh Sách Người Dùng -->
    <div class="card">
        <div class="card-body">
            <h5 class="card-title text-center">Danh Sách Người Dùng</h5>
            <table class="table table-bordered">
                <thead class="table-primary">
                    <tr>
                        <th>ID</th>
                        <th>Tên Đăng Nhập</th>
                        <th>Họ và Tên</th>
                        <th>Ngày Sinh</th>
                        <th>Email</th>
                        <th>Địa Chỉ</th>
                        <th>Số Điện Thoại</th>
                        <th>Vai Trò</th>
                        <th>Hành Động</th>
                    </tr>
                </thead>
                <tbody id="userTable"></tbody>
            </table>
        </div>
    </div>

    <script>
        const apiUrl = '/api/users';

        async function loadUsers() {
            try {
                const response = await axios.get(apiUrl);
                renderTable(response.data);
            } catch (error) {
                console.error('Lỗi tải danh sách người dùng:', error);
            }
        }


        function formatDate(dateString) {
            if (!dateString) return 'N/A';
            const date = new Date(dateString);
            return date.toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit', year: 'numeric' });
        }

        function renderTable(users) {
            const tableBody = document.getElementById('userTable');
            const searchValue = document.getElementById('searchInput').value.toLowerCase();
            const roleFilter = document.getElementById('roleFilter').value;

            tableBody.innerHTML = '';
            
            users
                .filter(user => 
                    (user.username.toLowerCase().includes(searchValue) ||
                    user.full_name.toLowerCase().includes(searchValue) ||
                    user.email.toLowerCase().includes(searchValue)) &&
                    (roleFilter === "" || user.role === roleFilter)
                )
                .forEach(user => {
                    tableBody.innerHTML += `
                        <tr>
                            <td>${user.id}</td>
                            <td>${user.username}</td>
                            <td>${user.full_name}</td>
                            <td>${formatDate(user.birth_date)}</td>
                            <td>${user.email}</td>
                            <td>${user.address || 'N/A'}</td>
                            <td>${user.phone_number || 'N/A'}</td>
                            <td>
                                <select class="form-select" onchange="updateRole(${user.id}, this.value)">
                                    <option value="admin" ${user.role === 'admin' ? 'selected' : ''}>Admin</option>
                                    <option value="cashier" ${user.role === 'cashier' ? 'selected' : ''}>Thu Ngân</option>
                                    <option value="user" ${user.role === 'user' ? 'selected' : ''}>Người Dùng</option>
                                </select>
                            </td>
                            <td>
                                <button class="btn btn-danger btn-sm" onclick="deleteUser(${user.id})">Xóa</button>
                            </td>
                        </tr>
                    `;
                });
        }

        async function addUser(event) {
            event.preventDefault();
            const newUser = {
                username: document.getElementById('username').value,
                password: document.getElementById('password').value,
                full_name: document.getElementById('full_name').value,
                birth_date: document.getElementById('birth_date').value,
                email: document.getElementById('email').value,
                address: document.getElementById('address').value,
                phone_number: document.getElementById('phone_number').value,
                role: document.getElementById('role').value,
            };

            try {
                await axios.post(apiUrl, newUser);
                alert('Thêm người dùng thành công!');
                document.getElementById('addUserForm').reset();
                loadUsers();
            } catch (error) {
                console.error('Lỗi khi thêm người dùng:', error);
            }
        }

        async function updateRole(userId, newRole) {
            try {
                await axios.put(`/api/users/${userId}/role`, { role: newRole });
                alert('Cập nhật vai trò thành công!');
            } catch (error) {
                console.error('Lỗi cập nhật role:', error);
            }
        }

        async function deleteUser(userId) {
            if (confirm('Bạn có chắc chắn muốn xóa người dùng này?')) {
                try {
                    await axios.delete(`/api/users/${userId}`);
                    loadUsers();
                } catch (error) {
                    console.error('Lỗi khi xóa người dùng:', error);
                }
            }
        }

        document.getElementById('searchInput').addEventListener('keyup', loadUsers);
        document.getElementById('roleFilter').addEventListener('change', loadUsers);
        document.getElementById('addUserForm').addEventListener('submit', addUser);
        loadUsers();
    </script>
@endsection

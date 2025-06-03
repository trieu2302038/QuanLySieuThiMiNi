<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UsersController extends Controller
{
    public function index()
    {
        return response()->json(User::all());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|unique:users',
            'password' => 'required|min:6',
            'full_name' => 'required',
            'birth_date' => 'nullable|date',
            'email' => 'required|email|unique:users',
            'address' => 'nullable|string',
            'phone_number' => 'nullable|string|max:15',
            'role' => 'required|in:admin,cashier,user',
        ]);

        $validated['password'] = Hash::make($request->password);

        User::create($validated);

        return response()->json(['message' => 'Thêm người dùng thành công!']);
    }

    public function updateRole(Request $request, $id)
    {
        if (auth()->user()->role !== 'admin') {
            return response()->json(['message' => 'Bạn không có quyền thay đổi role'], 403);
        }

        $request->validate([
            'role' => 'required|in:admin,cashier,user',
        ]);

        $user = User::findOrFail($id);
        $user->update(['role' => $request->role]);

        return response()->json(['message' => 'Cập nhật vai trò thành công']);
    }

    public function destroy($id)
    {
        User::destroy($id);
        return response()->json(['message' => 'Xóa người dùng thành công!']);
    }
}

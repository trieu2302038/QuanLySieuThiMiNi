<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    public function getBrands()
    {
        return response()->json(Brand::all());
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:brands',
            'description' => 'nullable|string',
        ], [
            'name.required' => 'Tên thương hiệu không được để trống.',
            'name.unique' => 'Tên thương hiệu đã tồn tại.',
            'description.string' => 'Mô tả phải là một chuỗi.',
        ]);

        Brand::create($request->all());
        return response()->json(['message' => 'Thương hiệu được thêm thành công!']);
    }


    public function update(Request $request, $id)
    {
        $brand = Brand::findOrFail($id);

        $request->validate([
            'name' => "required|unique:brands,name,$id",
            'description' => 'nullable|string',
        ], [
            'name.required' => 'Tên thương hiệu không được để trống.',
            'name.unique' => 'Tên thương hiệu đã tồn tại.',
            'description.string' => 'Mô tả phải là một chuỗi.',
        ]);

        $brand->update($request->all());
        return response()->json(['message' => 'Thương hiệu được cập nhật!']);
    }

    public function destroy($id)
    {
        Brand::destroy($id);
        return response()->json(['message' => 'Thương hiệu đã bị xóa!']);
    }
}

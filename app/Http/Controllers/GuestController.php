<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Brand;

class GuestController extends Controller
{
    public function index() {
        $products = Product::with('category', 'brand')->get();
        $categories = Category::all();
        $brands = Brand::all();

        return view('home', compact('products', 'categories', 'brands'));
        }
}

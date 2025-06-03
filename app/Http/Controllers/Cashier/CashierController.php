<?php

namespace App\Http\Controllers\Cashier;
use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class CashierController extends Controller
{
    public function index()
    {
        $products = Product::with(['category', 'brand'])->get();
        return view('cashier.dashboard', compact('products'));
    }
}

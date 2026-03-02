<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function profile()
    {
        $admin = auth()->guard('admin')->user();
        return view('admin.profile', compact('admin'));
    }   
}

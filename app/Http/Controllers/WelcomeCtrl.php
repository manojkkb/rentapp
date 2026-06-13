<?php

namespace App\Http\Controllers;

class WelcomeCtrl extends Controller
{
    public function index()
    {
        return view('welcome');
    }
}

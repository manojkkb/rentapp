<?php

namespace App\Http\Controllers;

use App\Support\SiteSeo;

class WelcomeCtrl extends Controller
{
    public function index()
    {
        return view('welcome', [
            'seo' => SiteSeo::forHome(),
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Support\SiteSeo;
use Illuminate\View\View;

class LegalController extends Controller
{
    public function privacy(): View
    {
        return view('legal.privacy', [
            'seo' => SiteSeo::forPage(
                'Privacy Policy',
                'Read the Rentkia privacy policy — how we collect, use, and protect your data on India\'s rental marketplace.',
                '/privacy-policy',
            ),
        ]);
    }

    public function terms(): View
    {
        return view('legal.terms', [
            'seo' => SiteSeo::forPage(
                'Terms & Conditions',
                'Read the Rentkia terms and conditions for using India\'s rental marketplace as a renter or vendor.',
                '/terms-and-conditions',
            ),
        ]);
    }
}

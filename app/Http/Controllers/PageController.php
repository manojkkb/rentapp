<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PageController extends Controller
{
    public function about(): View
    {
        return view('pages.about', [
            'pageTitle' => 'About Us',
            'pageSubtitle' => 'Building India\'s most trusted rental marketplace — one store, one booking, one happy customer at a time.',
            'pageBadge' => 'Our Story',
        ]);
    }

    public function howItWorks(): View
    {
        return view('pages.how-it-works', [
            'pageTitleHtml' => 'Rent in <span class="text-gradient">3 easy steps</span>',
            'pageSubtitle' => 'From browsing to returning — the entire journey takes minutes, not hours.',
            'pageBadge' => 'Simple Process',
            'contentWidth' => '6xl',
        ]);
    }

    public function team(): View
    {
        return view('pages.team', [
            'pageTitle' => 'Our Team',
            'pageSubtitle' => 'The people building a smarter, more sustainable way to rent.',
            'pageBadge' => 'People',
            'contentWidth' => '6xl',
        ]);
    }

    public function contact(): View
    {
        return view('pages.contact', [
            'pageTitle' => 'Contact Us',
            'pageSubtitle' => 'Questions, partnerships, or support — we would love to hear from you.',
            'pageBadge' => 'Get in Touch',
        ]);
    }

    public function contactSubmit(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'subject' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        return back()->with('success', 'Thank you for reaching out. Our team will get back to you soon.');
    }
}

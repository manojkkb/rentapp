<?php

namespace App\Http\Controllers;

use App\Support\SiteSeo;
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
            'seo' => SiteSeo::forPage(
                'About Us',
                'Learn about Rentkia — India\'s rental marketplace connecting renters with trusted local vendors for cameras, tools, electronics, and more.',
                '/about-us',
            ),
        ]);
    }

    public function howItWorks(): View
    {
        return view('pages.how-it-works', [
            'pageTitleHtml' => 'Rent in <span class="text-gradient">3 easy steps</span>',
            'pageSubtitle' => 'From browsing to returning — the entire journey takes minutes, not hours.',
            'pageBadge' => 'Simple Process',
            'contentWidth' => '6xl',
            'seo' => SiteSeo::forPage(
                'How It Works',
                'See how Rentkia works — browse stores, book online, and rent cameras, tools, and equipment from verified local vendors in India.',
                '/how-it-works',
            ),
        ]);
    }

    public function team(): View
    {
        return view('pages.team', [
            'pageTitle' => 'Our Team',
            'pageSubtitle' => 'The people building a smarter, more sustainable way to rent.',
            'pageBadge' => 'People',
            'contentWidth' => '6xl',
            'seo' => SiteSeo::forPage(
                'Our Team',
                'Meet the Rentkia team building India\'s trusted rental marketplace for renters and vendors.',
                '/our-team',
            ),
        ]);
    }

    public function contact(): View
    {
        return view('pages.contact', [
            'pageTitle' => 'Contact Us',
            'pageSubtitle' => 'Questions, partnerships, or support — we would love to hear from you.',
            'pageBadge' => 'Get in Touch',
            'seo' => SiteSeo::forPage(
                'Contact Us',
                'Contact Rentkia for support, partnerships, or questions about renting equipment from local vendors in India.',
                '/contact-us',
            ),
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

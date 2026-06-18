<?php

namespace App\Http\Controllers;

use App\Support\SiteSeo;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $entries = SiteSeo::buildSitemapEntries();

        $xml = view('sitemap.index', compact('entries'))->render();

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
        ]);
    }
}

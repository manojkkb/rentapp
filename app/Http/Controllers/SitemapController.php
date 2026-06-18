<?php

namespace App\Http\Controllers;

use App\Support\SiteSeo;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class SitemapController extends Controller
{
    public function index(): Response
    {
        try {
            $xml = SiteSeo::toXml(SiteSeo::buildSitemapEntries());
        } catch (\Throwable $e) {
            Log::error('Sitemap generation failed', ['error' => $e->getMessage()]);
            $xml = SiteSeo::toXml([
                ['loc' => url('/'), 'changefreq' => 'weekly', 'priority' => '1.0', 'lastmod' => now()->toAtomString()],
            ]);
        }

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
        ]);
    }
}

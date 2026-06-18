<?php

namespace App\Console\Commands;

use App\Support\SiteSeo;
use Illuminate\Console\Command;

class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate';

    protected $description = 'Write public/sitemap.xml for search engines';

    public function handle(): int
    {
        $entries = SiteSeo::buildSitemapEntries();
        $xml = SiteSeo::toXml($entries);
        $path = public_path('sitemap.xml');

        file_put_contents($path, $xml);

        $this->info('Sitemap written to '.$path.' ('.count($entries).' URLs)');

        return self::SUCCESS;
    }
}

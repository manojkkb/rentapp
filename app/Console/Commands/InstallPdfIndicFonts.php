<?php

namespace App\Console\Commands;

use App\Support\PdfIndicFonts;
use Illuminate\Console\Command;
use Throwable;

class InstallPdfIndicFonts extends Command
{
    protected $signature = 'pdf:install-indic-fonts';

    protected $description = 'Download and register Noto fonts for Indian-language PDF invoices';

    public function handle(): int
    {
        $this->info('Installing Indic fonts for DomPDF (storage/fonts)...');

        try {
            $installed = PdfIndicFonts::install();
        } catch (Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        foreach ($installed as $basename => $bytes) {
            $kb = number_format($bytes / 1024, 1);
            $this->line("  ✓ {$basename} ({$kb} KB)");
        }

        $this->newLine();
        $this->info('Done. Invoice PDFs will render Hindi, Tamil, Bengali, and other Indian scripts.');

        return self::SUCCESS;
    }
}

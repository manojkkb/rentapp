<?php

namespace Database\Seeders;

use App\Support\PlatformSettings;
use Illuminate\Database\Seeder;

class PlatformSettingsSeeder extends Seeder
{
    public function run(): void
    {
        PlatformSettings::seedDefaults();
    }
}

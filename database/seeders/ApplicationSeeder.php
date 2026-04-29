<?php

namespace Database\Seeders;

use App\Models\Application;
use Illuminate\Database\Seeder;

class ApplicationSeeder extends Seeder
{
    public function run(): void
    {
        $sites = [
            ['name' => 'Clinic System',    'domain' => 'clinic.webxkey.store',         'folder_path' => 'ClinicSystem',   'branch' => 'main'],
            ['name' => 'CurtainPlus',      'domain' => 'plus.webxkey.store',            'folder_path' => 'curtainplus',    'branch' => 'main'],
            ['name' => 'HardMen',          'domain' => 'hardmen.webxkey.store',         'folder_path' => 'hardmen',        'branch' => 'main'],
            ['name' => 'IB Laravel',       'domain' => 'ib.webxkey.store',              'folder_path' => 'ib_laravel',     'branch' => 'main'],
            ['name' => 'Jaffna Gold POS',  'domain' => 'jaffnagoldpos.webxkey.store',   'folder_path' => 'jaffnagoldpos',  'branch' => 'main'],
            ['name' => 'Masjid',           'domain' => 'masjid.webxkey.store',          'folder_path' => 'masjid',         'branch' => 'main'],
            ['name' => 'Mehara House',     'domain' => 'meharahouse.webxkey.store',     'folder_path' => 'meharahouse',    'branch' => 'main'],
            ['name' => 'Miking',           'domain' => 'miking.webxkey.store',          'folder_path' => 'miking',         'branch' => 'main'],
            ['name' => 'N8N',              'domain' => 'n8n.webxkey.store',             'folder_path' => 'n8n',            'branch' => 'main'],
            ['name' => 'NSE',              'domain' => 'nse.webxkey.store',             'folder_path' => 'nse',            'branch' => 'main'],
            ['name' => 'NWC',              'domain' => 'nwc.webxkey.store',             'folder_path' => 'nwc',            'branch' => 'main'],
            ['name' => 'Phoenix',          'domain' => 'phoenix.webxkey.store',         'folder_path' => 'phoenix',        'branch' => 'main'],
            ['name' => 'RNZ',              'domain' => 'rnz.webxkey.store',             'folder_path' => 'rnz',            'branch' => 'main'],
            ['name' => 'Safari Motors',    'domain' => 'safari-motors.webxkey.store',   'folder_path' => 'safari-motors',  'branch' => 'main'],
            ['name' => 'Sahar Lanka',      'domain' => 'sahar-lanka.webxkey.store',     'folder_path' => 'sahar-lanka',    'branch' => 'main'],
            ['name' => 'SevenA',           'domain' => 'sevena.webxkey.store',          'folder_path' => 'sevena',         'branch' => 'main'],
            ['name' => 'SportyNix',        'domain' => 'sportynix.webxkey.store',       'folder_path' => 'sportynix',      'branch' => 'main'],
            ['name' => 'Thilak',           'domain' => 'thilak.webxkey.store',          'folder_path' => 'thilak',         'branch' => 'main'],
            ['name' => 'USN Portal',       'domain' => 'usn.webxkey.store',             'folder_path' => 'usn',            'branch' => 'main'],
            ['name' => 'USN Parts',        'domain' => 'usn-parts.webxkey.store',       'folder_path' => 'usn-parts',      'branch' => 'main'],
            ['name' => 'WebXKey.com',      'domain' => 'webxkey.com',                   'folder_path' => 'webxkey.com',    'branch' => 'main'],
        ];

        foreach ($sites as $site) {
            Application::firstOrCreate(
                ['domain' => $site['domain']],
                array_merge($site, ['status' => 'live', 'php_version' => '8.3'])
            );
        }
    }
}

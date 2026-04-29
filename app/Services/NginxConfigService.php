<?php

namespace App\Services;

class NginxConfigService
{
    public function generateConfig(string $domain, string $folder, string $phpVersion = '8.3'): string
    {
        $phpSocket = "/var/run/php/php{$phpVersion}-fpm.sock";

        return <<<NGINX
server {
    listen 80;
    server_name {$domain} www.{$domain};
    root /var/www/{$folder}/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-XSS-Protection "1; mode=block";
    add_header X-Content-Type-Options "nosniff";

    index index.html index.htm index.php;

    charset utf-8;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php\$ {
        fastcgi_pass unix:{$phpSocket};
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
NGINX;
    }

    public function getConfigPath(string $domain): string
    {
        return "/etc/nginx/sites-available/{$domain}";
    }

    public function getEnabledPath(string $domain): string
    {
        return "/etc/nginx/sites-enabled/{$domain}";
    }
}

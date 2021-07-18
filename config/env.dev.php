<?php

return [
    # Disable WordPress debug mode
    'DEBUG' => true,
    # MySQL connection parameters
    'DB_NAME' => 'dbname',
    'DB_USER' => 'root',
    'DB_PASSWORD' => '',
    'DB_HOST' => 'localhost',
    # Site root URL
    'SITE_URL' => 'https://example.dev',
    # Proxy server parameters
    'PROXY' => false,
    'PROXY_HOST' => '',
    'PROXY_PORT' => '',
    'PROXY_USERNAME' => '',
    'PROXY_PASSWORD' => '',
    'PROXY_EXCLUDED_HOSTS' => '',
    # PHPMailer config
    'PHPMAILER_IS_SMTP' => false,
    'PHPMAILER_HOST' => '',
    'PHPMAILER_SMTP_AUTH' => false,
    'PHPMAILER_PORT' => 587,
    'PHPMAILER_USERNAME' => '',
    'PHPMAILER_PASSWORD' => '',
    'PHPMAILER_SMTP_SECURE' => false,
    'PHPMAILER_FROM' => '',
    # WordPress automatic updates parameters
    'AUTOMATIC_UPDATER_DISABLED' => true,
];

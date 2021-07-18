<?php

namespace Codevelopers\Fullstack\Env;

/**
 * Set the constant ENV.
 * - local (local environment)
 * - dev (development or testing environment)
 * - dist (production environment)
 */
define('ENV', 'local');

/**
 * Get environment configuration.
 */
$config = include __DIR__ . '/env.' . ENV . '.php';

/**
 * Retrieves the value of an environment variable or the default value.
 */
function get_env($key, $default = "", int $filter = FILTER_DEFAULT)
{
    global $config;
    $value = isset($_ENV[$key]) ? $_ENV[$key] : (isset($config[$key]) ? $config[$key] : $default);

    return filter_var($value, $filter);
};

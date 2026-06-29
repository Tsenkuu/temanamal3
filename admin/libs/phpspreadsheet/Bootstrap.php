<?php

namespace PhpOffice\PhpSpreadsheet;

use Psr\SimpleCache\CacheInterface;

class Bootstrap
{
    /**
     * @param ?CacheInterface $cache
     */
    public static function initialize(?CacheInterface $cache = null): void
    {
        // Your initialization logic here, if needed.
        // For this basic implementation, we can leave it empty.
        // In a full library, this might set up autoloaders or caching.
    }
}

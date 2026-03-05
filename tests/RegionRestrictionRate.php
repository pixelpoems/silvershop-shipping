<?php

declare(strict_types=1);

namespace SilverShop\Shipping\Tests;

use SilverShop\Shipping\Model\RegionRestriction;
use SilverStripe\Dev\TestOnly;

class RegionRestrictionRate extends RegionRestriction implements TestOnly
{
    private static string $table_name = 'RegionRestrictionRate';

    private static array $db = [
        'Rate' => 'Currency',
    ];
}

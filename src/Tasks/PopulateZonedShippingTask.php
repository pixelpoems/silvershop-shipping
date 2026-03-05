<?php

declare(strict_types=1);

namespace SilverShop\Shipping\Tasks;

use SilverStripe\Dev\BuildTask;
use SilverStripe\ORM\DataObject;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\YamlFixture;
use SilverStripe\ORM\DB;
use SilverStripe\PolyExecution\PolyOutput;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;

/**
 * @package silvershop-shipping
 */
class PopulateZonedShippingTask extends BuildTask
{
    protected string $title = "Populate Zoned Shipping Methods";

    protected static string $description = 'If no zoned shipping methods exist, it creates some.';

    protected function execute(InputInterface $input, PolyOutput $output): int
    {
        if (!DataObject::get_one('ZonedShippingMethod')) {
            $factory = Injector::inst()->create('FixtureFactory');
            $fixture = YamlFixture::create('silvershop/shipping:tests/ZonedShippingMethod.yml');
            $fixture->writeInto($factory);
            DB::alteration_message('Created zoned shipping methods', 'created');
        } else {
            DB::alteration_message('Some zoned shipping methods already exist. None were created.');
        }
        return Command::SUCCESS;
    }
}

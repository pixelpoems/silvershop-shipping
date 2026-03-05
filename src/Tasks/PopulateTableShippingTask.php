<?php

declare(strict_types=1);

namespace SilverShop\Shipping\Tasks;

use SilverShop\Shipping\Model\TableShippingMethod;
use SilverStripe\Core\Manifest\ModuleResourceLoader;
use SilverStripe\Dev\BuildTask;
use SilverStripe\Dev\FixtureFactory;
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
class PopulateTableShippingTask extends BuildTask
{
    protected string $title = "Populate Table Shipping Methods";

    protected static string $description = 'If no table shipping methods exist, it creates multiple different setups of table shipping.';

    protected function execute(InputInterface $input, PolyOutput $output): int
    {
        if (!DataObject::get_one(TableShippingMethod::class)) {
            $factory = Injector::inst()->create(FixtureFactory::class);
            $fixture = YamlFixture::create(ModuleResourceLoader::singleton()
                ->resolvePath('silvershop/shipping:tests/TableShippingMethod.yml'));
            $fixture->writeInto($factory);
            DB::alteration_message('Created table shipping methods', 'created');
        } else {
            DB::alteration_message('Some table shipping methods already exist. None were created.');
        }
        return Command::SUCCESS;
    }
}

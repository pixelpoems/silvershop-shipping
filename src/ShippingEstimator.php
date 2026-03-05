<?php

declare(strict_types=1);

namespace SilverShop\Shipping;

use SilverStripe\Model\List\ArrayList;
use SilverStripe\ORM\DataList;
use SilverStripe\Core\Injector\Injectable;
use SilverShop\Shipping\Model\ShippingMethod;
use SilverShop\Model\Order;
use SilverShop\Model\Address;

/**
 * Helper class for calculating rates for available shipping options.
 * Provides a little caching, so estimates aren't calculated more than once.
 */
class ShippingEstimator
{
    use Injectable;

    protected Order $order;

    protected ?Address $address;

    protected $estimates;

    protected $calculated = false;

    public function __construct(Order $order, Address $address = null)
    {
        $this->order = $order;
        $this->address = $address instanceof Address ? $address : $order->getShippingAddress();
    }

    public function getEstimates()
    {
        if ($this->calculated) {
            return $this->estimates;
        }

        $total = $this->order->TotalWithoutShipping();

        $output = ArrayList::create();
        if ($options = $this->getShippingMethods()) {
            foreach ($options as $option) {
                $rate = $option->getCalculator($this->order)->calculate($this->address, $total);
                if ($rate !== null) {
                    $option->CalculatedRate = $rate;
                    $output->push($option);
                }
            }
        }

        $output->sort(["CalculatedRate" => "ASC"]); //sort by rate, lowest to highest
        // cache estimates
        $this->estimates = $output;
        $this->calculated = true;

        return $output;
    }

    /**
     * Get options that apply to package and location,
     */
    public function getShippingMethods(): DataList
    {
        return ShippingMethod::get()->filter(["Enabled" => 1]);
    }
}

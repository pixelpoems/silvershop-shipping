<?php

declare(strict_types=1);

namespace SilverShop\Shipping\Extension;

use SilverStripe\Core\Extension;
use SilverShop\Shipping\ShippingPackage;
use SilverShop\Shipping\ShippingEstimator;
use SilverShop\Shipping\Model\ShippingMethod;
use SilverShop\Shipping\Model\Zone;
use Exception;

class OrderShippingExtension extends Extension
{
    public $owner;

    private static array $db = [
        'ShippingTotal' => 'Currency'
    ];

    private static array $has_one = [
        'ShippingMethod' => ShippingMethod::class
    ];

    private static array $casting = [
        'TotalWithoutShipping' => 'Currency'
    ];

    public function TotalWithoutShipping(): int|float
    {
        return $this->getOwner()->Total() - $this->getOwner()->ShippingTotal;
    }

    /**
     * create package, with total weight, dimensions, value, etc.
     * @param  integer $value
     * @return ShippingPackage
     */
    public function createShippingPackage($value = 0)
    {
        $items = $this->getOwner()->Items();

        if (!$items->exists()) {
            $package = ShippingPackage::create();
        } else {
            $weight = $items->Sum('Weight', true); //Sum is found on OrdItemList (Component Extension)
            $width = $items->Sum('Width', true);
            $height = $items->Sum('Height', true);
            $depth = $items->Sum('Depth', true);

            if (!$value) {
                $value = $this->getOwner()->SubTotal();
            }

            $quantity = $items->Quantity();

            $package = ShippingPackage::create(
                $weight,
                [$height,$width,$depth],
                [
                    'value' => $value,
                    'quantity' => $quantity
                ]
            );
        }

        $this->getOwner()->extend('updateShippingPackage', $package);

        return $package;
    }

    /**
     * Get shipping estimates.
     *
     * @return DataList
     */
    public function getShippingEstimates()
    {
        $address = $this->getOwner()->getShippingAddress();
        $estimator = ShippingEstimator::create($this->getOwner(), $address);

        return $estimator->getEstimates();
    }

    /**
     * Set shipping method and shipping cost
     *
     * @param $option - shipping option to set, and calculate shipping from
     * @return boolean sucess/failure of setting
     */
    public function setShippingMethod(ShippingMethod $option): bool
    {
        $package = $this->getOwner()->createShippingPackage();

        if (!$package) {
            throw new Exception(_t("OrderShippingExtension.NoPackage", "Shipping package information not available"));
        }

        $address = $this->getOwner()->getShippingAddress();

        if (!$address || !$address->exists() && $option->requiresAddress()) {
            throw new Exception(_t("OrderShippingExtension.NoAddress", "No address has been set"));
        }

        $this->getOwner()->ShippingTotal = $option->calculateRate($package, $address);
        $this->getOwner()->ShippingMethodID = $option->ID;
        $this->getOwner()->write();

        return true;
    }

    public function onSetBillingAddress($address): static
    {
        if ($address) {
            Zone::cache_zone_ids($address);
        }

        return $this;
    }

    public function onSetShippingAddress($address): static
    {
        if ($address) {
            Zone::cache_zone_ids($address);
        }

        return $this;
    }
}

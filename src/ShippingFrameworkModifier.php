<?php

declare(strict_types=1);

namespace SilverShop\Shipping;

use SilverShop\Model\Modifiers\OrderModifier;

class ShippingFrameworkModifier extends OrderModifier
{
    private static string $table_name = 'ShippingFrameworkModifier';

    private static string $singular_name = 'Shipping';

    public function value($incoming): int|float
    {
        $order = $this->Order();
        if ($order && $order->exists() && ($shipping = $order->ShippingMethod()) && $shipping->exists()) {
            $value = $shipping->getCalculator($order)->calculate(null, $incoming);
            $order->ShippingTotal = $value;
            $order->write();
            return $value ?? 0;
        }

        return 0;
    }

    public function TableTitle()
    {
        $title = $this->i18n_singular_name();

        if ($this->Order() && $this->Order()->ShippingMethod()->exists()) {
            $title .= " (" . $this->Order()->ShippingMethod()->Name . ")";
        }

        $this->extend('updateTableTitle', $title);

        return $title;
    }
}

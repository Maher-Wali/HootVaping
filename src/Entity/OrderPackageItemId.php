<?php

namespace App\Entity;

class OrderPackageItemId
{
    public ?int $orderPackage = null;
    public ?int $product = null;

    public function __construct(?int $orderPackage = null, ?int $product = null)
    {
        $this->orderPackage = $orderPackage;
        $this->product = $product;
    }
}

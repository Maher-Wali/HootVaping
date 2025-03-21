<?php

namespace App\Entity;

class CartPackageItemId
{
    public ?int $cartPackage = null;
    public ?int $product = null;

    public function __construct(?int $cartPackage = null, ?int $product = null)
    {
        $this->cartPackage = $cartPackage;
        $this->product = $product;
    }
}

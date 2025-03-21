<?php

namespace App\Entity;

use App\Repository\CartPackageItemRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CartPackageItemRepository::class)]
#[ORM\Table(name: "cart_package_item")]
#[ORM\IdClass(CartPackageItemId::class)] // Define a separate class for composite key
class CartPackageItem
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: CartPackage::class, inversedBy: "cartPackageItems")]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private ?CartPackage $cartPackage = null;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Product::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Product $product = null;

    #[ORM\Column(type: "integer")]
    private ?int $quantity = null;

    public function getCartPackage(): ?CartPackage
    {
        return $this->cartPackage;
    }

    public function setCartPackage(?CartPackage $cartPackage): self
    {
        $this->cartPackage = $cartPackage;
        return $this;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): self
    {
        $this->product = $product;
        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;
        return $this;
    }
}

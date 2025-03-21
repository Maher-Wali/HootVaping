<?php

namespace App\Entity;

use App\Repository\CartPackageItemRepository;
use App\Repository\OrderPackageItemRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderPackageItemRepository::class)]
#[ORM\Table(name: "order_package_item")]
#[ORM\IdClass(OrderPackageItemId::class)] // Define a separate class for composite key
class OrderPackageItem
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: OrderPackage::class, inversedBy: "orderPackageItems")]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private ?OrderPackage $orderPackage = null;

    #[ORM\Id]
    #[ORM\Column(length: 50)]
    private ?string $productName = null;

    #[ORM\Column(type: "integer")]
    private ?int $quantity = null;

    public function getOrderPackage(): ?OrderPackage
    {
        return $this->orderPackage;
    }

    public function setOrderPackage(?OrderPackage $orderPackage): self
    {
        $this->orderPackage = $orderPackage;
        return $this;
    }

    public function getProductName(): ?string
    {
        return $this->productName;
    }

    public function setProductName(?string $productName): self
    {
        $this->productName = $productName;
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

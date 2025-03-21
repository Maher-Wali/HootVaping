<?php

namespace App\Entity;

use App\Repository\CartPackageRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CartPackageRepository::class)]
class OrderPackage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Order::class, inversedBy: 'orderPackages')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Order $order = null;

    #[ORM\Column(length: 50)]
    private ?string $packageName = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrder(): ?order
    {
        return $this->order;
    }

    public function setOrder(?Order $order): self
    {
        $this->order = $order;
        return $this;
    }

    public function getPackageName(): ?string
    {
        return $this->packageName;
    }

    public function setPackageName(?string $packageName): self
    {
        $this->packageName = $packageName;
        return $this;
    }
}

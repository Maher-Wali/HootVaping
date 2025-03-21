<?php

namespace App\Entity;

use App\Repository\PromoCodeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PromoCodeRepository::class)]
class PromoCode
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: "string", unique: true)]
    private string $code;

    #[ORM\Column(type: "integer")]
    private int $discount;

    #[ORM\Column(type: "integer")]
    private int $maxUses;

    #[ORM\Column(type: "integer")]
    private int $currentUses = 0;

    #[ORM\Column(type: "boolean")]
    private bool $isActive = true;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;
        return $this;
    }

    public function getDiscount(): ?int
    {
        return $this->discount;
    }

    public function setDiscount(int $discount): self
    {
        $this->discount = $discount;
        return $this;
    }

    public function getMaxUses(): ?int
    {
        return $this->maxUses;
    }

    public function setMaxUses(int $maxUses): self
    {
        $this->maxUses = $maxUses;
        return $this;
    }

    public function getCurrentUses(): ?int
    {
        return $this->currentUses;
    }

    public function setCurrentUses(int $currentUses): self
    {
        $this->currentUses = $currentUses;
        return $this;
    }

    public function getIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;
        return $this;
    }

}

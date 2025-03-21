<?php

namespace App\Entity;

use App\Repository\ELiquidRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ELiquidRepository::class)]
class ELiquid extends Product
{
    #[ORM\Column(length: 250)]
    private ?string $flavor = null;

    public function getFlavor(): ?string
    {
        return $this->flavor;
    }

    public function setFlavor(string $flavor): static
    {
        $this->flavor = $flavor;

        return $this;
    }
}

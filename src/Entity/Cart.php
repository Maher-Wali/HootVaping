<?php

namespace App\Entity;

use App\Repository\CartRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CartRepository::class)]
class Cart
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?float $total = 0.0;

    #[ORM\OneToOne(targetEntity: User::class, inversedBy: 'cart')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?User $user = null;

    private ?EntityManagerInterface $entityManager = null;
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTotal(): ?float
    {
        return $this->total;
    }

    public function setTotal(float $total): static
    {
        $this->total = $total;

        return $this;
    }

    public function getUser(): ?User {
        return $this->user;
    }

    public function setUser(?User $user): self {
        $this->user = $user;
        return $this;
    }

    public function addToTotal(float $amount): self
    {
        $this->total += $amount;
        return $this;
    }

    public function setEntityManager(EntityManagerInterface $entityManager): self
    {
        $this->entityManager = $entityManager;
        return $this;
    }

    public function recalculateTotal(EntityManagerInterface $entityManager): self
    {
        if (!$entityManager) {
            throw new \LogicException('EntityManager must be set before recalculating total');
        }

        $cartPackages = $entityManager
            ->getRepository(CartPackage::class)
            ->findBy(['cart' => $this]);

        $total = 0;
        foreach ($cartPackages as $cartPackage) {
            $total += $cartPackage->getPackage()->getTotal();
        }

        $this->setTotal($total);
        return $this;
    }


}

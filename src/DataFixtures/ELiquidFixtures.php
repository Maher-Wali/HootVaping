<?php

namespace App\DataFixtures;

use App\Entity\Product;
use App\Entity\ELiquid;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ELiquidFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Creating a Product
        $product = new Product();
        $product->setName('Basic Vape Kit');
        $product->setPrice(25.99);
        $product->setImage('basic_vape_kit.jpg');
        $manager->persist($product);

        // Creating an ELiquid product
        $eliquid = new ELiquid();
        $eliquid->setName('Oni');
        $eliquid->setPrice(15);
        $eliquid->setImage('oni.jpg');
        $eliquid->setFlavor('Strawberry');
        $manager->persist($eliquid);

        $manager->flush(); // Save to DB
    }
}

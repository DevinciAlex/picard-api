<?php

namespace App\DataFixtures;

use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $products = [
            [
                'name' => 'Sandwich jambon fromage',
                'image' => 'https://placehold.co/300x200',
                'description' => 'Sandwich au jambon et au fromage.',
                'price' => 4.5,
                'rating' => 4.2,
                'available' => true,
            ],
            [
                'name' => 'Salade César',
                'image' => 'https://placehold.co/300x200',
                'description' => 'Salade César prête à consommer.',
                'price' => 6.9,
                'rating' => 4.5,
                'available' => true,
            ],
            [
                'name' => 'Cookie chocolat',
                'image' => 'https://placehold.co/300x200',
                'description' => 'Cookie aux pépites de chocolat.',
                'price' => 2.2,
                'rating' => 4.7,
                'available' => false,
            ],
        ];

        foreach ($products as $data) {
            $product = (new Product())
                ->setName($data['name'])
                ->setImage($data['image'])
                ->setDescription($data['description'])
                ->setPrice($data['price'])
                ->setRating($data['rating'])
                ->setAvailable($data['available']);

            $manager->persist($product);
        }

        $manager->flush();
    }
}

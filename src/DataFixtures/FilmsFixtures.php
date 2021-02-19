<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use App\Entity\Films;


class FilmsFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        // $product = new Product();
        // $manager->persist($product);
        $faker = Factory::create("FR-fr");

        for ($i = 0; $i < 20; $i++) {
            $films = new Films();
            $films->setNom($faker->state)
                ->setSynopsis($faker->text)
                ->setType("film");
            $manager->persist($films);
        }
        $manager->flush();
    }
}

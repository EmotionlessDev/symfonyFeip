<?php

declare(strict_types=1);

namespace App\Tests\Fixture;

use App\Entity\House;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;

final class HouseFixture extends AbstractFixture
{
    #[\Override]
    public function load(ObjectManager $manager): void
    {
        $house = new House();
        $house->setName('Test House');
        $house->setSleepingCapacity(4);
        $house->setBathrooms(2);
        $house->setLocation('Test Location');
        $house->setPrice(100);

        $manager->persist($house);
        $manager->flush();

        $this->addReference('test_house', $house);
    }
}

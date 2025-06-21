<?php

namespace App\Tests\Fixture;

use App\Entity\House;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Booking;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class BookingFixture extends AbstractFixture implements DependentFixtureInterface
{

    public function getDependencies(): array
    {
        return [
            HouseFixture::class,
        ];
    }
    public function load(ObjectManager $manager): void
    {
        $booking = new Booking();
        $booking->setPhoneNumber('1234567890');
        $booking->setComment('Test booking comment');
        $booking->setHouse($this->getReference('test_house', House::class));

        $manager->persist($booking);
        $manager->flush();

        $this->addReference('test_booking', $booking);
    }
}
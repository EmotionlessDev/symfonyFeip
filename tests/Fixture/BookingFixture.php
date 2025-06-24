<?php

declare(strict_types=1);

namespace App\Tests\Fixture;

use App\Entity\Booking;
use App\Entity\House;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

final class BookingFixture extends AbstractFixture implements DependentFixtureInterface
{
    #[\Override]
    public function getDependencies(): array
    {
        return [
            HouseFixture::class,
        ];
    }

    #[\Override]
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

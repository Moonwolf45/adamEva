<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Delivery;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class DeliveryFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $deliveries = [
            [
                'title' => 'Курьером',
                'description' => 'Доставка курьером по городу. Срок: 1-2 дня. Стоимость: 300 ₽.',
                'status' => true,
            ],
            [
                'title' => 'Пункт выдачи',
                'description' => 'Доставка до пункта выдачи. Срок: 2-5 дней. Стоимость: 200 ₽.',
                'status' => true,
            ],
            [
                'title' => 'Самовывоз',
                'description' => 'Самовывоз из магазина. Стоимость: бесплатно.',
                'status' => true,
            ],
            [
                'title' => 'Почта',
                'description' => 'Доставка почтой России. Срок: 5-14 дней.',
                'status' => false,
            ],
        ];

        foreach ($deliveries as $data) {
            $delivery = new Delivery();
            $delivery->setTitle($data['title']);
            $delivery->setDescription($data['description']);
            $delivery->setStatus($data['status']);

            $manager->persist($delivery);
            $this->addReference('delivery-' . mb_strtolower($data['title']), $delivery);
        }

        $manager->flush();
    }
}

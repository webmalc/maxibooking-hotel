<?php


namespace MBH\Bundle\BaseBundle\DataFixtures\MongoDB;


use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use MBH\Bundle\BaseBundle\Document\NotificationType;
use MBH\Bundle\BaseBundle\Lib\AbstractFixture;

class NotificationTypeData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $data = [
            NotificationType::OWNER_ALL => $this->getAllOwnerTypes(),
            NotificationType::OWNER_STUFF => $this->getStuffOwnerTypes(),
            NotificationType::OWNER_CLIENT => $this->getClientOwnerTypes(),
        ];

        foreach ($data as $owner => $types) {
            $this->createNotification($owner, $types, $manager);
        }

        $manager->flush();

    }

    public function getOrder()
    {
        return -999;
    }

    private function createNotification(string $owner, array $types, ObjectManager $manager)
    {
        foreach ($types as $type) {
            if ($manager->getRepository('MBHBaseBundle:NotificationType')->findOneBy(['type' => $type])) {
                continue;
            }
            $notificationType = new NotificationType();
            $notificationType
                ->setOwner($owner)
                ->setType($type);
            $manager->persist($notificationType);
        }
    }

    private function getAllOwnerTypes(): array
    {
        return [
            NotificationType::ONLINE_ORDER_TYPE,
            NotificationType::ONLINE_PAYMENT_CONFIRM_TYPE,
            NotificationType::ARRIVAL_TYPE
        ];
    }

    private function getStuffOwnerTypes(): array
    {
        return [
            NotificationType::CHANNEL_MANAGER_TYPE,
            NotificationType::UNPAID_TYPE,
            NotificationType::TASK_TYPE,
            NotificationType::DAILY_REPORT_TYPE
        ];
    }

    private function getClientOwnerTypes(): array
    {
        return [
            NotificationType::CASH_DOC_CONFIRMATION_TYPE,
            NotificationType::FEEDBACK_TYPE,
            NotificationType::CONFIRM_ORDER_TYPE
        ];
    }
}
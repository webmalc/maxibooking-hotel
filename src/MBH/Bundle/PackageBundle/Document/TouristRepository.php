<?php

namespace MBH\Bundle\PackageBundle\Document;

use Doctrine\ODM\MongoDB\DocumentRepository;

class TouristRepository extends DocumentRepository
{
    /**
     * @param Order $order
     * @return Tourist[]
     */
    public function getAllTouristsByOrder(Order $order)
    {
        $tourists = [];
        foreach ($order->getPackages() as $package) {
            /** @var Package $package */
            foreach ($package->getTourists() as $tourist) {
                /** @var Tourist $tourist */
                $tourists[$tourist->getId()] = $tourist;
            }
        }

        return $tourists;
    }

    /**
     * @param Package $package
     * @return Tourist[]
     */
    public function getForeignTouristsByPackage(Package $package)
    {
        $tourists = $package->getTourists();
        $tourists[] = $package->getMainTourist();

        $foreignTourists = [];
        /** @var Tourist $tourist */
        foreach($tourists as $tourist) {
            if($tourist) {
                $citizenship = $tourist->getCitizenship();
                if($citizenship === null || ($citizenship && $citizenship->getName() != "Россия")) {
                    $foreignTourists[] = $tourist;
                }
            }
        }

        return $foreignTourists;
    }

    /**
     * @param string $lastName
     * @param string $firstName
     * @param string $patronymic
     * @param \DateTime $birthday
     * @param string $email
     * @param string $phone
     * @param string $address
     * @param string $note
     * @param string $communicationLanguage
     * @return Tourist
     */
    public function fetchOrCreate(
        $lastName,
        $firstName,
        $patronymic = null,
        \DateTime $birthday = null,
        $email = null,
        $phone = null,
        $address = null,
        $note = null,
        $communicationLanguage = null
    ) {
        $dm = $this->getDocumentManager();
        $tourist  = null;
        $lastName = mb_convert_case(mb_strtolower($lastName), MB_CASE_TITLE);
        $firstName = mb_convert_case(mb_strtolower($firstName), MB_CASE_TITLE);
        !$patronymic ?: $patronymic = mb_convert_case(mb_strtolower($patronymic), MB_CASE_TITLE);
        !$phone ?: $phone = Tourist::cleanPhone($phone);

        // find tourist
        if ($email || $birthday || $phone) {
            $qb = $this->createQueryBuilder()
                ->field('lastName')->equals($lastName)
                ->field('firstName')->equals($firstName)
                ->field('deletedAt')->equals(null)
            ;

            if (!empty($email)) {
                $qb->addOr($qb->expr()->field('email')->equals($email));
            }
            if (!empty($phone)) {
                $qb->addOr($qb->expr()->field('phone')->equals($phone));
                $qb->addOr($qb->expr()->field('mobilePhone')->equals($phone));
            }
            if (!empty($birthday)) {
                $qb->addOr($qb->expr()->field('birthday')->equals($birthday));
            }
            $tourist = $qb->getQuery()->getSingleResult();
        }

        // empty tourist
        if (!$tourist) {
            $qb = $this->createQueryBuilder()
                ->field('lastName')->equals($lastName)
                ->field('firstName')->equals($firstName)
                ->field('deletedAt')->equals(null)
                ->field('email')->equals(null)
                ->field('phone')->equals(null)
                ->field('birthday')->equals(null)
            ;

            $tourist = $qb->getQuery()->getSingleResult();
        }
        // new tourist
        if (!$tourist || $tourist->getDeletedAt()) {
            $tourist = new Tourist();
            $tourist->setLastName($lastName)
                ->setFirstName($firstName)
            ;
        }

        if ($patronymic) {
            $tourist->setPatronymic($patronymic);
        }
        if ($email) {
            $tourist->setEmail($email);
        }
        if ($phone) {
            $tourist->setPhone($phone);
        }
        if ($birthday) {
            $tourist->setBirthday($birthday);
        }
        if ($note || $address) {
            $tourist->setNote($address ?  'Address: ' . $address . "\n\n" . $note : $note);
        }

        if ($communicationLanguage) {
            $tourist->setCommunicationLanguage($communicationLanguage);
        }

        $dm->persist($tourist);
        $dm->flush($tourist);

        return $tourist;
    }
}

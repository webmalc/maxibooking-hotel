<?php

namespace MBH\Bundle\PackageBundle\Document;

use Doctrine\ODM\MongoDB\DocumentRepository;

class TouristRepository extends DocumentRepository
{

    /**
     * @param string $lastName
     * @param string $firstName
     * @param string $patronymic
     * @param \DateTime $birthday
     * @param string $email
     * @param string $phone
     * @param string $address
     * @param string $note
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
        $note = null
    ) {
        $dm = $this->getDocumentManager();

        // find tourist
        if ($email || $birthday || $phone) {
            $qb = $this->createQueryBuilder('q')
                ->field('lastName')->equals($lastName)
                ->field('firstName')->equals($firstName);
            if ($patronymic) {
                $qb->field('patronymic')->equals($patronymic);
            }
            if (!empty($email)) {
                $qb->field('email')->equals($email);
            }
            if (!empty($phone)) {
                $qb->field('phone')->equals($phone);
            }
            if (!empty($birthday)) {
                $qb->field('birthday')->equals($birthday);
            }

            $tourist = $qb->getQuery()->getSingleResult();

            if ($tourist) {
                if ($phone) {
                    $tourist->setPhone($phone);
                }
                if ($birthday) {
                    $tourist->setBirthday($birthday);
                }
                if ($email) {
                    $tourist->setEmail($email);
                }
                if ($address) {
                    $tourist->setAddress($address);
                }
                if ($note) {
                    $tourist->setNote($note);
                }
                $dm->persist($tourist);
                $dm->flush($tourist);

                return $tourist;
            }

        }

        // create tourist
        $tourist = new Tourist();
        $tourist->setLastName($lastName)
            ->setFirstName($firstName)
            ->setPatronymic($patronymic)
            ->setEmail($email)
            ->setPhone($phone)
            ->setAddress($address)
            ->setNote($note)
        ;

        if ($birthday) {
            $tourist->setBirthday($birthday);
        }

        $dm->persist($tourist);
        $dm->flush($tourist);

        return $tourist;
    }
}

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
     * @return Tourist
     */
    public function fetchOrCreate($lastName, $firstName, $patronymic = null, \DateTime $birthday = null, $email = null, $phone = null)
    {
        $dm = $this->getDocumentManager();

        // find tourist
        if ($email || $birthday) {
            $qb = $this->createQueryBuilder('q')
                ->field('lastName')->equals($lastName)
                ->field('lastName')->equals($firstName)
            ;
            if ($patronymic) {
                $qb->field('patronymic')->equals($patronymic);
            }
            if (!empty($email)) {
                $qb->field('email')->equals($email);
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
                    $tourist->setBirthday($phone);
                }
                if ($email) {
                    $tourist->setEmail($email);
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
                ->setBirthday($birthday)
                ->setEmail($email)
                ->setPhone($phone)

        ;

        $dm->persist($tourist);
        $dm->flush($tourist);

        return $tourist;
    }
}

<?php

namespace MBH\Bundle\PackageBundle\Document;

use Doctrine\ODM\MongoDB\DocumentRepository;
use MBH\Bundle\PackageBundle\Document\Criteria\PackageQueryCriteria;
use MBH\Bundle\PackageBundle\Document\Criteria\TouristQueryCriteria;
use MBH\Bundle\BillingBundle\Lib\Model\Country;

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
        foreach ($tourists as $tourist) {
            if ($tourist) {
                $citizenship = $tourist->getCitizenshipTld();
                if ($citizenship && $citizenship !== Country::RUSSIA_TLD) {
                    $foreignTourists[] = $tourist;
                }
            }
        }

        return $foreignTourists;
    }

    /**
     * @param TouristQueryCriteria $criteria
     * @return \Doctrine\ODM\MongoDB\Query\Builder
     */
    public function queryCriteriaToBuilder(TouristQueryCriteria $criteria)
    {
        $queryBuilder = $this->createQueryBuilder();

        if ($criteria->search) {
            $fullNameRegex = new \MongoRegex('/.*' . $criteria->search . '.*/ui');
            if (is_numeric($criteria->search)) {
                $queryBuilder->field('documentRelation.number')->equals($fullNameRegex);
            } else {
                $queryBuilder->field('fullName')->equals($fullNameRegex);
            }
        }

        if ($criteria->citizenship) {
            if ($criteria->citizenship == TouristQueryCriteria::CITIZENSHIP_NATIVE) {
                $queryBuilder->addOr($queryBuilder->expr()->field('citizenshipTld')->equals(null));
                $queryBuilder->addOr($queryBuilder->expr()->field('citizenshipTld')->equals(Country::RUSSIA_TLD));
            } elseif ($criteria->citizenship == TouristQueryCriteria::CITIZENSHIP_FOREIGN) {
                $queryBuilder->field('citizenshipTld')->notEqual(Country::RUSSIA_TLD)->exists(true);
            }
        }

        $packageCriteria = new PackageQueryCriteria();

        if ($criteria->begin || $criteria->end) {
            $criteriaDates = true;
            $packageCriteria->begin = $criteria->begin;
            $packageCriteria->end = $criteria->end;
        }

        if ($criteria->getHotels()->count() > 0) {
            $criteriaHotel = true;
            $packageCriteria->hotel = $criteria->getHotels();
        }

        if (isset($criteriaDates) || isset($criteriaHotel)) {
            $packageRepository = $this->dm->getRepository('MBHPackageBundle:Package');
            $touristIDs = $packageRepository->findTouristIDsByCriteria($packageCriteria);
            $queryBuilder->field('id')->in($touristIDs);
        }

        return $queryBuilder;
    }

    /**
     * @param TouristQueryCriteria $criteria
     * @param int $offset
     * @param int $limit
     * @return Tourist[]
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function findByQueryCriteria(TouristQueryCriteria $criteria, $offset = 0, $limit = 10)
    {
        $queryBuilder = $this->queryCriteriaToBuilder($criteria);
        $queryBuilder
            ->skip($offset)
            ->limit($limit)
            ->sort('fullName', 'asc');
        $tourists = $queryBuilder->getQuery()->execute();

        return $tourists;
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
     * @throws \Exception
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
    )
    {
        $dm = $this->getDocumentManager();
        $tourist = null;
        $lastName = trim(mb_convert_case(mb_strtolower($lastName), MB_CASE_TITLE));
        $firstName = trim(mb_convert_case(mb_strtolower($firstName), MB_CASE_TITLE));
        if (empty($lastName)) {
            throw new \Exception('Empty tourist last name');
        }

        !$patronymic ?: $patronymic = mb_convert_case(mb_strtolower($patronymic), MB_CASE_TITLE);
        !$phone ?: $phone = Tourist::cleanPhone($phone);

        // find tourist
        if ($email || $birthday || $phone) {
            $qb = $this->createQueryBuilder()
                ->field('lastName')->equals($lastName)
                ->field('firstName')->equals($firstName)
                ->field('deletedAt')->equals(null);

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
                ->field('birthday')->equals(null);

            $tourist = $qb->getQuery()->getSingleResult();
        }
        // new tourist
        if (!$tourist || $tourist->getDeletedAt()) {
            $tourist = new Tourist();
            $tourist->setLastName($lastName)
                ->setFirstName($firstName);
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
            $tourist->setNote($address ? 'Address: ' . $address . "\n\n" . $note : $note);
        }

        if ($communicationLanguage) {
            $tourist->setCommunicationLanguage($communicationLanguage);
        }

        $dm->persist($tourist);
        $dm->flush($tourist);

        return $tourist;
    }

    /**
     * @param string $query
     * @return array
     * @throws \Doctrine\ODM\MongoDB\MongoDBException]
     */
    public function getIdsWithNameByQueryString(string $query): array
    {
        return $this
            ->createQueryBuilder()
            ->field('fullName')->equals(new \MongoRegex('/^.*' . $query . '.*/ui'))
            ->distinct('id')
            ->getQuery()
            ->execute()
            ->toArray();
    }
}

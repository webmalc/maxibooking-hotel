<?php

namespace MBH\Bundle\PackageBundle\Document;


use MBH\Bundle\PackageBundle\Lib\PayerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Class PayerRepository
 * @author Aleksandr Arofikin <sashaaro@gmail.com>
 */
class PayerRepository
{
    use ContainerAwareTrait;

    /**
     * @param $searchQuery
     * @return PayerInterface[]
     */
    public function search($searchQuery)
    {
        $mongoRegex = new \MongoRegex('/.*'.$searchQuery.'.*/i');


        $dm = $this->container->get('doctrine_mongodb')->getManager();
        $touristRepository = $dm->getRepository('MBHPackageBundle:Tourist');
        $organizationRepository = $dm->getRepository('MBHPackageBundle:Organization');

        $queryBuilder = $touristRepository->createQueryBuilder()->sort(['fullName' => 'asc', 'birthday' => 'desc']);
        $searchFields = ['fullName', 'email', 'phone'];
        foreach($searchFields as $fieldName) {
            $queryBuilder->addOr($queryBuilder->expr()->field($fieldName)->equals($mongoRegex));
        }
        $queryBuilder->limit(10);
        /** @var Tourist[] $tourists */
        $tourists = $queryBuilder->getQuery()->execute();

        $queryBuilder = $organizationRepository->createQueryBuilder()->sort(['fullName' => 'asc', 'birthday' => 'desc']);
        $searchFields = ['name', 'director_fio', 'inn'];
        foreach($searchFields as $fieldName) {
            $queryBuilder->addOr($queryBuilder->expr()->field($fieldName)->equals($mongoRegex));
        }
        $queryBuilder->limit(10);
        /** @var Organization[] $organizations */
        $organizations = $queryBuilder->getQuery()->execute();

        return ['tourists' => $tourists->toArray(), 'organizations' => $organizations->toArray()];
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: danya
 * Date: 28.07.17
 * Time: 15:21
 */

namespace MBH\Bundle\PackageBundle\Services;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Query\Builder;
use MBH\Bundle\BaseBundle\Lib\ClientDataTableParams;
use MBH\Bundle\BaseBundle\Lib\Searchable;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\PackageBundle\Document\Criteria\TouristQueryCriteria;
use MBH\Bundle\PackageBundle\Document\TouristRepository;
use MBH\Bundle\PackageBundle\Form\TouristFilterForm;
use MBH\Bundle\UserBundle\Document\User;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\Request;

class TouristManager implements Searchable
{
    /** @var  DocumentManager */
    private $dm;
    /** @var  FormFactory */
    private $formFactory;

    public function __construct(DocumentManager $dm, FormFactory $formFactory) {
        $this->dm = $dm;
        $this->formFactory = $formFactory;
    }

    /**
     * @param Request $request
     * @param User $user
     * @param Hotel $hotel
     * @return Builder
     */
    public function getQueryBuilderByRequestData(Request $request, User $user, Hotel $hotel)
    {
        $tableParams = ClientDataTableParams::createFromRequest($request);
        $formData = (array)$request->get('form');

        $form = $this->formFactory->create(TouristFilterForm::class);
        $form->submit($formData);

        if (!$form->isValid()) {
            return $form->getErrors()[0]->getMessage();
        }

        /** @var TouristQueryCriteria $criteria */
        $criteria = $form->getData();

        /** @var TouristRepository $touristRepository */
        $touristRepository = $this->dm->getRepository('MBHPackageBundle:Tourist');

        if ($criteria->begin && $criteria->end) {
            $diff = $criteria->begin->diff($criteria->end);
            if ($diff->y == 1 && $diff->m > 0 || $diff->y > 1) {
                $begin = clone($criteria->begin);
                $criteria->end = $begin->modify('+ 1 year');
            }
        }

        return $touristRepository
            ->queryCriteriaToBuilder($criteria)
            ->skip($tableParams->getStart())
            ->limit($tableParams->getLength())
            ->sort('fullName', 'asc');
    }
}
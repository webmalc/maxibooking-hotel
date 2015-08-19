<?php

namespace MBH\Bundle\BaseBundle\Controller;

use Doctrine\Common\Persistence\Mapping\MappingException;
use Doctrine\ODM\MongoDB\DocumentRepository;
use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\PackageBundle\Document\Order;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Document\Tourist;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ExportController
 * @author Aleksandr Arofikin <sasaharo@gmail.com>
 * @Route("/export")
 */
class ExportController extends BaseController
{
    /**
     * @Security("is_granted('ROLE_ADMIN')")
     * @Route("/csv/{repositoryName}", name="export_csv")
     */
    public function csvAction($repositoryName)
    {
        $repositoryName = $this->getRepositoryNameByShortcut($repositoryName);

        if(!$repositoryName) {
            throw $this->createNotFoundException();
        }
        /** @var DocumentRepository $repository */
        $repository = $this->dm->getRepository($repositoryName);
        /** @var \MongoCollection $collection */
        $collection = $this->get('mbh.mongo')->getCollection($repository->getClassMetadata()->getCollection());
        $filterCriteria = $this->dm->getFilterCollection()->getFilterCriteria($repository->getClassMetadata());
        /** @var \MongoCursor $mongoCursor */
        $mongoCursor = $collection->find($filterCriteria);

        if($mongoCursor->count(true) == 0) {
            throw $this->createNotFoundException("Data do not exists to generate csv");
        }
        $mongoCursor->next();
        $data[] = array_keys($mongoCursor->current());
        foreach($mongoCursor as $row) {
            $data[] = array_values($row);
        }

        $text = '';
        foreach($data as $row) {
            var_dump($row);
            $text .= implode(';', $row);
            $text .= "\n";
        }
        die();

        $response = new Response($text);
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="export.csv"');
        return $response;
    }

    /**
     * @param string $shortcut
     * @return string|null
     */
    private function getRepositoryNameByShortcut($shortcut)
    {
        $repositories =  [
            'tourists' => Tourist::class,
            'packages' => Package::class,
            'orders' => Order::class,
            'cash' => CashDocument::class,
        ];

        return array_key_exists($shortcut, $repositories) ? $repositories[$shortcut] : null;
    }
}
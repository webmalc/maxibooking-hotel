<?php

namespace MBH\Bundle\BaseBundle\Controller;

use Doctrine\Common\Persistence\Mapping\MappingException;
use Doctrine\ODM\MongoDB\DocumentRepository;
use Doctrine\ODM\MongoDB\PersistentCollection;
use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\PackageBundle\Document\Order;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Document\Tourist;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Class ExportController

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
        $className = $this->getClassNameByShortcut($repositoryName);

        if(!$repositoryName) {
            throw $this->createNotFoundException();
        }
        /** @var DocumentRepository $repository */
        $repository = $this->dm->getRepository($className);
        $fields = $repository->getClassMetadata()->getFieldNames();
        $methods = array_combine($fields, array_map(function($filed){
            return 'get'.ucfirst($filed);
        }, $fields));

        $reflection = new \ReflectionClass($className);
        $methods = array_filter($methods, function($method) use ($reflection){
            return $reflection->hasMethod($method) && $reflection->getMethod($method)->isPublic();
        });

        $documents = $repository->findAll();

        $data[] = $fields;
        foreach($documents as &$document) {
            $values = [];
            foreach($methods as $method) {
                $values[] = $this->handleValue(call_user_func_array([$document, $method], []));
            }
            $data[] = $values;
            $this->dm->detach($document);
            unset($document);
        }

        $fp = fopen('php://output', 'w');
        foreach ($data as $row) {
            fputcsv($fp, $row);
        }
        fclose($fp);

        $response = new Response(ob_get_clean());
        $response->headers->set('Content-Type', 'application/vnd.ms-excel');
        $response->headers->set('Content-Disposition', 'attachment; filename="export_'.$repositoryName.'.csv"');
        return $response;
    }

    /**
     * @param string $shortcut
     * @return string|null
     */
    private function getClassNameByShortcut($shortcut)
    {
        $repositories =  [
            'tourists' => Tourist::class,
            'packages' => Package::class,
            'orders' => Order::class,
            'cash' => CashDocument::class,
        ];

        return array_key_exists($shortcut, $repositories) ? $repositories[$shortcut] : null;
    }

    private function handleValue($value)
    {
        if($value instanceof PersistentCollection) {
            return $value->count();
        } elseif($value instanceof \DateTime) {
            return $value->format('d.m.Y');
        } elseif(is_bool($value)) {
            return $value ? 'Да' : 'Нет';
        } elseif(is_object($value)) {
            return $value->__toString();
        } else
            return $value;
    }
}
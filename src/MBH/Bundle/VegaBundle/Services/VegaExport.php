<?php

namespace MBH\Bundle\VegaBundle\Services;


use MBH\Bundle\PackageBundle\Document\OrderDocument;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Document\Tourist;
use MBH\Bundle\PackageBundle\Document\Order;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Exception\ValidatorException;
use Symfony\Component\Validator\ValidatorBuilder;

/**
 * Class VegaExport
 * @package MBH\Bundle\VegaBundle\Services
 *
 * @author Aleksandr Arofikin <sasaharo@gmail.com>
 */
class VegaExport
{
    private $container;

    public function __construct(Container $container)
    {
       $this->container = $container;
    }

    /**
     * @param Tourist $tourist
     * @return bool
     */
    private function isValid(Tourist $tourist)
    {
        /** @var ValidatorBuilder $validatorBuilder */
        $validatorBuilder = $this->container->get('validator.builder');
        $validatorBuilder->disableAnnotationMapping()->addYamlMapping(__DIR__.'/../Resources/config/additional_validation.yml');
        $validator = $validatorBuilder->getValidator();
        /** @var ConstraintViolationListInterface $constraintViolationList */
        $constraintViolationList = $validator->validate($tourist);

        return $constraintViolationList->count() == 0;
    }

    /**
     * @todo may be created used new \XMLWriter()
     * @todo try catch \Twig_Error
     * @todo throw ValidationException
     * @param Tourist $tourist
     * @param Package $package
     * @param array|OrderDocument[] $touristDocuments
     * @return string
     */
    public function getXMLString(Tourist $tourist, Package $package , array $touristDocuments = [])
    {
        //@todo $this->isValid($tourist);
        $documentTypes = $this->container->getParameter('mbh.vega.document.types');
        $scanTypes = $this->container->getParameter('mbh.vega.document.scan.types');

        $xml = $this->container->get('twig')->render('MBHVegaBundle::vega_export.xml.twig', [
            'tourist' => $tourist,
            'package' => $package,
            'documents' => $touristDocuments,
            'documentTypes' => $documentTypes,
            'scanTypes' => $scanTypes
        ]);

        return $xml;
    }

    /**
     * @param Order $order
     * @param Tourist $tourist
     * @return OrderDocument[]
     */
    public function getDocumentsByOrderAndTourist(Order $order,Tourist $tourist)
    {
        $orderDocuments = [];
        $documents = $order->getDocuments();
        foreach($documents as $document) {
            if($document->getTourist() && $document->getTourist()->getId() == $tourist->getId()) {
                $orderDocuments[] = $document;
            }
        }

        return $orderDocuments;
    }


    /**
     * @param Package[] $packages
     * @return \SplFileInfo|void
     */
    public function exportToZip(array $packages)
    {
        $xmlList = [];
        $fileName = 'FrOrg'.date('Ymdhis');

        foreach($packages as $package) {
            $tourists = $package->getTourists();
            $tourists[] = $package->getMainTourist();
            foreach($tourists as $k =>$tourist) {
                $touristDocuments = $this->getDocumentsByOrderAndTourist($package->getOrder(), $tourist);
                $xml = $this->getXMLString($tourist, $package, $touristDocuments);
                $files = array_map(function($document){ return $document->getFile(); }, $touristDocuments);
                $xmlList[$fileName.($k > 0 ? '_('.$k.')' : '').'.xml'] = ['xml' => $xml, 'files' => $files];
            }
        }

        if(!$xmlList) {
            return;
        }

        $uploadedPath = $this->container->get('kernel')->getRootDir().'/../protectedUpload/vegaDocuments';

        $zip = new \ZipArchive();
        $zipName = 'FrOrg'.date('Ymdhis').'.zip';
        $fullZipName = $uploadedPath.DIRECTORY_SEPARATOR.$zipName;

        if(is_file($fullZipName)) {
            unlink($fullZipName);
        }

        if($zip->open($fullZipName, \ZipArchive::CREATE))
        {
            foreach($xmlList as $fileName => $data) {
                $zip->addFromString($fileName, $data['xml']);
                /** @var UploadedFile $file */
                foreach($data['files'] as $file) {
                    $fileName = $file->getRealPath();
                    $localName = 'images'.DIRECTORY_SEPARATOR.$file->getClientOriginalName();
                    $zip->addFile($fileName, $localName);
                }
            }
            $zip->close();

            return new \SplFileInfo($fullZipName);
        }
    }
}

<?php

namespace MBH\Bundle\ClientBundle\Service;

use MBH\Bundle\BaseBundle\Document\Base;
use MBH\Bundle\ClientBundle\Document\DocumentTemplate;
use MBH\Bundle\PackageBundle\Component\PackageServiceGroupByService;
use MBH\Bundle\PackageBundle\Document\Organization;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Document\PackageService;
use Psr\Container\ContainerInterface;
use MBH\Bundle\UserBundle\Document\User;

class TemplateFormatter
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Get document html from DocumentTemplate
     *
     * @param DocumentTemplate $documentTemplate
     * @return string
     */
    public function prepareHtml(DocumentTemplate $documentTemplate, Base $entity)
    {
        $content = $this->fillContent($documentTemplate->getContent(), $entity);

        $html = sprintf('<!DOCTYPE html>
            <html>
                <head>
                    <meta charset="UTF-8" />
                    <title>%2$s</title>
                </head>
                <body>
                    %1$s.
                </body>
            </html>', $content, $documentTemplate->getTitle());

        return $html;
    }

    /**
     * @param $html
     * @param Base $entity
     * @return mixed
     */
    private function fillContent($html,Base $entity)
    {
        $templateParams = new TemplateParams();
        $html = preg_replace_callback('{{{ ([A-Za-z]+) }}}', function($name) use ($entity, $templateParams) {
            $variableName = $name[1];
            $value = $templateParams->getValueByName($variableName, $entity);
            if(!$value)
                $value = '_______';//todo
            return $value;
        }, $html);

        return $html;
    }

    public function generateDocumentTemplate(DocumentTemplate $doc, Package $package, ?User $user)
    {
        $order = $package->getOrder();
        $hotel = $doc->getHotel() ? $doc->getHotel() : $package->getRoomType()->getHotel();
        $organization = $doc->getOrganization() ? $doc->getOrganization() : $hotel->getOrganization();
        if ($organization === null) {
            $organization = new Organization();
        }
        $params = [
            'package'              => $this->container->get('MBH\Bundle\ClientBundle\Service\DocumentSerialize\Package')->newInstance($package),
            'order'                => $this->container->get('MBH\Bundle\ClientBundle\Service\DocumentSerialize\Order')->newInstance($order),
            'hotel'                => $this->container->get('MBH\Bundle\ClientBundle\Service\DocumentSerialize\Hotel')->newInstance($hotel),
            'payer'                => $this->container->get('MBH\Bundle\ClientBundle\Service\DocumentSerialize\Helper')->payerInstance($order->getPayer()),
            'organization'         => $this->container->get('MBH\Bundle\ClientBundle\Service\DocumentSerialize\HotelOrganization')->newInstance($organization),
            'user'                 => $this->container->get('MBH\Bundle\ClientBundle\Service\DocumentSerialize\User')->newInstance($user),
            'arrivalTimeDefault'   => $hotel->getPackageArrivalTime(),
            'departureTimeDefault' => $hotel->getPackageDepartureTime(),
            'documentTypes'        => $this->container->get('mbh.fms_dictionaries')->getDocumentTypes(),
            'currentDate'          => (new \DateTime())->format('d.m.Y'),
            'total'                => $package->getOrder()->getPrice(),
        ];

        $params = $this->addCalculatedParams($params, $package);

        $twig = $this->container->get('twig');
        $renderedTemplate = $twig->createTemplate($doc->getContent())->render($params);

        return $this->container->get('knp_snappy.pdf')
            ->getOutputFromHtml(
                $renderedTemplate, [
                                     'orientation' => $doc->getOrientation(),
                                 ]
            );
    }


    /**
     * @param array $params
     * @param Package $package
     * @return array
     * @throws \Exception
     */
    private function addCalculatedParams(array $params, Package $package)
    {
        /** @var PackageService[] $packageServices */
        $packageServices = [];

        /** @var PackageServiceGroupByService[] $packageServicesByType */
        $packageServicesByType = [];

        $packages = $package->getOrder()->getPackages();

        /** @var Package $package */
        foreach($packages as $package) {
            $packageServices = array_merge(iterator_to_array($package->getServices()), $packageServices);
        }

        foreach($packageServices as $ps) {
            $service = $ps->getService();
            $groupBy = $ps->getPrice().$service->getId();
            if(!array_key_exists($groupBy, $packageServicesByType)) {
                $packageServicesByType[$groupBy] = new PackageServiceGroupByService($service, $ps->getPrice());
            }
            $packageServicesByType[$groupBy]->add($ps);
        }

        return $params + [
                'packageServicesByType' => $packageServicesByType
            ];
    }
}
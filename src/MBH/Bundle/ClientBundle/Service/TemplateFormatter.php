<?php

namespace MBH\Bundle\ClientBundle\Service;

use MBH\Bundle\BaseBundle\Document\Base;
use MBH\Bundle\ClientBundle\Document\DocumentTemplate;
use MBH\Bundle\ClientBundle\Service\Document\HotelSerialize;
use MBH\Bundle\ClientBundle\Service\Document\OrderSerialize;
use MBH\Bundle\ClientBundle\Service\Document\Payer;
use MBH\Bundle\PackageBundle\Component\PackageServiceGroupByService;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Document\PackageService;
use MBH\Bundle\UserBundle\Document\User;
use Psr\Container\ContainerInterface;

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
        $params = [
            'package' => $package,
            'order' => new OrderSerialize($order),
            'hotel' => new HotelSerialize($hotel),
            'payer' => Payer::instance($order->getPayer()),
            'organization' => $organization,
            'user' => $user,
            'arrivalTimeDefault' => $hotel->getPackageArrivalTime(),
            'departureTimeDefault' => $hotel->getPackageDepartureTime(),
            'documentTypes' => $this->container->get('mbh.fms_dictionaries')->getDocumentTypes()
        ];

        $params = $this->addCalculatedParams($params, $package);


////        dump($params);
//        exit;
        $twig = $this->container->get('twig');
        $renderedTemplate = $twig->createTemplate($doc->getContent())->render($params);

        return  $this->container->get('knp_snappy.pdf')
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

        $total = 0;
        $packages = $package->getOrder()->getPackages();

        /** @var Package $package */
        foreach($packages as $package) {
            $packageServices = array_merge(iterator_to_array($package->getServices()), $packageServices);
            $total += $package->getPackagePrice(true);
        }

        foreach($packageServices as $ps) {
            $service = $ps->getService();
            $groupBy = $ps->getPrice().$service->getId();
            if(!array_key_exists($groupBy, $packageServicesByType)) {
                $packageServicesByType[$groupBy] = new PackageServiceGroupByService($service, $ps->getPrice());
            }
            $packageServicesByType[$groupBy]->add($ps);
            $total += $ps->getTotal();
        }

        return $params + [
                'total' => $total,
                'packageServicesByType' => $packageServicesByType
            ];
    }
}
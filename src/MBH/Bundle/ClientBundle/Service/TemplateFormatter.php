<?php

namespace MBH\Bundle\ClientBundle\Service;

use Liip\ImagineBundle\Templating\ImagineExtension;
use MBH\Bundle\BaseBundle\Document\Base;
use MBH\Bundle\ClientBundle\Document\DocumentTemplate;
use MBH\Bundle\PackageBundle\Component\PackageServiceGroupByService;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Document\PackageService;
use MBH\Bundle\UserBundle\Document\User;
use Psr\Container\ContainerInterface;
use Symfony\Bridge\Twig\Extension\AssetExtension;
use Symfony\Bridge\Twig\Extension\HttpFoundationExtension;
use Symfony\Bridge\Twig\Extension\TranslationExtension;
use Vich\UploaderBundle\Twig\Extension\UploaderExtension;

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
        $loader = new \Twig_Loader_Array(['template' => $doc->getContent()]);
        $env = new \Twig_Environment($loader);
        $env->addExtension($this->container->get('mbh.twig.extension'));
        $env->addExtension(new TranslationExtension($this->container->get('translator')));
        $env->addExtension(new AssetExtension($this->container->get('assets.packages')));
        $env->addExtension(new HttpFoundationExtension($this->container->get('request_stack')));
        $env->addExtension(new ImagineExtension($this->container->get('liip_imagine.cache.manager')));
        $env->addExtension(new UploaderExtension($this->container->get('vich_uploader.templating.helper.uploader_helper')));

        $order = $package->getOrder();
        $hotel = $doc->getHotel() ? $doc->getHotel() : $package->getRoomType()->getHotel();
        $organization = $doc->getOrganization() ? $doc->getOrganization() : $hotel->getOrganization();
        $params = [
            'package' => $package,
            'order' => $order,
            'hotel' => $hotel,
            'payer' => $order->getPayer(),
            'organization' => $organization,
            'user' => $user,
            'arrivalTimeDefault' => $hotel->getPackageArrivalTime(),
            'departureTimeDefault' => $hotel->getPackageDepartureTime()
        ];

        $params = $this->addCalculatedParams($params, $package);
        $renderedTemplate = $env->render('template', $params);

        return  $this->container->get('knp_snappy.pdf')->getOutputFromHtml($renderedTemplate);
    }

    /**
     * @param $params
     * @param Package $package
     * @return array
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
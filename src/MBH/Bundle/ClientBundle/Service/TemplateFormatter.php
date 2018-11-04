<?php

namespace MBH\Bundle\ClientBundle\Service;

use MBH\Bundle\BaseBundle\Document\Base;
use MBH\Bundle\ClientBundle\Document\DocumentTemplate;
use MBH\Bundle\ClientBundle\Service\DocumentSerialize\AdvancedAddressInterface;
use MBH\Bundle\ClientBundle\Service\DocumentSerialize\HotelOrganization;
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
        $hotel = $package->getRoomType()->getHotel() ?? $doc->getHotel();
        $organization = $doc->getOrganization() ?? $hotel->getOrganization() ?? new Organization();

        $params = [
            'package'              => $this->container->get('MBH\Bundle\ClientBundle\Service\DocumentSerialize\Package')->newInstance($package),
            'order'                => $this->container->get('MBH\Bundle\ClientBundle\Service\DocumentSerialize\Order')->newInstance($order),
            'hotel'                => $this->container->get('MBH\Bundle\ClientBundle\Service\DocumentSerialize\Hotel')->newInstance($hotel),
            'payer'                => $this->container->get('MBH\Bundle\ClientBundle\Service\DocumentSerialize\Helper')->payerInstance($order->getPayer()),
            'organization'         => $this->container->get('MBH\Bundle\ClientBundle\Service\DocumentSerialize\HotelOrganization')->newInstance($organization),
            'user'                 => !is_null($user) ? $this->container->get('MBH\Bundle\ClientBundle\Service\DocumentSerialize\User')->newInstance($user) : null,
            'arrivalTimeDefault'   => $hotel->getPackageArrivalTime(),
            'departureTimeDefault' => $hotel->getPackageDepartureTime(),
            'documentTypes'        => $this->container->get('mbh.fms_dictionaries')->getDocumentTypes(),
            'currentDate'          => (new \DateTime())->format('d.m.Y'),
            'total'                => $package->getOrder()->getPrice(),
        ];

        $params = $this->addCalculatedParams($params, $package);

        $this->addDataExecutor($params);
        $this->addDataCustomer($params);

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
     */
    private function addDataCustomer(array &$params): void
    {
        $payer = $params['payer'];
        $format = '<strong>%s</strong>';
        $name = $this->translate('package.pdf.not_specified');
        if (!empty($payer)) {
            $name = $payer->getShortName();
            if ($payer instanceof \MBH\Bundle\ClientBundle\Service\DocumentSerialize\Organization) {
                $format .= '. ' . $this->translate('package.pdf.inn', $payer->getInn(), ': ');
            }
        }

        $params['dataCustomer'] = sprintf($format, $name);
    }

    /**
     * @param array $params
     */
    private function addDataExecutor(array &$params): void
    {
        $dataExecutor = [];

        $hotel = $params['hotel'];

        $isAddressHotel = false;
        $isDataOrganization = $params['package']->getAddress() instanceof Organization;

        if ($isDataOrganization) {
            $entity =
                $this
                    ->container
                    ->get('MBH\Bundle\ClientBundle\Service\DocumentSerialize\HotelOrganization')
                    ->newInstance($params['hotel']->getOrganization());
        } else {
            $entity = $hotel;
        }

        $format = '<strong>%s</strong>' . ($isDataOrganization ? ' (%s)': '');
        $dataExecutor[] = sprintf($format, $entity->getName(), $hotel->getName());


        $addressStr = $this->composeAddress($entity);

        if (empty($addressStr) && $isDataOrganization) {
            $isAddressHotel = true;
            $addressStr = $this->composeAddress($params['hotel']);
        }

        if (!empty($addressStr)) {
            $idTranslate = 'package.pdf.address';
            if ($isDataOrganization && $isAddressHotel) {
                $idTranslate = 'package.pdf.address_hotel';
            }
            $dataExecutor[] = $this->translate($idTranslate, $addressStr, ': ');
        }

        // inn
        $dataExecutor[] = $isDataOrganization
            ? $this->translate('package.pdf.inn', $entity->getInn(), ': ')
            : '';

        if ($isDataOrganization && !empty($entity->getBank())) {
            $dataExecutor[] = '<br>'. $this->addBankDataExecutor($entity);
        }

        $params['dataExecutor'] = implode('. ', $dataExecutor);
    }

    /**
     * @param HotelOrganization $organization
     * @return string
     */
    private function addBankDataExecutor(HotelOrganization $organization): string
    {
        $bank = sprintf(
            '<strong>%s</strong>: %s',
            $this->translate('package.pdf.bank.name'),
            $organization->getBank()
        );

        if (!empty($organization->getBankAddress())) {
            $bank .= sprintf(
                ' %s %s' ,
                $this->translate('package.pdf.bank.address'),
                $organization->getBankAddress()
            );
        }

        $separator = ': ';
        $data = [];
        $data[] = $bank;
        $data[] = $this->translate('package.pdf.bank.bik', $organization->getBankBik(), $separator);
        $data[] = $this->translate('package.pdf.bank.correspondent_account', $organization->getCorrespondentAccount(), $separator);
        $data[] = $this->translate('package.pdf.bank.checking_account', $organization->getCheckingAccount(), $separator);

        return implode(', ', array_filter($data));
    }

    /**
     * @param AdvancedAddressInterface $entity
     * @return string
     */
    private function composeAddress(AdvancedAddressInterface $entity): string
    {
        if (empty($entity->getStreet())) {
            return '';
        }

        $str = [];

        $str[] = $entity->getCountry();
        $str[] = $entity->getRegion();
        $str[] = $entity->getSellement();
        $str[] = $this->translate('package.pdf.short_city_extend',$entity->getCity());
        $str[] = $this->translate('package.pdf.short_street_extend', $entity->getStreet());
        $str[] = $this->translate('package.pdf.short_home_extend', $entity->getHouse());
        $str[] = $this->translate('package.pdf.short_housing_extend', $entity->getCorpus());
        $str[] = $this->translate('package.pdf.short_apartment_extend', $entity->getFlat());

        return implode(', ', array_filter($str));
    }

    /**
     * @param string $id
     * @param null|string $msg
     * @param string $separator
     * @return string
     */
    private function translate(string $id, ?string $msg = null, string $separator = '. '): string
    {
        /** TODO подумать */
        if ($msg !== null && empty($msg)) {
            return '';
        }

        $translate = $this->container->get('translator');
        $bundle = "MBHPackageBundle";

        return $msg === null
            ? $translate->trans($id, [], $bundle)
            : $translate->trans($id, [], $bundle) . $separator . $msg;
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
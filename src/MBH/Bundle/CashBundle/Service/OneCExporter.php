<?php

namespace MBH\Bundle\CashBundle\Service;


use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\CashBundle\Document\CashDocumentQueryCriteria;
use MBH\Bundle\PackageBundle\Document\Organization;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Translation\DataCollectorTranslator;

/**
 * Class OneCExporter

 */
class OneCExporter
{
    /**
     * @var ContainerInterface
     */
    private $container;

    private $translator;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->translator = $this->container->get('translator');
    }

    /**
     * @return Organization
     */
    protected function createConfigOrganization()
    {
        $data = $this->container->getParameter('mbh.payer_organization');
        //create Hydrator
        $organization = new Organization();
        $organization->setName($data['name']);
        $organization->setCheckingAccount($data['checking_account']);
        $organization->setInn($data['inn']);
        $organization->setKpp($data['kpp']);
        $organization->setBank($data['bank']);
        $organization->setBankBik($data['bank_bik']);
        $organization->setCorrespondentAccount($data['correspondent_account']);
        $organization->setAccountantFio($data['accountant_fio']);
        return $organization;
    }

    /**
     * @param CashDocument[] $cashDocuments
     * @param CashDocumentQueryCriteria $queryCriteria
     * @param Organization $hotelOrganization
     * @return string
     */
    public function export($cashDocuments, CashDocumentQueryCriteria $queryCriteria, Organization $hotelOrganization = null)
    {
        $text = '';
        $configOrganizationPayer = $this->createConfigOrganization();

        $total = 0;

        foreach ($cashDocuments as $cashDocument) {
            if ($cashDocument->getMethod() == CashDocument::METHOD_CASHLESS) {
                $organizationPayer = $configOrganizationPayer;
            }elseif ($cashDocument->getOrganizationPayer()) {
                $organizationPayer = $cashDocument->getOrganizationPayer();
            } else {
                $organizationPayer =  new Organization();
            }

            $hotelOrganization = $cashDocument->getHotel() !== null ? $cashDocument->getHotel()->getOrganization() : null;
            if (!$hotelOrganization) {
                $hotelOrganization = new Organization();
            }

            $text .= sprintf($this->getContent($cashDocument,$hotelOrganization, $organizationPayer));

            $total += $cashDocument->getTotal();
        }

        return sprintf($this->getFullDocument($text, $total,$queryCriteria, $hotelOrganization));
    }

    private function getFullDocument(
        string $body,
        int $total,
        CashDocumentQueryCriteria $queryCriteria,
        Organization $hotelOrganization = null
    ): string
    {
        $dateEnd = (clone $queryCriteria->end)->modify('-1 day')->format('d.m.Y');

        $format[] = '1CClientBankExchange';
        $format[] = $this->trans('versiyaformata') . '=1.02';
        $format[] = $this->trans('kodirovka') .'=Windows';
        $format[] = $this->trans('otpravitel') . '=';
        $format[] = $this->trans('poluchatel') . '=';
        $format[] = $this->trans('datasozdaniya') . '=' . date('d.m.Y');
        $format[] = $this->trans('vremyasozdaniya') . '=' . date('H:i:s');
        $format[] = $this->trans('datanachala') . '=' . $queryCriteria->begin->format('d.m.Y');
        $format[] = $this->trans('datakontsa') . '=' . $dateEnd;
        $format[] = $this->trans('raschschet') . '=' . ($hotelOrganization ? $hotelOrganization->getCheckingAccount() : '');
        $format[] = $this->trans('sektsiyaraschschet');
        $format[] = $this->trans('datanachala') . '=' . $queryCriteria->begin->format('d.m.Y');
        $format[] = $this->trans('datakontsa') . '=' . $dateEnd;
        $format[] = $this->trans('nachalnyyostatok') . '=0';
        $format[] = $this->trans('raschschet') . '=' . ($hotelOrganization ? $hotelOrganization->getCheckingAccount() : '');
        $format[] = $this->trans('vsegospisano') . '=0';
        $format[] = $this->trans('vsegopostupilo') . '=' . number_format($total, 2, '.', '');
        $format[] = $this->trans('konechnyyostatok') . '=0';
        $format[] = $this->trans('konetsraschschet');
        $format[] = $body . $this->trans('konetsfayla');

        return implode("\n", $format);
    }

    private function getContent(
        CashDocument $cashDocument,
        Organization $hotelOrganization,
        Organization $organizationPayer
    ): string
    {

        $format = [];
        $format[] = $this->trans('sektsiyadokument') . '=' . $this->trans('platezhnoe.poruchenie');
        $format[] = $this->trans('nomer').'=' . $cashDocument->getNumber();
        $format[] = $this->trans('data').'='. $cashDocument->getCreatedAt()->format('d.m.Y');
        $format[] = $this->trans('summa') . '=' . number_format($cashDocument->getTotal(), 2, '.', '');
        $format[] = $this->trans('platelshchikschet') . '=' . $organizationPayer->getCheckingAccount();
        $format[] = $this->trans('dataspisano') . '=' . ($cashDocument->getIsPaid() ? $cashDocument->getPaidDate()->format('d.m.Y') : '');
        $format[] = $this->trans('platelshchik') . '=' . $organizationPayer->getName();
        $format[] = $this->trans('platelshchikinn') . '=' . $organizationPayer->getInn();
        $format[] = $this->trans('platelshchikkpp') . '=' . $organizationPayer->getKpp();
        $format[] = $this->trans('platelshchikraschschet') . '=' . $organizationPayer->getCheckingAccount();
        $format[] = $this->trans('form.cashDocumentType.platelshikBank1', false) . '=' . $organizationPayer->getBank();
        $format[] = $this->trans('platelshchikbik') . '=' . $organizationPayer->getBankBik();
        $format[] = $this->trans('platelshchikkorschet') . '=' . $organizationPayer->getCorrespondentAccount();
        $format[] = $this->trans('poluchatelʹschet') . '=' . $hotelOrganization->getCheckingAccount();
        $format[] = $this->trans('datapostupilo') . '=' . $cashDocument->getPaidDate()->format('d.m.Y');
        $format[] = $this->trans('poluchatel') . '=' . $hotelOrganization->getName();
        $format[] = $this->trans('poluchatelinn') . '=' . $hotelOrganization->getInn();
        $format[] = $this->trans('poluchatelkpp') . '=' . $hotelOrganization->getKpp();
        $format[] = $this->trans('poluchatelraschschet') . '=' . $hotelOrganization->getCheckingAccount();
        $format[] = $this->trans('poluchatelbank1') . '=' . $hotelOrganization->getBank();
        $format[] = $this->trans('poluchatelbik') . '=' . $hotelOrganization->getBankBik();
        $format[] = $this->trans('poluchatelkorschet') . '=' . $hotelOrganization->getCorrespondentAccount();
        $format[] = $this->trans('vidplatezha') . '=' . $this->trans($this->container->getParameter('mbh.cash.methods')[$cashDocument->getMethod()], false);
        $format[] = $this->trans('vidoplaty') . '=01';
        $format[] = $this->trans('kod') . '=';
        $format[] = $this->trans('statussostavitelya') . '=';
        $format[] = $this->trans('pokazatelkbk') . '=';
        $format[] = $this->trans('okato') . '=';
        $format[] = $this->trans('pokazatelosnovaniya') . '=';
        $format[] = $this->trans('pokazatelperioda') . '=';
        $format[] = $this->trans('pokazatelnomera') . '=';
        $format[] = $this->trans('pokazateldaty') . '=';
        $format[] = $this->trans('pokazateltipa') . '=';
        $format[] = $this->trans('ocherednost') . '=5';

        $target = $this->trans('schet') . '#' . $cashDocument->getNumber().' ';
        $target .= $this->trans('ot') . ' '.$cashDocument->getCreatedAt()->format('d.m.Y').'; ';
        $target .= $this->trans('fio') . ': "'.($cashDocument->getTouristPayer() ? $cashDocument->getTouristPayer()->getFullName() : $organizationPayer->getAccountantFio()).'"; ';
        $target .= $this->trans('adres') . ': '.($cashDocument->getTouristPayer() ? $cashDocument->getTouristPayer()->getAddress() : '').' ; ';
        $target .= $this->trans('contact') . ': '.($cashDocument->getTouristPayer() ? $cashDocument->getTouristPayer()->getPhone() : '').';';

        $format[] = $this->trans('naznacheniyeplatezha') . '=' . $target;
        $format[] = $this->trans('konetsdokumenta');

        return implode("\n", $format);
    }

    private function trans(string $name, bool $prefix = true): string
    {
        $id = $prefix ? 'mbhcashbundle.service.onecexporter.' . $name : $name;

        return $this->translator->trans($id);
    }
}
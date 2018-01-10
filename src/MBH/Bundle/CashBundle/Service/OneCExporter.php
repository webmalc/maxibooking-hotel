<?php

namespace MBH\Bundle\CashBundle\Service;


use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\CashBundle\Document\CashDocumentQueryCriteria;
use MBH\Bundle\PackageBundle\Document\Organization;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class OneCExporter

 */
class OneCExporter
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
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

        foreach ($cashDocuments as $cashDocument) {
            if ($cashDocument->getMethod() == 'cashless') {
                $organizationPayer = $configOrganizationPayer;
            }elseif ($cashDocument->getOrganizationPayer()) {
                $organizationPayer = $cashDocument->getOrganizationPayer();
            } else {
                $organizationPayer =  new Organization();
            }

            $hotelOrganization = $cashDocument->getHotel()->getOrganization();
            if (!$hotelOrganization) {
                $hotelOrganization = new Organization();
            }

            $text .= sprintf($this->container->get('translator')->trans('mbhcashbundle.service.onecexporter.sektsiyadokument') . '=' . $this->container->get('translator')->trans('mbhcashbundle.service.onecexporter.platezhnoe.poruchenie') . '
' . $this->container->get('translator')->trans('mbhcashbundle.service.onecexporter.nomer').'=' . $cashDocument->getNumber() . '
' . $this->container->get('translator')->trans('mbhcashbundle.service.onecexporter.data').'='. $cashDocument->getCreatedAt()->format('d.m.Y') . '
' . $this->container->get('translator')->trans('mbhcashbundle.service.onecexporter.summa') . '=' . number_format($cashDocument->getTotal(), 2, '.', '') . '
' . $this->container->get('translator')->trans('mbhcashbundle.service.onecexporter.platelshchikschet') . '=' . $organizationPayer->getCheckingAccount() . '
' . $this->container->get('translator')->trans('mbhcashbundle.service.onecexporter.dataspisano') . '=' . ($cashDocument->getIsPaid() ? $cashDocument->getPaidDate()->format('d.m.Y') : '') . '
' . $this->container->get('translator')->trans('mbhcashbundle.service.onecexporter.platelshchik') . '=' . $organizationPayer->getName() . '
' . $this->container->get('translator')->trans('mbhcashbundle.service.onecexporter.platelshchikinn') . '=' . $organizationPayer->getInn() . '
' . $this->container->get('translator')->trans('mbhcashbundle.service.onecexporter.platelshchikkpp') , '=' . $organizationPayer->getKpp() . '
' . $this->container->get('translator')->trans('mbhcashbundle.service.onecexporter.platelshchikraschschet') . '=' . $organizationPayer->getCheckingAccount() . '
' . $this->container->get('translator')->trans('form.cashDocumentType.platelshikBank1') . '=' . $organizationPayer->getBank() . '
' . $this->container->get('translator')->trans('mbhcashbundle.service.onecexporter.platelshchikbik') . '=' . $organizationPayer->getBankBik() . '
' . $this->container->get('translator')->trans('mbhcashbundle.service.onecexporter.platelshchikkorschet') . '=' . $organizationPayer->getCorrespondentAccount() . '
' . $this->container->get('translator')->trans('mbhcashbundle.service.onecexporter.poluchatelʹschet') . '=' . $hotelOrganization->getCheckingAccount() . '
' . $this->container->get('translator')->trans('mbhcashbundle.service.onecexporter.datapostupilo') . '=' . $cashDocument->getPaidDate()->format('d.m.Y') . '
' . $this->container->get('translator')->trans('mbhcashbundle.service.onecexporter.poluchatel') . '=' . $hotelOrganization->getName() . '
' . $this->container->get('translator')->trans('mbhcashbundle.service.onecexporter.poluchatelinn') . '=' . $hotelOrganization->getInn() . '
' . $this->container->get('translator')->trans('mbhcashbundle.service.onecexporter.poluchatelkpp') . '=' . $hotelOrganization->getKpp() . '
' . $this->container->get('translator')->trans('mbhcashbundle.service.onecexporter.poluchatelraschschet') . '=' . $hotelOrganization->getCheckingAccount() . '
' . $this->container->get('translator')->trans('mbhcashbundle.service.onecexporter.poluchatelbank1') . '=' . $hotelOrganization->getBank() . '
' . $this->container->get('translator')->trans('mbhcashbundle.service.onecexporter.poluchatelbik') . '=' . $hotelOrganization->getBankBik() . '
' . $this->container->get('translator')->trans('mbhcashbundle.service.onecexporter.poluchatelkorschet') . '=' . $hotelOrganization->getCorrespondentAccount() . '
' . $this->container->get('translator')->trans('mbhcashbundle.service.onecexporter.vidplatezha') . '=' . $this->container->get('translator')->trans($this->container->getParameter('mbh.cash.methods')[$cashDocument->getMethod()]) . '
' . $this->container->get('translator')->trans('mbhcashbundle.service.onecexporter.vidoplaty') . '=01
' . $this->container->get('translator')->trans('mbhcashbundle.service.onecexporter.kod') . '=
' . $this->container->get('translator')->trans('mbhcashbundle.service.onecexporter.statussostavitelya') . '=
' . $this->container->get('translator')->trans('mbhcashbundle.service.onecexporter.pokazatelkbk') . '=
' . $this->container->get('translator')->trans('mbhcashbundle.service.onecexporter.okato') . '=
' . $this->container->get('translator')->trans('mbhcashbundle.service.onecexporter.pokazatelosnovaniya') . '=
' . $this->container->get('translator')->trans('mbhcashbundle.service.onecexporter.pokazatelperioda') . '=
' . $this->container->get('translator')->trans('mbhcashbundle.service.onecexporter.pokazatelnomera') . '=
' . $this->container->get('translator')->trans('mbhcashbundle.service.onecexporter.pokazateldaty') . '=
' . $this->container->get('translator')->trans('mbhcashbundle.service.onecexporter.pokazateltipa') . '=
' . $this->container->get('translator')->trans('mbhcashbundle.service.onecexporter.ocherednost') . '=5
' . $this->container->get('translator')->trans('mbhcashbundle.service.onecexporter.naznacheniyeplatezha') . '=' .
                $this->container->get('translator')->trans('mbhcashbundle.service.onecexporter.schet') . '#' . $cashDocument->getNumber().' ' .
                $this->container->get('translator')->trans('mbhcashbundle.service.onecexporter.ot') . ' '.$cashDocument->getCreatedAt()->format('d.m.Y').'; ' .
                $this->container->get('translator')->trans('mbhcashbundle.service.onecexporter.fio') . ': "'.($cashDocument->getTouristPayer() ? $cashDocument->getTouristPayer()->getFullName() : $organizationPayer->getAccountantFio()).'"; ' .
                $this->container->get('translator')->trans('mbhcashbundle.service.onecexporter.adres') . ': '.($cashDocument->getTouristPayer() ? $cashDocument->getTouristPayer()->getAddress() : '').' ; ' .
                $this->container->get('translator')->trans('mbhcashbundle.service.onecexporter.contact') . ': '.($cashDocument->getTouristPayer() ? $cashDocument->getTouristPayer()->getPhone() : '').';
' . $this->container->get('translator')->trans('mbhcashbundle.service.onecexporter.konetsdokumenta')
            );
        }

        $total = 0.00;
        foreach ($cashDocuments as $cashDocument) {
            $total += $cashDocument->getTotal();
        }

        $result = sprintf(
            '1CClientBankExchange
' . $this->container->get('translator')->trans('mbhcashbundle.service.onecexporter.versiyaformata') . '=1.02
' . $this->container->get('translator')->trans('mbhcashbundle.service.onecexporter.kodirovka') .'=Windows
' . $this->container->get('translator')->trans('mbhcashbundle.service.onecexporter.otpravitel') . '=
' . $this->container->get('translator')->trans('mbhcashbundle.service.onecexporter.poluchatel') . '=
' . $this->container->get('translator')->trans('mbhcashbundle.service.onecexporter.datasozdaniya') . '=' . date('d.m.Y') . '
' . $this->container->get('translator')->trans('mbhcashbundle.service.onecexporter.vremyasozdaniya') . '=' . date('H:i:s') . '
' . $this->container->get('translator')->trans('mbhcashbundle.service.onecexporter.datanachala') . '=' . $queryCriteria->begin->format('d.m.Y') . '
' . $this->container->get('translator')->trans('mbhcashbundle.service.onecexporter.datakontsa') . '=' . $queryCriteria->end->format('d.m.Y') . '
' . $this->container->get('translator')->trans('mbhcashbundle.service.onecexporter.raschschet') . '=' . ($hotelOrganization ? $hotelOrganization->getCheckingAccount() : '') . '
' . $this->container->get('translator')->trans('mbhcashbundle.service.onecexporter.sektsiyaraschschet') . '
' . $this->container->get('translator')->trans('mbhcashbundle.service.onecexporter.datanachala') . '=' . $queryCriteria->begin->format('d.m.Y') . '
' . $this->container->get('translator')->trans('mbhcashbundle.service.onecexporter.datakontsa') . '=' . $queryCriteria->end->format('d.m.Y') . '
' . $this->container->get('translator')->trans('mbhcashbundle.service.onecexporter.nachalnyyostatok') . '=0
' . $this->container->get('translator')->trans('mbhcashbundle.service.onecexporter.raschschet') . '=' . ($hotelOrganization ? $hotelOrganization->getCheckingAccount() : '') . '
' . $this->container->get('translator')->trans('mbhcashbundle.service.onecexporter.vsegospisano') . '=0
' . $this->container->get('translator')->trans('mbhcashbundle.service.onecexporter.vsegopostupilo') . '=' . number_format($total, 2, '.', '') . '
' . $this->container->get('translator')->trans('mbhcashbundle.service.onecexporter.konechnyyostatok') . '=0
' . $this->container->get('translator')->trans('mbhcashbundle.service.onecexporter.konetsraschschet') . '
' . $text . $this->container->get('translator')->trans('mbhcashbundle.service.onecexporter.konetsfayla'));

        return $result;
    }
}
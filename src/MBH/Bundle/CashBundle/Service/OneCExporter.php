<?php

namespace MBH\Bundle\CashBundle\Service;


use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\CashBundle\Document\CashDocumentQueryCriteria;
use MBH\Bundle\PackageBundle\Document\Organization;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class OneCExporter
 * @author Aleksandr Arofikin <sasaharo@gmail.com>
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

            $text .= sprintf('СекцияДокумент=Платежное поручение
Номер=' . $cashDocument->getNumber() . '
Дата=' . $cashDocument->getCreatedAt()->format('d.m.Y') . '
Сумма=' . number_format($cashDocument->getTotal(), 2, '.', '') . '
ПлательщикСчет=' . $organizationPayer->getCheckingAccount() . '
ДатаСписано=' . ($cashDocument->getIsPaid() ? $cashDocument->getPaidDate()->format('d.m.Y') : '') . '
Плательщик=' . $organizationPayer->getName() . //ЗАПАДНО-УРАЛЬСКИЙ БАНК ОАО "СБЕРБАНК РОССИИ"//ЗЫРЯНОВА ЕЛЕНА СЕРГЕЕВНА//26859356266//614000 ПЕРМЬ МЕХАНОШИНА д.10 кв.44//
                '
ПлательщикИНН=' . $organizationPayer->getInn() . '
ПлательщикКПП=' . $organizationPayer->getKpp() . '
ПлательщикРасчСчет=' . $organizationPayer->getCheckingAccount() . '
ПлательщикБанк1=' . $organizationPayer->getBank() . '
ПлательщикБИК=' . $organizationPayer->getBankBik() . '
ПлательщикКорсчет=' . $organizationPayer->getCorrespondentAccount() . '
ПолучательСчет=' . $hotelOrganization->getCheckingAccount() . '
ДатаПоступило=' . $cashDocument->getPaidDate()->format('d.m.Y') . '
Получатель=' . $hotelOrganization->getName() . '
ПолучательИНН=' . $hotelOrganization->getInn() . '
ПолучательКПП=' . $hotelOrganization->getKpp() . '
ПолучательРасчСчет=' . $hotelOrganization->getCheckingAccount() . '
ПолучательБанк1=' . $hotelOrganization->getBank() . '
ПолучательБИК=' . $hotelOrganization->getBankBik() . '
ПолучательКорсчет=' . $hotelOrganization->getCorrespondentAccount() . '
ВидПлатежа=' . $this->container->getParameter('mbh.cash.methods')[$cashDocument->getMethod()] . '
ВидОплаты=01
Код=
СтатусСоставителя=
ПоказательКБК=
ОКАТО=
ПоказательОснования=
ПоказательПериода=
ПоказательНомера=
ПоказательДаты=
ПоказательТипа=
Очередность=5
НазначениеПлатежа=Заказ #'.$cashDocument->getNumber().' от '.$cashDocument->getCreatedAt()->format('d.m.Y').'; ФИО: "'.($cashDocument->getTouristPayer() ? $cashDocument->getTouristPayer()->getFullName() : $organizationPayer->getAccountantFio()).'"; АДРЕС: '.($cashDocument->getTouristPayer() ? $cashDocument->getTouristPayer()->getAddress() : '').' ; КОНТАКТ: '.($cashDocument->getTouristPayer() ? $cashDocument->getTouristPayer()->getPhone() : '').';
КонецДокумента
');
        }

        $total = 0.00;
        foreach ($cashDocuments as $cashDocument) {
            $total += $cashDocument->getTotal();
        }

        $result = sprintf(
            '1CClientBankExchange
ВерсияФормата=1.02
Кодировка=Windows
Отправитель=
Получатель=
ДатаСоздания=' . date('d.m.Y') . '
ВремяСоздания=' . date('H:i:s') . '
ДатаНачала=' . $queryCriteria->begin->format('d.m.Y') . '
ДатаКонца=' . $queryCriteria->end->format('d.m.Y') . '
РасчСчет=' . ($hotelOrganization ? $hotelOrganization->getCheckingAccount() : '') . '
СекцияРасчСчет
ДатаНачала=' . $queryCriteria->begin->format('d.m.Y') . '
ДатаКонца=' . $queryCriteria->end->format('d.m.Y') . '
НачальныйОстаток=0
РасчСчет=' . ($hotelOrganization ? $hotelOrganization->getCheckingAccount() : '') . '
ВсегоСписано=0
ВсегоПоступило=' . number_format($total, 2, '.', '') . '
КонечныйОстаток=0
КонецРасчСчет
' . $text . 'КонецФайла');

        return $result;
    }
}
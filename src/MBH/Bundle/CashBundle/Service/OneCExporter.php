<?php

namespace MBH\Bundle\CashBundle\Service;


use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\CashBundle\Document\CashDocumentQueryCriteria;
use MBH\Bundle\PackageBundle\Document\Organization;

class OneCExporter
{
    /**
     * @param CashDocument[] $cashDocuments
     * @param CashDocumentQueryCriteria $queryCriteria
     * @param Organization $hotelOrganization
     * @return string
     */
    public static function export($cashDocuments, CashDocumentQueryCriteria $queryCriteria, Organization $hotelOrganization = null)
    {
        $text = '';

        foreach ($cashDocuments as $cashDocument) {
            $organizationPayer = $cashDocument->getOrganizationPayer() ? $cashDocument->getOrganizationPayer() : new Organization();
            $hotelOrganization = $cashDocument->getHotel()->getOrganization();

            $text .= sprintf('СекцияДокумент=Платежное поручение
Номер=' . $cashDocument->getNumber() . '
Дата=' . $cashDocument->getCreatedAt()->format('d.m.Y') . '
Сумма=' . $cashDocument->getTotal() . '
ПлательщикСчет=' . $organizationPayer->getCheckingAccount() . '
ДатаСписано=' . ($cashDocument->getIsPaid() ? $cashDocument->getPaidDate()->format('d.m.Y') : '') . '
Плательщик=' . $cashDocument->getPayer()->getName() . //ЗАПАДНО-УРАЛЬСКИЙ БАНК ОАО "СБЕРБАНК РОССИИ"//ЗЫРЯНОВА ЕЛЕНА СЕРГЕЕВНА//26859356266//614000 ПЕРМЬ МЕХАНОШИНА д.10 кв.44//
                '
ПлательщикИНН=' . $organizationPayer->getInn() . '
ПлательщикКПП=' . $organizationPayer->getKpp() . '
ПлательщикРасчСчет=' . $organizationPayer->getCheckingAccount() . '
ПлательщикБанк1=' . $organizationPayer->getBank() . '
ПлательщикБИК=' . $organizationPayer->getBankBik() . '
ПлательщикКорсчет=' . $organizationPayer->getCorrespondentAccount() . '
ПолучательСчет=' . $hotelOrganization->getCorrespondentAccount() . '
ДатаПоступило=' . $cashDocument->getPaidDate()->format('d.m.Y') . '
Получатель=' . $hotelOrganization->getName() . '
ПолучательИНН=' . $hotelOrganization->getInn() . '
ПолучательКПП=' . $hotelOrganization->getKpp() . '
ПолучательРасчСчет=' . $hotelOrganization->getCheckingAccount() . '
ПолучательБанк1=' . $hotelOrganization->getBank() . '
ПолучательБИК=' . $hotelOrganization->getBankBik() . '
ПолучательКорсчет=' . $hotelOrganization->getCorrespondentAccount() . '
ВидПлатежа=' . $cashDocument->getMethod() . '
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
НазначениеПлатежа=Заказ #'.$cashDocument->getNumber().' от '.$cashDocument->getCreatedAt()->format('d.m.Y').'; ФИО: "'.($cashDocument->getTouristPayer() ? $cashDocument->getTouristPayer()->getFullName() : $cashDocument->getOrganizationPayer()->getAccountantFio()).'"; АДРЕС: '.($cashDocument->getTouristPayer() ? $cashDocument->getTouristPayer()->getAddress() : '').' ; КОНТАКТ: '.($cashDocument->getTouristPayer() ? $cashDocument->getTouristPayer()->getPhone() : '').';
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
ВремяСоздания=' . date('H.i.s') . '
ДатаНачала=' . $queryCriteria->begin->format('d.m.Y') . '
ДатаКонца=' . $queryCriteria->end->format('d.m.Y') . '
РасчСчет=' . ($hotelOrganization ? $hotelOrganization->getCheckingAccount() : '') . '
СекцияРасчСчет
ДатаНачала=' . $queryCriteria->begin->format('d.m.Y') . '
ДатаКонца=' . $queryCriteria->end->format('d.m.Y') . '
НачальныйОстаток=0
РасчСчет=' . ($hotelOrganization ? $hotelOrganization->getCheckingAccount() : '') . '
ВсегоСписано=0
ВсегоПоступило=' . $total . '
КонечныйОстаток=0
КонецРасчСчет
' . $text . 'КонецФайла
');

        return $result;
    }
}
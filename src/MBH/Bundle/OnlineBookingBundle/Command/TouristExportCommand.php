<?php

namespace MBH\Bundle\OnlineBookingBundle\Command;

use MBH\Bundle\PackageBundle\Document\DocumentRelation;
use MBH\Bundle\PackageBundle\Document\Tourist;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class TouristExportCommand
 * @author Aleksandr Arofikin <sashaaro@gmail.com>
 */
class TouristExportCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('azovsky:tourists:export')
            ->setDescription('Export');
    }

    protected function getPathTouristsCsv()
    {
        return $this->getContainer()->get('file_locator')->locate('@MBHOnlineBookingBundle/Resources/data/Clients.csv');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dm = $this->getContainer()->get('doctrine_mongodb')->getManager();

        /*$tourists = [];
        $mongoDate = new \MongoDate(time());
        for($i = 1; $i < 45000; $i ++) {
            $tourists[] = [
                "firstName" => "$i",
                "lastName" => "$i Рочева",
                "patronymic" => "$i Федоровна",
                "fullName" => "$i Рочева $i Федоровна",
                "birthday" => $mongoDate,
                "sex" => "male",
                "note" => "32299, Рочева Валентина Федоровна, 18.02.1963, Паспорт гражданина РФ, 5507, 050705, 22.02.2008, Нет, Рочева Валентина Федоровна, Нет, Клиенты, Нет, Рочева Валентина Федоровна, Паспорт гражданина РФ, 22.02.2008, , 18.02.1963, ОФМС России по НАО, , 050705, Нет, Женский, Паспорт гражданина РФ, серия: 5507, № 050705, выдан: 22 февраля 2008 года, ОФМС России по НАО, 5507, , Нет",
                "documentRelation" => [
                            "type" => "vega_russian_passport",
                    "series" => "5507",
                    "number" => "050705",
                    "issued" => $mongoDate
                ],
                "communicationLanguage" => "ru",
                "isUnwelcome"  => false,
                "isEnabled" => true,
                "createdAt" => $mongoDate,
                "updatedAt" => $mongoDate,
                "createdBy" => "admin",
                "updatedBy" => "admin"
            ];
        }
        $this->getContainer()->get('mbh.mongo')->batchInsert('Tourists', $tourists);
        return;*/


        $path = $this->getPathTouristsCsv();
        $resources = fopen($path, 'r');
        $columns = fgetcsv($resources, null, "\t");
        fgetcsv($resources, null, "\t");
        fgetcsv($resources, null, "\t");
        fgetcsv($resources, null, "\t");
        fgetcsv($resources, null, "\t");
        $i = 5;
        while (($rowData = fgetcsv($resources, null, "\t")) !== false) {
            $i++;
            /*$tourist = $this->rowToTourist($rowData, $i);

            if($tourist) {
                $dm->persist($tourist);
                $dm->persist($tourist->getDocumentRelation());
            }
            if ($i%1000 == 0) {
                $dm->flush();
                $dm->clear();
            }
            dump($i . ' - '. $rowData[0]);*/
        }

        $dm->flush();

        $output->writeln('Done. Total: ' . $i);
    }

    /**
     * @param array $rowData
     * @return Tourist
     */
    protected function rowToTourist($rowData, $i)
    {
        $tourist = new Tourist();
        //preg_match('~([А-Яа-я]+) ([А-Яа-я]+) ([А-Яа-я]+)~')
        $fio = array_values(array_filter(explode(' ' , trim($rowData[0]))));

        if(count($fio) < 1) {
            dump($i);
            dump($rowData);
            return null;
        }
        $lastName = $fio[0];
        $firstName = isset($fio[1]) ? $fio[1] : null;
        $patronymic = isset($fio[2]) ? $fio[2] : null;
        $birthDay = null;
        try {
            $birthDay = empty($rowData[1]) ? null : new \DateTime($rowData[1]);
        } catch(\Exception $e) {
            dump($i. ' ' . $e->getMessage());
        }
        $sex = null;
        if(isset($rowData[20])) {
            $sex = $rowData[20] == 'Женский' ? 'female' : $rowData[20] == 'Мужской' ? 'male' : null;
        }
        $dm = $this->getContainer()->get('doctrine_mongodb')->getManager();
        $user = $dm->getRepository('MBHUserBundle:User')->findOneBy(['username' => 'admin']);

        $tourist->setLastName($lastName);
        $tourist->setFirstName($firstName);
        $tourist->setPatronymic($patronymic);
        $tourist->setBirthday($birthDay);
        $tourist->setSex($sex);
        $tourist->setCreatedBy($user->getUsername());
        $tourist->setUpdatedBy($user->getUsername());

        $document = new DocumentRelation();
        $document->setSeries(isset($rowData[3]) ? $rowData[3] : null);
        $document->setNumber(isset($rowData[4]) ? $rowData[4] : null);
        $issued = isset($rowData[5]) && $rowData[5] ? new \DateTime($rowData[5]) : null;
        $document->setIssued($issued);
        if(isset($rowData[12])) {
            $rowData[12] = trim($rowData[12]);
            $type = null;
            if ($rowData[12] == 'Паспорт гражданина РФ') {
                $type = 'vega_russian_passport';
            } elseif($rowData[12] == 'Свидетельство о рождении') {
                $type = 'vega_birth_certificate';
            } elseif($rowData[12] == 'Иностранный паспорт') {

            }
            $document->setType($type);
        }
        /*if(isset($rowData[16]) && $rowData[16]) {
            $document->setAuthorityOrgan()
        }*/
        //$document->setType($type);
        $tourist->setDocumentRelation($document);


        $violationList = $this->getContainer()->get('validator')->validate($tourist);
        if ($violationList->count() > 0) {
            //dump($i . ' ' .$violationList->get(0));
        }

        $tourist->setNote($i. ', ' .implode(', ', $rowData));

        //dump($rowData);
        //dump($tourist);
        //die();

        return $tourist;
    }

    protected function column()
    {
        return [
            0 => "﻿ФИО",
            1 => "Дата рождения",
            2 => "Вид документа",
            3 => "Серия",
            4 => "Номер",
            5 => "Дата выдачи",
            6 => "Пометка удаления",
            7 => "ФИО",
            8 => "Это группа",
            9 => "Группа физ. лиц",
            10 => "Предопределенный",
            11 => "Ссылка",
            12 => "Вид документа",
            13 => "Дата выдачи",
            14 => "Дата записи в черный список",
            15 => "Дата рождения",
            16 => "Кем выдан",
            17 => "Код подразделения",
            18 => "Номер",
            19 => "Основной турист (или платильщик)",
            20 => "Пол",
            21 => "Документ",
            22 => "Серия",
            23 => "Срок действия",
            24 => "Является документом удостоверяющим личность",
        ];
    }
}
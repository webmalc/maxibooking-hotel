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
        $user = $dm->getRepository('MBHUserBundle:User')->findOneBy(['username' => 'admin']);
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
            $tourist = $this->rowToTourist($rowData, $i, $user);

            if($tourist) {
                $dm->persist($tourist);
                $dm->persist($tourist->getDocumentRelation());
            } else {
                $output->writeln(
                    '<error>Error: tourist == null row: ' . $i . ' Data: ' . implode('; ', $rowData) .'</error>'
                );
            }
            if ($i % 1000 == 0) {
                $dm->flush();
                $dm->clear();
            }
        }

        $dm->flush();
        $output->writeln('Done. Total: ' . $i);
    }

    /**
     * @param $rowData
     * @param $i
     * @param $user
     * @return Tourist
     *
     */
    protected function rowToTourist($rowData, $i, $user)
    {
        $tourist = new Tourist();
        $fio = array_values(array_filter(explode(' ' , trim($rowData[0]))));

        if(count($fio) < 1) {
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
        $sex = 'unknown';
        if(isset($rowData[20])) {
            if ($rowData[20] == 'Женский') {
                $sex = 'female';
            }
            if ($rowData[20] == 'Мужской') {
                $sex = 'male';
            }
        }

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

        $tourist->setNote('1C Export. Data: ' . $i. '; ' .implode('; ', $rowData));

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
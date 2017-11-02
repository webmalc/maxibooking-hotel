<?php

namespace MBH\Bundle\PackageBundle\Command;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Query\Builder;
use MBH\Bundle\VegaBundle\Document\VegaState;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class LocalAddressesDataToBillingConvertCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('mbh:local_addresses_to_billing')
            ->setDescription('Convert entities saved in local database to billing identifiers.');
    }

    private function getFilePath($fileName)
    {
        $root = $this->getContainer()->get('kernel')->getBundle('MBHPackageBundle')->getPath();

        return $root . DIRECTORY_SEPARATOR . 'Resources' . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . $fileName;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Start conversion!');
        $startTime = new \DateTime();

        $this->convertVegaStateIdsToBillingCountryIds();

        $endTime = new \DateTime();
        $timeDiffInSeconds = $endTime->diff($startTime)->s;
        $output->writeln('The conversion was completed in ' . $timeDiffInSeconds . ' seconds');
    }

    private function convertVegaStateIdsToBillingCountryIds()
    {
        /** @var DocumentManager $dm */
        $dm = $this->getDocumentManager();

        /** @var Builder $touristsQB */
        $touristsQB = $dm
            ->getRepository('MBHPackageBundle:Tourist')
            ->createQueryBuilder();

        $touristsWithFilledCountryData = $touristsQB
            ->field('citizenshipTld')->exists(false)
            ->addOr($touristsQB->expr()->field('addressObjectDecomposed.country')->notEqual(null))
            ->addOr($touristsQB->expr()->field('citizenship')->notEqual(null))
            ->hydrate(false)
            ->getQuery()
            ->execute()
            ->toArray();

        $updates = [];
        $touristsWithUnknownCountry = [];
        $billingCountryTldByVegaStateIds = $this->getBillingCountryTldByVegaStateIds();

        foreach ($touristsWithFilledCountryData as $touristData) {
            if (isset($touristData['citizenship'])) {
                $citizenshipId = $touristData['citizenship']['$id']->serialize();
                $vegaState = $dm->find('MBHVegaBundle:VegaState', $citizenshipId);
                if (isset($billingCountryTldByVegaStateIds[$citizenshipId])) {
                    $billingTld = $billingCountryTldByVegaStateIds[$citizenshipId];
                    $updates[] = [
                        'criteria' => ['_id' => $touristData['_id']],
                        'values' => [
                            'citizenshipTld' => $billingTld
                        ]
                    ];
                } else {
                    $touristsWithUnknownCountry[] = [
                        $touristData['_id'],
                        $touristData['fullName'],
                        'citizenship',
                        $vegaState->getId(),
                        $vegaState->getName()
                    ];
                }
            }
            if (isset($touristData['addressObjectDecomposed.country'])) {
                $citizenshipId = $touristData['addressObjectDecomposed.country']['$id']->serialize();
                $vegaState = $dm->find('MBHVegaBundle:VegaState', $citizenshipId);
                if (isset($billingCountryTldByVegaStateIds[$citizenshipId])) {
                    $billingTld = $billingCountryTldByVegaStateIds[$citizenshipId];
                    $updates[] = [
                        'criteria' => ['_id' => $touristData['_id']],
                        'values' => [
                            'addressObjectDecomposed.countryTld' => $billingTld
                        ]
                    ];
                } else {
                    $touristsWithUnknownCountry[] = [
                        $touristData['_id'],
                        $touristData['fullName'],
                        'addressObjectDecomposed.country',
                        $vegaState->getId(),
                        $vegaState->getName()
                    ];
                }
            }
        }

        if (!empty($touristsWithUnknownCountry)) {
            $dataToWriteInCsv = array_merge([['ID туриста', 'Имя', 'Поле', 'ID страны', 'Название страны']], $touristsWithUnknownCountry);
            $this->getContainer()->get('mbh.entities_exporter')->writeToCsv($dataToWriteInCsv, $this->getFilePath('test_file.csv'));
        }

        $this->getContainer()->get('mbh.mongo')->update('Tourists', $updates);
    }

    /**
     * @return array
     */
    private function getBillingCountryTldByVegaStateIds()
    {
        $resource = fopen($this->getFilePath('countries.csv'), 'r');
        $countryTldByNames = [];
        if ($resource) {
            while (($rowData = fgetcsv($resource, 1000, ";")) !== false) {
                $countryTldByNames[strtolower($rowData[1])] = $rowData[2];
            }
            fclose($resource);
        }

        $vegaStates = $this->getDocumentManager()->getRepository('MBHVegaBundle:VegaState')->findAll();
        $billingCountryTldByVegaStateIds = [];

        /** @var VegaState $vegaState */
        foreach ($vegaStates as $vegaState) {
            $lowerVegaStateName = strtolower($vegaState->getName());
            if (isset($countryTldByNames[$lowerVegaStateName])) {
                $billingCountryTldByVegaStateIds[$vegaState->getId()] = $countryTldByNames[$lowerVegaStateName];
            }
        }

        return $billingCountryTldByVegaStateIds;
    }

    /**
     * @return array
     */
//    private function convertVegaIdToBillingIdAndReturnMissing()
//    {
//        $vegaDocuments = $this->getDocumentManager() ->getRepository('MBHVegaBundle:VegaFMS')->findAll();
//        $resource = fopen($this->getFilePath('fms.csv'), 'r');
//        $billingIdsByCodes = [];
//
//        if ($resource) {
//            while (($rowData = fgetcsv($resource, 1000, ";")) !== false) {
//                $billingIdsByCodes[$rowData[2]][] = $rowData;
//            }
//            fclose($resource);
//        }
//
//        $missingDocumentsIds = [];
//        $localIdsToBillingIds = [];
//        foreach ($vegaDocuments as $vegaDocument) {
//            if (isset($billingIdsByCodes[$vegaDocument->getCode()])) {
//                $found = false;
//                foreach ($billingIdsByCodes[$vegaDocument->getCode()] as $billingIdsByCode) {
//                    if (mb_strtolower($billingIdsByCode[1]) == mb_strtolower($vegaDocument->getOriginalName())) {
//                        $localIdsToBillingIds[$vegaDocument->getId()] = $billingIdsByCode[0];
//                        $found = true;
//                    }
//                }
//
//                if (!$found) {
//                    $missingDocumentsIds[] = [$vegaDocument->getId()];
//                }
//
//            } else {
//                foreach ($billingIdsByCodes as $billingIdsByCode) {
//                    foreach ($billingIdsByCode as $item) {
//                        if (strtolower($item[1]) == strtolower($vegaDocOriginalName)) {
//                            $localIdsToBillingIds[$vegaDocument->getId()] = $billingIdsByCode[0];
//                            break 2;
//                        }
//                    }
//                }
//            }
//        }
//    }

    private function getDocumentManager()
    {
        return $this->getContainer()->get('doctrine_mongodb.odm.default_document_manager');
    }
}

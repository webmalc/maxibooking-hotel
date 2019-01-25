<?php

namespace MBH\Bundle\PackageBundle\Command;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Query\Builder;
use MBH\Bundle\ClientBundle\Lib\FMSDictionaries;
use MBH\Bundle\HotelBundle\Document\City;
use MBH\Bundle\HotelBundle\Document\Region;
use MBH\Bundle\VegaBundle\Document\VegaRegion;
use MBH\Bundle\VegaBundle\Document\VegaState;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

// АТЕНШЕН! ДАННЫЙ КОД НЕ РЕКОМЕНДУЕТСЯ ДЛЯ ЧТЕНИЯ И ИСПОЛЬЗУЕТСЯ ОДНОКРАТНО!!!
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

        $csvExporter = $this->getContainer()->get('mbh.entities_exporter');
        $csvExporter->writeToCsv($this->convertTouristsAddressData(), $this->getFilePath('missingTouristData.csv'));
        $output->writeln('Tourists conversion completed!');
        $csvExporter->writeToCsv($this->convertUserVegaStateIdsToBillingCountryIds(), $this->getFilePath('missingUserData.csv'));
        $output->writeln('Users conversion completed!');
        $csvExporter->writeToCsv($this->convertOrganizationsAddressData(), $this->getFilePath('missingOrganizationsData.csv'));
        $output->writeln('Organizations conversion completed!');
        $csvExporter->writeToCsv($this->convertHotelAddressData(), $this->getFilePath('missingHotelData.csv'));
        $output->writeln('Hotels conversion completed!');

        $endTime = new \DateTime();
        $output->writeln('The conversion was completed in '
            . $endTime->diff($startTime)->i . ' minutes '
            . $endTime->diff($startTime)->s . ' seconds');
    }

    private function convertTouristsAddressData()
    {
        /** @var DocumentManager $dm */
        $dm = $this->getDocumentManager();

        /** @var Builder $touristsQB */
        $touristsQB = $dm
            ->getRepository('MBHPackageBundle:Tourist')
            ->createQueryBuilder();

        $touristsWithFilledCountryData = $touristsQB
            ->addOr($touristsQB->expr()
                ->field('addressObjectDecomposed.country')->notEqual(null)
                ->field('addressObjectDecomposed.countryTld')->exists(false)
            )
            ->addOr($touristsQB->expr()
                ->field('addressObjectDecomposed.region')->notEqual(null)
                ->field('addressObjectDecomposed.regionId')->exists(false)
            )
            ->addOr(
                $touristsQB->expr()
                    ->field('citizenship')->notEqual(null)
                    ->field('citizenshipTld')->exists(false)
            )
            ->addOr(
                $touristsQB->expr()
                    ->field('birthplace.country')->notEqual(null)
                    ->field('birthplace.countryTld')->exists(false)
            )
            ->addOr(
                $touristsQB->expr()
                    ->field('documentRelation.type')->notEqual(null)
                    ->field('documentRelation.type')->type('string')
            )
            ->hydrate(false)
            ->getQuery()
            ->execute()
            ->toArray();

        $updates = [];
        $touristsWithUnknownCountry = [];
        $touristsWithUnknownRegion = [];
        $touristsWithUnknownDocType = [];

        $billingCountryTldByVegaStateIds = $this->getBillingCountryTldByVegaStateIds();
        $billingRegionIdByVegaRegionIds = $this->getBillingRegionIdsByVegaRegionIds();
        $fmsDocTypesIdsByVegaDocTypes = $this->getFMSDocumentTypeIdsByVegaDocumentTypes();

        foreach ($touristsWithFilledCountryData as $touristData) {
            $updatedValues = [];
            if (isset($touristData['citizenship'])) {
                $citizenshipId = $touristData['citizenship']['$id']->serialize();
                $vegaState = $dm->find('MBHVegaBundle:VegaState', $citizenshipId);
                if (isset($billingCountryTldByVegaStateIds[$citizenshipId])) {
                    $billingTld = $billingCountryTldByVegaStateIds[$citizenshipId];
                    $updatedValues['citizenshipTld'] = $billingTld;
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
            if (isset($touristData['birthplace']['country'])) {
                $citizenshipId = $touristData['birthplace']['country']['$id']->serialize();
                $vegaState = $dm->find('MBHVegaBundle:VegaState', $citizenshipId);
                if (isset($billingCountryTldByVegaStateIds[$citizenshipId])) {
                    $billingTld = $billingCountryTldByVegaStateIds[$citizenshipId];
                    $updatedValues['birthplace.countryTld'] = $billingTld;
                } else {
                    $touristsWithUnknownCountry[] = [
                        $touristData['_id'],
                        $touristData['fullName'],
                        'birthplace.country',
                        $vegaState->getId(),
                        $vegaState->getName()
                    ];
                }
            }
            if (isset($touristData['addressObjectDecomposed']['country'])) {
                $citizenshipId = $touristData['addressObjectDecomposed']['country']['$id']->serialize();
                $vegaState = $dm->find('MBHVegaBundle:VegaState', $citizenshipId);
                if (isset($billingCountryTldByVegaStateIds[$citizenshipId])) {
                    $billingTld = $billingCountryTldByVegaStateIds[$citizenshipId];
                    $updatedValues['addressObjectDecomposed.countryTld'] = $billingTld;
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
            if (isset($touristData['addressObjectDecomposed']['region'])) {
                $regionId = $touristData['addressObjectDecomposed']['region']['$id']->serialize();
                if (isset($billingRegionIdByVegaRegionIds[$regionId])) {
                    $billingId = $billingRegionIdByVegaRegionIds[$regionId];
                    $updatedValues['addressObjectDecomposed.regionId'] = $billingId;
                } else {
                    $vegaRegion = $dm->find('MBHVegaBundle:VegaRegion', $regionId);
                    $touristsWithUnknownRegion[] = [
                        $touristData['_id'],
                        $touristData['fullName'],
                        'addressObjectDecomposed.region',
                        $vegaRegion->getId(),
                        $vegaRegion->getName()
                    ];
                }
            }
            if (isset($touristData['documentRelation']['type']) && !is_int($touristData['documentRelation']['type'])) {
                $type = $touristData['documentRelation']['type'];
                if (isset($fmsDocTypesIdsByVegaDocTypes[$type])) {
                    $fmsDocTypeId = $fmsDocTypesIdsByVegaDocTypes[$type];
                    $updatedValues['documentRelation.type'] = $fmsDocTypeId;
                } else {
                    $touristsWithUnknownDocType[] = [
                        $touristData['_id'],
                        $touristData['fullName'],
                        'documentRelation.type',
                        $type
                    ];
                }
            }
            if (!empty($updatedValues)) {
                $updates[] = [
                    'criteria' => ['_id' => $touristData['_id']],
                    'values' => $updatedValues
                ];
            }
        }

        $this->getContainer()->get('mbh.mongo')->update('Tourists', $updates);

        $missingData = [];
        if (!empty($touristsWithUnknownCountry)) {
            $missingData = array_merge([['ID туриста', 'Имя', 'Поле', 'ID страны', 'Название страны']], $touristsWithUnknownCountry);
        }
        if (!empty($touristsWithUnknownRegion)) {
            $missingData = array_merge([['ID туриста', 'Имя', 'Поле', 'ID региона', 'Название региона']], $touristsWithUnknownRegion, $missingData);
        }
        if (!empty($touristsWithUnknownDocType)) {
            $missingData = array_merge([['ID туриста', 'Имя', 'Поле', 'Тип документа']], $touristsWithUnknownDocType, $missingData);
        }

        return $missingData;
    }

    private function getFMSDocumentTypeIdsByVegaDocumentTypes()
    {
        $relations = [];
        $vegaDocTypes = $this->getContainer()->get('mbh.vega.dictionary_provider')->getDocumentTypes();
        $fmsDocTypes = $this->getContainer()->get('mbh.fms_dictionaries')->getDocumentTypes();
        foreach ($vegaDocTypes as $vegaDocType) {
            foreach ($fmsDocTypes as $docTypeId => $documentType) {
                if (mb_strtolower($vegaDocType) === mb_strtolower($documentType)) {
                    $relations[$vegaDocType] = $docTypeId;
                    break;
                }
            }
        }
        $relations['vega_russian_passport'] = FMSDictionaries::RUSSIAN_PASSPORT_ID;
        $relations['vega_travel_passport'] = FMSDictionaries::TRAVEL_PASSPORT;
        $relations['vega_passport_foreigner'] = 136359;
        $relations['vega_residence'] = 135709;
        $relations['vega_national_id_republic_of_kazakhstan\''] = 139384;
        $relations['vega_national_id_republic_of_kazakhstan'] = 139384;
        $relations['vega_birth_certificate'] = 102974;
        $relations['vega_st_of_birth_foreigner'] = 103014;
        $relations['vega_refugee_certificate'] = 103000;
        $relations['vega_travel_passport biometrics'] = 136336;
        $relations['vega_non_citizen_passport'] = 102969;
        $relations['vega_visa']= 139356;
        $relations['vega_military_id_soldier'] = 102990;
        $relations['vega_seaman'] = 103021;
        $relations['vega_migration_cart'] = 103022;
        $relations['vega_predost_temporate_asylum'] = 102977;
        $relations['vega_return_certificate'] = 102983;
        $relations['vega_predost_temporate_asylum'] = 102977;
        $relations['vega_temporary_residence permit_foreigner'] = 139373;

        return $relations;
    }

    private function convertOrganizationsAddressData()
    {
        $billingCitiesIdsByLocalCitiesIds = $this->getBillingCityIdsByLocalCityIds();

        /** @var DocumentManager $dm */
        $dm = $this->getDocumentManager();

        /** @var Builder $organizationsQB */
        $organizationsQB = $dm
            ->getRepository('MBHPackageBundle:Organization')
            ->createQueryBuilder();

        $organizationsWithFilledAddressData = $organizationsQB
            ->addOr($organizationsQB->expr()
                ->field('country')->notEqual(null)
                ->field('countryTld')->exists(false)
            )
            ->addOr($organizationsQB->expr()
                ->field('region')->notEqual(null)
                ->field('regionId')->exists(false)
            )
            ->addOr($organizationsQB->expr()
                ->field('city')->notEqual(null)
                ->field('cityId')->exists(false)
            )
            ->hydrate(false)
            ->getQuery()
            ->execute()
            ->toArray();

        $updates = [];
        $organizationsWithUnknownCity = [];

        foreach ($organizationsWithFilledAddressData as $organizationData) {
            $updatedValues = [];
            if (isset($organizationData['city'])) {
                $localCityId = $organizationData['city']['$id']->serialize();
                $city = $dm->find('MBHHotelBundle:City', $localCityId);
                if (isset($billingCitiesIdsByLocalCitiesIds[$localCityId])) {
                    $billingCityId = $billingCitiesIdsByLocalCitiesIds[$localCityId];
                    $updatedValues['cityId'] = $billingCityId;
                } else {
                    $organizationsWithUnknownCity[] = [
                        $organizationData['_id'],
                        $organizationData['shortName'],
                        'city',
                        $city->getId(),
                        $city->getName()
                    ];
                }
            }
            if (!empty($updatedValues)) {
                $updates[] = [
                    'criteria' => ['_id' => $organizationData['_id']],
                    'values' => $updatedValues
                ];
            }
        }

        $this->getContainer()->get('mbh.mongo')->update('Organizations', $updates);

        $missingData = [];

        if (!empty($organizationsWithUnknownCity)) {
            $missingData = array_merge([['ID организации', 'Название', 'Поле', 'ID города', 'Название города']], $organizationsWithUnknownCity, $missingData);
        }

        return $missingData;
    }

    private function convertHotelAddressData()
    {
        $billingCitiesIdsByLocalCitiesIds = $this->getBillingCityIdsByLocalCityIds();
        
        /** @var DocumentManager $dm */
        $dm = $this->getDocumentManager();

        /** @var Builder $hotelsQB */
        $hotelsQB = $dm
            ->getRepository('MBHHotelBundle:Hotel')
            ->createQueryBuilder();

        $hotelsWithFilledAddressData = $hotelsQB
            ->addOr($hotelsQB->expr()
                ->field('country')->notEqual(null)
                ->field('countryTld')->exists(false)
            )
            ->addOr($hotelsQB->expr()
                ->field('region')->notEqual(null)
                ->field('regionId')->exists(false)
            )
            ->addOr($hotelsQB->expr()
                ->field('city')->notEqual(null)
                ->field('cityId')->exists(false)
            )
            ->hydrate(false)
            ->getQuery()
            ->execute()
            ->toArray();

        $updates = [];
        $hotelsWithUnknownCity = [];

        foreach ($hotelsWithFilledAddressData as $hotelData) {
            $updatedValues = [];
            if (isset($hotelData['city'])) {
                $localCityId = $hotelData['city']['$id']->serialize();
                $city = $dm->find('MBHHotelBundle:City', $localCityId);
                if (isset($billingCitiesIdsByLocalCitiesIds[$localCityId])) {
                    $billingTld = $billingCitiesIdsByLocalCitiesIds[$localCityId];
                    $updatedValues['cityId'] = $billingTld;
                } else {
                    $hotelsWithUnknownCity[] = [
                        $hotelData['_id'],
                        $hotelData['title'],
                        'city',
                        $city->getId(),
                        $city->getName()
                    ];
                }
            }
            if (!empty($updatedValues)) {
                $updates[] = [
                    'criteria' => ['_id' => $hotelData['_id']],
                    'values' => $updatedValues
                ];
            }
        }

        $this->getContainer()->get('mbh.mongo')->update('Hotels', $updates);

        $missingData = [];
        if (!empty($hotelsWithUnknownCity)) {
            $missingData = array_merge([['ID Отеля', 'Название', 'Поле', 'ID города', 'Название города']], $hotelsWithUnknownCity, $missingData);
        }

        return $missingData;
    }

    private function convertUserVegaStateIdsToBillingCountryIds()
    {
        /** @var DocumentManager $dm */
        $dm = $this->getDocumentManager();

        /** @var Builder $usersQB */
        $usersQB = $dm
            ->getRepository('MBHUserBundle:User')
            ->createQueryBuilder();

        $touristsWithFilledCountryData = $usersQB
            ->field('addressObjectDecomposed.countryTld')->exists(false)
            ->field('addressObjectDecomposed.country')->notEqual(null)
            ->hydrate(false)
            ->getQuery()
            ->execute()
            ->toArray();

        $touristsWithUnknownCountry = [];
        $updates = [];
        $billingCountryTldByVegaStateIds = $this->getBillingCountryTldByVegaStateIds();

        foreach ($touristsWithFilledCountryData as $touristData) {
            $citizenshipId = $touristData['addressObjectDecomposed']['country']['$id']->serialize();
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
                    $touristData['username'],
                    'addressObjectDecomposed.country',
                    $vegaState->getId(),
                    $vegaState->getName()
                ];
            }
        }

        $this->getContainer()->get('mbh.mongo')->update('Users', $updates);

        if (!empty($touristsWithUnknownCountry)) {
            return array_merge([['ID пользователя', 'Имя', 'Поле', 'ID страны', 'Название страны']], $touristsWithUnknownCountry);
        }

        return [];
    }

    /**
     * @return array
     */
    private function getBillingCountryTldByVegaStateIds()
    {
        $resource = fopen($this->getFilePath('countries.csv'), 'r');
        $countryTldByNames = [];
        $countryTldByAlternateNames = [];
        if ($resource) {
            while (($rowData = fgetcsv($resource, 1000, ";")) !== false) {
                $countryTldByNames[strtolower($rowData[1])] = $rowData[2];
                $countryTldByAlternateNames[strtolower($rowData[0])] = $rowData[2];
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
            } else {
                $countryTld = $this->searchByAlternateNames($countryTldByAlternateNames, $lowerVegaStateName, 'vegaCountry');
                if (!is_null($countryTld)) {
                    $billingCountryTldByVegaStateIds[$vegaState->getId()] = $countryTld;
                }
            }
        }

        return $billingCountryTldByVegaStateIds;
    }

    private $foundInAlternateNames;
    private function searchByAlternateNames($valuesByAlternateNames, $needle, $type)
    {
        foreach ($valuesByAlternateNames as $alternateName => $value) {
            if (strpos($alternateName, $needle) !== false) {
                $this->foundInAlternateNames[$type][$needle] = $value;
                return $value;
            }
        }

        return null;
    }

    /**
     * @return array
     */
    private function getBillingRegionIdsByVegaRegionIds()
    {
        $resource = fopen($this->getFilePath('regions.csv'), 'r');
        $regionIdsByNames = [];
        $regionNames = [];
        if ($resource) {
            while (($rowData = fgetcsv($resource, 1000, ";")) !== false) {
                $lowerBillingRegionName = mb_strtolower($rowData[1]);
                $regionNames[] = $lowerBillingRegionName;
                $regionIdsByNames[$lowerBillingRegionName] = $rowData[0];
            }
            fclose($resource);
        }

        $vegaRegions = $this->getDocumentManager()->getRepository('MBHVegaBundle:VegaRegion')->findAll();
        $billingRegionIdByVegaRegionIds = [];

        /** @var VegaRegion $vegaRegion */
        foreach ($vegaRegions as $vegaRegion) {
            $lowerVegaRegionName = mb_strtolower($vegaRegion->getOriginalName());
            if (isset($regionIdsByNames[$lowerVegaRegionName])) {
                $billingRegionIdByVegaRegionIds[$vegaRegion->getId()] = $regionIdsByNames[$lowerVegaRegionName];
            } elseif (!is_null($regionName = $this->getBillingNameByLocalName($regionNames, $lowerVegaRegionName))) {
                $billingRegionIdByVegaRegionIds[$vegaRegion->getId()] = $regionIdsByNames[$regionName];
            }
        }

        return $billingRegionIdByVegaRegionIds;
    }

    private $billingRegionIdsByLocalRegionIds = [];
    private $isBillingRegionIdsByLocalRegionIdsInit = false;

    public function getBillingRegionIdsByLocalRegionIds()
    {
        if (!$this->isBillingRegionIdsByLocalRegionIdsInit) {
            $resource = fopen($this->getFilePath('regions.csv'), 'r');
            $regionIdsByNames = [];
            $regionNames = [];
            $valuesByAlternateNames = [];
            if ($resource) {
                while (($rowData = fgetcsv($resource, 1000, ";")) !== false) {
                    $lowerBillingRegionName = mb_strtolower($rowData[2]);
                    $regionNames[] = $lowerBillingRegionName;
                    $regionId = $rowData[0];
                    $regionIdsByNames[$lowerBillingRegionName] = $regionId;
                    $valuesByAlternateNames[mb_strtolower($rowData[1])] = $regionId;
                }
                fclose($resource);
            }

            $localRegions = $this->getDocumentManager()->getRepository('MBHHotelBundle:Region')->findAll();

            /** @var Region $localRegion */
            foreach ($localRegions as $localRegion) {
                $lowerVegaRegionName = mb_strtolower($localRegion->getTitle());
                if (isset($regionIdsByNames[$lowerVegaRegionName])) {
                    $this->billingRegionIdsByLocalRegionIds[$localRegion->getId()] = $regionIdsByNames[$lowerVegaRegionName];
                } elseif (!is_null($regionName = $this->getBillingNameByLocalName($regionNames, $lowerVegaRegionName))) {
                    $this->billingRegionIdsByLocalRegionIds[$localRegion->getId()] = $regionIdsByNames[$regionName];
                } elseif (!is_null($regionId = $this->searchByAlternateNames($valuesByAlternateNames, $lowerVegaRegionName, 'vegaRegion'))) {
                    $this->billingRegionIdsByLocalRegionIds[$localRegion->getId()] = $regionId;
                }
            }
            $this->isBillingRegionIdsByLocalRegionIdsInit = true;
        }

        return $this->billingRegionIdsByLocalRegionIds;
    }

    private $billingCityIdsByLocalCityIds;
    private $isBillingCityIdsByLocalCityIdsInit = false;

    public function getBillingCityIdsByLocalCityIds()
    {
        if (!$this->isBillingCityIdsByLocalCityIdsInit) {
            $resource = fopen($this->getFilePath('cities.csv'), 'r');
            $citiesIdsByNames = [];
            $citiesNames = [];
            $citiesIdsByAlternateNames = [];
            if ($resource) {
                while (($rowData = fgetcsv($resource, 1000, ";")) !== false) {
                    $lowerBillingRegionName = mb_strtolower($rowData[2]);
                    $citiesNames[] = $lowerBillingRegionName;
                    $citiesIdsByNames[$lowerBillingRegionName] = $rowData[0];
                    $citiesIdsByAlternateNames[mb_strtolower($rowData[1])] = $rowData[0];
                }
                fclose($resource);
            }

            $localCities = $this->getDocumentManager()->getRepository('MBHHotelBundle:City')->findAll();

            /** @var City $localCity */
            foreach ($localCities as $localCity) {
                $lowerVegaCityName = mb_strtolower($localCity->getTitle());
                if (isset($citiesIdsByNames[$lowerVegaCityName])) {
                    $this->billingCityIdsByLocalCityIds[$localCity->getId()] = $citiesIdsByNames[$lowerVegaCityName];
                } elseif (!is_null($regionName = $this->getBillingNameByLocalName($citiesNames, $lowerVegaCityName))) {
                    $this->billingCityIdsByLocalCityIds[$localCity->getId()] = $citiesIdsByNames[$regionName];
                } else {
                    $cityId = $this->searchByAlternateNames($citiesIdsByAlternateNames, $lowerVegaCityName, 'vegaCity');
                    if (!is_null($cityId)) {
                        $this->billingCityIdsByLocalCityIds[$localCity->getId()] = $cityId;
                    }
                }
            }
            $this->isBillingCityIdsByLocalCityIdsInit = true;
        }

        return $this->billingCityIdsByLocalCityIds;
    }

    private function getBillingNameByLocalName($billingRegionNames, $localName)
    {
        foreach ($billingRegionNames as $billingRegionName) {
            if (strpos($billingRegionName, $localName) !== false) {
                return $billingRegionName;
            }
        }

        return null;
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

    /**
     * @return DocumentManager
     */
    private function getDocumentManager()
    {
        return $this->getContainer()->get('doctrine_mongodb.odm.default_document_manager');
    }
}

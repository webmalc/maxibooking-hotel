<?php

namespace MBH\Bundle\PackageBundle\Services;

use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\PackageBundle\Document\SearchQuery;
use MBH\Bundle\PackageBundle\Lib\SearchResult;
use MBH\Bundle\PackageBundle\Services\Search\SearchFactory;

class RandomPackagesGenerator
{
    const CREATION_START_IN_MONTHS_AGO = 3;
    private $search;
    private $orderManager;
    private $dm;

    public function __construct(SearchFactory $search, OrderManager $orderManager, DocumentManager $dm) {
        $this->search = $search;
        $this->orderManager = $orderManager;
        $this->dm = $dm;
    }

    public function generate(\DateTime $begin, \DateTime $end, $packagesNumber)
    {
        $numberOfCreated = 0;
        $numberOfErrorAttempts = 0;
        $numberOfSearches = 0;
        $errors = [];
        $tourists = $this->dm->getRepository('MBHPackageBundle:Tourist')->findAll();
        $users = $this->dm->getRepository('MBHUserBundle:User')->findAll();
        $creationBegin = new \DateTime('midnight - ' . self::CREATION_START_IN_MONTHS_AGO . 'months');

        while ($numberOfCreated < $packagesNumber
            && $numberOfErrorAttempts < $packagesNumber
            && $numberOfSearches < ($packagesNumber * 3)
        ) {
            $lengthOfPackage = random_int(3, 15);
            $fromBeginOffset = random_int(0, $begin->diff($end)->days - $lengthOfPackage);

            if ($fromBeginOffset < 0) {
                continue;
            }

            $query = new SearchQuery();
            $query->begin = (clone $begin)->modify('+' . $fromBeginOffset . 'days');
            $query->end = (clone $begin)->modify('+' . ($fromBeginOffset + $lengthOfPackage) . 'days');
            $query->adults = random_int(1, 2);
            $query->children = random_int(0, 3 - $query->adults);
            $query->accommodations = true;
            /** @var SearchResult[] $searchResults */
            $searchResults = $this->search->search($query);
            $numberOfSearches++;

            foreach ($searchResults as $searchResult) {
                $packages = [];
                $rooms = $searchResult->getRooms()->toArray();
                $accommodationId = array_keys($rooms)[0];
                $packages[] = [
                    'begin' => $searchResult->getBegin()->format('d.m.Y'),
                    'end' => $searchResult->getEnd()->format('d.m.Y'),
                    'adults' => $searchResult->getAdults(),
                    'children' => $searchResult->getChildren(),
                    'roomType' => $searchResult->getRoomType()->getId(),
                    'tariff' => $searchResult->getTariff()->getId(),
                    'special' => null,
                    'accommodation' => $accommodationId,
                    'forceBooking' => false,
                    'infants' => 0,
                    'childrenAges' => null,
                ];
                $data = [
                    'packages' => $packages,
                    'status' => 'offline',
                    'confirmed' => true,
                    'tourist' => $tourists[array_rand($tourists)]->getId(),
                ];

                $paidSum = $searchResult->getPrice($searchResult->getAdults(), $searchResult->getChildren());
                if ($numberOfCreated % 3 === 0) {
                    $paidSum = $paidSum * rand(2, 8) / 10;
                } elseif ($numberOfCreated % 4 === 0) {
                    $paidSum = 0;
                }

                $cashData = [
                    'method' => CashDocument::getAvailableMethods()[array_rand(CashDocument::getAvailableMethods())],
                    'total' => $paidSum
                ];

                $user = $users[array_rand($users)];
                try {
                    $order = $this->orderManager->createPackages($data, null, $user, $cashData);
                    $package = $order->getFirstPackage();
                    ($order->getCashDocuments()[0])->setIsPaid(true);

                    if ($searchResult->getBegin() < new \DateTime('midnight')) {
                        $package->setArrivalTime($searchResult->getBegin());
                    }
                    if ($searchResult->getEnd() < new \DateTime('midnight')) {
                        $package->setDepartureTime($searchResult->getEnd());
                    }

                    $timestamp = mt_rand($creationBegin->getTimestamp(), $searchResult->getBegin()->getTimestamp());
                    $creationDate = new \DateTime('@' . $timestamp);
                    $package->setCreatedAt($creationDate);
                    $package->setCreatedBy($user->getUsername());

                    $numberOfCreated++;
                    $this->dm->flush();
                } catch (\Exception $exception) {
                    $numberOfErrorAttempts++;
                    $errors[] = $exception->getTraceAsString();
                }
            }
        }

        return ['errors' => $numberOfErrorAttempts, 'numberOfSearches' => $numberOfSearches, 'created' => $numberOfCreated, 'errorsMessages' => $errors];
    }
}
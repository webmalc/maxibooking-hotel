<?php

namespace MBH\Bundle\PackageBundle\Services;

use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\PackageBundle\Document\Package;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CsvGenerate
{
    /**
     * @var DocumentManager
     */
    protected $dm;
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    const DATA = [
        'type' => ['title' => 'csv.type.package', 'method' => 'getStatus'],
        'orderSource' => ['title' => 'csv.type.order.source', 'method' => 'getSource'],
        'numberWithPrefix' => ['title' => '#', 'method' => 'getNumberWithPrefix'],
        'dateBegin' => ['title' => 'csv.type.begin', 'method' => 'getBegin'],
        'dateEnd' => ['title' => 'csv.type.end', 'method' => 'getEnd'],
        'tariffType' => ['title' => 'csv.type.tariffType', 'method' => 'getRoomType'],
        'tariffAccomodation' => ['title' => 'csv.type.tariffAccomodation', 'method' => 'getAccommodation'],
        'guests' => ['title' => 'csv.type.guests', 'method' => 'getMainTourist'],
        'adults' => ['title' => 'csv.type.adults', 'method' => 'getAdults'],
        'children' => ['title' => 'csv.type.children', 'method' => 'getChildren'],
        'countNight' => ['title' => 'csv.form.countNight', 'method' => 'getNights'],
        'countPersons' => ['title' => 'csv.form.countPersons', 'method' => 'countPersons'],
        'price' => ['title' => 'csv.form.price', 'method' => 'getPrice'],
        'packagePrice' => ['title' => 'csv.form.package_price', 'method' => 'getPackagePrice'],
        'packageServicesPrice' => ['title' => 'csv.form.package_services_price', 'method' => 'getServicesPrice'],
        'paids' => ['title' => 'csv.type.paids', 'method' => 'getPaids'],
        'rest' => ['title' => 'csv.type.rest', 'method' => 'getRest'],
        'tariff' => ['title' => 'csv.type.tariff', 'method' => 'getTariff'],
        'createdAt' => ['title' => 'csv.type.createdAt', 'method' => 'getCreatedAt'],
        'createdBy' => ['title' => 'csv.type.createdBy', 'method' => 'getCreatedBy'],
        'note' => ['title' => 'csv.form.note', 'method' => 'getNote']
    ];

    const DELIMITER = ";";


    public function __construct(ContainerInterface $container)
    {
        $this->dm = $container->get('doctrine_mongodb')->getManager();
        $this->container = $container;
    }

    public function generateCsv($data, $formData)
    {
        $translator = $this->container->get('translator');
        $entities = $this->dm->getRepository('MBHPackageBundle:Package')->fetch($data);
        $title = [];
        foreach (self::DATA as $key => $item) {
            if (!empty($formData[$key])) {
                $title[] = $translator->trans($item['title']);
            }
        }

        $rows[] = implode(self::DELIMITER, $title);
        $dataCsv = [];

        /** @var Package $entity */
        foreach ($entities as $entity) {
            foreach (self::DATA as $key => $item) {

                if (!empty($formData[$key])) {

                    $method = $item['method'];

                    switch ($method) {
                        case 'countPersons':
                            $dataCsv[] = $entity->getAdults() + $entity->getChildren();
                            break;

                        case 'getPaids':
                            $dataCsv[] = $entity->getCalculatedPayment();
                            break;

                        case 'getRest':
                            $dataCsv[] = round($entity->getPrice() - $entity->getCalculatedPayment(), 2);
                            break;

                        case 'getServicesPrice':
                            $dataCsv[] = $entity->getServicesPrice() ? $entity->getServicesPrice() : 0;
                            break;

                        case 'getNote':
                            $dataCsv[] = $entity->getNote()
                                ? str_replace(["\r", "\n"], '', $entity->getNote())
                                : '';
                            break;

                        case 'getMainTourist':
                            if ($entity->getOrder()->getOrganization()) {
                                $dataCsv[] = $entity->getOrder()->getOrganization()->getName();
                            } else {
                                $dataCsv[] = $entity->$method();
                            }
                            break;

                        default:
                            $call = $entity->$method();
                            if ($call instanceof \DateTime) {
                                $dataCsv[] = $entity->$method()->format('d.m.Y');
                            } else {
                                $method == 'getStatus' ? $dataCsv[] = $translator
                                    ->trans('manager.' . $entity->$method()) : $dataCsv[] = $entity->$method();
                            }
                    }
                }
            }

            $rows[] = implode(self::DELIMITER, $dataCsv);
            $dataCsv = [];
        }

        $content = implode("\n", $rows);
        $content = iconv('UTF-8', 'windows-1251//TRANSLIT', $content);

        return $content;
    }
}

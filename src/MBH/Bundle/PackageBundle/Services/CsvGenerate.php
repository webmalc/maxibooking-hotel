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
        'typeOrder' => ['title' => 'csv.type.order.type', 'method' => 'getOrder'],
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
        'price' => ['title' => 'csv.type.price', 'method' => 'getPrice'],
        'paids' => ['title' => 'csv.type.paids', 'method' => 'getPaids'],
        'rest' => ['title' => 'csv.type.rest', 'method' => 'getRest'],
        'tariff' => ['title' => 'csv.type.tariff', 'method' => 'getTariff'],
        'createdAt' => ['title' => 'csv.type.createdAt', 'method' => 'getCreatedAt'],
        'createdBy' => ['title' => 'csv.type.createdBy', 'method' => 'getCreatedBy'],
    ];

    const DELIMETER = ";";


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

        $rows[] = implode(self::DELIMETER, $title);
        $dataCsv = [];
        /** @var Package $entity */
        foreach ($entities as $entity) {
            foreach (self::DATA as $key => $item) {

                if (!empty($formData[$key])) {

                    $method = $item['method'];

                    if ($method == 'countPersons') {

                        $dataCsv[] = $entity->getAdults() + $entity->getChildren();

                    } elseif ($method == 'getOrder') {
                        $entity->getStatus() == 'channel_manager' ? $dataCsv[] = $translator->trans('manager.channel_manager.' . $entity->getChannelManagerType()) : $dataCsv[] = '';
                    } elseif ($method == 'getPaids') {
                        $dataCsv[] = $entity->getCalculatedPayment();
                    } elseif ($method == 'getRest') {
                        $dataCsv[] = round($entity->getPrice() - $entity->getCalculatedPayment(), 2);
                    } else {

                        $call = $entity->$method();

                        if ($call instanceof \DateTime) {
                            $dataCsv[] = $entity->$method()->format('d.m.Y');
                        } else {
                            $method == 'getStatus' ? $dataCsv[] = $translator->trans('manager.' . $entity->$method()) : $dataCsv[] = $entity->$method();
                        }
                    }

                }
            }

            $rows[] = implode(self::DELIMETER, $dataCsv);
            $dataCsv = [];
        }

        $content = implode("\n", $rows);
        $content = iconv('UTF-8', 'windows-1251', $content);
        return $content;

    }

}
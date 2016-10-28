<?php
namespace MBH\Bundle\PackageBundle\Services;

use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use MBH\Bundle\BaseBundle\Lib\Exception;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\Validator\ConstraintValidatorFactoryInterface;
use Symfony\Component\HttpFoundation\Response;
use MBH\Bundle\BaseBundle\Lib\QueryBuilder;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\PackageBundle\Document\Order;
use MBH\Bundle\PackageBundle\Document\PackageRepository;
use Doctrine\ODM\MongoDB\DocumentRepository;

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
        'tariff' => ['title' => 'csv.type.tariff', 'method' => 'getTariff'],
        'createdAt' => ['title' => 'csv.type.createdAt', 'method' => 'getCreatedAt'],
        'createdBy' => ['title' => 'csv.type.createdBy', 'method' => 'getCreatedBy'],
    ];


    public function __construct(ContainerInterface $container)
    {
        $this->dm = $container->get('doctrine_mongodb')->getManager();
        $this->container = $container;
    }

    public function generateCsv($data, $formData)
    {
        $translator = $this->container->get('translator');
        $entities = $this->dm->getRepository('MBHPackageBundle:Package')->fetch($data);

        foreach (self::DATA as $key => $item) {
            if (!empty($formData[$key])) {

                $title[] = $translator->trans($item['title']);
            }
        }

        $rows[] = implode(',', $title);

        foreach ($entities as $entity) {
            foreach (self::DATA as $key => $item) {

                if (!empty($formData[$key])) {

                    $method = $item['method'];

                    if ($method == 'countPersons') {

                        $dataCsv[] = $entity->getAdults() + $entity->getChildren();

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

            $rows[] = implode(',', $dataCsv);
            $dataCsv = [];
        }

        $content = implode("\n", $rows);
        return $content;

    }

}
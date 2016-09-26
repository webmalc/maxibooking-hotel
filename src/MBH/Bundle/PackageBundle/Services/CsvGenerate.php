<?php
namespace MBH\Bundle\PackageBundle\Services;

use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use MBH\Bundle\BaseBundle\Lib\Exception;
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

    public function __construct(ContainerInterface $container)
    {
        $this->dm = $container->get('doctrine_mongodb')->getManager();
        $this->container = $container;
    }

    public function generateCsv($data, $formData)
    {
        $entities = $this->dm->getRepository('MBHPackageBundle:Package')->fetch($data);

        $title = [];
        ($formData['type'] == true) ? $title[] = 'Тип Брони' : null;
        ($formData['numberWithPrefix'] == true) ? $title[] = '#' : null;
        ($formData['dateBegin'] == true) ? $title[] = 'С' : null;
        ($formData['dateEnd'] == true) ? $title[] = 'По' : null;
        ($formData['tariffType'] == true) ? $title[] = 'Тип номера' : null;
        ($formData['tariffAccomodation'] == true) ? $title[] = 'Размещение' : null;
        ($formData['guests'] == true) ? $title[] = 'Плательщик' : null;
        ($formData['Adults'] == true) ? $title[] = 'Взрослых' : null;
        ($formData['Children'] == true) ? $title[] = 'Детей' : null;
        ($formData['price'] == true) ? $title[] = 'Стоимость' : null;
        ($formData['tariff'] == true) ? $title[] = 'Тариф' : null;
        ($formData['createdAt'] == true) ? $title[] = 'Дата создания' : null;
        ($formData['createdBy'] == true) ? $title[] = 'Создал' : null;

        $rows[] = implode(',', $title);

        foreach ($entities as $entity) {

            $dataCsv = [];
            if ($formData['type'] == true) {
                if ($entity->getStatus() == 'offline') {
                    $dataCsv[] = 'Оффлайн';
                } elseif ($entity->getStatus() == 'online') {
                    $dataCsv[] = 'Онлайн';
                } else {
                    $dataCsv[] = 'Channel manager';
                }
            }
            ($formData['numberWithPrefix'] == true) ? $dataCsv[] = $entity->getNumberWithPrefix() : null;
            ($formData['dateBegin'] == true) ? $dataCsv[] = $entity->getBegin()->format('d.m.Y') : null;
            ($formData['dateEnd'] == true) ? $dataCsv[] = $entity->getEnd()->format('d.m.Y') : null;
            ($formData['tariffType'] == true) ? $dataCsv[] = $entity->getRoomType()->getFullTitle() : null;

            if ($formData['tariffAccomodation'] == true) {
                if (empty($entity->getAccommodation())) {
                    $dataCsv[] = 'Не размещено';
                } else {
                    $dataCsv[] = $entity->getAccommodation()->getFullTitle();
                }
            }
            if ($formData['guests'] == true) {

                if (!empty($entity->getOrder()->getMainTourist())) {
                    $dataCsv[] = $entity->getOrder()->getMainTourist()->getFullName();
                } else {
                    $dataCsv[] = 'Нет плательщика';
                }
            }
            ($formData['Adults'] == true) ? $dataCsv[] = $entity->getAdults() : null;
            ($formData['Children'] == true) ? $dataCsv[] = $entity->getChildren() : null;
            ($formData['price'] == true) ? $dataCsv[] = $entity->getPrice() : null;
            ($formData['tariff'] == true) ? $dataCsv[] = $entity->getTariff()->getFullTitle() : null;
            ($formData['createdAt'] == true) ? $dataCsv[] = $entity->getCreatedAt()->format('d.m.Y H:i') : null;
            ($formData['createdBy'] == true) ? $dataCsv[] = $entity->getCreatedBy() : null;

            $rows[] = implode(',', $dataCsv);

        }

        $content = implode("\n", $rows);
        return $content;

    }

}
<?php
namespace MBH\Bundle\RestaurantBundle\Service;

use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use MBH\Bundle\RestaurantBundle\Document\Chair;
use MBH\Bundle\RestaurantBundle\Document\Table;
use MBH\Bundle\BaseBundle\Lib\Exception;
use Symfony\Component\Validator\ConstraintValidatorFactoryInterface;
use Symfony\Component\HttpFoundation\Response;

class TableManager
{
    /**
     * @var \Doctrine\Bundle\MongoDBBundle\ManagerRegistry
     */
    protected $dm;
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
   private $container;
    /**
     * @var \Symfony\Component\Validator\Validator;
     */
    protected $validator;

    public function __construct(DocumentManager $dm, ContainerInterface $container)
    {
        $this->dm = $dm;
        $this->container = $container;
        $this->validator = $container->get('validator');
    }

    public function generateChair($count, $type, Table $item)
    {
        $all_items = $item->getChairs()->toArray();
        $last_item = array_pop($all_items);

        if ($count) {
            $session = $this->container->get('session');
            $last_item ?  $newcount = $last_item->getFullTitle()+1 : $newcount=1;

            for ($i = $newcount; $i < $count+$newcount; $i++) {

                $errors = $this->validator->validate($item);

                if (count($errors)>0) {

                    foreach ($errors as $error)
                    {
                        $errorsString = (string) $error->getMessage();
                        $session->getFlashBag()->add('danger',$errorsString);
                    }
                    return ;
                }
                else{
                    $chair = new Chair();
                    $chair->setType($type);
                    $chair->setTable($item);
                    $chair->setFullTitle($i);
                    $this->dm->persist($chair);
                    $this->dm->flush();
                }
            }

        }
    }

}
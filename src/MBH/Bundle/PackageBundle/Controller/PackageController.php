<?php

namespace MBH\Bundle\PackageBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use MBH\Bundle\PackageBundle\Document\Package;

/**
 */
class PackageController extends Controller
{
    /**
     * @Route("/")
     * @Template()
     */
    public function indexAction()
    {
        //53be5b5070bf7659168b4567
        
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();

        $tariff = $dm->getRepository('MBHPriceBundle:Tariff')
           ->find('53be912970bf7692288b4567');
        
        $tourist = $dm->getRepository('MBHPackageBundle:Tourist')
           ->find('53be5b5070bf7659168b4567');
        
        $begin = new \DateTime('2014-11-15');
        $end = new \DateTime('2014-11-20');
        
        $package = new Package();
        $package->setBegin($begin)
                ->setEnd($end)
                ->setTariff($tariff)
                ->setFood($tariff->getHotel()->getFood()[0])
                ->setRoomType($tariff->getHotel()->getRoomTypes()[0])
                ->setMainTourist($tourist)
                ->addTourist($tourist)
        ;
        
        $errors = $this->get('validator')->validate($package);
        
        if (count($errors)) {
            foreach($errors as $error) {
                var_dump($error->getMessage());
            }
        } else {
            $dm->persist($package);
            $dm->flush($package);
            var_dump('ok');
        }
        
        
        
        return array();
    }
}

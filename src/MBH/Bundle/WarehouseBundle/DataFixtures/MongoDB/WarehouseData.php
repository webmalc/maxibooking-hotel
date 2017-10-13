<?php
/**
 * Script to initial insertion of Ware Categories (tune $cats to set example titles).
 * 
 * Usage: issue the following
 * bin/console doctrine:mongodb:fixtures:load --append --fixtures=/var/www/mbh/src/MBH/Bundle/WarehouseBundle/DataFixtures/MongoDB/
 * 
 * Jvb 15.04.2016
 * 
 */

namespace MBH\Bundle\WarehouseBundle\DataFixtures\MongoDB;

use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use MBH\Bundle\WarehouseBundle\Document\WareCategory;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class WarehouseData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
	private function cats()
    {
        return [
            $this->container->get('translator')->trans('warehouse.fixtures.mongodb.warehousedata.household_chemical'),
            $this->container->get('translator')->trans('warehouse.fixtures.mongodb.warehousedata.food'),
            $this->container->get('translator')->trans('warehouse.fixtures.mongodb.warehousedata.underwear')
        ];
    }

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
		$existingCats = $manager->getRepository('MBHWarehouseBundle:WareCategory')->findAll();
		
		// skip existing categories
		foreach ($this->cats() as $name) {
			if ($this->checkExistance($existingCats, $name)) {
				continue;
			}
			
			$cat = new WareCategory();

			$cat->setFullTitle($name)->setTitle($name)->setSystem(true);

			$manager->persist($cat);
		}
				
		$manager->flush();
    }
	
	/**
	 * Aux: iterate through all the categories and check if one we are going to insert exists.
	 * 
	 * @param WareCategory $existingCats
	 * @param string $cat
	 * @return boolean
	 */
	function checkExistance($existingCats, $cat) {
		foreach ($existingCats as $v) {
			if ($v->getFullTitle() == $cat) {
				return true;
			}
		}
		
		return false;
	}

    public function getOrder()
    {
        return 9993;
    }
	
}

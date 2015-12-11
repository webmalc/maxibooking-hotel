<?php

namespace MBH\Bundle\CashBundle\DataFixtures;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use MBH\Bundle\CashBundle\Document\CashDocumentArticle;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class CashArticleData implements FixtureInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    protected function getXmlPath()
    {
        return $this->container->get('file_locator')->locate('@MBHCashBundle/Resources/data/Новый1.csv');
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $resource = fopen($this->getXmlPath(), 'r');
        fgetcsv($resource, null, ',');
        while(($data = fgetcsv($resource, null, ',')) !== false) {
            $article = new CashDocumentArticle();
            $article->setCode($data[0]);
            $article->setTitle($data[2]);
            $manager->persist($article);
        }
        $manager->flush();
    }
}
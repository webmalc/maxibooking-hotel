<?php

namespace MBH\Bundle\CashBundle\DataFixtures\MongoDB;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use MBH\Bundle\CashBundle\Document\CashDocumentArticle;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class CashDocumentArticleData implements FixtureInterface, ContainerAwareInterface
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

        $parents = [];
        while(($data = fgetcsv($resource, null, ',')) !== false) {
            $article = new CashDocumentArticle();
            $article->setCode($data[0]);
            $article->setTitle($data[2]);
            $manager->persist($article);

            $preg = [];
            if (preg_match('/^([0-9]{2,3})\.[0-9]+/', $article->getCode(), $preg)) {
                if(isset($parents[$preg[1]])) {
                    $parentArticle = $parents[$preg[1]];
                    $article->setParent($parentArticle);
                }
            } else {
                $parents[$article->getCode()] = $article;
            }
        }

        $manager->flush();

        $manager->clear();

        $cashDocumentArticleRepository = $manager->getRepository(CashDocumentArticle::class);
        $articles = $cashDocumentArticleRepository->findAll();

        foreach($articles as $article) {
            if (!$article->getParent() && count($article->getChildren()) == 0) {
                $sameParent = new CashDocumentArticle();
                $sameParent->setCode($article->getCode());
                $sameParent->setTitle($article->getTitle());

                $article->setParent($sameParent);
                $manager->persist($sameParent);
                $manager->persist($article);
            }
        }

        $manager->flush();
    }
}
<?php

namespace Tests\Bundle\ApiBundle\Controller;

use MBH\Bundle\BaseBundle\Lib\Test\WebTestCase;
use MBH\Bundle\PackageBundle\Document\Package;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PackageApiControllerTest extends WebTestCase
{
    public static function setUpBeforeClass()
    {
        self::baseFixtures();
    }

    public static function tearDownAfterClass()
    {
        self::clearDB();
    }

    public function testPackagesActionWithoutParams()
    {
        $this->client->request('GET', '/api/v1/packages/');
        $this->isSuccessful($this->client->getResponse(), true, 'application/json');
        $decodedContent = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals(true, $decodedContent['success']);
        $this->assertEquals(15, count($decodedContent['data']));
    }

    public function testPackagesAction()
    {
        $limit = 7;
        $skip = 3;
        $this->client->request('GET', '/api/v1/packages/?limit=' . $limit . '&skip=' . $skip);

        $this->isSuccessful($this->client->getResponse(), true, 'application/json');
        $decodedContent = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals(true, $decodedContent['success']);

        $packages = $decodedContent['data'];
        $packagesInDB = $this
            ->getContainer()
            ->get('doctrine.odm.mongodb.document_manager')
            ->getRepository('MBHPackageBundle:Package')
            ->findBy([], ['createdAt' => 'desc'], $limit, $skip);

        $this->assertEquals(count($packagesInDB), count($packages));

        /** @var Package $expected */
        $expected = current($packagesInDB);
        $actual = current($packages);
        $this->assertEquals($expected->getNumberWithPrefix(), $actual['numberWithPrefix']);
        $this->assertEquals($expected->getId(), $actual['id']);
        $this->assertEquals($expected->getAdults(), $actual['adults']);
        $this->assertEquals($expected->getStatus(), $actual['status']);
        $this->assertEquals($expected->getBegin()->format('d.m.Y'), $actual['begin']);
        $this->assertEquals($expected->getEnd()->format('d.m.Y'), $actual['end']);
        $this->assertEquals($expected->getChildren(), $actual['children']);
        $this->assertEquals([
            'id' => $expected->getRoomType()->getId(),
            'name' => $expected->getRoomType()->getName()
        ], $actual['roomType']);
    }

    public function testConfirmOrder()
    {
        $dm = $this->getDm();
        $order = $dm->getRepository('MBHPackageBundle:Order')->findOneBy([]);
        $order->setConfirmed(false);
        $dm->flush();

        $this->client->request('POST', '/api/v1/packages/confirm_order/' . $order->getFirstPackage()->getId());
        $this->isSuccessful($this->client->getResponse(), true, 'application/json');

        $dm->refresh($order);
        $this->assertTrue($order->getConfirmed());
    }

    public function testConfirmNonExistentOrder()
    {
        $this->client->request('POST', '/api/v1/packages/confirm_order/' . 'id-of-non-existent-package');
        $this->assertEquals(404, $this->client->getResponse()->getStatusCode());
    }
}
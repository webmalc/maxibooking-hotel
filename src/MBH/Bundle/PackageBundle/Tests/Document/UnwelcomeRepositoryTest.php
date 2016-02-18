<?php

namespace MBH\Bundle\PackageBundle\Tests\Document;

use MBH\Bundle\ClientBundle\Service\Mbhs;
use MBH\Bundle\PackageBundle\Document\Tourist;
use MBH\Bundle\PackageBundle\Document\Unwelcome;
use MBH\Bundle\PackageBundle\Document\UnwelcomeRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class UnwelcomeRepositoryTest
 * @package MBH\Bundle\PackageBundle\Tests\Document

 */
class UnwelcomeRepositoryTest extends WebTestCase
{
    /**
     * @var UnwelcomeRepository
     */
    protected $repository;
    /**
     * @var Mbhs
     */
    protected $mbhs;

    public function setUp()
    {
        self::$kernel->boot();
        //$this->repository = self::$kernel->getContainer()->get('mbh.package.unwelcome_repository');

        $this->mbhs = $this->getMockBuilder(Mbhs::class)->setConstructorArgs([self::$kernel->getContainer()])->getMock();
        $unwelcomeRepository = new UnwelcomeRepository($this->mbhs);
        $this->repository = $unwelcomeRepository;
    }

    public function testFindByTourist()
    {
        /*$this->mbhs->expects($this->any())->method('findUnwelcomeListByTourist')->willReturn([
            'unwelcomeList' => [['comment' => 'test']]
        ]);
        $tourist = new Tourist();
        $unwelcomeHistory = $this->repository->findByTourist($tourist);
        $items = $unwelcomeHistory->getItems();
        $unwelcome = reset($items);
        $this->assertEquals('test', $unwelcome->getComment());*/
    }
}

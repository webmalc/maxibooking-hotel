<?php

namespace Tests\Bundle\ApiBundle\Services;

use MBH\Bundle\ApiBundle\Service\ApiRequestManager;
use MBH\Bundle\BaseBundle\Lib\Test\WebTestCase;
use MBH\Bundle\OnlineBundle\Services\ApiResponseCompiler;
use MBH\Bundle\PackageBundle\Document\Order;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\ParameterBag;

class ApiRequestManagerTest extends WebTestCase
{
    /** @var ContainerInterface */
    private $container;
    /** @var ApiResponseCompiler */
    private $responseCompiler;
    /** @var ApiRequestManager */
    private $apiRequestManager;

    public function setUp()
    {
        $this->container = $this->getContainer();
        $this->responseCompiler = $this->container->get('mbh.api_response_compiler');
        $this->apiRequestManager = $this->container->get('mbh.api_request_manager');
    }

    /**
     * @throws \ReflectionException
     */
    public function testGetPackageCriteria()
    {
        $bag = new ParameterBag();
        $bag->set(ApiRequestManager::LIMIT_PARAM, 5);
        $bag->set(ApiRequestManager::SKIP_PARAM, 4);
        $bag->set(ApiRequestManager::CRITERIA_PARAM, [
            'isConfirmed' => "true",
            'status' => Order::CHANNEL_MANAGER_STATUS,
            'begin' => (new \DateTime())->format('d.m.Y')
        ]);

        $criteria = $this
            ->apiRequestManager
            ->getPackageCriteria($bag, $this->responseCompiler);
        $this->assertEquals(new \DateTime('midnight'), $criteria->begin);
        $this->assertEquals(true, $criteria->isConfirmed);
        $this->assertEquals(5, $criteria->limit);
        $this->assertEquals(4, $criteria->skip);
        $this->assertEquals(Order::CHANNEL_MANAGER_STATUS, $criteria->status);
        $this->assertTrue($this->responseCompiler->isSuccessful());
    }

    public function testCheckIsArrayFields()
    {
        $bag = new ParameterBag([
            'first' => [123, 34],
            'second' => [122, 'sfasdf']
        ]);
        $fieldNames = ['first', 'second'];

        $this->apiRequestManager->checkIsArrayFields($bag, $fieldNames, $this->responseCompiler);
        $this->assertTrue($this->responseCompiler->isSuccessful());

        $bag = new ParameterBag(['first' => [13123], 'second' => 12313]);
        $this->apiRequestManager->checkIsArrayFields($bag, $fieldNames, $this->responseCompiler);
        $this->assertFalse($this->responseCompiler->isSuccessful());
        $expectedError = $this->container
            ->get('translator')
            ->trans($this->responseCompiler::FIELD_MUST_BE_TYPE_OF_ARRAY, [ '%field%' => 'second']);
        $this->assertEquals(['second' => $expectedError], $this->responseCompiler->getErrors());
    }

    public function testCheckMandatoryFields()
    {
        $bag = new ParameterBag([
            'first' => [123, 34],
            'second' => [122, 'sfasdf'],
        ]);
        $fieldNames = ['first', 'third'];

        $this->apiRequestManager->checkMandatoryFields($bag, $fieldNames, $this->responseCompiler);
        $this->assertFalse($this->responseCompiler->isSuccessful());

        $expectedError = $this
            ->container
            ->get('translator')
            ->trans(ApiResponseCompiler::MANDATORY_FIELD_MISSING, ['%field%' => 'third']);
        $this->assertEquals(['third' => $expectedError], $this->responseCompiler->getErrors());
    }
}
<?php

namespace Tests\Bundle\BaseBundle\Service;

use MBH\Bundle\BaseBundle\Service\YmlManager;
use PHPUnit\Framework\TestCase;

class YmlManagerTest extends TestCase
{
    const TEST_FILE_RELATIVE_PATH = '/../Resources/data/yml_manager_test_data.yml';
    const ADDED_PARAMETER_NAME = 'added_param';
    const PARENT_PARAMETER_NAME = 'parent_param';

    private $testFilePath;

    public function setUp()
    {
        $this->testFilePath = __DIR__ . self::TEST_FILE_RELATIVE_PATH;
    }

    public function testGetParsedData()
    {
        $manager = $this->getYmlManager();
        $parsedData = $manager->getParsedData($this->testFilePath);

        $this->assertTrue(is_array($parsedData));
        $this->assertEquals($parsedData['first_param'], 123);
        $this->assertEquals($parsedData['second_param'], 546);
        $this->assertTrue(is_array($parsedData['third_param_enclosed']));

        $enclosedArray = $parsedData['third_param_enclosed'];
        $this->assertTrue(is_array($enclosedArray['enclosed_arr']));
        $this->assertEquals($enclosedArray['enclosed_arr'], ['enclosed1', 'enclosed2']);
    }

    public function testGetParameter()
    {
        $manager = $this->getYmlManager();
        $testParameter = $manager->getParameter($this->testFilePath, 'fourth_param');
        $this->assertEquals($testParameter, 'some string test data');
    }

    public function testGetSingleEnclosedParameter()
    {
        $manager = $this->getYmlManager();
        $testParameter = $manager->getEnclosedParameter($this->testFilePath, 'third_param_enclosed', 'enclosed_arr');
        $this->assertEquals($testParameter, ['enclosed1', 'enclosed2']);
    }

    public function testSetSingleParameter()
    {
        $manager = $this->getYmlManager();

        $additionResult = $manager->setSingleParameter($this->testFilePath, self::ADDED_PARAMETER_NAME, 'added_param_value');
        $this->assertTrue($additionResult);

        $addedValue = $manager->getParameter($this->testFilePath, self::ADDED_PARAMETER_NAME);
        $this->assertEquals($addedValue, 'added_param_value');
    }

    public function testSetSingleEnclosedParameter()
    {
        $manager = $this->getYmlManager();

        $additionResult = $manager->setSingleEnclosedParameter(
            $this->testFilePath,
            self::PARENT_PARAMETER_NAME,
            self::ADDED_PARAMETER_NAME,
            'enclosed_param_value'
        );
        $this->assertTrue($additionResult);

        $addedValue = $manager->getEnclosedParameter($this->testFilePath, self::PARENT_PARAMETER_NAME, self::ADDED_PARAMETER_NAME);
        $this->assertEquals($addedValue, 'enclosed_param_value');
    }

    public function testUnsetSingleParameter()
    {
        $manager = $this->getYmlManager();
        $this->assertTrue($manager->unsetSingleParameter($this->testFilePath, self::ADDED_PARAMETER_NAME));
        $this->assertNull($manager->getParameter($this->testFilePath, self::ADDED_PARAMETER_NAME));
    }

    /**
     * @return YmlManager
     */
    private function getYmlManager()
    {
        return new YmlManager();
    }
}

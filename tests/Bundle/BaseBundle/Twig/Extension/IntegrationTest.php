<?php
/**
 * Created by PhpStorm.
 * Date: 21.06.18
 */

namespace Tests\Bundle\BaseBundle\Twig\Extension;


use MBH\Bundle\BaseBundle\Twig\Extension;


class IntegrationTest extends \Twig_Test_IntegrationTestCase
{
    public function getExtensions()
    {
        $container = $this->createMock(\Symfony\Component\DependencyInjection\ContainerInterface::class);
        $container->expects($this->any())
            ->method('get')
            ->will($this->returnCallback(function ($name) {
                if ($name === 'doctrine_mongodb') {
                    return new class {
                        public function getManager()
                        {
                            return '';
                        }
                    };
                }
                return '';
            }));

        return [
            new Extension($container),
        ];
    }

    public function getFixturesDir()
    {
        return __DIR__.'/Fixtures/';
    }

    /**
     * заглушка , т.к. не нашел что тестировать
     */
    public function testLegacyIntegration(
        $file = null,
        $message = null,
        $condition = null,
        $templates = null,
        $exception = null,
        $outputs = null
    )
    {
        $this->assertTrue(true);
    }
}
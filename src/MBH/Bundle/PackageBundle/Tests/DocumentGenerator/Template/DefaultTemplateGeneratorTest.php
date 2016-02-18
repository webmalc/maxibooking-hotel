<?php

namespace MBH\Bundle\PackageBundle\Tests\DocumentGenerator\Template;

use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\DocumentGenerator\Template\DefaultTemplateGenerator;
use MBH\Bundle\PackageBundle\DocumentGenerator\Template\TemplateGeneratorFactory;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

/**
 * Class DefaultTemplateGeneratorTest
 * @package MBH\Bundle\PackageBundle\Tests\DocumentGenerator\Template

 */
class DefaultTemplateGeneratorTest extends WebTestCase
{
    /**
     * @var DefaultTemplateGenerator
     */
    private $generator;

    public function setUp()
    {
        self::$kernel->boot();
        $container = self::$kernel->getContainer();
        $session = new Session(new MockArraySessionStorage());
        $request = new Request();
        $request->setSession($session);
        $container->get('request_stack')->push($request);
        $this->generator = new DefaultTemplateGenerator(TemplateGeneratorFactory::TYPE_CONFIRMATION);
        $this->generator->setContainer($container);
    }

    private function getFormData()
    {
        $package = new Package();

        return [
            'package' => $package,
            'vegaDocumentTypes' => [],
            'total' => 1,
        ];
    }

    public function testGenerateResponse()
    {
        $formData = $this->getFormData();
        $response = $this->generator->generateResponse($formData);
        $this->assertTrue($response instanceof Response);
        //$this->assertTrue(strpos($response->getContent(), 'Подтверждение') !== false);
    }
}

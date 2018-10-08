<?php
/**
 * Created by PhpStorm.
 * Date: 01.10.18
 */

namespace Tests\Bundle\ClientBundle\Controller;


use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\Lib\Test\WebTestCase;
use MBH\Bundle\ClientBundle\DataFixtures\MongoDB\DocumentTemplateData;
use MBH\Bundle\ClientBundle\Document\DocumentTemplate;
use MBH\Bundle\ClientBundle\Form\DocumentTemplateType;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PackageBundle\Document\Order;
use MBH\Bundle\PackageBundle\Document\Package;
use Psr\Container\ContainerInterface;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Class DocumentTemplateControllerTest
 * @package Tests\Bundle\ClientBundle\Controller
 */
class DocumentTemplateControllerTest extends WebTestCase
{
    public const URL_LIST = '/management/client/templates/';

    /**
     * @var DocumentManager
     */
    private static $dm;

    /**
     * @var DocumentTemplate[]
     */
    private static $holderDocumentTemplates = [];

    /**
     * @var Crawler
     */
    private static $crawler;

    /**
     * @var Package
     */
    private static $packageWithoutData;

    /**
     * Используется в тестах про добавление через форму
     *
     * @var DocumentTemplate|null
     */
    private static $documentTemplate;

    public static function setUpBeforeClass()
    {
        self::baseFixtures();

        $container = self::getContainerStat();

        $collectionName = ltrim(strrchr(DocumentTemplate::class, '\\'),'\\');

        $container->get('mbh.mongo')->dropCollection($collectionName);

        $dm = static::$dm = $container->get('doctrine.odm.mongodb.document_manager');

        self::loadAllDefaultDocumentTemplate($container, $dm);
        self::setPackageWithEmptyData($dm);
    }

    public static function tearDownAfterClass()
    {
        self::clearDB();
    }

    public function testStatusCode()
    {
        $this->getListCrawler(self::URL_LIST);

        $this->assertStatusCodeWithMsg(self::URL_LIST, 200);
    }

    /**
     * Этот тест фейлится если не установлен Wkhtmltopdf
     */
    public function testInstallWkhtmltopdf()
    {
        $container = self::getContainerStat();

        $install = true;
        $e = null;
        try {
            $container->get('knp_snappy.pdf')
                ->getOutputFromHtml('<html><body><h1>TEST</h1></body>></html>');
        } catch (\Throwable $e) {
            $install = false;
        }

        $this->assertTrue(
            $install,
            $e !== null
                ?  'Most likely not install Wkhtmltopdf. Error: '.$e->getMessage()
                : ''
        );
    }

    /**
     * @return iterable
     */
    public function getDefaultTemplateName(): iterable
    {
        foreach (DocumentTemplateData::DOCUMENT_TEMPLATE_DATA as $localeName => $data) {
            foreach ($data as $name => $templateFile) {

                $nameWithLocale = sprintf('%s_%s', $localeName, $name);

                yield $name => [$nameWithLocale, $name, $templateFile];
            }
        }
    }

    /**
     * @param string $title
     * @dataProvider getDefaultTemplateName
     */
    public function testDefaultDocumentAvailabilityInDB(string $title, string $originalTitle, string $fileName)
    {
        $template = $this->getDefaultDocumentTemplate($title);

        $templateInDb = $this->getDocumentManager()->getRepository('MBHClientBundle:DocumentTemplate')
            ->findOneBy(['title' => $title]);

        $this->assertNotNull($templateInDb, sprintf("Not found document in db for name '%s'", $title));

        $this->assertTrue(
            $template === $templateInDb,
            sprintf('Document from the DB not equal document from the array.')
        );
    }

    /**
     * @param string $title
     * @depends      testDefaultDocumentAvailabilityInDB
     * @dataProvider getDefaultTemplateName
     */
    public function testDefaultDocumentAvailabilityForEdit(string $title, string $originalTitle, string $fileName)
    {
        $template = $this->getDefaultDocumentTemplate($title);

        $crawler = $this->getList();

        $link = $crawler->filter('a[href*="' . $template->getId() . '"]')->link();

        $crawler = $this->client->click($link);

        $form = $crawler->filter('form[name="' . DocumentTemplateType::FORM_NAME . '"]')->form();
        $titleInForm = $form->get(DocumentTemplateType::FORM_NAME)['title']->getValue();

        $this->assertEquals(
            200,
            $this->client->getResponse()->getStatusCode(),
            sprintf('An error occurred while opening the document: %s.', $title)
        );

        $this->assertEquals(
            $title,
            $titleInForm,
            sprintf(
                'The title of the document in db "%s" and on the open page "%s" do not match.',
                $title,
                $titleInForm)
        );
    }

    /**
     * @param string $title
     * @test
     * @depends testInstallWkhtmltopdf
     * @depends testDefaultDocumentAvailabilityForEdit
     * @dataProvider getDefaultTemplateName
     */
    public function defaultDocumentAvailabilityForViewWithPackageWithoutData(string $title, string $originalTitle, string $fileName)
    {
        $template = $this->getDefaultDocumentTemplate($title);

        $url = $this->linkForView($template, $this->getPackageWithoutData());

        $this->client->request('GET', $url);

        $format = "The expected response status code \"200\" from the URL with document \"%s\" , received \"%s\".";
        $format .= "\nError message:\n<BEGIN>%s<END>";
        $response = $this->client->getResponse();

        $text = '';

        if ($response->getStatusCode() !== 200) {
            $crawler = new Crawler($response->getContent());

            $text = $crawler->filter('h1.exception-message')->text();
        }

        $this->assertEquals(
            200,
            $response->getStatusCode(),
            sprintf($format, $originalTitle, $response->getStatusCode(), $text)
        );

        $this->assertContains(
            'application/pdf',
            $this->client->getResponse()->headers->get('Content-Type')
        );
    }

    /**
     * @depends testStatusCode
     */
    public function testAddEmptyDataTemplate()
    {
        $crawler = $this->getListCrawler(self::URL_LIST . 'new');

        $form = $crawler->filter('form[name="' . DocumentTemplateType::FORM_NAME . '"]')->form();

        $this->client->submit($form);

        $this->assertValidationErrors(
            ['data.title','data.content'],
            $this->client->getContainer()
        );
    }

    /**
     * @depends testStatusCode
     */
    public function testAddValidDataTemplate()
    {
        $url = self::URL_LIST . 'new';
        $this->client->followRedirects(true);

        $crawler = $this->getListCrawler($url);

        $form = $crawler->filter('form[name="' . DocumentTemplateType::FORM_NAME . '"]')->form();

        $newTitle = 'testTitle_' . time();

        $form->setValues(
            [
                DocumentTemplateType::FORM_NAME . '[title]'   => $newTitle,
                DocumentTemplateType::FORM_NAME . '[content]' => 'testContent',
            ]
        );

        $this->client->submit($form);

        static::$documentTemplate = $newTemplate = $this->getDocumentManager()
            ->getRepository('MBHClientBundle:DocumentTemplate')
            ->findOneBy([
                'title' => $newTitle
            ]);

        $this->assertStatusCodeWithMsg($url, 200);

        $this->assertNotNull(
            $newTemplate,
            sprintf('Not found new template in DB with title "%s".', $newTitle)
        );
    }

    /**
     * @depends testAddValidDataTemplate
     */
    public function testViewNewDocumentTemplate()
    {
        $url = $this->linkForView($this->getDocumentTemplate(),$this->getPackageWithoutData());

        $this->client->request('GET', $url);

        $this->assertStatusCodeWithMsg($url, 200);

        $this->assertContains(
            'application/pdf',
            $this->client->getResponse()->headers->get('Content-Type')
        );
    }

    /**
     * @depends testAddValidDataTemplate
     */
    public function testAvailbilityLinkNewDocumentTemplateInList()
    {
        $crawler = $this->getListCrawler(self::URL_LIST);

        $amountLink = $crawler->filter('a[href*="edit/' . $this->getDocumentTemplate()->getId() . '"]')->count();

        $expectedLink = 2;

        $this->assertEquals(
            $expectedLink,
            $amountLink,
            'Not found link for new document template in the list.'
        );
    }

    /**
     * @depends testAddValidDataTemplate
     */
    public function testEditNewDocumentTemplate()
    {
        $url = self::URL_LIST . 'edit/' . $this->getDocumentTemplate()->getId();
        $this->client->followRedirects(true);

        $crawler = $this->getListCrawler($url);

        $form = $crawler->filter('form[name="' . DocumentTemplateType::FORM_NAME . '"]')->form();

        $editTitle = 'testEditTitle_' . time();

        $form->setValues(
            [
                DocumentTemplateType::FORM_NAME . '[title]'   => $editTitle,
            ]
        );

        $this->client->submit($form);

        $crawler = $this->getListCrawler($url);

        $form = $crawler->filter('form[name="' . DocumentTemplateType::FORM_NAME . '"]')->form();

        $formTitle = $form->get(DocumentTemplateType::FORM_NAME)['title'];

        $this->assertEquals($editTitle, $formTitle->getValue());
    }

    /**
     * @depends testEditNewDocumentTemplate
     */
    public function testRemoveNewDocumentTemplate()
    {
        $url = self::URL_LIST . 'delete/' . $this->getDocumentTemplate()->getId();

        $this->client->request('GET', $url);

        $crawler = $this->getListCrawler(self::URL_LIST);

        $amountLink = $crawler->filter('a[href*="edit/' . $this->getDocumentTemplate()->getId() . '"]')->count();

        $expectedLink = 0;

        $this->assertEquals(
            $expectedLink,
            $amountLink,
            'Found link for "new document template" in the list after remove.'
        );
    }

    /**
     * @return DocumentTemplate
     */
    private function getDocumentTemplate(): DocumentTemplate
    {
        return self::$documentTemplate;
    }

    /**
     * @param DocumentTemplate $template
     * @param Package $package
     * @return string
     */
    private function linkForView(DocumentTemplate $template, Package $package): string
    {
        return self::URL_LIST . 'show/' . $template->getId() . '/order/' . $package->getId();
    }

    /**
     * @return DocumentManager
     */
    private function getDocumentManager(): DocumentManager
    {
        return static::$dm;
    }

    private function getDefaultDocumentTemplate(string $title) : DocumentTemplate
    {
        return static::$holderDocumentTemplates[$title];
    }

    /**
     * Это убирает с каждой итерации в тесте testDefaultDocumentAvailabilityForEdit ~ 1.5 сек
     *
     * @return Crawler
     */
    private function getList(): Crawler
    {
        if (static::$crawler === null) {
            static::$crawler = $this->getListCrawler(self::URL_LIST);
        }

        return static::$crawler;
    }

    /**
     * @return Package
     */
    private function getPackageWithoutData(): Package
    {
        return self::$packageWithoutData;
    }

    /**
     * @param ContainerInterface $container
     * @param DocumentManager $dm
     */
    private static function loadAllDefaultDocumentTemplate(ContainerInterface $container, DocumentManager $dm): void
    {
        foreach (DocumentTemplateData::DOCUMENT_TEMPLATE_DATA as $localeName => $data) {
            foreach ($data as $name => $templateFile) {
                $filePath = $filePath = DocumentTemplateData::generateFilePath($container, $templateFile);

                $nameWithLocale = sprintf('%s_%s', $localeName, $name);

                $content = file_get_contents($filePath);
                $template = (new DocumentTemplate())
                    ->setTitle($nameWithLocale)
                    ->setContent($content)
                    ->setIsDefault(true);
                $dm->persist($template);

                static::$holderDocumentTemplates[$nameWithLocale] =  $template;
            }
        }

        $dm->flush();
    }

    /**
     * @param DocumentManager $dm
     */
    private static function setPackageWithEmptyData(DocumentManager $dm): void
    {
        $package = $dm->getRepository('MBHPackageBundle:Package')
            ->findOneBy([
                'isEnabled'   => true,
            ]);

        /** @var Order $order */
        $order = $package->getOrder();
        $order->setOrganization();
        $order->setMainTourist();

        $dm->flush($order);
        $dm->refresh($package);

        foreach ($package->getTourists() as $tourist) {
            $package->removeTourist($tourist);
        }

        foreach ($package->getServices() as $service) {
            $package->removeService($service);
        }

        $hotel = new Hotel();
        $hotel->setTitle('EmptyDataHotel');

        $dm->persist($hotel);
        $dm->flush();

        /** @var RoomType $roomType */
        $roomType = $package->getRoomType();
        $roomType->setHotel($hotel);

        $dm->flush($roomType);
        $dm->refresh($package);

        $dm->flush($package);

        static::$packageWithoutData = $package;
    }
}
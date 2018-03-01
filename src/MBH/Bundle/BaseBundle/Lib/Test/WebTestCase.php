<?php
namespace MBH\Bundle\BaseBundle\Lib\Test;

use Liip\FunctionalTestBundle\Test\WebTestCase as Base;
use MBH\Bundle\BaseBundle\Lib\Exception;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Form;

abstract class WebTestCase extends Base
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var string
     */
    private $listUrl;

    /**
     * @var string
     */
    private $listContainer = 'table ';

    /**
     * @var array
     */
    private $listHeaders = [];

    /**
     * @var string
     */
    private $newUrl;

    /**
     * @var string
     */
    private $newTitle;

    /**
     * @var string
     */
    private $editTitle;

    /**
     * @var string
     */
    private $formName;

    /**
     * @var array
     */
    private $newFormValues;

    /**
     * @var array
     */
    private $editFormValues;

    /**
     * @var array
     */
    private $newFormErrors;

    /**
     * @var array
     */
    private $editFormErrors;

    /**
     * @var int
     */
    private $listItemsCount;

    /**
     * @return string
     */
    public function getListContainer(): string
    {
        return $this->listContainer;
    }

    /**
     * @param string $listContainer
     * @return WebTestCase
     */
    public function setListContainer(string $listContainer): WebTestCase
    {
        $this->listContainer = $listContainer;
        return $this;
    }
    
    
    /**
     * @return array
     */
    public function getListHeaders(): array
    {
        return $this->listHeaders;
    }

    /**
     * @param array $listHeaders
     * @return WebTestCase
     */
    public function setListHeaders(array $listHeaders): WebTestCase
    {
        $this->listHeaders = $listHeaders;
        return $this;
    }

    public function setAjaxList(): self
    {
        $this->listHeaders = array_merge($this->listHeaders, [
            'HTTP_X-Requested-With' => 'XMLHttpRequest'
        ]);
        $this->listContainer = '';
        return $this;
    }

    /**
     * @return int
     */
    public function getListItemsCount(): int
    {
        if (!is_numeric($this->listItemsCount)) {
            throw new \InvalidArgumentException('not valid listItemsCount');
        }
        return $this->listItemsCount;
    }

    /**
     * @param int $listItemsCount
     */
    public function setListItemsCount(int $listItemsCount)
    {
        $this->listItemsCount = $listItemsCount;
    }

    /**
     * @return array
     */
    public function getEditFormValues(): array
    {
        if (!$this->editFormValues) {
            throw new \InvalidArgumentException('not valid editFormValues');
        }
        return $this->editFormValues;
    }

    /**
     * @param array $editFormValues
     * @return WebTestCase
     */
    public function setEditFormValues(array $editFormValues): WebTestCase
    {
        $this->editFormValues = $editFormValues;
        return $this;
    }

    /**
     * @return array
     */
    public function getNewFormValues(): array
    {
        if (!$this->newFormValues) {
            throw new \InvalidArgumentException('not valid newFormValues');
        }
        return $this->newFormValues;
    }

    /**
     * @param array $newFormValues
     * @return WebTestCase
     */
    public function setNewFormValues(array $newFormValues): WebTestCase
    {
        $this->newFormValues = $newFormValues;
        return $this;
    }

    /**
     * @return array
     */
    public function getEditFormErrors(): array
    {
        if (!$this->editFormErrors) {
            throw new \InvalidArgumentException('not valid editFormErrors');
        }
        return $this->editFormErrors;
    }

    /**
     * @param array $editFormErrors
     * @return WebTestCase
     */
    public function setEditFormErrors(array $editFormErrors): WebTestCase
    {
        $this->editFormErrors = $editFormErrors;
        return $this;
    }

    /**
     * @return array
     */
    public function getNewFormErrors(): array
    {
        if (!$this->newFormErrors) {
            throw new \InvalidArgumentException('not valid newFormErrors');
        }
        return $this->newFormErrors;
    }

    /**
     * @param array $newFormErrors
     * @return WebTestCase
     */
    public function setNewFormErrors(array $newFormErrors): WebTestCase
    {
        $this->newFormErrors = $newFormErrors;
        return $this;
    }

    /**
     * @return string
     */
    public function getListUrl(): string
    {
        if (!$this->listUrl) {
            throw new \InvalidArgumentException('not valid listUrl');
        }
        return $this->listUrl;
    }

    /**
     * @param string $listUrl
     * @return WebTestCase
     */
    public function setListUrl(string $listUrl): WebTestCase
    {
        $this->listUrl = $listUrl;
        return $this;
    }

    /**
     * @return string
     */
    public function getNewUrl(): string
    {
        if (!$this->newUrl) {
            throw new \InvalidArgumentException('not valid newUrl');
        }
        return $this->newUrl;
    }

    /**
     * @param string $newUrl
     * @return WebTestCase
     */
    public function setNewUrl(string $newUrl): WebTestCase
    {
        $this->newUrl = $newUrl;
        return $this;
    }

    /**
     * @return string
     */
    public function getNewTitle(): string
    {
        if (!$this->newTitle) {
            throw new \InvalidArgumentException('not valid newTitle');
        }
        return $this->newTitle;
    }

    /**
     * @param string $newTitle
     * @return WebTestCase
     */
    public function setNewTitle(string $newTitle): WebTestCase
    {
        $this->newTitle = $newTitle;
        return $this;
    }

    /**
     * @return string
     */
    public function getEditTitle(): string
    {
        if (!$this->editTitle) {
            throw new \InvalidArgumentException('not valid editTitle');
        }
        return $this->editTitle;
    }

    /**
     * @param string $editTitle
     * @return WebTestCase
     */
    public function setEditTitle(string $editTitle): WebTestCase
    {
        $this->editTitle = $editTitle;
        return $this;
    }

    /**
     * @return string
     */
    public function getFormName(): string
    {
        if (!$this->formName) {
            throw new \InvalidArgumentException('not valid formName');
        }
        return $this->formName;
    }

    /**
     * @param string $formName
     * @return WebTestCase
     */
    public function setFormName(string $formName): WebTestCase
    {
        $this->formName = $formName;
        return $this;
    }

    /**
     * Run console command
     * @param string $name
     */
    public static function command(string $name)
    {
        self::bootKernel();
        $application = new Application(self::$kernel);
        $application->setAutoExit(false);

        $input = new ArrayInput(['command' => $name]);
        $output = new NullOutput();
        $application->run($input, $output);
    }

    /**
     * Load base fixtures
     */
    public static function baseFixtures()
    {
        self::clearDB();
        self::command('doctrine:mongodb:schema:create');
        self::command('mbh:base:fixtures');
    }

    /**
     * Drop database
     */
    public static function clearDB()
    {
        self::command('doctrine:mongodb:schema:drop');
    }

    /**
     * Base setup
     */
    public function setUp()
    {
        $this->client = self::makeClient(true);
    }

    /**
     * @param string $method
     * @param string $url
     * @return \Symfony\Component\DomCrawler\Crawler
     */
    public function getListCrawler($url = null, $method = 'GET'): Crawler
    {
        $url = $url ?? $this->getListUrl();
        return $this->client->request($method, $url, [], [], $this->getListHeaders());
    }

    /**
     * @param string $formName
     * @param array $values
     * @return array
     */
    public static function prepareFormValues(string $formName, array $values): array
    {
        return array_combine(
            array_map(function ($v) use ($formName) {
                return $formName . '[' . $v . ']';
            }, array_keys($values)),
            array_map(function ($v) {
                if ($v instanceof \DateTime) {
                    $v = $v->format('d.m.Y');
                }
                if ($v instanceof \MBH\Bundle\BaseBundle\Document\Base) {
                    $v = $v->getId();
                }
                return $v;
            }, $values)
        );
    }

    /**
     * @param string|null $url
     * @param string|null $title
     * @param int|null $count
     */
    protected function listBaseTest(string $url = null, string $title = null, int $count = null)
    {
        $title = $title ?? $this->getNewTitle();
        $count = $count ?? $this->getListItemsCount();

        $crawler = $this->getListCrawler($url);
        $this->assertSame(1, $crawler->filter($this->getListContainer() . 'a:contains("' . $title . '")')->count());
        $this->assertSame($count + 1, $crawler->filter($this->getListContainer() . 'a[rel="main"]')->count());
    }

    /**
     * @param string|null $url
     * @param string|null $title
     * @param int|null $count
     */
    protected function deleteBaseTest(string $url = null, string $title = null, int $count = null)
    {
        $url = $url ?? $this->getListUrl();
        $title = $title ?? $this->getEditTitle();
        $count = $count ?? $this->getListItemsCount();
        $this->clickLinkInList($url, ' a[data-text="Вы действительно хотите удалить запись «' . $title . '»?"]', true);
        $this->assertSame($count, $this->getListCrawler()->filter($this->getListContainer() . 'a[rel="main"]')->count());
    }

    /**
     * @param array|null $values
     * @param array|null $errors
     * @param string|null $url
     * @param string|null $title
     * @param string|null $formName
     */
    protected function newFormBaseTest(array $values = null, array $errors = null, string $url = null, string $title = null, string $formName = null)
    {
        $url = $url ?? $this->getNewUrl();
        $title = $title ?? $this->getNewTitle();
        $formName = $formName ?? $this->getFormName();
        $values = $values ?? $this->getNewFormValues();
        $errors = $errors ?? $this->getNewFormErrors();

        $crawler = $this->client->request('GET', $url);
        $this->assertStatusCode(200, $this->client);
        $formClass = 'form[name="' . $formName . '"]';

        //send invalid form
        $form = $crawler->filter($formClass)->form();
        $crawler = $this->client->submit($form);
        $this->assertStatusCode(200, $this->client);
        $this->assertValidationErrors($errors, $this->client->getContainer());

        //send valid form
        $form = $crawler->filter($formClass)->form();
        $form->setValues(self::prepareFormValues($formName, $values));
        $this->client->submit($form);
        $this->client->followRedirect();

        //check saved object
        $this->assertSame(1, $this->getListCrawler()->filter($this->getListContainer() . 'a:contains("' . $title . '")')->count());
    }

    /**
     * @param array|null $values
     * @param string|null $url
     * @param string|null $title
     * @param string|null $titleEdited
     * @param string|null $formName
     */
    protected function editFormBaseTest(array $values = null, string $url = null, string $title = null, string $titleEdited = null, string $formName = null)
    {
        $title = $title ?? $this->getNewTitle();
        $titleEdited = $titleEdited ?? $this->getEditTitle();
        $formName = $formName ?? $this->getFormName();
        $values = $values ?? $this->getEditFormValues();

        $crawler = $this->clickLinkInList($url, 'a:contains("' . $title . '")');

        $form = $crawler->filter('form[name="' . $formName . '"]')->form();

        $form->setValues(self::prepareFormValues($formName, $values));

        $this->client->submit($form);
        $this->client->followRedirect();

        //check saved object
        $this->assertSame(1, $this->getListCrawler()->filter($this->getListContainer() . 'a:contains("' . $titleEdited . '")')->count());
    }

    /**
     * @param string $url
     * @param string $filter
     * @param bool $redirect
     * @return \Symfony\Component\DomCrawler\Crawler
     */
    protected function clickLinkInList(string $url = null, string $filter, bool $redirect = false)
    {
        $crawler = $this->getListCrawler($url);
        $link = $crawler->filter($this->getListContainer() . $filter)->link();

        $crawler = $this->client->click($link);
        if ($redirect) {
            $crawler = $this->client->followRedirect();
        }

        return $crawler;
    }

    /**
     * @param Form $form
     * @param array $values
     * @return bool
     */
    public static function checkValuesInForm(Form $form, array $values): bool
    {
        $formValues = $form->getValues();
        foreach ($values as $key => $value) {
            if (!isset($formValues[$key]) || $value != $formValues[$key]) {
                return false;
            }
        }
        return true;
    }
}
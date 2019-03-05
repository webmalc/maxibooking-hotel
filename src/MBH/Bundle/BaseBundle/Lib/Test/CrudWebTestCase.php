<?php
/**
 * Created by PhpStorm.
 * Date: 20.06.18
 */

namespace MBH\Bundle\BaseBundle\Lib\Test;


use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Form;

abstract class CrudWebTestCase extends WebTestCase
{
    /**
     * @var string
     */
    private $listContainer = 'table ';

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

    public function testNew()
    {
        $this->newFormBaseTest();
    }

    /**
     * @depends testNew
     */
    public function testIndex()
    {
        $this->listBaseTest();
    }

    /**
     * @depends testIndex
     */
    public function testEdit()
    {
        $this->editFormBaseTest();
    }

    /**
     * @depends testEdit
     */
    public function testDelete()
    {
        $this->deleteBaseTest();
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
     * @return string
     */
    public function getListContainer(): string
    {
        return $this->listContainer;
    }

    /**
     * @param string $listContainer
     * @return CrudWebTestCase
     */
    public function setListContainer(string $listContainer): CrudWebTestCase
    {
        $this->listContainer = $listContainer;
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
     * @return CrudWebTestCase
     */
    public function setEditFormValues(array $editFormValues): CrudWebTestCase
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
     * @return CrudWebTestCase
     */
    public function setNewFormValues(array $newFormValues): CrudWebTestCase
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
     * @return CrudWebTestCase
     */
    public function setEditFormErrors(array $editFormErrors): CrudWebTestCase
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
     * @return CrudWebTestCase
     */
    public function setNewFormErrors(array $newFormErrors): CrudWebTestCase
    {
        $this->newFormErrors = $newFormErrors;
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
     * @return CrudWebTestCase
     */
    public function setNewUrl(string $newUrl): CrudWebTestCase
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
     * @return CrudWebTestCase
     */
    public function setNewTitle(string $newTitle): CrudWebTestCase
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
     * @return CrudWebTestCase
     */
    public function setEditTitle(string $editTitle): CrudWebTestCase
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
     * @return CrudWebTestCase
     */
    public function setFormName(string $formName): CrudWebTestCase
    {
        $this->formName = $formName;
        return $this;
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
        $this->sendInvalidForm($crawler, $formClass, $errors);

        //send valid form
        $this->sendValidForm($crawler, $formClass, $formName, $values);

        //check saved object
        $this->checkSavedObject($title);
    }

    protected function checkSavedObject(string $title): void
    {
        $this->assertSame(1, $this->getListCrawler()->filter($this->getListContainer() . 'a:contains("' . $title . '")')->count());
    }

    protected function sendInvalidForm(Crawler $crawler, string $formClass, array $errors): void
    {
        $form = $crawler->filter($formClass)->form();
        $this->client->submit($form);
        $this->assertStatusCode(200, $this->client);
        $this->assertValidationErrors($errors, $this->client->getContainer());
    }

    protected function sendValidForm(Crawler $crawler, string $formClass, string $formName, array $values): void
    {
        $form = $crawler->filter($formClass)->form();
        $form->setValues(self::prepareFormValues($formName, $values));
        $this->client->submit($form);
        $this->client->followRedirect();
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
     * @param array $params
     * @param string $method
     * @return \Symfony\Component\DomCrawler\Crawler
     */
    protected function clickLinkInList(string $url = null, string $filter, bool $redirect = false, string $method = 'GET')
    {
        $crawler = $this->getListCrawler($url, $method);
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

    /**
     * @param array $array
     * @return string
     */
    protected function arraysFromValidJsonToString(array $array) : string
    {
        $imploded = '';
        foreach ($array as $key => $value) {
            if (is_array($key)) {
                $imploded .= $this->arraysFromValidJsonToString($key);
            }
            if (is_array($value)) {
                $imploded .= $this->arraysFromValidJsonToString($value);
            }
            if (is_string($value)) {
                $imploded .= $value;
            }
            if (is_string($key)) {
                $imploded .= $key;
            }
        }

        return $imploded;
    }
}
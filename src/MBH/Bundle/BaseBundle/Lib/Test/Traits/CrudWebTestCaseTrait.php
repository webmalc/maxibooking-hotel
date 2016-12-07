<?php
namespace MBH\Bundle\BaseBundle\Lib\Test\Traits;

trait CrudWebTestCaseTrait
{
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
}
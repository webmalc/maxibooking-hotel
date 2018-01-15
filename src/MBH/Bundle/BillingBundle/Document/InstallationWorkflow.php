<?php


namespace MBH\Bundle\BillingBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Bundle\MongoDBBundle\Validator\Constraints\Unique as MongoDBUnique;


/**
 * Class InstallationWorkflow
 * @package MBH\Bundle\BillingBundle\Document
 * @ODM\Document(collection="InstallationWorkflows")

 *
 */
class InstallationWorkflow
{
    /**
     * @var string
     * @ODM\Id
     */
    protected $id;

    /**
     * @var string
     * @ODM\Field(type="string", name="clientName")
     * @MongoDBUnique(fields="clientName")
     */
    protected $clientName;
    /**
     * @var string
     * @ODM\Field(type="string", name="currentPlace")
     * @Assert\Length(max=32)
     */
    protected $currentPlace;

    /**
     * @return string
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId(string $id): void
    {
        $this->id = $id;
    }


    /**
     * @return string
     */
    public function getClientName(): ?string
    {
        return $this->clientName;
    }

    /**
     * @param string $clientName
     */
    public function setClientName(string $clientName): void
    {
        $this->clientName = $clientName;
    }

    /**
     * @return string
     */
    public function getCurrentPlace(): ?string
    {
        return $this->currentPlace;
    }

    /**
     * @param string $currentPlace
     */
    public function setCurrentPlace(string $currentPlace): void
    {
        $this->currentPlace = $currentPlace;
    }

    public static function createInstallationWorkflow(string $clientName)
    {
        $wf = new static;
        $wf->setClientName($clientName);

        return $wf;
    }



}
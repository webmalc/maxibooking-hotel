<?php


namespace MBH\Bundle\BillingBundle\Document;

use MBH\Bundle\BillingBundle\Lib\InstallWorkflowInterface;

/**
 * Class InstallationWorkflow
 * @package MBH\Bundle\BillingBundle\Document
 * @ODM\Document(collection="InstallationWorkflows")
 */
class AfterInstallationWorkFlow extends InstallationWorkflow implements InstallWorkflowInterface
{

}
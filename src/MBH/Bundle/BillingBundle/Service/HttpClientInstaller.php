<?php


namespace MBH\Bundle\BillingBundle\Service;


use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\BillingBundle\Lib\Exceptions\AfterInstallException;
use MBH\Bundle\BillingBundle\Lib\Exceptions\ClientMaintenanceException;
use MBH\Bundle\BillingBundle\Lib\Maintenance\MaintenanceManager;
use MBH\Bundle\BillingBundle\Lib\Model\Answer;
use MBH\Bundle\BillingBundle\Lib\Model\Client;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class HttpClientInstaller
{
    /** @var  MaintenanceManager */
    protected $manager;

    protected $options;
    /** @var  ClientListGetter */
    protected $listGetter;
    /** @var  Helper */
    protected $helper;

    public function __construct(MaintenanceManager $manager, ClientListGetter $clientListGetter, Helper $helper)
    {
        $this->manager = $manager;
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);
        $this->options = $resolver->resolve();
        $this->listGetter = $clientListGetter;
        $this->helper = $helper;

    }

    public function install(Client $client): Answer
    {
        $answer = new Answer();
        if (in_array($client->getName(), $this->listGetter->getClientsList()) && false) {
            $answer->setError('Client already exists');
        } else {
            try {
                $client->setPassword($this->helper->getRandomString());
                $this->manager->install($client);
                $this->manager->afterInstall($client);
                $answer
                    ->setPassword($client->getPassword())
                    ->setUrl($client->getUrl())
                    ->setStatus(true);
            } catch (ClientMaintenanceException|AfterInstallException $e) {
                $this->manager->rollBack($client);
                $answer->setStatus(false);
                $answer->setError($e->getMessage());
            }
        }

        $this->sendAnswer($answer);

        return $answer;

    }

    public function remove(Client $client, bool $force = false): Answer
    {
        $answer = new Answer();
        if (!in_array($client->getName(), $this->listGetter->getClientsList()) && !$force) {
            $answer->setError('No client found');
        } else {
            try {
                $this->manager->remove($client);
                $answer->setStatus(true);
            } catch (ClientMaintenanceException $e) {
                $answer->setStatus(false);
                $answer->setError($e->getMessage());
            }
        }

        $this->sendAnswer($answer);

        return $answer;
    }

    protected function configureOptions(OptionsResolver $resolver)
    {
        $encoders = [new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);

        $resolver->setDefault('serializer', $serializer);

    }


    public function toJson($object)
    {
        /** @var Serializer $serializer */
        $serializer = $this->options['serializer'];

        return $serializer->serialize($object, 'json');
    }

    private function sendAnswer(Answer $answer)
    {

    }

    private function sendResponse(Client $client, Answer $answer)
    {
        $url = $client->getResponseUrl();

    }

}
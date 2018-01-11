<?php


namespace MBH\Bundle\BillingBundle\Command;


use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BillingBundle\Lib\Model\Client;
use MBH\Bundle\BillingBundle\Service\BillingApi;
use MBH\Bundle\HotelBundle\Document\Hotel;
use Monolog\Logger;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * Class BillingInstallCommand
 * @package MBH\Bundle\BillingBundle\Command
 * @deprecated
 */
class BillingInstallCommand extends Command
{

    /** @var  array */
    protected $options;

    /** @var  BillingApi */
    protected $api;

    /** @var  DocumentManager */
    protected $dm;

    /** @var  Logger */
    protected $logger;

    public function __construct(BillingApi $api, DocumentManager $dm, Logger $logger, $name = null)
    {
        $this->api = $api;
        $this->dm = $dm;
        $this->logger = $logger;
        parent::__construct($name);
    }


    protected function configure()
    {
        $this
            ->setName('mbh:install:billing')
            ->setHidden(true)
        ;

        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);
        $this->options = $resolver->resolve();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $client = $this->createClient();
            $this->changeInDb($client);
        } catch (\Throwable $e) {
            $this->logger->addCritical('Billing after install process error. '.$e->getMessage());
            $this->api->sendFalse($client->getName());
        }

        $this->api->sendSuccess('json');


    }

    private function createClient(): Client
    {
        $client = new Client();
        $container = $this->getContainer();
        $kernel = $container->get('kernel');
        $client->setName($kernel->getClient());
        $password = $container->get('mbh.helper')->getRandomString(20);
        $client->setPassword($password);

        return $client;
    }

    private function changeInDb(Client $client): void
    {
        $this->changeAdminPassword($client);
        $this->changeHotelName($client);
        $this->dm->flush();

    }

    private function changeAdminPassword(Client $client): void
    {
        $admin = $this->dm->getRepository('MBHUserBundle:User')->findOneBy(['username' => 'admin']);
        $admin->setPassword($client->getPassword());
    }

    private function changeHotelName(Client $client): void
    {
        foreach ($client->getProperties() as $property) {
            $hotel = new Hotel();
            $this->dm->persist($hotel);
        }

    }

    private function getClientProperties(Client $client): array
    {
        $result = [];

        return $result;
    }

    private function configureOptions(OptionsResolver $resolver)
    {
        $encoders = [new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);

        $resolver->setDefault('serializer', $serializer);

    }


}
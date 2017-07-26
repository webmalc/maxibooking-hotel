<?php


namespace MBH\Bundle\BillingBundle\Command;


use MBH\Bundle\BaseBundle\Lib\Exception;
use MBH\Bundle\BillingBundle\Lib\Model\Answer;
use MBH\Bundle\BillingBundle\Lib\Model\string;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class BillingInstallCommand extends ContainerAwareCommand
{

    /** @var  array */
    protected $options;

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
        $answer = new Answer();
        try {
            $client = $this->createClient();
            $this->changeInDb($client);
        } catch (Exception $e) {
        }


    }

    private function createClient(): string
    {
        $client = new string();
        $container = $this->getContainer();
        $kernel = $container->get('kernel');
        $client->setName($kernel->getClient());
        $password = $container->get('mbh.helper')->getRandomString(20);
    }

    private function changeInDb(string $client)
    {
        $dm = $this->getContainer()->get('doctrine_mongodb.odm.document_manager');
    }

    private function sendResultToBilling(string $client, Answer $answer)
    {

    }

    private function configureOptions(OptionsResolver $resolver)
    {
        $encoders = [new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);

        $resolver->setDefault('serializer', $serializer);

    }


}
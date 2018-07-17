<?php


namespace MBH\Bundle\ChannelManagerBundle\Command;


use MBH\Bundle\ChannelManagerBundle\Document\OstrovokConfig;
use MBH\Bundle\ChannelManagerBundle\Lib\Ostrovok\OstrovokApiServiceException;
use MBH\Bundle\HotelBundle\Document\Hotel;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class OstrovokCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('mbh:channelmanager:ostrovok:pull')
            ->addArgument('uuIds', InputArgument::IS_ARRAY | InputArgument::REQUIRED)
            ->setDescription('Force pull orders from ostrovok by UUID');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dm = $this->getContainer()->get('doctrine_mongodb.odm.default_document_manager');
        $hotels = $dm->getRepository(Hotel::class)->findAll();
        $uuIds = $input->getArgument('uuIds');
        foreach ($hotels as $hotel) {
            /** @var Hotel $hotel */
            $ostrovokConfig = $hotel->getOstrovokConfig();
            if (!$ostrovokConfig) {
                $output->writeln('No Ostrovok Config for hotel ' . $hotel->getName());
                continue;
            }
            $ostrovokHotelId = $ostrovokConfig->getHotelId();
            try {
                $orders = $this->getOrdersByUuIds($ostrovokHotelId, $uuIds);
            } catch (OstrovokApiServiceException $e) {
                $output->writeln('Error in OstrovokApiService. ' . $e->getMessage());
                continue;
            }
            if (\count($orders)) {
                $result = $this->forceCreateOrder($orders, $ostrovokConfig);
                $output->writeln($result);
            } else {
                $output->writeln('No Orders found');
            }
        }
    }

    /**
     * @param int $hotelId
     * @param array $uuIds
     * @return array
     * @throws OstrovokApiServiceException
     */
    private function getOrdersByUuIds(int $hotelId, array $uuIds): array
    {
        $ostrovokApi = $this->getContainer()->get('ostrovok_api_service');
        $orders = [];
        foreach ($uuIds as $uuid) {
            $orders = $ostrovokApi->getBookings([
                'hotel' => $hotelId,
                'uuid' => $uuid
            ]);
        }

        return $orders;
    }

    private function forceCreateOrder(array $reservations, OstrovokConfig $config)
    {
        $dm = $this->getContainer()->get('doctrine_mongodb.odm.default_document_manager');
        $ostrovok = $this->getContainer()->get('mbh.channelmanager.ostrovok');
        foreach ($reservations as $reservation) {
            $order = $dm->getRepository('MBHPackageBundle:Order')->findOneBy(
                [
                    'channelManagerId' => (string)$reservation['uuid'],
                    'channelManagerType' => 'ostrovok',
                ]
            );
            if (!$order) {
                $ostrovok->createPackage($reservation, $config);
            }
        }

    }


}
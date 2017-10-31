<?php

namespace MBH\Bundle\PackageBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class LocalAddressesDataToBillingConvertCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('mbh:local_addresses_to_billing')
            ->setDescription('Convert entities saved in local database to billing identifiers.');
    }

    private function getFilePath($fileName)
    {
        $root = $this->get('kernel')->getBundle('MBHPackageBundle')->getPath();

        return $root . DIRECTORY_SEPARATOR . 'Resources' . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . $fileName;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Start conversion!');
        $startTime = new \DateTime();

        $vegaDocs = $this->dm->getRepository('MBHVegaBundle:VegaFMS')->findAll();

        $resource = fopen($this->getFilePath('fms.csv'), 'r');
        $billingIdsByCodes = [];

        if ($resource) {
            while (($rowData = fgetcsv($resource, 1000, ";")) !== false) {
                $billingIdsByCodes[$rowData[2]][] = $rowData;
            }
            fclose($resource);
        }


        $endTime = new \DateTime();
        $timeDiffInSeconds = $endTime->diff($startTime)->s;
        $output->writeln('The conversion was completed in' . $timeDiffInSeconds . ' seconds');
    }

    /**
     * @param array $vegaDocumentsData
     * @return array
     */
    private function convertVegaIdToBillingIdAndReturnMissing(array $vegaDocumentsData)
    {
        $missingDocumentsIds = [];
        $localIdsToBillingIds = [];
        foreach ($vegaDocumentsData as $vegaDocData) {
            $vegaDocId = $vegaDocData['_id']->serialize();
            $vegaDocCode = $vegaDocData['code'];
            $vegaDocOriginalName = $vegaDoc['originalName'];
            if (isset($billingIdsByCodes[$vegaDocCode])) {
                $found = false;
                foreach ($billingIdsByCodes[$vegaDocCode] as $billingIdsByCode) {
                    if (mb_strtolower($billingIdsByCode[1]) == mb_strtolower($vegaDocOriginalName)) {
                        $localIdsToBillingIds[$vegaDocId] = $billingIdsByCode[0];
                        $found = true;
                    }
                }

                if (!$found) {
                    $missingDocumentsIds[] = [$vegaDocId];
                }

            } else {
                foreach ($billingIdsByCodes as $billingIdsByCode) {
                    foreach ($billingIdsByCode as $item) {
                        if (strtolower($item[1]) == strtolower($vegaDocOriginalName)) {
                            $localIdsToBillingIds[$vegaDocId] = $billingIdsByCode[0];
                            break 2;
                        }
                    }
                }
            }
        }

        return $missingDocumentsIds;
    }
}

<?php
/**
 * Created by PhpStorm.
 * Date: 30.01.19
 */

namespace MBH\Bundle\CashBundle\Command;


use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\PackageBundle\Document\Package;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class AddCashDocumentCommand extends ContainerAwareCommand
{
    public const COMMAND_NAME = 'mbh:helper:add_cash_documents';

    private const OPT_NAME_BEGIN_DATE = 'begin-date';
    private const OPT_NAME_END_DATE = 'end-date';
    private const OPT_NAME_NOT_CONFIRMED = 'not-confirmed';
    private const OPT_NAME_NOT_ADD_PAYER = 'not-add-payer';
    private const OPT_NAME_TOTAL_ALL = 'full-cost';

    protected function configure()
    {
        $this
            ->setName(self::COMMAND_NAME)
            ->setDescription('Add cash document for order (see help)')
            ->addOption(self::OPT_NAME_BEGIN_DATE, null,InputOption::VALUE_OPTIONAL, 'Start date for package search')
            ->addOption(self::OPT_NAME_END_DATE, null, InputOption::VALUE_OPTIONAL, 'End date for package search')
            ->addOption(self::OPT_NAME_NOT_CONFIRMED, null,InputOption::VALUE_NONE, 'Set cash document not confirmed')
            ->addOption(self::OPT_NAME_NOT_ADD_PAYER, null,InputOption::VALUE_NONE, 'Do not add payer')
            ->addOption(self::OPT_NAME_TOTAL_ALL, null,InputOption::VALUE_NONE, 'Set the full cost of the order')
            ->setHelp(<<<EOF
The <info>%command.name%</info> command add cash documents for packages who between date begin and date end or only begin or end date.
Format date: dd.mm.YYYY.

for example:
  <info>php %command.full_name% --end-date=01.01.2019</info>
, after the command is executed, cash documents are created for all packages where the completion date is 01/01/2019.
EOF
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $criteria = $this->prepareCriteria($input);

        if ($criteria === []) {
            $output->writeln('Check date.');
            exit(1);
        }

        $addPayer = !$input->getOption(self::OPT_NAME_NOT_ADD_PAYER);
        $confirmed = !$input->getOption(self::OPT_NAME_NOT_CONFIRMED);
        $fullCost = $input->getOption(self::OPT_NAME_TOTAL_ALL);

        $dm = $this->getContainer()->get('doctrine.odm.mongodb.document_manager');

        $packages = $dm->getRepository(Package::class)->findBy($criteria);

        $countUpdate = 0;

        /** @var Package $package */
        foreach ($packages as $package) {
            $order = $package->getOrder();

            if ($order->getIsPaid()) {
                continue;
            }

            $cash = new CashDocument();
            $cash
                ->setIsConfirmed($confirmed)
                ->setIsPaid(true)
                ->setMethod(CashDocument::METHOD_CASH)
                ->setOperation(CashDocument::OPERATION_IN)
                ->setOrder($order);

            if ($fullCost) {
                $cash->setTotal($order->getPrice());
            } else {
                $cash->setTotal($order->getPrice() - $order->getPaid());
            }

            if ($addPayer) {
                if ($order->getMainTourist() !== null) {
                    $cash->setTouristPayer($order->getMainTourist());
                } else if ($order->getOrganization() !== null) {
                    $cash->setOrganizationPayer($order->getOrganization());
                }
            }

            $order->addCashDocument($cash);
            $dm->persist($cash);
            $countUpdate ++;
        }

        $dm->flush();

        $output->writeln(sprintf('Create cash document: %s.', $countUpdate));
    }

    private function prepareCriteria(InputInterface $input): array
    {
        $criteria = [];

        $beginDate = $this->formatDate($input->getOption(self::OPT_NAME_BEGIN_DATE));
        $endDate = $this->formatDate($input->getOption(self::OPT_NAME_END_DATE));

        if ($beginDate === null && $endDate === null) {
            return $criteria;
        }

        if ($beginDate !== null) {
            $criteria['begin'] = ['$gte' => $beginDate->modify('midnight')];
        }

        if ($endDate !== null) {
            $criteria['end'] = ['$lte' => $endDate->setTime(23,59,59)];
        }

        if (count($criteria) === 2 && $endDate < $beginDate) {
            return [];
        }

        return $criteria;
    }

    private function formatDate(?string $inputOption): ?\DateTime
    {
        if ($inputOption === null) {
            return null;
        }

        $format = 'd.m.Y';

        $date = \DateTime::createFromFormat($format, $inputOption);

        return $date && $date->format($format) === $inputOption ? $date : null;
    }
}
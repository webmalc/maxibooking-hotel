<?php

namespace MBH\Bundle\BaseBundle\Command;

use Doctrine\ODM\MongoDB\Mapping\Annotations\Field;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
use Doctrine\ODM\MongoDB\Query\Builder;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DatesRecalculationCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('mbh:dates_recalculation_command')
            ->addOption('hours', null, InputOption::VALUE_REQUIRED, 'Number of hours that would be subtracted from current datetimes');
    }

    /**
     * {@inheritdoc}
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $numberOfHours = (int)$input->getOption('hours');
        $output->writeln('Number of hours that would be subtracted: ' . $numberOfHours);
        if ($numberOfHours !== 0) {
            $reader = $this->getContainer()->get('annotations.reader');
            /** @var ClassMetadata $classMetadata */
            $dm = $this->getContainer()->get('doctrine.odm.mongodb.document_manager');
            foreach ($dm->getMetadataFactory()->getAllMetadata() as $classMetadata) {
                $dateFields = [];
                foreach ($classMetadata->reflFields as $reflectionField) {
                    $fieldPropertyAnnotation = $reader->getPropertyAnnotation($reflectionField, Field::class);
                    if (!is_null($fieldPropertyAnnotation) && $fieldPropertyAnnotation->type === 'date') {
                        $dateFields[] = $reflectionField->name;
                    }
                }

                $classTraits = class_uses($classMetadata->name);
                foreach ($classTraits as $trait) {
                    if ($trait === 'Gedmo\Timestampable\Traits\TimestampableDocument') {
                        $dateFields[] = 'createdAt';
                        $dateFields[] = 'updatedAt';
                    } elseif ($trait === 'Gedmo\SoftDeleteable\Traits\SoftDeleteableDocument') {
                        $dateFields[] = 'deletedAt';
                    }
                }

                if (!empty($dateFields)) {
                    $updates = [];
                    /** @var Builder $qb */
                    $qb = $docs = $dm->getRepository($classMetadata->name)->createQueryBuilder();

                    if ($dm->getFilterCollection()->isEnabled('softdeleteable')) {
                        $dm->getFilterCollection()->disable('softdeleteable');
                    }

                    $qb->select(array_merge(['_id'], $dateFields))->hydrate(false);

                    if (!$dm->getFilterCollection()->isEnabled('softdeleteable')) {
                        $dm->getFilterCollection()->enable('softdeleteable');
                    }

                    foreach ($qb->getQuery()->execute() as $item) {
                        $itemUpdates = [];
                        foreach ($dateFields as $dateField) {
                            if (isset($item[$dateField])) {
                                $dateFieldValue = $item[$dateField];
                                $itemUpdates[$dateField] = new \MongoDate(
                                    $dateFieldValue->sec - $numberOfHours * 60 * 60
                                );
                            }
                        }
                        if (!empty($itemUpdates)) {
                            $updates[] = [
                                'criteria' => ['_id' => $item['_id']],
                                'values' => $itemUpdates
                            ];
                        }
                    }
                    $this->getContainer()->get('mbh.mongo')->update($classMetadata->collection, $updates);
                }
            }
        }
    }
}

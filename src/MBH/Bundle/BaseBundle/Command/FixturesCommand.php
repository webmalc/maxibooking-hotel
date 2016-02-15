<?php

namespace MBH\Bundle\BaseBundle\Command;

use MBH\Bundle\HotelBundle\DataFixtures\MongoDB\TaskData;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\PackageBundle\Document\PollQuestion;
use MBH\Bundle\PriceBundle\Document\ServiceCategory;
use MBH\Bundle\PriceBundle\Document\Service;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\UserBundle\Document\User;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Process;

class FixturesCommand extends ContainerAwareCommand
{
    /**
     * @var \Doctrine\ODM\MongoDB\DocumentManager
     */
    private $dm;

    private $pollQuestions = [
        'poll.question.category.service' => ['amiability', 'doc_speed', 'rules'],
        'poll.question.category.accommodation' => ['room_comfort', 'beds_comfort', 'room_service'],
        'poll.question.category.hotel' => ['hotel_location', 'hotel_food', 'hotel_entertainment'],
    ];

    /**
     * @var string
     */
    private $user = 'admin';

    /**
     * @var string
     */
    private $hotel = 'Мой отель';

    protected function configure()
    {
        $this
            ->setName('mbh:base:fixtures')
            ->setDescription('Install project fixtures')
            ->addOption('cities', null, InputOption::VALUE_NONE, 'with cities?')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $start = new \DateTime();
        $this->dm = $this->getContainer()->get('doctrine_mongodb')->getManager();

        //Hotel
        $hotels = $this->createHotel();

        //User
        $this->createUser();

        $hotelManager = $this->getContainer()->get('mbh.hotel.hotel_manager');

        foreach ($hotels as $hotel) {
            $hotelManager->updateFixture($hotel);
        }

        //Cities
        if ($input->getOption('cities')) {
            $this->createCities();
        }

        $this->createTaskTypes();

        //PollQuestions
        $this->createPollQuestions($hotel);
        
        $time = $start->diff(new \DateTime());
        $output->writeln('Installing complete. Elapsed time: ' . $time->format('%H:%I:%S'));
    }

    /**
     * @return array
     */
    private function createHotel()
    {
        $hotels = $this->dm->getRepository('MBHHotelBundle:Hotel')->findAll();

        if (count($hotels)) {
            return $hotels;
        }

        $hotel = new Hotel();
        $hotel->setFullTitle($this->hotel)->setIsDefault(true);
        $this->dm->persist($hotel);
        $this->dm->flush();

        return [$hotel];
    }

    /**
     * @return User|null
     */
    private function createUser()
    {
        $repo = $this->dm->getRepository('MBHUserBundle:User');

        if (!count($repo->findAll())) {
            $user = new User();
            $user->setUsername($this->user)
                ->setEmail($this->user . '@example.com')
                ->addRole('ROLE_ADMIN')
                ->setPlainPassword($this->user)
                ->setEnabled(true)
                ->setLocked(false)
            ;
            $this->dm->persist($user);
            $this->dm->flush();

            return $user;
        }

        return null;
    }

    /**
     * @return int
     */
    private function createCities()
    {
        $process = new Process(
            'nohup php ' . $this->getContainer()->get('kernel')->getRootDir() . '/../bin/console mbh:city:load --no-debug'
        );

        return $process->run();
    }

    /**
     * @return int
     */
    private function createTaskTypes()
    {
        $taskData = new TaskData();
        $taskData->setContainer($this->getContainer());
        $taskData->load($this->dm);
    }

    private function createPollQuestions()
    {
        $oldQuestions = $this->dm->getRepository('MBHPackageBundle:PollQuestion')->findAll();
        $sort = 0;
        foreach ($this->pollQuestions as $questionCat => $questions) {
            foreach ($questions as $question) {
                foreach ($oldQuestions as $old) {
                    if ($old->getId() == $question) {
                        continue 2;
                    }
                }

                $new = new PollQuestion();
                $new->setId($question)
                    ->setCategory($questionCat)
                    ->setText('poll.question.' . $question)
                    ->setSort($sort);
                $sort++;
                $this->dm->persist($new);
                $this->dm->flush();
            }
        }
    }
}
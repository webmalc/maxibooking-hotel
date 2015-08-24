<?php

namespace MBH\Bundle\BaseBundle\Command;

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

    /**
     * @var array
     */
    private $serviceCategories = [
      'Питание' => [
          'Breakfast' => ['name' => 'Завтрак', 'calcType' => 'per_night', 'enabled' => false],
          'Continental breakfast' => ['name' => 'Континентальный завтрак', 'calcType' => 'per_night', 'enabled' => false],
          'American breakfast' => ['name' => 'Американский завтрак', 'calcType' => 'per_night', 'enabled' => false],
          'Buffet breakfast' => ['name' => 'Завтрак-буфет', 'calcType' => 'per_night', 'enabled' => false],
          'Full english breakfast' => ['name' => 'Полный английский завтрак', 'calcType' => 'per_night', 'enabled' => false],
          'Lunch' => ['name' => 'Обед', 'calcType' => 'per_night', 'enabled' => false],
          'Dinner' => ['name' => 'Ужин', 'calcType' => 'per_night', 'enabled' => false],
          'Half board' => ['name' => 'Полупансион', 'calcType' => 'per_night', 'enabled' => false],
          'Breakfast and Lunch' => ['name' => 'Завтрак и обед', 'calcType' => 'per_night', 'enabled' => false],
          'Full board' => ['name' => 'Полный пансион', 'calcType' => 'per_night', 'enabled' => false],
          'Full pansion' => ['name' => 'Обед и ужин', 'calcType' => 'per_night', 'enabled' => false],
          'Breakfast for Children' => ['name' => 'Детский завтрак', 'calcType' => 'per_night', 'enabled' => false],
          'Continental breakfast for Children' => ['name' => 'Детский континентальный завтрак', 'calcType' => 'per_night', 'enabled' => false],
          'American breakfast for Children' => ['name' => 'Детский американский завтрак', 'calcType' => 'per_night', 'enabled' => false],
          'Buffet breakfast for Children' => ['name' => 'Детский завтрак-буфет', 'calcType' => 'per_night', 'enabled' => false],
          'Full english breakfast for Children' => ['name' => 'Детский полный английский завтрак', 'calcType' => 'per_night', 'enabled' => false],
          'Lunch for Children' => ['name' => 'Детский обед', 'calcType' => 'per_night', 'enabled' => false],
          'Dinner for Children' => ['name' => 'Детский ужин', 'calcType' => 'per_night', 'enabled' => false],
          'Half board for Children' => ['name' => 'Детский полупансион', 'calcType' => 'per_night', 'enabled' => false],
          'Full board for Children' => ['name' => 'Детский полный пансион', 'calcType' => 'per_night', 'enabled' => false],
      ],
      'Размещение' => [
          'Extrabed' => ['name' => 'Дополнительная кровать', 'calcType' => 'per_night', 'enabled' => true],
          'Infant' => ['name' => 'Инфант', 'calcType' => 'per_night', 'enabled' => true],
          'Early check-in'  => ['name' => 'Ранний заезд', 'calcType' => 'day_percent', 'enabled' => true],
          'Late check-out'  => ['name' => 'Поздний выезд', 'calcType' => 'day_percent', 'enabled' => true],
      ],
      'Опции' => [
          'WiFi' => ['name' => 'WiFi', 'calcType' => 'per_night', 'enabled' => false],
          'Internet' => ['name' => 'Интернет', 'calcType' => 'per_night', 'enabled' => false],
          'Parking space' => ['name' => 'Парковка', 'calcType' => 'per_night', 'enabled' => false],
          'Babycot' => ['name' => 'Детская кровать', 'calcType' => 'per_night', 'enabled' => false],

      ],
      'Трансфер' => [
            'Transfer' => ['name' => 'Трансфер', 'calcType' => 'not_applicable', 'date' => true, 'time' => true, 'enabled' => false]
      ]
    ];

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

    /**
     * @var string
     */
    private $tariff = 'Основной тариф';

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

        foreach ($hotels as $hotel) {

            //Services
            $this->createServices($hotel);

            //Tariffs
            $this->createTariffs($hotel);
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
     * Create hotel default services
     * @param Hotel $hotel
     */
    private function createServices(Hotel $hotel)
    {
        foreach ($this->serviceCategories as $catName => $services) {
            $category = $this->dm->getRepository('MBHPriceBundle:ServiceCategory')->findOneBy([
                'system' => true,
                'fullTitle' => $catName,
                'hotel.id' => $hotel->getId()
                    ])
            ;

            if (empty($category)) {
                $category = new ServiceCategory();
                $category->setSystem(true)
                        ->setIsEnabled(true)
                        ->setFullTitle($catName)
                        ->setHotel($hotel)
                        ;
                $this->dm->persist($category);
                $this->dm->flush();
            }

            foreach ($services as $code => $info) {
                $service = $this->dm->getRepository('MBHPriceBundle:Service')->findOneBy([
                'system' => true,
                'code' => $code,
                'category.id' => $category->getId()
                    ])
                ;

                if (empty($service)) {
                    $service = new Service;
                    $service->setCode($code)
                            ->setSystem(true)
                            ->setIsEnabled($info['enabled'])
                            ->setFullTitle($info['name'])
                            ->setPrice(0)
                            ->setCalcType($info['calcType'])
                            ->setDate(!empty($info['date']) ? $info['date'] : null)
                            ->setTime(!empty($info['time']) ? $info['time'] : null)
                            ->setCategory($category)
                    ;
                    $this->dm->persist($service);
                    $this->dm->flush();
                }

            }
        }
    }

    /**
     * @param Hotel $hotel
     * @return Tariff
     */
    private function createTariffs(Hotel $hotel)
    {
        $baseTariff = $this->dm->getRepository('MBHPriceBundle:Tariff')->fetchBaseTariff($hotel);

        if ($baseTariff) {
            return $baseTariff;
        }

        $tariff = new Tariff();
        $tariff->setFullTitle($this->tariff)
            ->setIsDefault(true)
            ->setIsOnline(true)
            ->setHotel($hotel);
        $this->dm->persist($tariff);
        $this->dm->flush();

        return $tariff;
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
        $process = new Process(
            'nohup php ' . $this->getContainer()->get('kernel')->getRootDir() . '/../bin/console mbh:task:load --force --no-debug'
        );

        return $process->run();
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
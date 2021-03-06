<?php

namespace MBH\Bundle\PackageBundle\DataFixtures\MongoDB;


use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use MBH\Bundle\PackageBundle\Document\PollQuestion;


class PollQuestionData extends AbstractFixture implements OrderedFixtureInterface
{
    const DATA = [
        'poll.question.category.service' => ['amiability', 'doc_speed', 'rules'],
        'poll.question.category.accommodation' => ['room_comfort', 'beds_comfort', 'room_service'],
        'poll.question.category.hotel' => ['hotel_location', 'hotel_food', 'hotel_entertainment'],
    ];

    public function load(ObjectManager $manager)
    {
        $oldQuestions = $manager->getRepository('MBHPackageBundle:PollQuestion')->findAll();
        $sort = 0;
        foreach (self::DATA as $questionCat => $questions) {
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
                $manager->persist($new);
                $manager->flush();
            }
        }
    }

    public function getOrder()
    {
        return 9992;
    }
}
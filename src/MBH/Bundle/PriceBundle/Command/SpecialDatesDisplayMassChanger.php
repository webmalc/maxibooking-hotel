<?php


namespace MBH\Bundle\PriceBundle\Command;


use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

class SpecialDatesDisplayMassChanger extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('mbh:special:mass-dates-change');
    }

}
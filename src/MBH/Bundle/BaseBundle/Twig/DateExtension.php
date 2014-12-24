<?php
namespace MBH\Bundle\BaseBundle\Twig;

use Symfony\Component\DependencyInjection\ContainerInterface;

class DateExtension extends \Twig_Extension
{

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface 
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'mbh_date_extension';
    }

    /**
     * @return string
     */
    public function format(\DateTime $date)
    {
        $now = new \DateTime();

        if ($now->format('Y') != $date->format('Y')) {
            return $date->format('d.m.Y');
        }

        $months = [
            'янв', 'февр', 'март',  'апр',  'май', 'июнь', 'июль', 'авг', 'сент', 'окт', 'нояб', 'дек'
        ];

        return $date->format('d') . ' ' . $months[$date->format('n') - 1] . '.';
    }

    /**
     * @return array
     */
    public function getFilters()
    {
        return [
            'mbh_format' => new \Twig_Filter_Method($this, 'format'),
        ];
    }

}

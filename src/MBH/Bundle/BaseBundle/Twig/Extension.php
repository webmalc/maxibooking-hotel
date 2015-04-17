<?php
namespace MBH\Bundle\BaseBundle\Twig;

use Symfony\Component\DependencyInjection\ContainerInterface;

class Extension extends \Twig_Extension
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
        return 'mbh_twig_extension';
    }

    /**
     * @return string
     */
    public function format(\DateTime $date)
    {
        $now = new \DateTime();

        if ($now->format('Y') != $date->format('Y')) {
            return $date->format('d.m.y');
        }

        $months = [
            $this->get('translator')->trans('twig.extension.jan'),
            $this->get('translator')->trans('twig.extension.feb'),
            $this->get('translator')->trans('twig.extension.march'),
            $this->get('translator')->trans('twig.extension.april'),
            $this->get('translator')->trans('twig.extension.may'),
            $this->get('translator')->trans('twig.extension.june'),
            $this->get('translator')->trans('twig.extension.july'),
            $this->get('translator')->trans('twig.extension.august'),
            $this->get('translator')->trans('twig.extension.september'),
            $this->get('translator')->trans('twig.extension.october'),
            $this->get('translator')->trans('twig.extension.november'),
            $this->get('translator')->trans('twig.extension.december')
        ];

        return $date->format('d') . ' ' . $months[$date->format('n') - 1] . '.';
    }

    public function md5($value)
    {
        return md5($value);
    }

    /**
     * @return array
     */
    public function getFilters()
    {
        return [
            'mbh_format' => new \Twig_Filter_Method($this, 'format'),
            'mbh_md5' => new \Twig_Filter_Method($this, 'md5'),
        ];
    }

}

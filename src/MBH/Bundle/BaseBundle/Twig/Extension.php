<?php
namespace MBH\Bundle\BaseBundle\Twig;

use Symfony\Component\DependencyInjection\ContainerInterface;

class Extension extends \Twig_Extension
{

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface 
     */
    protected $container;

    protected $translator;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->translator = $this->container->get('translator');
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
            $this->translator->trans('twig.extension.jan', [], 'MBHBaseBundle'),
            $this->translator->trans('twig.extension.feb', [], 'MBHBaseBundle'),
            $this->translator->trans('twig.extension.march', [], 'MBHBaseBundle'),
            $this->translator->trans('twig.extension.april', [], 'MBHBaseBundle'),
            $this->translator->trans('twig.extension.may', [], 'MBHBaseBundle'),
            $this->translator->trans('twig.extension.june', [], 'MBHBaseBundle'),
            $this->translator->trans('twig.extension.july', [], 'MBHBaseBundle'),
            $this->translator->trans('twig.extension.august', [], 'MBHBaseBundle'),
            $this->translator->trans('twig.extension.september', [], 'MBHBaseBundle'),
            $this->translator->trans('twig.extension.october', [], 'MBHBaseBundle'),
            $this->translator->trans('twig.extension.november', [], 'MBHBaseBundle'),
            $this->translator->trans('twig.extension.december', [], 'MBHBaseBundle')
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

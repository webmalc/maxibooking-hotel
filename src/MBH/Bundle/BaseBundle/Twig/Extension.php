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
            $this->translator->trans('twig.extension.jan', []),
            $this->translator->trans('twig.extension.feb', []),
            $this->translator->trans('twig.extension.march', []),
            $this->translator->trans('twig.extension.april', []),
            $this->translator->trans('twig.extension.may', []),
            $this->translator->trans('twig.extension.june', []),
            $this->translator->trans('twig.extension.july', []),
            $this->translator->trans('twig.extension.august', []),
            $this->translator->trans('twig.extension.september', []),
            $this->translator->trans('twig.extension.october', []),
            $this->translator->trans('twig.extension.november', []),
            $this->translator->trans('twig.extension.december', [])
        ];

        return $date->format('d') . " <span class='date-month'> " . $months[$date->format('n') - 1] . '.</span>';
    }

    public function md5($value)
    {
        return md5($value);
    }

    public function num2str($value)
    {
        return $this->container->get('mbh.helper')->num2str($value);
    }

    public function num2enStr($value)
    {
        return $this->container->get('mbh.helper')->convertNumberToWords($value);
    }

    public function translateToLat($value)
    {
        return $this->container->get('mbh.helper')->translateToLat($value);
    }

    /**
     * @param \MongoDate $mongoDate
     * @return \DateTime
     *
     * @author Aleksandr Arofikin <sashaaro@gmail.com>
     */
    public function convertMongoDate(\MongoDate $mongoDate)
    {
        return new \DateTime('@' . $mongoDate->sec);
    }

    /**
     * @return array
     */
    public function currency()
    {
        return $this->container->get('mbh.currency')->info();
    }

    /**
     * @return array
     */
    public function getFilters()
    {
        return [
            'mbh_format' => new \Twig_Filter_Method($this, 'format', ['is_safe' => array('html')]),
            'mbh_md5' => new \Twig_Filter_Method($this, 'md5'),
            'num2str' => new \Twig_Filter_Method($this, 'num2str'),
            'num2enStr' => new \Twig_Filter_Method($this, 'num2enStr'),
            'transToLat' => new \Twig_Filter_Method($this, 'translateToLat'),
            'convertMongoDate' => new \Twig_Filter_Method($this, 'convertMongoDate'),
        ];
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            'currency' => new \Twig_Function_Method($this, 'currency', array('is_safe' => array('html')))
        ];
    }
}

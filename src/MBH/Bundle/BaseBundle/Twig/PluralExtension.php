<?php
namespace MBH\Bundle\BaseBundle\Twig;

use Symfony\Component\DependencyInjection\ContainerInterface;

class PluralExtension extends \Twig_Extension
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
        return 'mbh_plural_extension';
    }

    /**
     * @param $n number
     * @param $s1 word form 1
     * @param $s2 word form 2
     * @param $s3 word form 3
     * @return string
     */
    public function plural($n, $s1, $s2, $s3)
    {
        return $this->container->get('mbh.helper')->plural($n, $s1, $s2, $s3);
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return array(
            'plural' => new \Twig_Function_Method($this, 'plural', array('is_safe' => array('html'))),
        );
    }

}

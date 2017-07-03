<?php

namespace MBH\Bundle\ClientBundle\Twig;

use MBH\Bundle\ClientBundle\Service\Dashboard\Dashboard;

class DashboardExtension extends \Twig_Extension
{

    /**
     * @var Dashboard
     */
    private $dashboard;

    /**
     * constructor.
     */
    public function __construct(Dashboard $dashboard)
    {
        $this->dashboard = $dashboard;
    }


    public function getName()
    {
        return 'mbh_dashboard_extension';
    }


    public function getFunctions()
    {
        return [
            'dashboard_messages' => new \Twig_SimpleFunction(
                'dashboard_messages',
                [$this, 'dashboard'],
                ['is_safe' => array('html')]
            )
        ];
    }

    public function dashboard()
    {
        return $this->dashboard->getMessages();
    }
}

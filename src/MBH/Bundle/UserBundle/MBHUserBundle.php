<?php

namespace MBH\Bundle\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class MBHUserBundle extends Bundle
{

    public function getParent()
    {
        return 'FOSUserBundle';
    }

}

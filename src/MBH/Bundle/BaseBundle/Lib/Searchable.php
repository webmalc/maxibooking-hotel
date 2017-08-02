<?php
/**
 * Created by PhpStorm.
 * User: danya
 * Date: 28.07.17
 * Time: 14:50
 */

namespace MBH\Bundle\BaseBundle\Lib;

use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\UserBundle\Document\User;
use Symfony\Component\HttpFoundation\Request;

interface Searchable
{
    public function getQueryBuilderByRequestData(Request $request, User $user, Hotel $hotel);
}
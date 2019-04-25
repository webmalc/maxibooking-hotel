<?php

namespace MBH\Bundle\OnlineBundle\Services;


class OnlineFormValidator
{
    /**
     * @param $request
     * @return bool
     */
    public function isValid($request) : bool
    {
        $userData = $request->user;
        if (!($userData->firstName && $userData->lastName && $userData->phone && $this->isEmailValid($userData->email))) {
            return false;
        }

        return true;
    }

    /**
     * @param string $email
     * @return bool
     */
    private function isEmailValid(string $email) : bool
    {
        if ($email === '') {
            return true;
        }
        return (bool)preg_match('/^[a-z0-9._%+-]+@[a-z0-9._%+-]+\\.\\w{2,4}$/', $email);
    }
}

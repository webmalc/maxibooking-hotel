<?php


namespace MBH\Bundle\PriceBundle\Form\Partial;


trait PreRedirectParams
{
    private function getBeginDate($options)
    {
        $beginDate = null;
        try {
            $beginDate = $options['preRedirectFormData']['begin']
                ? new \DateTime($options['preRedirectFormData']['begin'])
                : new \DateTime('midnight');
        } catch (\Exception $e) {
            $beginDate = new \DateTime('midnight');
        }

        return $beginDate;
    }

    private function getEndDate($options)
    {
        try {
            $endDate = $options['preRedirectFormData']['end']
                ? new \DateTime($options['preRedirectFormData']['end'])
                : null;
        } catch (\Exception $e) {
            $endDate = null;
        }

        return $endDate;
    }
}
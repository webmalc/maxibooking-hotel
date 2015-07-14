<?php

namespace MBH\Bundle\PackageBundle\Component\DocumentTemplateGenerator\Extended;

/**
 * Class EnConfirmationTemplateGenerator
 * @author Aleksandr Arofikin <sasaharo@gmail.com>
 */
class EnConfirmationTemplateGenerator extends ConfirmationTemplateGenerator
{
    public function getTemplate()
    {
        /** @var \Symfony\Component\Translation\DataCollectorTranslator $translator */
        $translator = $this->container->get('translator');
        $currentLocale = $translator->getLocale();
        $translator->setLocale('en');

        $hotel = $this->package->getRoomType()->getHotel();

        $country = $hotel->getCountry();
        if($country) {
            $country->setTranslatableLocale('en_EN');
            $this->container->get('doctrine_mongodb')->getManager()->refresh($country);
        }
        //var_dump($translator->trans('package.document.type_passport')); die();

        $html = parent::getTemplate();
        $translator->setLocale($currentLocale);

        return $html;
    }
}
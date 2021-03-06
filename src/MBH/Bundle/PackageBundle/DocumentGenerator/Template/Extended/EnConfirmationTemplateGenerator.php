<?php

namespace MBH\Bundle\PackageBundle\DocumentGenerator\Template\Extended;

use Gedmo\Translatable\TranslatableListener;

/**
 * Class EnConfirmationTemplateGenerator

 */
class EnConfirmationTemplateGenerator extends ConfirmationTemplateGenerator
{
    public function getTemplate(array $formData)
    {
        /** @var \Symfony\Component\Translation\DataCollectorTranslator $translator */
        $translator = $this->container->get('translator');
        $currentLocale = $translator->getLocale();
        $translator->setLocale('en');

        /** @var TranslatableListener $translatableListener */
        /*$translatableListener = $this->container->get('gedmo.listener.translatable');
        $translatableListener->setTranslatableLocale('en_EN'); // not work..

        if($country = $this->package->getRoomType()->getHotel()->getCountry()) { //todo remove
            $country->setTranslatableLocale('en_EN');
            $this->container->get('doctrine_mongodb')->getManager()->refresh($country);
        }

        if($this->package->getOrder() && $this->package->getOrder()->getMainTourist() && //todo remove
            $this->package->getOrder()->getMainTourist()->getAddressObjectDecomposed() &&
            $country = $this->package->getOrder()->getMainTourist()->getAddressObjectDecomposed()->getCountry()) {
            $country->setTranslatableLocale('en_EN');
            $this->container->get('doctrine_mongodb')->getManager()->refresh($country);
        }
*/
        $html = parent::getTemplate($formData);
        $translator->setLocale($currentLocale);

        return $html;
    }
}
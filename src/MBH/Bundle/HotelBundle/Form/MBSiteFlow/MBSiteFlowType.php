<?php

namespace MBH\Bundle\HotelBundle\Form\MBSiteFlow;

use MBH\Bundle\BaseBundle\Service\MBHFormBuilder;
use MBH\Bundle\OnlineBundle\Document\SiteConfig;
use MBH\Bundle\OnlineBundle\Form\SiteForm;
use MBH\Bundle\OnlineBundle\Services\SiteManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Translation\TranslatorInterface;

class MBSiteFlowType extends AbstractType
{
    private $mbhFormBuilder;
    private $siteManager;
    private $translator;

    public function __construct(MBHFormBuilder $mbhFormBuilder, SiteManager $siteManager, TranslatorInterface $translator) {
        $this->mbhFormBuilder = $mbhFormBuilder;
        $this->siteManager = $siteManager;
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        switch ($options['flow_step']) {
            case MBSiteFlow::NAME_STEP:
                $builder->add(
                    'siteDomain',
                    TextType::class,
                    [
                        'label' => 'site_form.web_address.label',
                        'required' => true,
                        'addonText' => SiteManager::SITE_DOMAIN,
                        'preAddonText' => SiteManager::SITE_PROTOCOL,
                    ]
                );
                break;
            case MBSiteFlow::PAYMENT_TYPES_STEP:
                $this->mbhFormBuilder->mergeFormFields($builder, SiteForm::class, $builder->getData(), ['paymentTypes']);
                break;
            case MBSiteFlow::COLOR_THEME_STEP:
                $builder->add('colorTheme', ChoiceType::class, [
                'label' => 'site_config.color_theme.colors.label',
                'choices' => array_keys(SiteConfig::COLORS_BY_THEMES),
                'choice_label' => function(string $theme) {
                    return 'site_config.color_theme.colors.' . $theme;
                },
                'help' => '<a class="btn btn-success" id="go-to-site_with-save" target="_blank" href="' . $this->siteManager->getSiteAddress() . '">'
                    . $this->translator->trans('site_form.site_domain.go_to_site_button.text'). '</a>'
                ]);
                break;
            case MBSiteFlow::KEY_WORDS_STEP:
                $this->mbhFormBuilder->mergeFormFields($builder, SiteForm::class, $builder->getData(), ['keyWords']);
                break;
        }
    }
}
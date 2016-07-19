<?php

/* knp_menu_base.html.twig */
class __TwigTemplate_1cefcc7a136bc0d40ea3b71b5f0b7bed584892a00f105e59e12fb7a75272433a extends MBH\Bundle\BaseBundle\Twig\Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = array(
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        $__internal_36f25932d289c47de28c09ca1f6755fb1dcc2cae98ca2a01502fdc71b6cc28e5 = $this->env->getExtension("native_profiler");
        $__internal_36f25932d289c47de28c09ca1f6755fb1dcc2cae98ca2a01502fdc71b6cc28e5->enter($__internal_36f25932d289c47de28c09ca1f6755fb1dcc2cae98ca2a01502fdc71b6cc28e5_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "knp_menu_base.html.twig"));

        // line 1
        if ($this->getAttribute((isset($context["options"]) ? $context["options"] : $this->getContext($context, "options")), "compressed", array())) {
            $this->displayBlock("compressed_root", $context, $blocks);
        } else {
            $this->displayBlock("root", $context, $blocks);
        }
        
        $__internal_36f25932d289c47de28c09ca1f6755fb1dcc2cae98ca2a01502fdc71b6cc28e5->leave($__internal_36f25932d289c47de28c09ca1f6755fb1dcc2cae98ca2a01502fdc71b6cc28e5_prof);

    }

    public function getTemplateName()
    {
        return "knp_menu_base.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  22 => 1,);
    }
}
/* {% if options.compressed %}{{ block('compressed_root') }}{% else %}{{ block('root') }}{% endif %}*/
/* */

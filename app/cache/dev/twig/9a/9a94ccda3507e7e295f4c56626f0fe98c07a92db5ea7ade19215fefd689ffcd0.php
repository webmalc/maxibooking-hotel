<?php

/* MBHBaseBundle::logo.html.twig */
class __TwigTemplate_b27fe8dbab4185e7cec68e3e473559877a1c24a983abd090f941db1648730347 extends MBH\Bundle\BaseBundle\Twig\Template
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
        $__internal_9e70ef4d624032e2f142deef73be96a190e3614f41b7a6b3944e16bb38b98618 = $this->env->getExtension("native_profiler");
        $__internal_9e70ef4d624032e2f142deef73be96a190e3614f41b7a6b3944e16bb38b98618->enter($__internal_9e70ef4d624032e2f142deef73be96a190e3614f41b7a6b3944e16bb38b98618_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "MBHBaseBundle::logo.html.twig"));

        // line 1
        echo "<a href=\"";
        echo $this->env->getExtension('routing')->getPath("_welcome");
        echo "\" class=\"logo\">
    <span class=\"logo-mini\"><i class=\"mb-icon-logo navbar-logo\"></i></span>
    <span class=\"logo-lg\">
        <i class=\"mb-icon-logo navbar-logo\"></i>&nbsp;<b>Maxi</b>Booking
    </span>
</a>";
        
        $__internal_9e70ef4d624032e2f142deef73be96a190e3614f41b7a6b3944e16bb38b98618->leave($__internal_9e70ef4d624032e2f142deef73be96a190e3614f41b7a6b3944e16bb38b98618_prof);

    }

    public function getTemplateName()
    {
        return "MBHBaseBundle::logo.html.twig";
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
/* <a href="{{ path('_welcome') }}" class="logo">*/
/*     <span class="logo-mini"><i class="mb-icon-logo navbar-logo"></i></span>*/
/*     <span class="logo-lg">*/
/*         <i class="mb-icon-logo navbar-logo"></i>&nbsp;<b>Maxi</b>Booking*/
/*     </span>*/
/* </a>*/

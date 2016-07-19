<?php

/* @WebProfiler/Collector/router.html.twig */
class __TwigTemplate_a4c496a1e1aa7dce8073a916bb5500521b148b97160d5027fb8bc366aeeab3fc extends MBH\Bundle\BaseBundle\Twig\Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        // line 1
        $this->parent = $this->loadTemplate("@WebProfiler/Profiler/layout.html.twig", "@WebProfiler/Collector/router.html.twig", 1);
        $this->blocks = array(
            'toolbar' => array($this, 'block_toolbar'),
            'menu' => array($this, 'block_menu'),
            'panel' => array($this, 'block_panel'),
        );
    }

    protected function doGetParent(array $context)
    {
        return "@WebProfiler/Profiler/layout.html.twig";
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        $__internal_8feb1518e6e1b83649657a6b2962d31a828206b7653b0c9701ea45838e4c82cd = $this->env->getExtension("native_profiler");
        $__internal_8feb1518e6e1b83649657a6b2962d31a828206b7653b0c9701ea45838e4c82cd->enter($__internal_8feb1518e6e1b83649657a6b2962d31a828206b7653b0c9701ea45838e4c82cd_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "@WebProfiler/Collector/router.html.twig"));

        $this->parent->display($context, array_merge($this->blocks, $blocks));
        
        $__internal_8feb1518e6e1b83649657a6b2962d31a828206b7653b0c9701ea45838e4c82cd->leave($__internal_8feb1518e6e1b83649657a6b2962d31a828206b7653b0c9701ea45838e4c82cd_prof);

    }

    // line 3
    public function block_toolbar($context, array $blocks = array())
    {
        $__internal_e7f46c965b6be486ce78664b1f158cae1a210c384c284827875dea1626856d31 = $this->env->getExtension("native_profiler");
        $__internal_e7f46c965b6be486ce78664b1f158cae1a210c384c284827875dea1626856d31->enter($__internal_e7f46c965b6be486ce78664b1f158cae1a210c384c284827875dea1626856d31_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "toolbar"));

        
        $__internal_e7f46c965b6be486ce78664b1f158cae1a210c384c284827875dea1626856d31->leave($__internal_e7f46c965b6be486ce78664b1f158cae1a210c384c284827875dea1626856d31_prof);

    }

    // line 5
    public function block_menu($context, array $blocks = array())
    {
        $__internal_51795174fd99b7e58d2872e9d9f90c17f1a5dd6e987485eb561097f96a49aecb = $this->env->getExtension("native_profiler");
        $__internal_51795174fd99b7e58d2872e9d9f90c17f1a5dd6e987485eb561097f96a49aecb->enter($__internal_51795174fd99b7e58d2872e9d9f90c17f1a5dd6e987485eb561097f96a49aecb_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "menu"));

        // line 6
        echo "<span class=\"label\">
    <span class=\"icon\">";
        // line 7
        echo twig_include($this->env, $context, "@WebProfiler/Icon/router.svg");
        echo "</span>
    <strong>Routing</strong>
</span>
";
        
        $__internal_51795174fd99b7e58d2872e9d9f90c17f1a5dd6e987485eb561097f96a49aecb->leave($__internal_51795174fd99b7e58d2872e9d9f90c17f1a5dd6e987485eb561097f96a49aecb_prof);

    }

    // line 12
    public function block_panel($context, array $blocks = array())
    {
        $__internal_44cbdac2441c9eaee9802c7bce13a5b5132fa9f281943f3518dc98cdd30f8d64 = $this->env->getExtension("native_profiler");
        $__internal_44cbdac2441c9eaee9802c7bce13a5b5132fa9f281943f3518dc98cdd30f8d64->enter($__internal_44cbdac2441c9eaee9802c7bce13a5b5132fa9f281943f3518dc98cdd30f8d64_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "panel"));

        // line 13
        echo "    ";
        echo $this->env->getExtension('http_kernel')->renderFragment($this->env->getExtension('routing')->getPath("_profiler_router", array("token" => (isset($context["token"]) ? $context["token"] : $this->getContext($context, "token")))));
        echo "
";
        
        $__internal_44cbdac2441c9eaee9802c7bce13a5b5132fa9f281943f3518dc98cdd30f8d64->leave($__internal_44cbdac2441c9eaee9802c7bce13a5b5132fa9f281943f3518dc98cdd30f8d64_prof);

    }

    public function getTemplateName()
    {
        return "@WebProfiler/Collector/router.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  73 => 13,  67 => 12,  56 => 7,  53 => 6,  47 => 5,  36 => 3,  11 => 1,);
    }
}
/* {% extends '@WebProfiler/Profiler/layout.html.twig' %}*/
/* */
/* {% block toolbar %}{% endblock %}*/
/* */
/* {% block menu %}*/
/* <span class="label">*/
/*     <span class="icon">{{ include('@WebProfiler/Icon/router.svg') }}</span>*/
/*     <strong>Routing</strong>*/
/* </span>*/
/* {% endblock %}*/
/* */
/* {% block panel %}*/
/*     {{ render(path('_profiler_router', { token: token })) }}*/
/* {% endblock %}*/
/* */

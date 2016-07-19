<?php

/* @WebProfiler/Collector/exception.html.twig */
class __TwigTemplate_bbda6b13834efc9c5653ae144ff85e9619d79b82d51754e29d8f58f10a5cea32 extends MBH\Bundle\BaseBundle\Twig\Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        // line 1
        $this->parent = $this->loadTemplate("@WebProfiler/Profiler/layout.html.twig", "@WebProfiler/Collector/exception.html.twig", 1);
        $this->blocks = array(
            'head' => array($this, 'block_head'),
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
        $__internal_fd265e56d9a22a02a2db5742ee9a5520693bd5141975f5969b242a7a93880310 = $this->env->getExtension("native_profiler");
        $__internal_fd265e56d9a22a02a2db5742ee9a5520693bd5141975f5969b242a7a93880310->enter($__internal_fd265e56d9a22a02a2db5742ee9a5520693bd5141975f5969b242a7a93880310_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "@WebProfiler/Collector/exception.html.twig"));

        $this->parent->display($context, array_merge($this->blocks, $blocks));
        
        $__internal_fd265e56d9a22a02a2db5742ee9a5520693bd5141975f5969b242a7a93880310->leave($__internal_fd265e56d9a22a02a2db5742ee9a5520693bd5141975f5969b242a7a93880310_prof);

    }

    // line 3
    public function block_head($context, array $blocks = array())
    {
        $__internal_608e2c1b1b5aebeba3e5b2c418286a8b792c69b26577ededd5f3fc20327acdf2 = $this->env->getExtension("native_profiler");
        $__internal_608e2c1b1b5aebeba3e5b2c418286a8b792c69b26577ededd5f3fc20327acdf2->enter($__internal_608e2c1b1b5aebeba3e5b2c418286a8b792c69b26577ededd5f3fc20327acdf2_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "head"));

        // line 4
        echo "    ";
        if ($this->getAttribute((isset($context["collector"]) ? $context["collector"] : $this->getContext($context, "collector")), "hasexception", array())) {
            // line 5
            echo "        <style>
            ";
            // line 6
            echo $this->env->getExtension('http_kernel')->renderFragment($this->env->getExtension('routing')->getPath("_profiler_exception_css", array("token" => (isset($context["token"]) ? $context["token"] : $this->getContext($context, "token")))));
            echo "
        </style>
    ";
        }
        // line 9
        echo "    ";
        $this->displayParentBlock("head", $context, $blocks);
        echo "
";
        
        $__internal_608e2c1b1b5aebeba3e5b2c418286a8b792c69b26577ededd5f3fc20327acdf2->leave($__internal_608e2c1b1b5aebeba3e5b2c418286a8b792c69b26577ededd5f3fc20327acdf2_prof);

    }

    // line 12
    public function block_menu($context, array $blocks = array())
    {
        $__internal_74427e9c0dd396ed311dd8164a26a56e5f67009f2c98ca69ddd66410ccd69e1e = $this->env->getExtension("native_profiler");
        $__internal_74427e9c0dd396ed311dd8164a26a56e5f67009f2c98ca69ddd66410ccd69e1e->enter($__internal_74427e9c0dd396ed311dd8164a26a56e5f67009f2c98ca69ddd66410ccd69e1e_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "menu"));

        // line 13
        echo "    <span class=\"label ";
        echo (($this->getAttribute((isset($context["collector"]) ? $context["collector"] : $this->getContext($context, "collector")), "hasexception", array())) ? ("label-status-error") : ("disabled"));
        echo "\">
        <span class=\"icon\">";
        // line 14
        echo twig_include($this->env, $context, "@WebProfiler/Icon/exception.svg");
        echo "</span>
        <strong>Exception</strong>
        ";
        // line 16
        if ($this->getAttribute((isset($context["collector"]) ? $context["collector"] : $this->getContext($context, "collector")), "hasexception", array())) {
            // line 17
            echo "            <span class=\"count\">
                <span>1</span>
            </span>
        ";
        }
        // line 21
        echo "    </span>
";
        
        $__internal_74427e9c0dd396ed311dd8164a26a56e5f67009f2c98ca69ddd66410ccd69e1e->leave($__internal_74427e9c0dd396ed311dd8164a26a56e5f67009f2c98ca69ddd66410ccd69e1e_prof);

    }

    // line 24
    public function block_panel($context, array $blocks = array())
    {
        $__internal_2269eeaee681777e200c8fbee7a873028aedb6b364eb2f0af086162ff8e56a29 = $this->env->getExtension("native_profiler");
        $__internal_2269eeaee681777e200c8fbee7a873028aedb6b364eb2f0af086162ff8e56a29->enter($__internal_2269eeaee681777e200c8fbee7a873028aedb6b364eb2f0af086162ff8e56a29_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "panel"));

        // line 25
        echo "    <h2>Exceptions</h2>

    ";
        // line 27
        if ( !$this->getAttribute((isset($context["collector"]) ? $context["collector"] : $this->getContext($context, "collector")), "hasexception", array())) {
            // line 28
            echo "        <div class=\"empty\">
            <p>No exception was thrown and caught during the request.</p>
        </div>
    ";
        } else {
            // line 32
            echo "        <div class=\"sf-reset\">
            ";
            // line 33
            echo $this->env->getExtension('http_kernel')->renderFragment($this->env->getExtension('routing')->getPath("_profiler_exception", array("token" => (isset($context["token"]) ? $context["token"] : $this->getContext($context, "token")))));
            echo "
        </div>
    ";
        }
        
        $__internal_2269eeaee681777e200c8fbee7a873028aedb6b364eb2f0af086162ff8e56a29->leave($__internal_2269eeaee681777e200c8fbee7a873028aedb6b364eb2f0af086162ff8e56a29_prof);

    }

    public function getTemplateName()
    {
        return "@WebProfiler/Collector/exception.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  117 => 33,  114 => 32,  108 => 28,  106 => 27,  102 => 25,  96 => 24,  88 => 21,  82 => 17,  80 => 16,  75 => 14,  70 => 13,  64 => 12,  54 => 9,  48 => 6,  45 => 5,  42 => 4,  36 => 3,  11 => 1,);
    }
}
/* {% extends '@WebProfiler/Profiler/layout.html.twig' %}*/
/* */
/* {% block head %}*/
/*     {% if collector.hasexception %}*/
/*         <style>*/
/*             {{ render(path('_profiler_exception_css', { token: token })) }}*/
/*         </style>*/
/*     {% endif %}*/
/*     {{ parent() }}*/
/* {% endblock %}*/
/* */
/* {% block menu %}*/
/*     <span class="label {{ collector.hasexception ? 'label-status-error' : 'disabled' }}">*/
/*         <span class="icon">{{ include('@WebProfiler/Icon/exception.svg') }}</span>*/
/*         <strong>Exception</strong>*/
/*         {% if collector.hasexception %}*/
/*             <span class="count">*/
/*                 <span>1</span>*/
/*             </span>*/
/*         {% endif %}*/
/*     </span>*/
/* {% endblock %}*/
/* */
/* {% block panel %}*/
/*     <h2>Exceptions</h2>*/
/* */
/*     {% if not collector.hasexception %}*/
/*         <div class="empty">*/
/*             <p>No exception was thrown and caught during the request.</p>*/
/*         </div>*/
/*     {% else %}*/
/*         <div class="sf-reset">*/
/*             {{ render(path('_profiler_exception', { token: token })) }}*/
/*         </div>*/
/*     {% endif %}*/
/* {% endblock %}*/
/* */

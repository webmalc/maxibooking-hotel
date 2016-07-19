<?php

/* MBHBaseBundle:Partials:filter.html.twig */
class __TwigTemplate_f742ab701d3e813c8cec9c11a7b943f3576905b4752d289a3cfb1c085994a0c0 extends MBH\Bundle\BaseBundle\Twig\Template
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
        $__internal_d11741926e5e51b122e8e958564abef3e3cba3fe385611a9a29ed5da44f2ba02 = $this->env->getExtension("native_profiler");
        $__internal_d11741926e5e51b122e8e958564abef3e3cba3fe385611a9a29ed5da44f2ba02->enter($__internal_d11741926e5e51b122e8e958564abef3e3cba3fe385611a9a29ed5da44f2ba02_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "MBHBaseBundle:Partials:filter.html.twig"));

        // line 2
        echo "<div class=\"box box-default box-solid\" ";
        if (array_key_exists("id", $context)) {
            echo "id=\"";
            echo twig_escape_filter($this->env, (isset($context["id"]) ? $context["id"] : $this->getContext($context, "id")), "html", null, true);
            echo "\"";
        }
        echo ">
    <div class=\"box-header\">
        <h3 class=\"box-title\">";
        // line 4
        echo $this->env->getExtension('translator')->trans(((array_key_exists("header", $context)) ? (_twig_default_filter((isset($context["header"]) ? $context["header"] : $this->getContext($context, "header")), "form.filter")) : ("form.filter")));
        echo "
            ";
        // line 5
        if ((array_key_exists("smallHeader", $context) &&  !twig_test_empty((isset($context["smallHeader"]) ? $context["smallHeader"] : $this->getContext($context, "smallHeader"))))) {
            // line 6
            echo "                <small>";
            echo $this->env->getExtension('translator')->trans((isset($context["smallHeader"]) ? $context["smallHeader"] : $this->getContext($context, "smallHeader")));
            echo "</small>
            ";
        }
        // line 8
        echo "        </h3>
        <div class=\"box-tools pull-right\">
            <button class=\"btn btn-box-tool form-group-collapse\" ";
        // line 10
        if (array_key_exists("id", $context)) {
            echo "id=\"";
            echo twig_escape_filter($this->env, (isset($context["id"]) ? $context["id"] : $this->getContext($context, "id")), "html", null, true);
            echo "-collapse\"";
        }
        echo " data-widget=\"collapse\"><i class=\"fa fa-minus\"></i></button>
        </div>
    </div>
    <div class=\"";
        // line 13
        echo twig_escape_filter($this->env, ((array_key_exists("class", $context)) ? (_twig_default_filter((isset($context["class"]) ? $context["class"] : $this->getContext($context, "class")), "bg-gray-disabled color-palette")) : ("bg-gray-disabled color-palette")), "html", null, true);
        echo " box-body\">";
        
        $__internal_d11741926e5e51b122e8e958564abef3e3cba3fe385611a9a29ed5da44f2ba02->leave($__internal_d11741926e5e51b122e8e958564abef3e3cba3fe385611a9a29ed5da44f2ba02_prof);

    }

    public function getTemplateName()
    {
        return "MBHBaseBundle:Partials:filter.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  58 => 13,  48 => 10,  44 => 8,  38 => 6,  36 => 5,  32 => 4,  22 => 2,);
    }
}
/* {#todo remove. use embed_filter.html.twig#}*/
/* <div class="box box-default box-solid" {% if id is defined %}id="{{ id }}"{% endif %}>*/
/*     <div class="box-header">*/
/*         <h3 class="box-title">{{ header|default('form.filter')|trans|raw }}*/
/*             {% if smallHeader is defined and smallHeader is not empty %}*/
/*                 <small>{{ smallHeader|trans|raw }}</small>*/
/*             {% endif %}*/
/*         </h3>*/
/*         <div class="box-tools pull-right">*/
/*             <button class="btn btn-box-tool form-group-collapse" {% if id is defined %}id="{{ id }}-collapse"{% endif %} data-widget="collapse"><i class="fa fa-minus"></i></button>*/
/*         </div>*/
/*     </div>*/
/*     <div class="{{ class|default('bg-gray-disabled color-palette')}} box-body">*/

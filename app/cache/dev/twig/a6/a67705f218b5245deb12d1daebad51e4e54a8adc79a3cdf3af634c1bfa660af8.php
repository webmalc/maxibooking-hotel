<?php

/* MBHBaseBundle:Actions:update.html.twig */
class __TwigTemplate_7a033217015c0ac0f56d4f880b30af08cc904d2a8ef63ef6700be5e18be5c2c0 extends MBH\Bundle\BaseBundle\Twig\Template
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
        $__internal_a18a0582b18579de6f9578bb094f6a2fc0dc85fc8ed32f742aa5927456e56975 = $this->env->getExtension("native_profiler");
        $__internal_a18a0582b18579de6f9578bb094f6a2fc0dc85fc8ed32f742aa5927456e56975->enter($__internal_a18a0582b18579de6f9578bb094f6a2fc0dc85fc8ed32f742aa5927456e56975_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "MBHBaseBundle:Actions:update.html.twig"));

        // line 1
        if ( !array_key_exists("save_close", $context)) {
            // line 2
            echo "    ";
            $context["save_close"] = true;
        }
        // line 4
        echo "
";
        // line 5
        if (( !array_key_exists("delete_role", $context) && array_key_exists("route", $context))) {
            // line 6
            echo "    ";
            $context["delete_role"] = (("ROLE_" . twig_upper_filter($this->env, (isset($context["route"]) ? $context["route"] : $this->getContext($context, "route")))) . "_DELETE");
        }
        // line 8
        echo "
";
        // line 9
        if (( !array_key_exists("delete_route", $context) && array_key_exists("route", $context))) {
            // line 10
            echo "    ";
            $context["delete_route"] = ((isset($context["route"]) ? $context["route"] : $this->getContext($context, "route")) . "_delete");
        }
        // line 12
        echo "

<div id=\"actions\" class=\"navbar navbar-default navbar-fixed-bottom main-footer\">
    <div class=\"container-fluid\">
        ";
        // line 16
        $this->loadTemplate("MBHBaseBundle:Actions:saveButtons.html.twig", "MBHBaseBundle:Actions:update.html.twig", 16)->display(array_merge($context, array("save_close" => (isset($context["save_close"]) ? $context["save_close"] : $this->getContext($context, "save_close")), "save_text" => ((array_key_exists("save_text", $context)) ? (_twig_default_filter((isset($context["save_text"]) ? $context["save_text"] : $this->getContext($context, "save_text")), $this->env->getExtension('translator')->trans("views.actions.update.save", array(), "MBHBaseBundle"))) : ($this->env->getExtension('translator')->trans("views.actions.update.save", array(), "MBHBaseBundle"))))));
        // line 17
        echo "
        ";
        // line 18
        if ((((array_key_exists("delete_route", $context) &&  !twig_test_empty((isset($context["delete_route"]) ? $context["delete_route"] : $this->getContext($context, "delete_route")))) && array_key_exists("delete_role", $context)) && $this->env->getExtension('security')->isGranted((isset($context["delete_role"]) ? $context["delete_role"] : $this->getContext($context, "delete_role"))))) {
            // line 19
            echo "        <ul class=\"nav navbar-nav navbar-right\">
            <li>
                <button data-href=\"";
            // line 21
            echo twig_escape_filter($this->env, $this->env->getExtension('routing')->getPath((isset($context["delete_route"]) ? $context["delete_route"] : $this->getContext($context, "delete_route")), array("id" => $this->getAttribute((isset($context["entity"]) ? $context["entity"] : $this->getContext($context, "entity")), "id", array()))), "html", null, true);
            echo "\" ";
            if (array_key_exists("confirm_text", $context)) {
                echo " data-text=\"";
                echo twig_escape_filter($this->env, (isset($context["confirm_text"]) ? $context["confirm_text"] : $this->getContext($context, "confirm_text")), "html", null, true);
                echo "\" ";
            }
            echo " class=\"btn btn-danger navbar-btn delete-link\">
                    <i class=\"fa fa-trash-o\"> </i> ";
            // line 22
            echo twig_escape_filter($this->env, ((array_key_exists("delete_title", $context)) ? (_twig_default_filter((isset($context["delete_title"]) ? $context["delete_title"] : $this->getContext($context, "delete_title")), $this->env->getExtension('translator')->trans("views.actions.update.delete", array(), "MBHBaseBundle"))) : ($this->env->getExtension('translator')->trans("views.actions.update.delete", array(), "MBHBaseBundle"))), "html", null, true);
            echo "
                </button>
            </li>
        </ul>
        ";
        }
        // line 27
        echo "    </div>
</div>";
        
        $__internal_a18a0582b18579de6f9578bb094f6a2fc0dc85fc8ed32f742aa5927456e56975->leave($__internal_a18a0582b18579de6f9578bb094f6a2fc0dc85fc8ed32f742aa5927456e56975_prof);

    }

    public function getTemplateName()
    {
        return "MBHBaseBundle:Actions:update.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  81 => 27,  73 => 22,  63 => 21,  59 => 19,  57 => 18,  54 => 17,  52 => 16,  46 => 12,  42 => 10,  40 => 9,  37 => 8,  33 => 6,  31 => 5,  28 => 4,  24 => 2,  22 => 1,);
    }
}
/* {% if save_close is not defined %}*/
/*     {% set save_close = true %}*/
/* {% endif %}*/
/* */
/* {% if delete_role is not defined and route is defined%}*/
/*     {% set delete_role = 'ROLE_' ~route|upper ~ '_DELETE' %}*/
/* {% endif %}*/
/* */
/* {% if delete_route is not defined and route is defined %}*/
/*     {% set delete_route = route ~ '_delete' %}*/
/* {% endif %}*/
/* */
/* */
/* <div id="actions" class="navbar navbar-default navbar-fixed-bottom main-footer">*/
/*     <div class="container-fluid">*/
/*         {% include 'MBHBaseBundle:Actions:saveButtons.html.twig' with {'save_close': save_close, 'save_text': save_text|default('views.actions.update.save'|trans({}, 'MBHBaseBundle'))}%}*/
/* */
/*         {% if delete_route is defined and delete_route is not empty and delete_role is defined and is_granted(delete_role) %}*/
/*         <ul class="nav navbar-nav navbar-right">*/
/*             <li>*/
/*                 <button data-href="{{ path(delete_route, {'id': entity.id}) }}" {% if confirm_text is defined  %} data-text="{{ confirm_text }}" {% endif %} class="btn btn-danger navbar-btn delete-link">*/
/*                     <i class="fa fa-trash-o"> </i> {{ delete_title|default('views.actions.update.delete'|trans({}, 'MBHBaseBundle')) }}*/
/*                 </button>*/
/*             </li>*/
/*         </ul>*/
/*         {% endif %}*/
/*     </div>*/
/* </div>*/

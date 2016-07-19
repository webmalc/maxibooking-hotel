<?php

/* MBHRestaurantBundle:DishOrder:layout.html.twig */
class __TwigTemplate_002846af1608dbd097b0ca3f108bfc45f90a11cb118ab7b60a3b7a77cee1ebf5 extends MBH\Bundle\BaseBundle\Twig\Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        // line 1
        $this->parent = $this->loadTemplate("MBHRestaurantBundle::layout.html.twig", "MBHRestaurantBundle:DishOrder:layout.html.twig", 1);
        $this->blocks = array(
            'scripts' => array($this, 'block_scripts'),
        );
    }

    protected function doGetParent(array $context)
    {
        return "MBHRestaurantBundle::layout.html.twig";
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        $__internal_03a8bf9a3a1d5f95cc0738c583365b8857289ffa2742c037575bae9e1409aa8e = $this->env->getExtension("native_profiler");
        $__internal_03a8bf9a3a1d5f95cc0738c583365b8857289ffa2742c037575bae9e1409aa8e->enter($__internal_03a8bf9a3a1d5f95cc0738c583365b8857289ffa2742c037575bae9e1409aa8e_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "MBHRestaurantBundle:DishOrder:layout.html.twig"));

        // line 2
        $context["title"] = twig_capitalize_string_filter($this->env, $this->env->getExtension('translator')->trans("restaurant.dishorder.common.title"));
        // line 3
        $context["title_url"] = $this->env->getExtension('routing')->getPath("restaurant_dishorder_list");
        // line 1
        $this->parent->display($context, array_merge($this->blocks, $blocks));
        
        $__internal_03a8bf9a3a1d5f95cc0738c583365b8857289ffa2742c037575bae9e1409aa8e->leave($__internal_03a8bf9a3a1d5f95cc0738c583365b8857289ffa2742c037575bae9e1409aa8e_prof);

    }

    // line 5
    public function block_scripts($context, array $blocks = array())
    {
        $__internal_06e841da0642b5b6dfbaa624a384305bcdeb7bc4fe655c235f241144322c4f6b = $this->env->getExtension("native_profiler");
        $__internal_06e841da0642b5b6dfbaa624a384305bcdeb7bc4fe655c235f241144322c4f6b->enter($__internal_06e841da0642b5b6dfbaa624a384305bcdeb7bc4fe655c235f241144322c4f6b_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "scripts"));

        // line 6
        echo "    ";
        $this->displayParentBlock("scripts", $context, $blocks);
        echo "

    ";
        // line 8
        if (isset($context['assetic']['debug']) && $context['assetic']['debug']) {
            // asset "52dd436_0"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('routing')->getPath("_assetic_52dd436_0") : $this->env->getExtension('asset')->getAssetUrl("js/52dd436_005-filterOrder_1.js");
            // line 11
            echo "    <script type=\"text/javascript\" src=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : $this->getContext($context, "asset_url")), "html", null, true);
            echo "\"></script>
    ";
        } else {
            // asset "52dd436"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('routing')->getPath("_assetic_52dd436") : $this->env->getExtension('asset')->getAssetUrl("js/52dd436.js");
            echo "    <script type=\"text/javascript\" src=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : $this->getContext($context, "asset_url")), "html", null, true);
            echo "\"></script>
    ";
        }
        unset($context["asset_url"]);
        
        $__internal_06e841da0642b5b6dfbaa624a384305bcdeb7bc4fe655c235f241144322c4f6b->leave($__internal_06e841da0642b5b6dfbaa624a384305bcdeb7bc4fe655c235f241144322c4f6b_prof);

    }

    public function getTemplateName()
    {
        return "MBHRestaurantBundle:DishOrder:layout.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  55 => 11,  51 => 8,  45 => 6,  39 => 5,  32 => 1,  30 => 3,  28 => 2,  11 => 1,);
    }
}
/* {% extends 'MBHRestaurantBundle::layout.html.twig' %}*/
/* {% set title = 'restaurant.dishorder.common.title'|trans|capitalize %}*/
/* {% set title_url = path('restaurant_dishorder_list') %}*/
/* */
/* {% block scripts %}*/
/*     {{ parent() }}*/
/* */
/*     {% javascripts filter='uglifyjs2'*/
/*     '@MBHRestaurantBundle/Resources/public/js/005-filterOrder.js'*/
/*     %}*/
/*     <script type="text/javascript" src="{{ asset_url }}"></script>*/
/*     {% endjavascripts %}*/
/* {% endblock %}*/
/* */

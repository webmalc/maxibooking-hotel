<?php

/* MBHRestaurantBundle:DishOrder:newOrder.html.twig */
class __TwigTemplate_9dfe2fd39b98d336792dea93191882f51a7d1757bfc25c9c344ecf930d0f0f62 extends MBH\Bundle\BaseBundle\Twig\Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        // line 1
        $this->parent = $this->loadTemplate("MBHRestaurantBundle:DishOrder:layout.html.twig", "MBHRestaurantBundle:DishOrder:newOrder.html.twig", 1);
        $this->blocks = array(
            'content' => array($this, 'block_content'),
            'editAction' => array($this, 'block_editAction'),
            'scripts' => array($this, 'block_scripts'),
        );
    }

    protected function doGetParent(array $context)
    {
        return "MBHRestaurantBundle:DishOrder:layout.html.twig";
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        $__internal_0089e1de64e09baf6a2aaf7e317cee6ce8a66bbf08a8bb92774094e34bc36270 = $this->env->getExtension("native_profiler");
        $__internal_0089e1de64e09baf6a2aaf7e317cee6ce8a66bbf08a8bb92774094e34bc36270->enter($__internal_0089e1de64e09baf6a2aaf7e317cee6ce8a66bbf08a8bb92774094e34bc36270_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "MBHRestaurantBundle:DishOrder:newOrder.html.twig"));

        // line 3
        $context["small_title"] = $this->env->getExtension('translator')->trans("restaurant.dishorder.actions.add");
        // line 4
        $context["layout"] = "box";
        // line 1
        $this->parent->display($context, array_merge($this->blocks, $blocks));
        
        $__internal_0089e1de64e09baf6a2aaf7e317cee6ce8a66bbf08a8bb92774094e34bc36270->leave($__internal_0089e1de64e09baf6a2aaf7e317cee6ce8a66bbf08a8bb92774094e34bc36270_prof);

    }

    // line 6
    public function block_content($context, array $blocks = array())
    {
        $__internal_d53b6aeb433ee5eb0313cf4e0a628ae89d4597e89c81934d06f60404e91a7a9b = $this->env->getExtension("native_profiler");
        $__internal_d53b6aeb433ee5eb0313cf4e0a628ae89d4597e89c81934d06f60404e91a7a9b->enter($__internal_d53b6aeb433ee5eb0313cf4e0a628ae89d4597e89c81934d06f60404e91a7a9b_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "content"));

        // line 7
        echo "    ";
        $this->env->getExtension('form')->renderer->setTheme($this->getAttribute((isset($context["form"]) ? $context["form"] : $this->getContext($context, "form")), "dishes", array()), array(0 => "@MBHRestaurant/Form/dishItemCollection.html.twig"));
        // line 8
        echo "    ";
        echo         $this->env->getExtension('form')->renderer->renderBlock((isset($context["form"]) ? $context["form"] : $this->getContext($context, "form")), 'form_start', array("action" => $this->env->getExtension('routing')->getPath("restaurant_dishorder_new"), "method" => ((array_key_exists("method", $context)) ? (_twig_default_filter((isset($context["method"]) ? $context["method"] : $this->getContext($context, "method")), "POST")) : ("POST")), "attr" => array("class" => "form-horizontal")));
        echo "
    ";
        // line 9
        $this->loadTemplate("MBHBaseBundle:Actions:update.html.twig", "MBHRestaurantBundle:DishOrder:newOrder.html.twig", 9)->display(array_merge($context, array("entity" => (isset($context["order"]) ? $context["order"] : $this->getContext($context, "order")), "delete_route" => "restaurant_dishorder_delete", "delete_role" => "ROLE_RESTAURANT_ORDER_MANAGER_DELETE")));
        // line 10
        echo "    ";
        echo         $this->env->getExtension('form')->renderer->renderBlock((isset($context["form"]) ? $context["form"] : $this->getContext($context, "form")), 'form');
        echo "
    ";
        // line 11
        echo         $this->env->getExtension('form')->renderer->renderBlock((isset($context["form"]) ? $context["form"] : $this->getContext($context, "form")), 'form_end');
        echo "
    ";
        // line 12
        $this->displayBlock('editAction', $context, $blocks);
        
        $__internal_d53b6aeb433ee5eb0313cf4e0a628ae89d4597e89c81934d06f60404e91a7a9b->leave($__internal_d53b6aeb433ee5eb0313cf4e0a628ae89d4597e89c81934d06f60404e91a7a9b_prof);

    }

    public function block_editAction($context, array $blocks = array())
    {
        $__internal_1e5b39615e206b4218a263978ad7bb980a9e222979d774a9f3a3db06db50ed71 = $this->env->getExtension("native_profiler");
        $__internal_1e5b39615e206b4218a263978ad7bb980a9e222979d774a9f3a3db06db50ed71->enter($__internal_1e5b39615e206b4218a263978ad7bb980a9e222979d774a9f3a3db06db50ed71_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "editAction"));

        // line 13
        echo "    ";
        
        $__internal_1e5b39615e206b4218a263978ad7bb980a9e222979d774a9f3a3db06db50ed71->leave($__internal_1e5b39615e206b4218a263978ad7bb980a9e222979d774a9f3a3db06db50ed71_prof);

    }

    // line 16
    public function block_scripts($context, array $blocks = array())
    {
        $__internal_ecdcbb9192d550266e8d7c233fa8159d775cff3f0b8863296bd3d29bae58f9c0 = $this->env->getExtension("native_profiler");
        $__internal_ecdcbb9192d550266e8d7c233fa8159d775cff3f0b8863296bd3d29bae58f9c0->enter($__internal_ecdcbb9192d550266e8d7c233fa8159d775cff3f0b8863296bd3d29bae58f9c0_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "scripts"));

        // line 17
        echo "    ";
        $this->displayParentBlock("scripts", $context, $blocks);
        echo "
    ";
        // line 18
        if (isset($context['assetic']['debug']) && $context['assetic']['debug']) {
            // asset "94eafd0_0"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('routing')->getPath("_assetic_94eafd0_0") : $this->env->getExtension('asset')->getAssetUrl("js/94eafd0_001-add_dish_item_1.js");
            // line 22
            echo "    <script type=\"text/javascript\" src=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : $this->getContext($context, "asset_url")), "html", null, true);
            echo "\"></script>
    ";
            // asset "94eafd0_1"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('routing')->getPath("_assetic_94eafd0_1") : $this->env->getExtension('asset')->getAssetUrl("js/94eafd0_004-calculate-orderprice_2.js");
            echo "    <script type=\"text/javascript\" src=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : $this->getContext($context, "asset_url")), "html", null, true);
            echo "\"></script>
    ";
        } else {
            // asset "94eafd0"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('routing')->getPath("_assetic_94eafd0") : $this->env->getExtension('asset')->getAssetUrl("js/94eafd0.js");
            echo "    <script type=\"text/javascript\" src=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : $this->getContext($context, "asset_url")), "html", null, true);
            echo "\"></script>
    ";
        }
        unset($context["asset_url"]);
        // line 24
        echo "    ";
        $this->loadTemplate("@MBHRestaurant/DishOrder/dishOrderJSVariable.html.twig", "MBHRestaurantBundle:DishOrder:newOrder.html.twig", 24)->display($context);
        
        $__internal_ecdcbb9192d550266e8d7c233fa8159d775cff3f0b8863296bd3d29bae58f9c0->leave($__internal_ecdcbb9192d550266e8d7c233fa8159d775cff3f0b8863296bd3d29bae58f9c0_prof);

    }

    public function getTemplateName()
    {
        return "MBHRestaurantBundle:DishOrder:newOrder.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  120 => 24,  100 => 22,  96 => 18,  91 => 17,  85 => 16,  78 => 13,  66 => 12,  62 => 11,  57 => 10,  55 => 9,  50 => 8,  47 => 7,  41 => 6,  34 => 1,  32 => 4,  30 => 3,  11 => 1,);
    }
}
/* {% extends 'MBHRestaurantBundle:DishOrder:layout.html.twig' %}*/
/* */
/* {% set small_title = 'restaurant.dishorder.actions.add'|trans %}*/
/* {%  set layout = 'box' %}*/
/* */
/* {% block content %}*/
/*     {% form_theme form.dishes '@MBHRestaurant/Form/dishItemCollection.html.twig'  %}*/
/*     {{ form_start(form, {'action': path('restaurant_dishorder_new'), 'method': method|default('POST'), 'attr':{'class':'form-horizontal'}}) }}*/
/*     {% include 'MBHBaseBundle:Actions:update.html.twig' with {'entity': order, 'delete_route': 'restaurant_dishorder_delete', 'delete_role':'ROLE_RESTAURANT_ORDER_MANAGER_DELETE' } %}*/
/*     {{ form(form) }}*/
/*     {{ form_end(form) }}*/
/*     {% block editAction %}*/
/*     {% endblock editAction %}*/
/* {% endblock %}*/
/* */
/* {% block scripts %}*/
/*     {{ parent() }}*/
/*     {% javascripts filter='uglifyjs2'*/
/*     '@MBHRestaurantBundle/Resources/public/js/001-add_dish_item.js'*/
/*     '@MBHRestaurantBundle/Resources/public/js/004-calculate-orderprice.js'*/
/*     %}*/
/*     <script type="text/javascript" src="{{ asset_url }}"></script>*/
/*     {% endjavascripts %}*/
/*     {% include '@MBHRestaurant/DishOrder/dishOrderJSVariable.html.twig' %}*/
/* {% endblock scripts %}*/

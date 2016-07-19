<?php

/* MBHRestaurantBundle::layout.html.twig */
class __TwigTemplate_82c7e792f5800d3efb0b19d3b8f6a841efa781e76549e9bc4bb79fcf02c539c6 extends MBH\Bundle\BaseBundle\Twig\Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        // line 1
        $this->parent = $this->loadTemplate("MBHBaseBundle::page.html.twig", "MBHRestaurantBundle::layout.html.twig", 1);
        $this->blocks = array(
            'scripts' => array($this, 'block_scripts'),
            'styles' => array($this, 'block_styles'),
        );
    }

    protected function doGetParent(array $context)
    {
        return "MBHBaseBundle::page.html.twig";
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        $__internal_4f3819436d63744fbc4c247b2285ab214751addd83120d26ed5f4d634dbff65c = $this->env->getExtension("native_profiler");
        $__internal_4f3819436d63744fbc4c247b2285ab214751addd83120d26ed5f4d634dbff65c->enter($__internal_4f3819436d63744fbc4c247b2285ab214751addd83120d26ed5f4d634dbff65c_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "MBHRestaurantBundle::layout.html.twig"));

        $this->parent->display($context, array_merge($this->blocks, $blocks));
        
        $__internal_4f3819436d63744fbc4c247b2285ab214751addd83120d26ed5f4d634dbff65c->leave($__internal_4f3819436d63744fbc4c247b2285ab214751addd83120d26ed5f4d634dbff65c_prof);

    }

    // line 3
    public function block_scripts($context, array $blocks = array())
    {
        $__internal_40b7ad64a3359708661064759a904539a8c6e744d2a46600408cfaa5d6895274 = $this->env->getExtension("native_profiler");
        $__internal_40b7ad64a3359708661064759a904539a8c6e744d2a46600408cfaa5d6895274->enter($__internal_40b7ad64a3359708661064759a904539a8c6e744d2a46600408cfaa5d6895274_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "scripts"));

        // line 4
        echo "    ";
        $this->displayParentBlock("scripts", $context, $blocks);
        echo "

    ";
        // line 6
        if (isset($context['assetic']['debug']) && $context['assetic']['debug']) {
            // asset "cb61631_0"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('routing')->getPath("_assetic_cb61631_0") : $this->env->getExtension('asset')->getAssetUrl("js/cb61631_003-spinners_1.js");
            // line 9
            echo "    <script type=\"text/javascript\" src=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : $this->getContext($context, "asset_url")), "html", null, true);
            echo "\"></script>
    ";
        } else {
            // asset "cb61631"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('routing')->getPath("_assetic_cb61631") : $this->env->getExtension('asset')->getAssetUrl("js/cb61631.js");
            echo "    <script type=\"text/javascript\" src=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : $this->getContext($context, "asset_url")), "html", null, true);
            echo "\"></script>
    ";
        }
        unset($context["asset_url"]);
        // line 11
        echo "
";
        
        $__internal_40b7ad64a3359708661064759a904539a8c6e744d2a46600408cfaa5d6895274->leave($__internal_40b7ad64a3359708661064759a904539a8c6e744d2a46600408cfaa5d6895274_prof);

    }

    // line 14
    public function block_styles($context, array $blocks = array())
    {
        $__internal_d42bc10f440e439bf7de4d13fb36533ece6c2764632259f3f38786678785dfad = $this->env->getExtension("native_profiler");
        $__internal_d42bc10f440e439bf7de4d13fb36533ece6c2764632259f3f38786678785dfad->enter($__internal_d42bc10f440e439bf7de4d13fb36533ece6c2764632259f3f38786678785dfad_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "styles"));

        // line 15
        echo "    ";
        $this->displayParentBlock("styles", $context, $blocks);
        echo "

    ";
        // line 17
        if (isset($context['assetic']['debug']) && $context['assetic']['debug']) {
            // asset "469d4c0_0"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('routing')->getPath("_assetic_469d4c0_0") : $this->env->getExtension('asset')->getAssetUrl("css/469d4c0_part_1_001-dish_item_1.css");
            // line 18
            echo "    <link rel=\"stylesheet\" href=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : $this->getContext($context, "asset_url")), "html", null, true);
            echo "\"/>
    ";
            // asset "469d4c0_1"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('routing')->getPath("_assetic_469d4c0_1") : $this->env->getExtension('asset')->getAssetUrl("css/469d4c0_part_1_style_2.css");
            echo "    <link rel=\"stylesheet\" href=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : $this->getContext($context, "asset_url")), "html", null, true);
            echo "\"/>
    ";
        } else {
            // asset "469d4c0"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('routing')->getPath("_assetic_469d4c0") : $this->env->getExtension('asset')->getAssetUrl("css/469d4c0.css");
            echo "    <link rel=\"stylesheet\" href=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : $this->getContext($context, "asset_url")), "html", null, true);
            echo "\"/>
    ";
        }
        unset($context["asset_url"]);
        
        $__internal_d42bc10f440e439bf7de4d13fb36533ece6c2764632259f3f38786678785dfad->leave($__internal_d42bc10f440e439bf7de4d13fb36533ece6c2764632259f3f38786678785dfad_prof);

    }

    public function getTemplateName()
    {
        return "MBHRestaurantBundle::layout.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  89 => 18,  85 => 17,  79 => 15,  73 => 14,  65 => 11,  51 => 9,  47 => 6,  41 => 4,  35 => 3,  11 => 1,);
    }
}
/* {% extends "MBHBaseBundle::page.html.twig" %}*/
/* */
/* {% block scripts %}*/
/*     {{ parent() }}*/
/* */
/*     {% javascripts filter='uglifyjs2'*/
/*         '@MBHRestaurantBundle/Resources/public/js/003-spinners.js'*/
/*     %}*/
/*     <script type="text/javascript" src="{{ asset_url }}"></script>*/
/*     {% endjavascripts %}*/
/* */
/* {% endblock %}*/
/* */
/* {% block styles %}*/
/*     {{ parent() }}*/
/* */
/*     {% stylesheets filter='cssrewrite, uglifycss' '@MBHRestaurantBundle/Resources/public/css/*'%}*/
/*     <link rel="stylesheet" href="{{ asset_url }}"/>*/
/*     {% endstylesheets %}*/
/* {% endblock %}*/

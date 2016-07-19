<?php

/* MBHBaseBundle::sidebar.html.twig */
class __TwigTemplate_10e9f85c7dbf136e70c48118e3ecce6dddfad3fe277cf05286fb6b9092afd90b extends MBH\Bundle\BaseBundle\Twig\Template
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
        $__internal_3c41584519d712f5ebfe0877fb25395955695adf6ffb6d58245c2819a77d4c1b = $this->env->getExtension("native_profiler");
        $__internal_3c41584519d712f5ebfe0877fb25395955695adf6ffb6d58245c2819a77d4c1b->enter($__internal_3c41584519d712f5ebfe0877fb25395955695adf6ffb6d58245c2819a77d4c1b_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "MBHBaseBundle::sidebar.html.twig"));

        // line 1
        echo "<aside class=\"main-sidebar\">
    <!-- sidebar: style can be found in sidebar.less -->
    <section class=\"sidebar\">

        <!-- Sidebar Menu -->
        ";
        // line 6
        if (twig_test_empty($this->env->getExtension('mbh_hotel_selector_extension')->getSelectedHotel())) {
            // line 7
            echo "            ";
            $context["menu"] = $this->env->getExtension('knp_menu')->get("MBHBaseBundle:Builder:createHotelMenu", array(), array("title_url" => ((array_key_exists("title_url", $context)) ? ((isset($context["title_url"]) ? $context["title_url"] : $this->getContext($context, "title_url"))) : (null))));
            // line 8
            echo "            ";
            echo $this->env->getExtension('knp_menu')->render((isset($context["menu"]) ? $context["menu"] : $this->getContext($context, "menu")));
            echo "
        ";
        } else {
            // line 10
            echo "            ";
            $context["menu"] = $this->env->getExtension('knp_menu')->get("MBHBaseBundle:Builder:mainMenu", array(), array("title_url" => ((array_key_exists("title_url", $context)) ? ((isset($context["title_url"]) ? $context["title_url"] : $this->getContext($context, "title_url"))) : (null))));
            // line 11
            echo "            ";
            echo $this->env->getExtension('knp_menu')->render((isset($context["menu"]) ? $context["menu"] : $this->getContext($context, "menu")));
            echo "
            ";
            // line 12
            $context["menu"] = $this->env->getExtension('knp_menu')->get("MBHBaseBundle:Builder:managementMenu", array(), array("title_url" => ((array_key_exists("title_url", $context)) ? ((isset($context["title_url"]) ? $context["title_url"] : $this->getContext($context, "title_url"))) : (null))));
            // line 13
            echo "            ";
            echo $this->env->getExtension('knp_menu')->render((isset($context["menu"]) ? $context["menu"] : $this->getContext($context, "menu")));
            echo "
        ";
        }
        // line 15
        echo "        <!-- /.sidebar-menu -->
    </section>
    <!-- /.sidebar -->
</aside>";
        
        $__internal_3c41584519d712f5ebfe0877fb25395955695adf6ffb6d58245c2819a77d4c1b->leave($__internal_3c41584519d712f5ebfe0877fb25395955695adf6ffb6d58245c2819a77d4c1b_prof);

    }

    public function getTemplateName()
    {
        return "MBHBaseBundle::sidebar.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  56 => 15,  50 => 13,  48 => 12,  43 => 11,  40 => 10,  34 => 8,  31 => 7,  29 => 6,  22 => 1,);
    }
}
/* <aside class="main-sidebar">*/
/*     <!-- sidebar: style can be found in sidebar.less -->*/
/*     <section class="sidebar">*/
/* */
/*         <!-- Sidebar Menu -->*/
/*         {% if selected_hotel() is empty %}*/
/*             {% set menu = knp_menu_get('MBHBaseBundle:Builder:createHotelMenu', [], {'title_url': title_url is defined ? title_url : null}) %}*/
/*             {{ knp_menu_render(menu) }}*/
/*         {% else %}*/
/*             {% set menu = knp_menu_get('MBHBaseBundle:Builder:mainMenu', [], {'title_url': title_url is defined ? title_url : null}) %}*/
/*             {{ knp_menu_render(menu) }}*/
/*             {% set menu = knp_menu_get('MBHBaseBundle:Builder:managementMenu', [], {'title_url': title_url is defined ? title_url : null}) %}*/
/*             {{ knp_menu_render(menu) }}*/
/*         {% endif %}*/
/*         <!-- /.sidebar-menu -->*/
/*     </section>*/
/*     <!-- /.sidebar -->*/
/* </aside>*/

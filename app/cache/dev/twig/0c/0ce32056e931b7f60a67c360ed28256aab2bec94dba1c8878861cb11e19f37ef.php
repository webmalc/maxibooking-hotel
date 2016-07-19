<?php

/* MBHBaseBundle::page.html.twig */
class __TwigTemplate_38cfb20c45e95bc38d31eaf8ca3908e0a60ceff0e89108aea43e3367ae465ed8 extends MBH\Bundle\BaseBundle\Twig\Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        // line 1
        $this->parent = $this->loadTemplate("MBHBaseBundle::meta.html.twig", "MBHBaseBundle::page.html.twig", 1);
        $this->blocks = array(
            'body' => array($this, 'block_body'),
            'print_hotel_logo' => array($this, 'block_print_hotel_logo'),
            'messages' => array($this, 'block_messages'),
            'prepend_content' => array($this, 'block_prepend_content'),
            'content' => array($this, 'block_content'),
            'append_content' => array($this, 'block_append_content'),
        );
    }

    protected function doGetParent(array $context)
    {
        return "MBHBaseBundle::meta.html.twig";
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        $__internal_9f270c55d06081ab309f1c2f6a32767fae9d5ab5e0e0c31edd4a7f61261dc437 = $this->env->getExtension("native_profiler");
        $__internal_9f270c55d06081ab309f1c2f6a32767fae9d5ab5e0e0c31edd4a7f61261dc437->enter($__internal_9f270c55d06081ab309f1c2f6a32767fae9d5ab5e0e0c31edd4a7f61261dc437_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "MBHBaseBundle::page.html.twig"));

        $this->parent->display($context, array_merge($this->blocks, $blocks));
        
        $__internal_9f270c55d06081ab309f1c2f6a32767fae9d5ab5e0e0c31edd4a7f61261dc437->leave($__internal_9f270c55d06081ab309f1c2f6a32767fae9d5ab5e0e0c31edd4a7f61261dc437_prof);

    }

    // line 3
    public function block_body($context, array $blocks = array())
    {
        $__internal_195f0c9398819b220d0f3ce8835fef6d874315fc59f1eec19517e75b31350971 = $this->env->getExtension("native_profiler");
        $__internal_195f0c9398819b220d0f3ce8835fef6d874315fc59f1eec19517e75b31350971->enter($__internal_195f0c9398819b220d0f3ce8835fef6d874315fc59f1eec19517e75b31350971_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "body"));

        // line 4
        echo "    ";
        $this->displayParentBlock("body", $context, $blocks);
        echo "

    <header class=\"main-header\">
        <!-- Logo -->
        ";
        // line 8
        $this->loadTemplate("MBHBaseBundle::logo.html.twig", "MBHBaseBundle::page.html.twig", 8)->display($context);
        // line 9
        echo "        <!-- Header Navbar -->
        ";
        // line 10
        $this->loadTemplate("MBHBaseBundle::navbar.html.twig", "MBHBaseBundle::page.html.twig", 10)->display($context);
        // line 11
        echo "    </header>

    ";
        // line 13
        if (array_key_exists("form", $context)) {
            // line 14
            echo "        ";
            $this->env->getExtension('form')->renderer->setTheme((isset($context["form"]) ? $context["form"] : $this->getContext($context, "form")), array(0 => "MBHBaseBundle:Form:fields.html.twig"));
            // line 15
            echo "    ";
        }
        // line 16
        echo "
    ";
        // line 17
        if (array_key_exists("edit_form", $context)) {
            // line 18
            echo "        ";
            $this->env->getExtension('form')->renderer->setTheme((isset($context["form"]) ? $context["form"] : $this->getContext($context, "form")), array(0 => "MBHBaseBundle:Form:fields.html.twig"));
            // line 19
            echo "    ";
        }
        // line 20
        echo "
    ";
        // line 21
        $this->loadTemplate("MBHBaseBundle::sidebar.html.twig", "MBHBaseBundle::page.html.twig", 21)->display($context);
        // line 22
        echo "
    <div class=\"content-wrapper main-container\">

        ";
        // line 25
        $this->displayBlock('print_hotel_logo', $context, $blocks);
        // line 32
        echo "
        <div class=\"print-clearfix\"></div>

        <section class=\"content-header\">
            <h1>
                ";
        // line 37
        echo twig_escape_filter($this->env, ((array_key_exists("title", $context)) ? (_twig_default_filter((isset($context["title"]) ? $context["title"] : $this->getContext($context, "title")), (isset($context["project_title"]) ? $context["project_title"] : $this->getContext($context, "project_title")))) : ((isset($context["project_title"]) ? $context["project_title"] : $this->getContext($context, "project_title")))), "html", null, true);
        echo "
                ";
        // line 38
        if (array_key_exists("small_title", $context)) {
            // line 39
            echo "                <small>";
            echo (isset($context["small_title"]) ? $context["small_title"] : $this->getContext($context, "small_title"));
            echo "</small>
                ";
        }
        // line 41
        echo "            </h1>
            <ol class=\"breadcrumb\" id=\"main-breadcrumb\">
                <li><a href=\"";
        // line 43
        echo $this->env->getExtension('routing')->getPath("_welcome");
        echo "\"><i class=\"fa fa-home\"></i></a></li>
                ";
        // line 44
        if ((array_key_exists("title_url", $context) && (isset($context["title_url"]) ? $context["title_url"] : $this->getContext($context, "title_url")))) {
            // line 45
            echo "                <li><a href=\"";
            echo twig_escape_filter($this->env, (isset($context["title_url"]) ? $context["title_url"] : $this->getContext($context, "title_url")), "html", null, true);
            echo "\">";
            echo twig_escape_filter($this->env, ((array_key_exists("title", $context)) ? (_twig_default_filter((isset($context["title"]) ? $context["title"] : $this->getContext($context, "title")), (isset($context["project_title"]) ? $context["project_title"] : $this->getContext($context, "project_title")))) : ((isset($context["project_title"]) ? $context["project_title"] : $this->getContext($context, "project_title")))), "html", null, true);
            echo "</a></li>
                ";
        }
        // line 47
        echo "                ";
        if (array_key_exists("small_title", $context)) {
            // line 48
            echo "                <li class=\"active\">";
            echo (isset($context["small_title"]) ? $context["small_title"] : $this->getContext($context, "small_title"));
            echo "</li>
                ";
        }
        // line 50
        echo "            </ol>
        </section>

        <section class=\"content\">
            ";
        // line 54
        $this->displayBlock('messages', $context, $blocks);
        // line 57
        echo "
            ";
        // line 58
        $this->displayBlock('prepend_content', $context, $blocks);
        // line 60
        echo "
            ";
        // line 61
        if ((array_key_exists("layout", $context) && ((isset($context["layout"]) ? $context["layout"] : $this->getContext($context, "layout")) == "tabs"))) {
            // line 62
            echo "                <div class=\"nav-tabs-custom\">
            ";
        }
        // line 64
        echo "            ";
        if ((array_key_exists("layout", $context) && ((isset($context["layout"]) ? $context["layout"] : $this->getContext($context, "layout")) == "box"))) {
            // line 65
            echo "                <div class=\"box box-default\"><div class=\"box-body\">
            ";
        }
        // line 67
        echo "
            ";
        // line 68
        $this->displayBlock('content', $context, $blocks);
        // line 69
        echo "
            ";
        // line 70
        if ((array_key_exists("layout", $context) && ((isset($context["layout"]) ? $context["layout"] : $this->getContext($context, "layout")) == "tabs"))) {
            // line 71
            echo "                </div>
            ";
        }
        // line 73
        echo "            ";
        if ((array_key_exists("layout", $context) && ((isset($context["layout"]) ? $context["layout"] : $this->getContext($context, "layout")) == "box"))) {
            // line 74
            echo "                </div></div>
            ";
        }
        // line 76
        echo "
            ";
        // line 77
        $this->displayBlock('append_content', $context, $blocks);
        // line 79
        echo "
            ";
        // line 80
        $this->loadTemplate("MBHBaseBundle:Partials:entityDeleteForm.html.twig", "MBHBaseBundle::page.html.twig", 80)->display($context);
        // line 81
        echo "
            <div id=\"print-user-info\">
                <div class=\"row\">
                    <div class=\"col-md-4\">
                        <h5>";
        // line 85
        echo twig_escape_filter($this->env, twig_date_format_filter($this->env, "now", "d.m.Y H:i"), "html", null, true);
        echo "</h5>
                    </div>
                    <div class=\"col-md-8 text-right\">
                        ";
        // line 88
        if ($this->getAttribute((isset($context["app"]) ? $context["app"] : $this->getContext($context, "app")), "user", array())) {
            // line 89
            echo "                            <h5>";
            echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute((isset($context["app"]) ? $context["app"] : $this->getContext($context, "app")), "user", array()), "fullName", array(0 => true), "method"), "html", null, true);
            echo "&nbsp_____________________</h5>
                        ";
        }
        // line 91
        echo "                    </div>
                </div>
            </div>
        </section>

    </div>
";
        
        $__internal_195f0c9398819b220d0f3ce8835fef6d874315fc59f1eec19517e75b31350971->leave($__internal_195f0c9398819b220d0f3ce8835fef6d874315fc59f1eec19517e75b31350971_prof);

    }

    // line 25
    public function block_print_hotel_logo($context, array $blocks = array())
    {
        $__internal_16b8509479cabe1ed578a83aacbf374ca1888354b47ab197e2b02b09a9db2b2e = $this->env->getExtension("native_profiler");
        $__internal_16b8509479cabe1ed578a83aacbf374ca1888354b47ab197e2b02b09a9db2b2e->enter($__internal_16b8509479cabe1ed578a83aacbf374ca1888354b47ab197e2b02b09a9db2b2e_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "print_hotel_logo"));

        // line 26
        echo "            ";
        if (($this->env->getExtension('mbh_hotel_selector_extension')->getSelectedHotel() && $this->getAttribute($this->env->getExtension('mbh_hotel_selector_extension')->getSelectedHotel(), "logo", array()))) {
            // line 27
            echo "                <div id=\"print-hotel-logo\">
                    <img class=\"grayscale\" src=\"";
            // line 28
            echo twig_escape_filter($this->env, $this->env->getExtension('liip_imagine')->filter($this->env->getExtension('asset')->getAssetUrl($this->getAttribute($this->env->getExtension('mbh_hotel_selector_extension')->getSelectedHotel(), "logoUrl", array())), "thumb_95x80"), "html", null, true);
            echo "\"/>
                </div>
            ";
        }
        // line 31
        echo "        ";
        
        $__internal_16b8509479cabe1ed578a83aacbf374ca1888354b47ab197e2b02b09a9db2b2e->leave($__internal_16b8509479cabe1ed578a83aacbf374ca1888354b47ab197e2b02b09a9db2b2e_prof);

    }

    // line 54
    public function block_messages($context, array $blocks = array())
    {
        $__internal_b0075b512a0f0f1b933fc7257b1c2ac3a2ce0ebac0e1ddbde524a350a000b19b = $this->env->getExtension("native_profiler");
        $__internal_b0075b512a0f0f1b933fc7257b1c2ac3a2ce0ebac0e1ddbde524a350a000b19b->enter($__internal_b0075b512a0f0f1b933fc7257b1c2ac3a2ce0ebac0e1ddbde524a350a000b19b_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "messages"));

        // line 55
        echo "                <div id=\"messages\">";
        $this->loadTemplate("MBHBaseBundle::messages.html.twig", "MBHBaseBundle::page.html.twig", 55)->display($context);
        echo "</div>
            ";
        
        $__internal_b0075b512a0f0f1b933fc7257b1c2ac3a2ce0ebac0e1ddbde524a350a000b19b->leave($__internal_b0075b512a0f0f1b933fc7257b1c2ac3a2ce0ebac0e1ddbde524a350a000b19b_prof);

    }

    // line 58
    public function block_prepend_content($context, array $blocks = array())
    {
        $__internal_3e8b9dd4f1f11af72b62a1913c2f0b3d1588e0ddfa011a0d5e5aadb844e82954 = $this->env->getExtension("native_profiler");
        $__internal_3e8b9dd4f1f11af72b62a1913c2f0b3d1588e0ddfa011a0d5e5aadb844e82954->enter($__internal_3e8b9dd4f1f11af72b62a1913c2f0b3d1588e0ddfa011a0d5e5aadb844e82954_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "prepend_content"));

        // line 59
        echo "            ";
        
        $__internal_3e8b9dd4f1f11af72b62a1913c2f0b3d1588e0ddfa011a0d5e5aadb844e82954->leave($__internal_3e8b9dd4f1f11af72b62a1913c2f0b3d1588e0ddfa011a0d5e5aadb844e82954_prof);

    }

    // line 68
    public function block_content($context, array $blocks = array())
    {
        $__internal_5b884af7bdfef47b355f63676e1223eb3c24b948f8b24dcf62f2da9010a8270b = $this->env->getExtension("native_profiler");
        $__internal_5b884af7bdfef47b355f63676e1223eb3c24b948f8b24dcf62f2da9010a8270b->enter($__internal_5b884af7bdfef47b355f63676e1223eb3c24b948f8b24dcf62f2da9010a8270b_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "content"));

        
        $__internal_5b884af7bdfef47b355f63676e1223eb3c24b948f8b24dcf62f2da9010a8270b->leave($__internal_5b884af7bdfef47b355f63676e1223eb3c24b948f8b24dcf62f2da9010a8270b_prof);

    }

    // line 77
    public function block_append_content($context, array $blocks = array())
    {
        $__internal_65c09d972de278bbc9f051637b8add0d4b86956d1b8138e5af33a301673a9432 = $this->env->getExtension("native_profiler");
        $__internal_65c09d972de278bbc9f051637b8add0d4b86956d1b8138e5af33a301673a9432->enter($__internal_65c09d972de278bbc9f051637b8add0d4b86956d1b8138e5af33a301673a9432_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "append_content"));

        // line 78
        echo "            ";
        
        $__internal_65c09d972de278bbc9f051637b8add0d4b86956d1b8138e5af33a301673a9432->leave($__internal_65c09d972de278bbc9f051637b8add0d4b86956d1b8138e5af33a301673a9432_prof);

    }

    public function getTemplateName()
    {
        return "MBHBaseBundle::page.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  305 => 78,  299 => 77,  288 => 68,  281 => 59,  275 => 58,  265 => 55,  259 => 54,  252 => 31,  246 => 28,  243 => 27,  240 => 26,  234 => 25,  221 => 91,  215 => 89,  213 => 88,  207 => 85,  201 => 81,  199 => 80,  196 => 79,  194 => 77,  191 => 76,  187 => 74,  184 => 73,  180 => 71,  178 => 70,  175 => 69,  173 => 68,  170 => 67,  166 => 65,  163 => 64,  159 => 62,  157 => 61,  154 => 60,  152 => 58,  149 => 57,  147 => 54,  141 => 50,  135 => 48,  132 => 47,  124 => 45,  122 => 44,  118 => 43,  114 => 41,  108 => 39,  106 => 38,  102 => 37,  95 => 32,  93 => 25,  88 => 22,  86 => 21,  83 => 20,  80 => 19,  77 => 18,  75 => 17,  72 => 16,  69 => 15,  66 => 14,  64 => 13,  60 => 11,  58 => 10,  55 => 9,  53 => 8,  45 => 4,  39 => 3,  11 => 1,);
    }
}
/* {% extends 'MBHBaseBundle::meta.html.twig' %}*/
/* */
/* {% block body %}*/
/*     {{ parent() }}*/
/* */
/*     <header class="main-header">*/
/*         <!-- Logo -->*/
/*         {% include 'MBHBaseBundle::logo.html.twig' %}*/
/*         <!-- Header Navbar -->*/
/*         {% include 'MBHBaseBundle::navbar.html.twig' %}*/
/*     </header>*/
/* */
/*     {% if form is defined %}*/
/*         {% form_theme form 'MBHBaseBundle:Form:fields.html.twig' %}*/
/*     {% endif %}*/
/* */
/*     {% if edit_form is defined %}*/
/*         {% form_theme form 'MBHBaseBundle:Form:fields.html.twig' %}*/
/*     {% endif %}*/
/* */
/*     {% include 'MBHBaseBundle::sidebar.html.twig' %}*/
/* */
/*     <div class="content-wrapper main-container">*/
/* */
/*         {% block print_hotel_logo %}*/
/*             {% if selected_hotel() and selected_hotel().logo %}*/
/*                 <div id="print-hotel-logo">*/
/*                     <img class="grayscale" src="{{ asset(selected_hotel().logoUrl)|imagine_filter('thumb_95x80') }}"/>*/
/*                 </div>*/
/*             {% endif %}*/
/*         {% endblock %}*/
/* */
/*         <div class="print-clearfix"></div>*/
/* */
/*         <section class="content-header">*/
/*             <h1>*/
/*                 {{ title|default(project_title) }}*/
/*                 {% if small_title is defined %}*/
/*                 <small>{{ small_title|raw }}</small>*/
/*                 {% endif %}*/
/*             </h1>*/
/*             <ol class="breadcrumb" id="main-breadcrumb">*/
/*                 <li><a href="{{ path('_welcome') }}"><i class="fa fa-home"></i></a></li>*/
/*                 {% if title_url is defined and title_url %}*/
/*                 <li><a href="{{ title_url }}">{{ title|default(project_title) }}</a></li>*/
/*                 {% endif %}*/
/*                 {% if small_title is defined %}*/
/*                 <li class="active">{{ small_title|raw }}</li>*/
/*                 {% endif %}*/
/*             </ol>*/
/*         </section>*/
/* */
/*         <section class="content">*/
/*             {% block messages %}*/
/*                 <div id="messages">{% include 'MBHBaseBundle::messages.html.twig' %}</div>*/
/*             {% endblock %}*/
/* */
/*             {% block prepend_content %}*/
/*             {% endblock %}*/
/* */
/*             {% if layout is defined and layout == 'tabs' %}*/
/*                 <div class="nav-tabs-custom">*/
/*             {% endif %}*/
/*             {% if layout is defined and layout == 'box' %}*/
/*                 <div class="box box-default"><div class="box-body">*/
/*             {% endif %}*/
/* */
/*             {% block content %}{% endblock %}*/
/* */
/*             {% if layout is defined and layout == 'tabs' %}*/
/*                 </div>*/
/*             {% endif %}*/
/*             {% if layout is defined and layout == 'box' %}*/
/*                 </div></div>*/
/*             {% endif %}*/
/* */
/*             {% block append_content %}*/
/*             {% endblock %}*/
/* */
/*             {% include 'MBHBaseBundle:Partials:entityDeleteForm.html.twig' %}*/
/* */
/*             <div id="print-user-info">*/
/*                 <div class="row">*/
/*                     <div class="col-md-4">*/
/*                         <h5>{{ 'now'|date('d.m.Y H:i') }}</h5>*/
/*                     </div>*/
/*                     <div class="col-md-8 text-right">*/
/*                         {% if app.user %}*/
/*                             <h5>{{ app.user.fullName(true) }}&nbsp_____________________</h5>*/
/*                         {% endif %}*/
/*                     </div>*/
/*                 </div>*/
/*             </div>*/
/*         </section>*/
/* */
/*     </div>*/
/* {% endblock %}*/

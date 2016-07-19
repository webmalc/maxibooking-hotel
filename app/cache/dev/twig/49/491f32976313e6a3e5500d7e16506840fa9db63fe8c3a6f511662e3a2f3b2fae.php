<?php

/* MBHRestaurantBundle:DishOrder:index.html.twig */
class __TwigTemplate_a598c00354cd313e3043306fda0d440da21631e86e1ee4b9d60bc4d42b150bb9 extends MBH\Bundle\BaseBundle\Twig\Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        // line 1
        $this->parent = $this->loadTemplate("MBHRestaurantBundle:DishOrder:layout.html.twig", "MBHRestaurantBundle:DishOrder:index.html.twig", 1);
        $this->blocks = array(
            'content' => array($this, 'block_content'),
        );
    }

    protected function doGetParent(array $context)
    {
        return "MBHRestaurantBundle:DishOrder:layout.html.twig";
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        $__internal_445f83c338e9a138dbe987645e2bf1f2d8aa184f81b2b12b8601a8b342ee375d = $this->env->getExtension("native_profiler");
        $__internal_445f83c338e9a138dbe987645e2bf1f2d8aa184f81b2b12b8601a8b342ee375d->enter($__internal_445f83c338e9a138dbe987645e2bf1f2d8aa184f81b2b12b8601a8b342ee375d_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "MBHRestaurantBundle:DishOrder:index.html.twig"));

        // line 2
        $context["small_title"] = $this->env->getExtension('translator')->trans("restaurant.dishorder.actions.list.small_title");
        // line 3
        $context["layout"] = "box";
        // line 1
        $this->parent->display($context, array_merge($this->blocks, $blocks));
        
        $__internal_445f83c338e9a138dbe987645e2bf1f2d8aa184f81b2b12b8601a8b342ee375d->leave($__internal_445f83c338e9a138dbe987645e2bf1f2d8aa184f81b2b12b8601a8b342ee375d_prof);

    }

    // line 5
    public function block_content($context, array $blocks = array())
    {
        $__internal_2d042cdd1fd4323e54df4e84f1ffa4bcaab61c2616f3b1b9879c459b35f704a4 = $this->env->getExtension("native_profiler");
        $__internal_2d042cdd1fd4323e54df4e84f1ffa4bcaab61c2616f3b1b9879c459b35f704a4->enter($__internal_2d042cdd1fd4323e54df4e84f1ffa4bcaab61c2616f3b1b9879c459b35f704a4_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "content"));

        // line 6
        echo "    ";
        $this->loadTemplate("MBHRestaurantBundle:DishOrder:index.html.twig", "MBHRestaurantBundle:DishOrder:index.html.twig", 6, "867708677")->display(array_merge($context, array("id" => "dishorder-table-filter-widget")));
        // line 34
        echo "    <div class=\"box-tools pull-rigth\">

        ";
        // line 36
        if ($this->env->getExtension('security')->isGranted("ROLE_RESTAURANT_ORDER_MANAGER_NEW")) {
            // line 37
            echo "            <div class=\"btn-list spacer-bottom text-right\">
                <div class=\"btn\">
                    <a href=\"";
            // line 39
            echo $this->env->getExtension('routing')->getPath("restaurant_dishorder_new");
            echo "\" class=\"btn btn-sm btn-success\"
                       data-toggle=\"tooltip\"
                       data-placement=\"bottom\" title=\"";
            // line 41
            echo twig_escape_filter($this->env, twig_capitalize_string_filter($this->env, $this->env->getExtension('translator')->trans("restaurant.dishorder.actions.add")), "html", null, true);
            echo "\">
                        <i class=\"fa fa-plus\"></i> ";
            // line 42
            echo twig_escape_filter($this->env, twig_capitalize_string_filter($this->env, $this->env->getExtension('translator')->trans("restaurant.dishorder.actions.add")), "html", null, true);
            echo "</a>
                </div>
            </div>
        ";
        }
        // line 46
        echo "
    </div>

        <div class=\"box-body\">
        <table id=\"dishorder-table\"
               class=\"table table-actions not-auto-datatable table-striped table-hover table-condensed text-center\">
            <thead>
            <tr>
                <th class=\"td-xs\"></th>
                <th class=\"td-xs\">";
        // line 55
        echo twig_escape_filter($this->env, $this->env->getExtension('translator')->trans("restaurant.dishorder.table.number"), "html", null, true);
        echo "</th>
                <th class=\"td-xs\">";
        // line 56
        echo twig_escape_filter($this->env, $this->env->getExtension('translator')->trans("restaurant.dishorder.table.table"), "html", null, true);
        echo "</th>
                <th class=\"td-xs\">";
        // line 57
        echo twig_escape_filter($this->env, $this->env->getExtension('translator')->trans("restaurant.dishorder.table.time"), "html", null, true);
        echo "</th>
                <th class=\"td-sm\">";
        // line 58
        echo twig_escape_filter($this->env, $this->env->getExtension('translator')->trans("restaurant.dishorder.table.price"), "html", null, true);
        echo "</th>
                <th class=\"td-sm\">";
        // line 59
        echo twig_escape_filter($this->env, $this->env->getExtension('translator')->trans("restaurant.dishorder.table.freezed"), "html", null, true);
        echo "</th>
                <th class=\"td-lg text-center\">";
        // line 60
        echo twig_escape_filter($this->env, $this->env->getExtension('translator')->trans("restaurant.dishorder.table.doing"), "html", null, true);
        echo "</th>
            </tr>
            </thead>

            <tbody>


            </tbody>
        </table>

    </div> <!-- /.box-body -->
";
        
        $__internal_2d042cdd1fd4323e54df4e84f1ffa4bcaab61c2616f3b1b9879c459b35f704a4->leave($__internal_2d042cdd1fd4323e54df4e84f1ffa4bcaab61c2616f3b1b9879c459b35f704a4_prof);

    }

    public function getTemplateName()
    {
        return "MBHRestaurantBundle:DishOrder:index.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  105 => 60,  101 => 59,  97 => 58,  93 => 57,  89 => 56,  85 => 55,  74 => 46,  67 => 42,  63 => 41,  58 => 39,  54 => 37,  52 => 36,  48 => 34,  45 => 6,  39 => 5,  32 => 1,  30 => 3,  28 => 2,  11 => 1,);
    }
}


/* MBHRestaurantBundle:DishOrder:index.html.twig */
class __TwigTemplate_a598c00354cd313e3043306fda0d440da21631e86e1ee4b9d60bc4d42b150bb9_867708677 extends MBH\Bundle\BaseBundle\Twig\Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        // line 6
        $this->parent = $this->loadTemplate("@MBHBase/Partials/embed_filter.html.twig", "MBHRestaurantBundle:DishOrder:index.html.twig", 6);
        $this->blocks = array(
            'content' => array($this, 'block_content'),
        );
    }

    protected function doGetParent(array $context)
    {
        return "@MBHBase/Partials/embed_filter.html.twig";
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        $__internal_c34fee07bc3e834e202b05c5c48f60b24f0c290c7aa0b4335333a702ee8d82fd = $this->env->getExtension("native_profiler");
        $__internal_c34fee07bc3e834e202b05c5c48f60b24f0c290c7aa0b4335333a702ee8d82fd->enter($__internal_c34fee07bc3e834e202b05c5c48f60b24f0c290c7aa0b4335333a702ee8d82fd_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "MBHRestaurantBundle:DishOrder:index.html.twig"));

        $this->parent->display($context, array_merge($this->blocks, $blocks));
        
        $__internal_c34fee07bc3e834e202b05c5c48f60b24f0c290c7aa0b4335333a702ee8d82fd->leave($__internal_c34fee07bc3e834e202b05c5c48f60b24f0c290c7aa0b4335333a702ee8d82fd_prof);

    }

    // line 7
    public function block_content($context, array $blocks = array())
    {
        $__internal_68bc4d60eb133c3e8f60400fda7202704f6ae86282af0ab0c24761a37c938e83 = $this->env->getExtension("native_profiler");
        $__internal_68bc4d60eb133c3e8f60400fda7202704f6ae86282af0ab0c24761a37c938e83->enter($__internal_68bc4d60eb133c3e8f60400fda7202704f6ae86282af0ab0c24761a37c938e83_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "content"));

        // line 8
        echo "            ";
        if (array_key_exists("form", $context)) {
            // line 9
            echo "                ";
            echo             $this->env->getExtension('form')->renderer->renderBlock((isset($context["form"]) ? $context["form"] : $this->getContext($context, "form")), 'form_start');
            echo "

                ";
            // line 11
            if ($this->getAttribute((isset($context["form"]) ? $context["form"] : null), "begin", array(), "any", true, true)) {
                // line 12
                echo "                    <div class=\"form-group\">
                        <label><i class=\"fa fa-calendar\" title='Даты' data-toggle='tooltip'></i></label>
                        ";
                // line 14
                echo $this->env->getExtension('form')->renderer->searchAndRenderBlock($this->getAttribute((isset($context["form"]) ? $context["form"] : $this->getContext($context, "form")), "begin", array()), 'widget');
                echo " —
                        ";
                // line 15
                echo $this->env->getExtension('form')->renderer->searchAndRenderBlock($this->getAttribute((isset($context["form"]) ? $context["form"] : $this->getContext($context, "form")), "end", array()), 'widget');
                echo "
                    </div>
                ";
            }
            // line 18
            echo "                ";
            if ($this->getAttribute((isset($context["form"]) ? $context["form"] : null), "money_begin", array(), "any", true, true)) {
                // line 19
                echo "                    <div class=\"form-group\">
                        <label><i class=\"fa fa-money\" title=\"Цены\" data-toggle=\"tooltip\"></i></label>
                        ";
                // line 21
                echo $this->env->getExtension('form')->renderer->searchAndRenderBlock($this->getAttribute((isset($context["form"]) ? $context["form"] : $this->getContext($context, "form")), "money_begin", array()), 'widget');
                echo "—
                        ";
                // line 22
                echo $this->env->getExtension('form')->renderer->searchAndRenderBlock($this->getAttribute((isset($context["form"]) ? $context["form"] : $this->getContext($context, "form")), "money_end", array()), 'widget');
                echo "
                    </div>
                ";
            }
            // line 25
            echo "                <div class=\"form-group\">
                    <label><i class=\"fa fa-money\" title=\"Оплачен\" data-toggle=\"tooltip\"></i></label>
                    &nbsp;";
            // line 27
            echo $this->env->getExtension('form')->renderer->searchAndRenderBlock($this->getAttribute((isset($context["form"]) ? $context["form"] : $this->getContext($context, "form")), "is_freezed", array()), 'widget');
            echo "
                    ";
            // line 28
            echo $this->env->getExtension('form')->renderer->searchAndRenderBlock($this->getAttribute((isset($context["form"]) ? $context["form"] : $this->getContext($context, "form")), "_token", array()), 'widget');
            echo "
                </div>
                ";
            // line 30
            echo             $this->env->getExtension('form')->renderer->renderBlock((isset($context["form"]) ? $context["form"] : $this->getContext($context, "form")), 'form_end', array("render_rest" => false));
            echo "
            ";
        }
        // line 32
        echo "        ";
        
        $__internal_68bc4d60eb133c3e8f60400fda7202704f6ae86282af0ab0c24761a37c938e83->leave($__internal_68bc4d60eb133c3e8f60400fda7202704f6ae86282af0ab0c24761a37c938e83_prof);

    }

    public function getTemplateName()
    {
        return "MBHRestaurantBundle:DishOrder:index.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  237 => 32,  232 => 30,  227 => 28,  223 => 27,  219 => 25,  213 => 22,  209 => 21,  205 => 19,  202 => 18,  196 => 15,  192 => 14,  188 => 12,  186 => 11,  180 => 9,  177 => 8,  171 => 7,  148 => 6,  105 => 60,  101 => 59,  97 => 58,  93 => 57,  89 => 56,  85 => 55,  74 => 46,  67 => 42,  63 => 41,  58 => 39,  54 => 37,  52 => 36,  48 => 34,  45 => 6,  39 => 5,  32 => 1,  30 => 3,  28 => 2,  11 => 1,);
    }
}
/* {% extends "MBHRestaurantBundle:DishOrder:layout.html.twig" %}*/
/* {% set small_title = 'restaurant.dishorder.actions.list.small_title'|trans %}*/
/* {% set layout = "box" %}*/
/* */
/* {% block content %}*/
/*     {% embed '@MBHBase/Partials/embed_filter.html.twig' with {'id': 'dishorder-table-filter-widget'}  %}*/
/*         {% block content %}*/
/*             {% if form is defined %}*/
/*                 {{ form_start(form) }}*/
/* */
/*                 {% if form.begin is defined %}*/
/*                     <div class="form-group">*/
/*                         <label><i class="fa fa-calendar" title='Даты' data-toggle='tooltip'></i></label>*/
/*                         {{ form_widget(form.begin) }} —*/
/*                         {{ form_widget(form.end) }}*/
/*                     </div>*/
/*                 {% endif %}*/
/*                 {% if form.money_begin is defined %}*/
/*                     <div class="form-group">*/
/*                         <label><i class="fa fa-money" title="Цены" data-toggle="tooltip"></i></label>*/
/*                         {{ form_widget(form.money_begin) }}—*/
/*                         {{ form_widget(form.money_end) }}*/
/*                     </div>*/
/*                 {% endif %}*/
/*                 <div class="form-group">*/
/*                     <label><i class="fa fa-money" title="Оплачен" data-toggle="tooltip"></i></label>*/
/*                     &nbsp;{{ form_widget(form.is_freezed) }}*/
/*                     {{ form_widget(form._token) }}*/
/*                 </div>*/
/*                 {{ form_end(form, {render_rest: false}) }}*/
/*             {% endif %}*/
/*         {% endblock %}*/
/*     {% endembed %}*/
/*     <div class="box-tools pull-rigth">*/
/* */
/*         {% if is_granted('ROLE_RESTAURANT_ORDER_MANAGER_NEW') %}*/
/*             <div class="btn-list spacer-bottom text-right">*/
/*                 <div class="btn">*/
/*                     <a href="{{ path('restaurant_dishorder_new') }}" class="btn btn-sm btn-success"*/
/*                        data-toggle="tooltip"*/
/*                        data-placement="bottom" title="{{ 'restaurant.dishorder.actions.add'|trans|capitalize }}">*/
/*                         <i class="fa fa-plus"></i> {{ 'restaurant.dishorder.actions.add'|trans|capitalize }}</a>*/
/*                 </div>*/
/*             </div>*/
/*         {% endif %}*/
/* */
/*     </div>*/
/* */
/*         <div class="box-body">*/
/*         <table id="dishorder-table"*/
/*                class="table table-actions not-auto-datatable table-striped table-hover table-condensed text-center">*/
/*             <thead>*/
/*             <tr>*/
/*                 <th class="td-xs"></th>*/
/*                 <th class="td-xs">{{ 'restaurant.dishorder.table.number'|trans }}</th>*/
/*                 <th class="td-xs">{{ 'restaurant.dishorder.table.table'|trans }}</th>*/
/*                 <th class="td-xs">{{ 'restaurant.dishorder.table.time'|trans }}</th>*/
/*                 <th class="td-sm">{{ 'restaurant.dishorder.table.price'|trans }}</th>*/
/*                 <th class="td-sm">{{ 'restaurant.dishorder.table.freezed'|trans }}</th>*/
/*                 <th class="td-lg text-center">{{ 'restaurant.dishorder.table.doing'|trans }}</th>*/
/*             </tr>*/
/*             </thead>*/
/* */
/*             <tbody>*/
/* */
/* */
/*             </tbody>*/
/*         </table>*/
/* */
/*     </div> <!-- /.box-body -->*/
/* {% endblock %}*/
/* */
/* */
/* */
/* */

<?php

/* MBHRestaurantBundle:DishOrder:json.json.twig */
class __TwigTemplate_0b9425a7f5e1ec5ab70b93ccf175a8f29edda7161f607a44ed5e365c4dd171e4 extends MBH\Bundle\BaseBundle\Twig\Template
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
        $__internal_e07d9bec2dc7ac4811757546c2a832dcdd572350301f80eef48e44159408b8d4 = $this->env->getExtension("native_profiler");
        $__internal_e07d9bec2dc7ac4811757546c2a832dcdd572350301f80eef48e44159408b8d4->enter($__internal_e07d9bec2dc7ac4811757546c2a832dcdd572350301f80eef48e44159408b8d4_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "MBHRestaurantBundle:DishOrder:json.json.twig"));

        // line 1
        echo "{
    \"draw\": \"";
        // line 2
        echo twig_escape_filter($this->env, (isset($context["draw"]) ? $context["draw"] : $this->getContext($context, "draw")), "html", null, true);
        echo "\",
    \"recordsTotal\": \"";
        // line 3
        echo twig_escape_filter($this->env, (isset($context["total"]) ? $context["total"] : $this->getContext($context, "total")), "html", null, true);
        echo "\",
    \"recordsFiltered\": \"";
        // line 4
        echo twig_escape_filter($this->env, (isset($context["recordsFiltered"]) ? $context["recordsFiltered"] : $this->getContext($context, "recordsFiltered")), "html", null, true);
        echo "\",
    \"data\": [
        ";
        // line 6
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable((isset($context["dishorders"]) ? $context["dishorders"] : $this->getContext($context, "dishorders")));
        $context['loop'] = array(
          'parent' => $context['_parent'],
          'index0' => 0,
          'index'  => 1,
          'first'  => true,
        );
        if (is_array($context['_seq']) || (is_object($context['_seq']) && $context['_seq'] instanceof Countable)) {
            $length = count($context['_seq']);
            $context['loop']['revindex0'] = $length - 1;
            $context['loop']['revindex'] = $length;
            $context['loop']['length'] = $length;
            $context['loop']['last'] = 1 === $length;
        }
        foreach ($context['_seq'] as $context["_key"] => $context["order"]) {
            // line 7
            echo "
        ";
            // line 8
            if ((( !$this->getAttribute($context["order"], "isfreezed", array()) && $this->env->getExtension('security')->isGranted("ROLE_RESTAURANT_ORDER_MANAGER_EDIT")) || ($this->getAttribute($context["order"], "isfreezed", array()) && $this->env->getExtension('security')->isGranted("ROLE_RESTAURANT_ORDER_MANAGER_FREEZED_EDIT")))) {
                // line 9
                echo "            ";
                $context["editbutton"] = (((("<a href='" . $this->env->getExtension('routing')->getPath("restaurant_dishorder_edit", array("id" => $this->getAttribute($context["order"], "id", array())))) . "' class='btn btn-success btn-xs' title='") . twig_capitalize_string_filter($this->env, $this->env->getExtension('translator')->trans("restaurant.dishorder.actions.edit"))) . "' data-toggle='tooltip'><i class='fa fa-pencil-square-o'></i></a>");
                // line 10
                echo "        ";
            } else {
                // line 11
                echo "            ";
                $context["editbutton"] = "<a href='#' class='btn btn-success btn-xs disabled'><i class='fa fa-pencil-square-o'></i></a>";
                // line 12
                echo "        ";
            }
            // line 13
            echo "
        ";
            // line 14
            if ((( !$this->getAttribute($context["order"], "isfreezed", array()) && $this->env->getExtension('security')->isGranted("ROLE_RESTAURANT_ORDER_MANAGER_DELETE")) || ($this->getAttribute($context["order"], "isfreezed", array()) && $this->env->getExtension('security')->isGranted("ROLE_RESTAURANT_ORDER_MANAGER_FREEZED_DELETE")))) {
                // line 15
                echo "            ";
                $context["deletebutton"] = (((("<a href='" . $this->env->getExtension('routing')->getPath("restaurant_dishorder_delete", array("id" => $this->getAttribute($context["order"], "id", array())))) . "' data-toggle='tooltip' class='btn btn-danger btn-xs delete-link'  title='") . twig_capitalize_string_filter($this->env, $this->env->getExtension('translator')->trans("restaurant.dishorder.actions.delete"))) . "'><i class='fa fa-trash-o'></i></a>");
                // line 16
                echo "        ";
            } else {
                // line 17
                echo "            ";
                $context["deletebutton"] = "<a href='#' class='btn btn-danger btn-xs disabled'><i class='fa fa-trash-o'></i></a>";
                // line 18
                echo "        ";
            }
            // line 19
            echo "
        ";
            // line 20
            if ($this->getAttribute($context["order"], "isfreezed", array())) {
                // line 21
                echo "            ";
                $context["freezedbutton"] = (((("<a href='" . $this->env->getExtension('routing')->getPath("restaurant_dishorder_showfreezed", array("id" => $this->getAttribute($context["order"], "id", array())))) . "' class='btn btn-success btn-sm'>") . twig_capitalize_string_filter($this->env, $this->env->getExtension('translator')->trans("restaurant.dishorder.actions.isfreezed"))) . "</a>");
                // line 22
                echo "        ";
            } else {
                // line 23
                echo "            ";
                if ($this->env->getExtension('security')->isGranted("ROLE_RESTAURANT_ORDER_MANAGER_PAY")) {
                    // line 24
                    echo "                ";
                    $context["freezedbutton"] = ((((((((((((("<a href='" . $this->env->getExtension('routing')->getPath("restaurant_dishorder_freeze", array("id" => $this->getAttribute($context["order"], "id", array())))) . "' class='btn btn-danger btn-sm delete-link' data-text='") . twig_capitalize_string_filter($this->env, $this->env->getExtension('translator')->trans("restaurant.dishorder.actions.pay"))) . " ") . $this->getAttribute($context["order"], "id", array())) . " ?' data-button='") . twig_capitalize_string_filter($this->env, $this->env->getExtension('translator')->trans("restaurant.dishorder.actions.confirm"))) . "' data-button-icon='fa-money' title='") . twig_capitalize_string_filter($this->env, $this->env->getExtension('translator')->trans("restaurant.dishorder.actions.freezed"))) . "' data-toggle='tooltip'><i class='fa fa-money'></i>") . " ") . twig_capitalize_string_filter($this->env, $this->env->getExtension('translator')->trans("restaurant.dishorder.actions.freezed"))) . "</a>");
                    // line 25
                    echo "            ";
                } else {
                    // line 26
                    echo "                ";
                    $context["freezebutton"] = "";
                    // line 27
                    echo "            ";
                }
                // line 28
                echo "        ";
            }
            // line 29
            echo "
        [
            \"<div class='text-center'><i class='fa fa-cutlery'></i></div>\",
            \"";
            // line 32
            echo twig_escape_filter($this->env, $this->getAttribute($context["order"], "id", array()), "html", null, true);
            echo "\",
            \"";
            // line 33
            echo twig_escape_filter($this->env, $this->getAttribute($context["order"], "table", array()), "html", null, true);
            echo "\",
            \"";
            // line 34
            echo twig_escape_filter($this->env, twig_date_format_filter($this->env, $this->getAttribute($context["order"], "createdAt", array()), "d.m"), "html", null, true);
            echo "\",
            \"";
            // line 35
            echo twig_escape_filter($this->env, twig_number_format_filter($this->env, $this->getAttribute($context["order"], "price", array()), 2, ".", ","), "html", null, true);
            echo "\",
            \"";
            // line 36
            echo (isset($context["freezedbutton"]) ? $context["freezedbutton"] : $this->getContext($context, "freezedbutton"));
            echo "\",
            \"<div class='btn-list text-center'>";
            // line 37
            echo (isset($context["editbutton"]) ? $context["editbutton"] : $this->getContext($context, "editbutton"));
            echo (isset($context["deletebutton"]) ? $context["deletebutton"] : $this->getContext($context, "deletebutton"));
            echo "</div>\"
        ]
        ";
            // line 39
            if (($this->getAttribute($context["loop"], "index", array()) != (isset($context["total"]) ? $context["total"] : $this->getContext($context, "total")))) {
                echo ",";
            }
            // line 40
            echo "
        ";
            ++$context['loop']['index0'];
            ++$context['loop']['index'];
            $context['loop']['first'] = false;
            if (isset($context['loop']['length'])) {
                --$context['loop']['revindex0'];
                --$context['loop']['revindex'];
                $context['loop']['last'] = 0 === $context['loop']['revindex0'];
            }
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['order'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 42
        echo "    ]
}
";
        
        $__internal_e07d9bec2dc7ac4811757546c2a832dcdd572350301f80eef48e44159408b8d4->leave($__internal_e07d9bec2dc7ac4811757546c2a832dcdd572350301f80eef48e44159408b8d4_prof);

    }

    public function getTemplateName()
    {
        return "MBHRestaurantBundle:DishOrder:json.json.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  168 => 42,  153 => 40,  149 => 39,  143 => 37,  139 => 36,  135 => 35,  131 => 34,  127 => 33,  123 => 32,  118 => 29,  115 => 28,  112 => 27,  109 => 26,  106 => 25,  103 => 24,  100 => 23,  97 => 22,  94 => 21,  92 => 20,  89 => 19,  86 => 18,  83 => 17,  80 => 16,  77 => 15,  75 => 14,  72 => 13,  69 => 12,  66 => 11,  63 => 10,  60 => 9,  58 => 8,  55 => 7,  38 => 6,  33 => 4,  29 => 3,  25 => 2,  22 => 1,);
    }
}
/* {*/
/*     "draw": "{{ draw }}",*/
/*     "recordsTotal": "{{ total }}",*/
/*     "recordsFiltered": "{{ recordsFiltered }}",*/
/*     "data": [*/
/*         {% for order in dishorders %}*/
/* */
/*         {% if ( not order.isfreezed and is_granted('ROLE_RESTAURANT_ORDER_MANAGER_EDIT') ) or (order.isfreezed and is_granted('ROLE_RESTAURANT_ORDER_MANAGER_FREEZED_EDIT')) %}*/
/*             {% set editbutton = "<a href='" ~ path('restaurant_dishorder_edit', {'id': order.id }) ~ "' class='btn btn-success btn-xs' title='"~('restaurant.dishorder.actions.edit')|trans|capitalize~"' data-toggle='tooltip'><i class='fa fa-pencil-square-o'></i></a>" %}*/
/*         {% else %}*/
/*             {% set editbutton = "<a href='#' class='btn btn-success btn-xs disabled'><i class='fa fa-pencil-square-o'></i></a>" %}*/
/*         {% endif %}*/
/* */
/*         {% if ( not order.isfreezed and is_granted('ROLE_RESTAURANT_ORDER_MANAGER_DELETE') ) or (order.isfreezed and is_granted('ROLE_RESTAURANT_ORDER_MANAGER_FREEZED_DELETE')) %}*/
/*             {% set deletebutton = "<a href='"~path('restaurant_dishorder_delete', {'id': order.id})~"' data-toggle='tooltip' class='btn btn-danger btn-xs delete-link'  title='"~('restaurant.dishorder.actions.delete'|trans|capitalize)~"'><i class='fa fa-trash-o'></i></a>" %}*/
/*         {% else %}*/
/*             {% set deletebutton = "<a href='#' class='btn btn-danger btn-xs disabled'><i class='fa fa-trash-o'></i></a>" %}*/
/*         {% endif %}*/
/* */
/*         {% if order.isfreezed %}*/
/*             {% set freezedbutton = "<a href='" ~ path('restaurant_dishorder_showfreezed', {'id': order.id}) ~ "' class='btn btn-success btn-sm'>"~'restaurant.dishorder.actions.isfreezed'|trans|capitalize~"</a>" %}*/
/*         {% else %}*/
/*             {% if is_granted('ROLE_RESTAURANT_ORDER_MANAGER_PAY') %}*/
/*                 {% set freezedbutton = "<a href='"~ path('restaurant_dishorder_freeze', {'id': order.id }) ~ "' class='btn btn-danger btn-sm delete-link' data-text='"~ 'restaurant.dishorder.actions.pay'|trans|capitalize ~ " " ~ order.id ~" ?' data-button='"~'restaurant.dishorder.actions.confirm'|trans|capitalize~"' data-button-icon='fa-money' title='"~'restaurant.dishorder.actions.freezed'|trans|capitalize ~"' data-toggle='tooltip'><i class='fa fa-money'></i>"~" "~'restaurant.dishorder.actions.freezed'|trans|capitalize~"</a>" %}*/
/*             {% else %}*/
/*                 {% set freezebutton = "" %}*/
/*             {% endif %}*/
/*         {% endif %}*/
/* */
/*         [*/
/*             "<div class='text-center'><i class='fa fa-cutlery'></i></div>",*/
/*             "{{ order.id }}",*/
/*             "{{ order.table }}",*/
/*             "{{ order.createdAt|date("d.m") }}",*/
/*             "{{ order.price|number_format(2,'.',',') }}",*/
/*             "{{ freezedbutton|raw }}",*/
/*             "<div class='btn-list text-center'>{{ editbutton|raw }}{{ deletebutton|raw }}</div>"*/
/*         ]*/
/*         {% if loop.index != total %},{% endif %}*/
/* */
/*         {% endfor %}*/
/*     ]*/
/* }*/
/* */

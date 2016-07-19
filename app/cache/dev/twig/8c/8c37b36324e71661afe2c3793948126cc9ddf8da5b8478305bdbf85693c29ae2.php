<?php

/* @MBHRestaurant/DishOrder/dishOrderJSVariable.html.twig */
class __TwigTemplate_b3ac37b869c3968db2e74c78f1775b064dfb637b098e70b7d080bd03a75711bc extends MBH\Bundle\BaseBundle\Twig\Template
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
        $__internal_a6a33cf1014e54ae5d4dced55f35304aefa720a9809db921d0fe5297ddab9bf7 = $this->env->getExtension("native_profiler");
        $__internal_a6a33cf1014e54ae5d4dced55f35304aefa720a9809db921d0fe5297ddab9bf7->enter($__internal_a6a33cf1014e54ae5d4dced55f35304aefa720a9809db921d0fe5297ddab9bf7_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "@MBHRestaurant/DishOrder/dishOrderJSVariable.html.twig"));

        // line 1
        echo "<script>
    var dishes = {
    ";
        // line 3
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable((isset($context["dishes"]) ? $context["dishes"] : $this->getContext($context, "dishes")));
        foreach ($context['_seq'] as $context["_key"] => $context["dish"]) {
            // line 4
            echo "        '";
            echo twig_escape_filter($this->env, $this->getAttribute($context["dish"], "id", array()), "html", null, true);
            echo "': {
            'price': ";
            // line 5
            echo twig_escape_filter($this->env, $this->getAttribute($context["dish"], "actualPrice", array()), "html", null, true);
            echo "
        },
    ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['dish'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 8
        echo "    };
</script>";
        
        $__internal_a6a33cf1014e54ae5d4dced55f35304aefa720a9809db921d0fe5297ddab9bf7->leave($__internal_a6a33cf1014e54ae5d4dced55f35304aefa720a9809db921d0fe5297ddab9bf7_prof);

    }

    public function getTemplateName()
    {
        return "@MBHRestaurant/DishOrder/dishOrderJSVariable.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  44 => 8,  35 => 5,  30 => 4,  26 => 3,  22 => 1,);
    }
}
/* <script>*/
/*     var dishes = {*/
/*     {% for dish in dishes %}*/
/*         '{{ dish.id }}': {*/
/*             'price': {{ dish.actualPrice }}*/
/*         },*/
/*     {% endfor %}*/
/*     };*/
/* </script>*/

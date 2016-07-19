<?php

/* MBHBaseBundle::messages.html.twig */
class __TwigTemplate_460e945269b14fb397b0d1c600d4949c7247a2680948db2567cfd85e99119f74 extends MBH\Bundle\BaseBundle\Twig\Template
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
        $__internal_ba5edb8af0523b3b7852fa8adec477ddf19ad95071c6a7cf9d7fef99a655f47b = $this->env->getExtension("native_profiler");
        $__internal_ba5edb8af0523b3b7852fa8adec477ddf19ad95071c6a7cf9d7fef99a655f47b->enter($__internal_ba5edb8af0523b3b7852fa8adec477ddf19ad95071c6a7cf9d7fef99a655f47b_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "MBHBaseBundle::messages.html.twig"));

        // line 1
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable($this->getAttribute($this->getAttribute($this->getAttribute((isset($context["app"]) ? $context["app"] : $this->getContext($context, "app")), "session", array()), "flashbag", array()), "keys", array(), "method"));
        foreach ($context['_seq'] as $context["_key"] => $context["key"]) {
            // line 2
            echo "    ";
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable($this->getAttribute($this->getAttribute($this->getAttribute((isset($context["app"]) ? $context["app"] : $this->getContext($context, "app")), "session", array()), "flashbag", array()), "get", array(0 => $context["key"]), "method"));
            foreach ($context['_seq'] as $context["_key"] => $context["message"]) {
                // line 3
                echo "        ";
                $context["info"] = twig_split_filter($this->env, $context["key"], "|");
                // line 4
                echo "
        <div class=\"";
                // line 5
                echo ((($this->getAttribute((isset($context["info"]) ? $context["info"] : null), 1, array(), "array", true, true) &&  !twig_test_empty($this->getAttribute((isset($context["info"]) ? $context["info"] : $this->getContext($context, "info")), 1, array(), "array")))) ? ("autohide") : (""));
                echo "  alert alert-";
                echo twig_escape_filter($this->env, $this->getAttribute((isset($context["info"]) ? $context["info"] : $this->getContext($context, "info")), 0, array(), "array"), "html", null, true);
                echo " alert-dismissable\">
            <button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-hidden=\"true\">&times;</button>
            ";
                // line 7
                echo $this->env->getExtension('translator')->trans($context["message"]);
                echo "
        </div>
    ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['message'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['key'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        
        $__internal_ba5edb8af0523b3b7852fa8adec477ddf19ad95071c6a7cf9d7fef99a655f47b->leave($__internal_ba5edb8af0523b3b7852fa8adec477ddf19ad95071c6a7cf9d7fef99a655f47b_prof);

    }

    public function getTemplateName()
    {
        return "MBHBaseBundle::messages.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  44 => 7,  37 => 5,  34 => 4,  31 => 3,  26 => 2,  22 => 1,);
    }
}
/* {% for key in  app.session.flashbag.keys()%}*/
/*     {% for message in app.session.flashbag.get(key) %}*/
/*         {% set info = key|split('|') %}*/
/* */
/*         <div class="{{info[1] is defined and info[1] is not empty ? 'autohide' }}  alert alert-{{info[0]}} alert-dismissable">*/
/*             <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>*/
/*             {{ message|trans|raw }}*/
/*         </div>*/
/*     {% endfor %}*/
/* {% endfor %}*/
/* */

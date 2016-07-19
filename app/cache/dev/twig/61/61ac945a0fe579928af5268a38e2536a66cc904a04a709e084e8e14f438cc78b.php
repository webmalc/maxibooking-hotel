<?php

/* MBHBaseBundle:Actions:saveButtons.html.twig */
class __TwigTemplate_067d584f62810ab6a6db290826722dc04ff52eb9631ed8c19aa66e762a01c13f extends MBH\Bundle\BaseBundle\Twig\Template
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
        $__internal_6edbc2ab888292a72b95167c5abc58a7b6fe9400ce4a2fd09dc769a2cec39e5e = $this->env->getExtension("native_profiler");
        $__internal_6edbc2ab888292a72b95167c5abc58a7b6fe9400ce4a2fd09dc769a2cec39e5e->enter($__internal_6edbc2ab888292a72b95167c5abc58a7b6fe9400ce4a2fd09dc769a2cec39e5e_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "MBHBaseBundle:Actions:saveButtons.html.twig"));

        // line 1
        if ( !array_key_exists("save_close", $context)) {
            // line 2
            echo "    ";
            $context["save_close"] = true;
        }
        // line 4
        if ( !array_key_exists("save", $context)) {
            // line 5
            echo "    ";
            $context["save"] = true;
        }
        // line 7
        echo "
<ul class=\"nav navbar-nav\">
    ";
        // line 9
        if ((isset($context["save_close"]) ? $context["save_close"] : $this->getContext($context, "save_close"))) {
            echo "<li><button type=\"submit\" name=\"save_close\" class=\"btn btn-success navbar-btn\"><i class=\"fa fa-check-square-o\"> </i><span class=\"hidden-xs\">&nbsp;";
            echo twig_escape_filter($this->env, $this->env->getExtension('translator')->trans("views.actions.saveButtons.save_and_close", array(), "MBHBaseBundle"), "html", null, true);
            echo "</span></button></li>";
        }
        // line 10
        echo "    ";
        if ((isset($context["save"]) ? $context["save"] : $this->getContext($context, "save"))) {
            // line 11
            echo "        <li>
        <button type=\"submit\" name=\"save\" class=\"btn btn-primary navbar-btn\"><i
                    class=\"fa fa-check-square-o\"> </i><span class=\"hidden-xs\">&nbsp;";
            // line 13
            echo twig_escape_filter($this->env, ((array_key_exists("save_text", $context)) ? (_twig_default_filter((isset($context["save_text"]) ? $context["save_text"] : $this->getContext($context, "save_text")), $this->env->getExtension('translator')->trans("views.actions.saveButtons.save", array(), "MBHBaseBundle"))) : ($this->env->getExtension('translator')->trans("views.actions.saveButtons.save", array(), "MBHBaseBundle"))), "html", null, true);
            echo "</span>
        </button></li>";
        }
        // line 15
        echo "    <li><button type=\"button\" onclick=\"location.href = '";
        echo twig_escape_filter($this->env, ((array_key_exists("title_url", $context)) ? (_twig_default_filter((isset($context["title_url"]) ? $context["title_url"] : $this->getContext($context, "title_url")), $this->env->getExtension('routing')->getPath("_welcome"))) : ($this->env->getExtension('routing')->getPath("_welcome"))), "html", null, true);
        echo "'\" class=\"btn btn-default navbar-btn\"><i class=\"fa fa-ban\"></i><span class=\"hidden-xs\">&nbsp;";
        echo twig_escape_filter($this->env, $this->env->getExtension('translator')->trans("views.actions.saveButtons.cancel", array(), "MBHBaseBundle"), "html", null, true);
        echo "</span></button></li>
</ul>";
        
        $__internal_6edbc2ab888292a72b95167c5abc58a7b6fe9400ce4a2fd09dc769a2cec39e5e->leave($__internal_6edbc2ab888292a72b95167c5abc58a7b6fe9400ce4a2fd09dc769a2cec39e5e_prof);

    }

    public function getTemplateName()
    {
        return "MBHBaseBundle:Actions:saveButtons.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  56 => 15,  51 => 13,  47 => 11,  44 => 10,  38 => 9,  34 => 7,  30 => 5,  28 => 4,  24 => 2,  22 => 1,);
    }
}
/* {% if save_close is not defined %}*/
/*     {% set save_close = true %}*/
/* {% endif %}*/
/* {% if save is not defined %}*/
/*     {% set save = true %}*/
/* {% endif %}*/
/* */
/* <ul class="nav navbar-nav">*/
/*     {% if save_close %}<li><button type="submit" name="save_close" class="btn btn-success navbar-btn"><i class="fa fa-check-square-o"> </i><span class="hidden-xs">&nbsp;{{ 'views.actions.saveButtons.save_and_close'|trans({}, 'MBHBaseBundle') }}</span></button></li>{% endif %}*/
/*     {% if save %}*/
/*         <li>*/
/*         <button type="submit" name="save" class="btn btn-primary navbar-btn"><i*/
/*                     class="fa fa-check-square-o"> </i><span class="hidden-xs">&nbsp;{{ save_text|default('views.actions.saveButtons.save'|trans({}, 'MBHBaseBundle')) }}</span>*/
/*         </button></li>{% endif %}*/
/*     <li><button type="button" onclick="location.href = '{{ title_url|default(path('_welcome'))}}'" class="btn btn-default navbar-btn"><i class="fa fa-ban"></i><span class="hidden-xs">&nbsp;{{ 'views.actions.saveButtons.cancel'|trans({}, 'MBHBaseBundle') }}</span></button></li>*/
/* </ul>*/

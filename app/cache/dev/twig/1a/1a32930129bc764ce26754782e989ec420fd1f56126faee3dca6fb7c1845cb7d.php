<?php

/* @MBHRestaurant/Form/dishItemCollection.html.twig */
class __TwigTemplate_96b91fa88dd492c5025e1b149a04c61bdad406c49596f70eed496e8fc29bfee6 extends MBH\Bundle\BaseBundle\Twig\Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        // line 1
        $this->parent = $this->loadTemplate("@MBHBase/Form/fields.html.twig", "@MBHRestaurant/Form/dishItemCollection.html.twig", 1);
        $this->blocks = array(
            'form_row' => array($this, 'block_form_row'),
        );
    }

    protected function doGetParent(array $context)
    {
        return "@MBHBase/Form/fields.html.twig";
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        $__internal_ff4891e770bc7531dd76937019300b1f4e411bc9297ede224c2675905a01e47a = $this->env->getExtension("native_profiler");
        $__internal_ff4891e770bc7531dd76937019300b1f4e411bc9297ede224c2675905a01e47a->enter($__internal_ff4891e770bc7531dd76937019300b1f4e411bc9297ede224c2675905a01e47a_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "@MBHRestaurant/Form/dishItemCollection.html.twig"));

        $this->parent->display($context, array_merge($this->blocks, $blocks));
        
        $__internal_ff4891e770bc7531dd76937019300b1f4e411bc9297ede224c2675905a01e47a->leave($__internal_ff4891e770bc7531dd76937019300b1f4e411bc9297ede224c2675905a01e47a_prof);

    }

    // line 2
    public function block_form_row($context, array $blocks = array())
    {
        $__internal_e374a7f4b8f4869109917c0a31e90fe0327b11f3882c624694daaf82212ae5c2 = $this->env->getExtension("native_profiler");
        $__internal_e374a7f4b8f4869109917c0a31e90fe0327b11f3882c624694daaf82212ae5c2->enter($__internal_e374a7f4b8f4869109917c0a31e90fe0327b11f3882c624694daaf82212ae5c2_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "form_row"));

        // line 3
        echo "    <div class=\"dish-item-ingredients form-group form-inline\">
        ";
        // line 4
        echo $this->env->getExtension('form')->renderer->searchAndRenderBlock((isset($context["form"]) ? $context["form"] : $this->getContext($context, "form")), 'label');
        echo "
        <div class=\"col-sm-6\">
            <ul>
                ";
        // line 7
        if (twig_length_filter($this->env, (isset($context["form"]) ? $context["form"] : $this->getContext($context, "form")))) {
            // line 8
            echo "                    ";
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable((isset($context["form"]) ? $context["form"] : $this->getContext($context, "form")));
            foreach ($context['_seq'] as $context["_key"] => $context["dishItem"]) {
                // line 9
                echo "                        <li>
                            <div class='dish-ingredient-amount'>
                                <div> ";
                // line 11
                echo $this->env->getExtension('form')->renderer->searchAndRenderBlock($this->getAttribute($context["dishItem"], "amount", array()), 'widget');
                echo " </div>
                                <small>";
                // line 12
                echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute($this->getAttribute($context["dishItem"], "amount", array()), "vars", array()), "help", array()), "html", null, true);
                echo "</small>
                            </div>
                            <div class='dish-ingredient-list-name'>
                                <div>";
                // line 15
                echo $this->env->getExtension('form')->renderer->searchAndRenderBlock($this->getAttribute($context["dishItem"], "dishMenuItem", array()), 'widget');
                echo " </div>
                                <span class=\"help-block\">
                                    <small class=\"currentprice\"></small>
                                </span>
                            </div>
                            <i class=\"fa fa-times\"></i>
                        </li>
                    ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['dishItem'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 23
            echo "                ";
        }
        // line 24
        echo "            </ul>
            <a data-prototype=\"
                <div class='dish-ingredient-amount'>
                    <div>";
        // line 27
        echo twig_escape_filter($this->env, $this->env->getExtension('form')->renderer->searchAndRenderBlock($this->getAttribute($this->getAttribute($this->getAttribute((isset($context["form"]) ? $context["form"] : $this->getContext($context, "form")), "vars", array()), "prototype", array()), "amount", array()), 'widget'));
        echo "</div>
                    <small></small>
                </div>
                <div class='dish-ingredient-list-name'>
                    <div>";
        // line 31
        echo twig_escape_filter($this->env, $this->env->getExtension('form')->renderer->searchAndRenderBlock($this->getAttribute($this->getAttribute($this->getAttribute((isset($context["form"]) ? $context["form"] : $this->getContext($context, "form")), "vars", array()), "prototype", array()), "dishMenuItem", array()), 'widget'));
        echo "</div>
                    <span class='help-block'>
                        <small class='currentprice'></small>
                    </span>
                </div>
                ";
        // line 36
        echo twig_escape_filter($this->env, "<i class=\"fa fa-times\"></i> ");
        echo "\"
               class=\"btn btn-xs btn-success\"><i class=\"fa fa-plus\"></i> ";
        // line 37
        echo twig_escape_filter($this->env, $this->env->getExtension('translator')->trans("crud.add"), "html", null, true);
        echo "</a>
        </div>
    </div>
";
        
        $__internal_e374a7f4b8f4869109917c0a31e90fe0327b11f3882c624694daaf82212ae5c2->leave($__internal_e374a7f4b8f4869109917c0a31e90fe0327b11f3882c624694daaf82212ae5c2_prof);

    }

    public function getTemplateName()
    {
        return "@MBHRestaurant/Form/dishItemCollection.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  111 => 37,  107 => 36,  99 => 31,  92 => 27,  87 => 24,  84 => 23,  70 => 15,  64 => 12,  60 => 11,  56 => 9,  51 => 8,  49 => 7,  43 => 4,  40 => 3,  34 => 2,  11 => 1,);
    }
}
/* {% extends '@MBHBase/Form/fields.html.twig' %}*/
/* {% block form_row %}*/
/*     <div class="dish-item-ingredients form-group form-inline">*/
/*         {{ form_label(form) }}*/
/*         <div class="col-sm-6">*/
/*             <ul>*/
/*                 {% if form|length %}*/
/*                     {% for dishItem in form %}*/
/*                         <li>*/
/*                             <div class='dish-ingredient-amount'>*/
/*                                 <div> {{ form_widget(dishItem.amount) }} </div>*/
/*                                 <small>{{ dishItem.amount.vars.help }}</small>*/
/*                             </div>*/
/*                             <div class='dish-ingredient-list-name'>*/
/*                                 <div>{{ form_widget(dishItem.dishMenuItem) }} </div>*/
/*                                 <span class="help-block">*/
/*                                     <small class="currentprice"></small>*/
/*                                 </span>*/
/*                             </div>*/
/*                             <i class="fa fa-times"></i>*/
/*                         </li>*/
/*                     {% endfor %}*/
/*                 {% endif %}*/
/*             </ul>*/
/*             <a data-prototype="*/
/*                 <div class='dish-ingredient-amount'>*/
/*                     <div>{{ form_widget(form.vars.prototype.amount)|e }}</div>*/
/*                     <small></small>*/
/*                 </div>*/
/*                 <div class='dish-ingredient-list-name'>*/
/*                     <div>{{ form_widget(form.vars.prototype.dishMenuItem)|e }}</div>*/
/*                     <span class='help-block'>*/
/*                         <small class='currentprice'></small>*/
/*                     </span>*/
/*                 </div>*/
/*                 {{ '<i class="fa fa-times"></i> '|e }}"*/
/*                class="btn btn-xs btn-success"><i class="fa fa-plus"></i> {{ 'crud.add'|trans }}</a>*/
/*         </div>*/
/*     </div>*/
/* {% endblock %}*/
/* */

<?php

/* @MBHBase/Partials/embed_filter.html.twig */
class __TwigTemplate_c917ab9ee486d4ef9f2d0aee8904090b5add80c44cd06c935f0f044b0823987a extends MBH\Bundle\BaseBundle\Twig\Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = array(
            'content' => array($this, 'block_content'),
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        $__internal_d0df7da3dc356169ea520ee400e3f77cd58683222cfecdaa272ae7d544a2c87e = $this->env->getExtension("native_profiler");
        $__internal_d0df7da3dc356169ea520ee400e3f77cd58683222cfecdaa272ae7d544a2c87e->enter($__internal_d0df7da3dc356169ea520ee400e3f77cd58683222cfecdaa272ae7d544a2c87e_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "@MBHBase/Partials/embed_filter.html.twig"));

        // line 1
        $this->loadTemplate("MBHBaseBundle:Partials:filter.html.twig", "@MBHBase/Partials/embed_filter.html.twig", 1)->display($context);
        // line 2
        echo "    ";
        $this->displayBlock('content', $context, $blocks);
        // line 4
        echo "    </div>
</div>";
        
        $__internal_d0df7da3dc356169ea520ee400e3f77cd58683222cfecdaa272ae7d544a2c87e->leave($__internal_d0df7da3dc356169ea520ee400e3f77cd58683222cfecdaa272ae7d544a2c87e_prof);

    }

    // line 2
    public function block_content($context, array $blocks = array())
    {
        $__internal_ca0ca2d16a843f1dcf86efbe6801a590853cdb23357044bf12cb2c5b028aeda1 = $this->env->getExtension("native_profiler");
        $__internal_ca0ca2d16a843f1dcf86efbe6801a590853cdb23357044bf12cb2c5b028aeda1->enter($__internal_ca0ca2d16a843f1dcf86efbe6801a590853cdb23357044bf12cb2c5b028aeda1_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "content"));

        // line 3
        echo "    ";
        
        $__internal_ca0ca2d16a843f1dcf86efbe6801a590853cdb23357044bf12cb2c5b028aeda1->leave($__internal_ca0ca2d16a843f1dcf86efbe6801a590853cdb23357044bf12cb2c5b028aeda1_prof);

    }

    public function getTemplateName()
    {
        return "@MBHBase/Partials/embed_filter.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  42 => 3,  36 => 2,  28 => 4,  25 => 2,  23 => 1,);
    }
}
/* {% include 'MBHBaseBundle:Partials:filter.html.twig' %}*/
/*     {% block content %}*/
/*     {% endblock content %}*/
/*     </div>*/
/* </div>*/

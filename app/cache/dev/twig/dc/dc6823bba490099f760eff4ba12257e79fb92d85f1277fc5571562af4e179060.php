<?php

/* MBHBaseBundle:Partials:entityDeleteForm.html.twig */
class __TwigTemplate_ce94cb1b1ac88109b2ae0ec71b3072b8464cb19556f013ae46af8bef78f8721f extends MBH\Bundle\BaseBundle\Twig\Template
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
        $__internal_29253e57e1cfdda09edd34b452bbeddcacbdbd9f44c340a06d67d3b6635f8664 = $this->env->getExtension("native_profiler");
        $__internal_29253e57e1cfdda09edd34b452bbeddcacbdbd9f44c340a06d67d3b6635f8664->enter($__internal_29253e57e1cfdda09edd34b452bbeddcacbdbd9f44c340a06d67d3b6635f8664_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "MBHBaseBundle:Partials:entityDeleteForm.html.twig"));

        // line 1
        echo "<div class=\"modal fade modal-danger\" id=\"entity-delete-confirmation\" tabindex=\"-1\" role=\"dialog\" aria-labelledby=\"entity-delete-button\" aria-hidden=\"true\">
  <div class=\"modal-dialog modal-sm\">
    <div class=\"modal-content\">
      <div class=\"modal-header\">
        <button type=\"button\" class=\"close\" data-dismiss=\"modal\" aria-hidden=\"true\">&times;</button>
        <h4 class=\"modal-title\" data-default=";
        // line 6
        echo twig_escape_filter($this->env, $this->env->getExtension('translator')->trans("views.partials.entityDeleteForm.confirm_delete_quote", array(), "MBHBaseBundle"), "html", null, true);
        echo " id=\"entity-delete-modal-header\">";
        echo twig_escape_filter($this->env, $this->env->getExtension('translator')->trans("views.partials.entityDeleteForm.confirm_delete", array(), "MBHBaseBundle"), "html", null, true);
        echo "</h4>
      </div>
      <div class=\"modal-body\" data-default=\"";
        // line 8
        echo twig_escape_filter($this->env, $this->env->getExtension('translator')->trans("views.partials.entityDeleteForm.are_you_sure_delete_record_quote", array(), "MBHBaseBundle"), "html", null, true);
        echo "\" id=\"entity-delete-modal-text\">
          ";
        // line 9
        echo twig_escape_filter($this->env, $this->env->getExtension('translator')->trans("views.partials.entityDeleteForm.are_you_sure_delete_record", array(), "MBHBaseBundle"), "html", null, true);
        echo "
      </div>
      <div class=\"modal-footer\">
        <button type=\"button\" class=\"btn btn-outline pull-left\" data-dismiss=\"modal\"><i class=\"fa fa-ban\"> </i> ";
        // line 12
        echo twig_escape_filter($this->env, $this->env->getExtension('translator')->trans("views.partials.entityDeleteForm.cancel", array(), "MBHBaseBundle"), "html", null, true);
        echo "</button>
        <button type=\"button\" id=\"entity-delete-button\" data-default=\"outline\" class=\"btn btn-outline\"><i id=\"entity-delete-button-icon\" data-default=\"fa-trash-o\"  class=\"fa fa-trash-o\"></i>&nbsp;<span data-default=\"";
        // line 13
        echo twig_escape_filter($this->env, ((array_key_exists("delete_title", $context)) ? (_twig_default_filter((isset($context["delete_title"]) ? $context["delete_title"] : $this->getContext($context, "delete_title")), "Удалить")) : ("Удалить")), "html", null, true);
        echo " \" id=\"entity-delete-button-text\">";
        echo twig_escape_filter($this->env, ((array_key_exists("delete_title", $context)) ? (_twig_default_filter((isset($context["delete_title"]) ? $context["delete_title"] : $this->getContext($context, "delete_title")), "Удалить")) : ("Удалить")), "html", null, true);
        echo "</span></button>
      </div>
    </div>
  </div>
</div>";
        
        $__internal_29253e57e1cfdda09edd34b452bbeddcacbdbd9f44c340a06d67d3b6635f8664->leave($__internal_29253e57e1cfdda09edd34b452bbeddcacbdbd9f44c340a06d67d3b6635f8664_prof);

    }

    public function getTemplateName()
    {
        return "MBHBaseBundle:Partials:entityDeleteForm.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  50 => 13,  46 => 12,  40 => 9,  36 => 8,  29 => 6,  22 => 1,);
    }
}
/* <div class="modal fade modal-danger" id="entity-delete-confirmation" tabindex="-1" role="dialog" aria-labelledby="entity-delete-button" aria-hidden="true">*/
/*   <div class="modal-dialog modal-sm">*/
/*     <div class="modal-content">*/
/*       <div class="modal-header">*/
/*         <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>*/
/*         <h4 class="modal-title" data-default={{ 'views.partials.entityDeleteForm.confirm_delete_quote'|trans({}, 'MBHBaseBundle') }} id="entity-delete-modal-header">{{ 'views.partials.entityDeleteForm.confirm_delete'|trans({}, 'MBHBaseBundle') }}</h4>*/
/*       </div>*/
/*       <div class="modal-body" data-default="{{ 'views.partials.entityDeleteForm.are_you_sure_delete_record_quote'|trans({}, 'MBHBaseBundle') }}" id="entity-delete-modal-text">*/
/*           {{ 'views.partials.entityDeleteForm.are_you_sure_delete_record'|trans({}, 'MBHBaseBundle') }}*/
/*       </div>*/
/*       <div class="modal-footer">*/
/*         <button type="button" class="btn btn-outline pull-left" data-dismiss="modal"><i class="fa fa-ban"> </i> {{ 'views.partials.entityDeleteForm.cancel'|trans({}, 'MBHBaseBundle') }}</button>*/
/*         <button type="button" id="entity-delete-button" data-default="outline" class="btn btn-outline"><i id="entity-delete-button-icon" data-default="fa-trash-o"  class="fa fa-trash-o"></i>&nbsp;<span data-default="{{ delete_title|default('Удалить') }} " id="entity-delete-button-text">{{ delete_title|default('Удалить') }}</span></button>*/
/*       </div>*/
/*     </div>*/
/*   </div>*/
/* </div>*/

<?php

/* MBHBaseBundle:Form:fields.html.twig */
class __TwigTemplate_a8f4c7bbef57648c3a62263bf22858c1ee0d75d8f0e385d3074c796385d70c22 extends MBH\Bundle\BaseBundle\Twig\Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = array(
            'choice_widget_expanded' => array($this, 'block_choice_widget_expanded'),
            'form_widget_compound' => array($this, 'block_form_widget_compound'),
            'form_row' => array($this, 'block_form_row'),
            'datetime_widget' => array($this, 'block_datetime_widget'),
            'form_label' => array($this, 'block_form_label'),
            'form_widget_simple' => array($this, 'block_form_widget_simple'),
            'choice_widget_collapsed' => array($this, 'block_choice_widget_collapsed'),
            'textarea_widget' => array($this, 'block_textarea_widget'),
            'form_errors' => array($this, 'block_form_errors'),
            'roles_widget' => array($this, 'block_roles_widget'),
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        $__internal_ab5aa6aca9b033013252e7a944737d9a017239fa7077e160f67f64587c0fb666 = $this->env->getExtension("native_profiler");
        $__internal_ab5aa6aca9b033013252e7a944737d9a017239fa7077e160f67f64587c0fb666->enter($__internal_ab5aa6aca9b033013252e7a944737d9a017239fa7077e160f67f64587c0fb666_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "MBHBaseBundle:Form:fields.html.twig"));

        // line 1
        $this->displayBlock('choice_widget_expanded', $context, $blocks);
        // line 13
        echo "
";
        // line 14
        $this->displayBlock('form_widget_compound', $context, $blocks);
        // line 50
        echo "
";
        // line 51
        $this->displayBlock('form_row', $context, $blocks);
        // line 71
        $this->displayBlock('datetime_widget', $context, $blocks);
        // line 84
        $this->displayBlock('form_label', $context, $blocks);
        // line 102
        echo "
";
        // line 103
        $this->displayBlock('form_widget_simple', $context, $blocks);
        // line 118
        echo "
";
        // line 119
        $this->displayBlock('choice_widget_collapsed', $context, $blocks);
        // line 138
        echo "
";
        // line 139
        $this->displayBlock('textarea_widget', $context, $blocks);
        // line 145
        echo "
";
        // line 146
        $this->displayBlock('form_errors', $context, $blocks);
        // line 157
        echo "
";
        // line 158
        $this->displayBlock('roles_widget', $context, $blocks);
        
        $__internal_ab5aa6aca9b033013252e7a944737d9a017239fa7077e160f67f64587c0fb666->leave($__internal_ab5aa6aca9b033013252e7a944737d9a017239fa7077e160f67f64587c0fb666_prof);

    }

    // line 1
    public function block_choice_widget_expanded($context, array $blocks = array())
    {
        $__internal_7f1eda7a13e4aa49141205695a54ea6315820be049152f3799a0f1c043c02ec0 = $this->env->getExtension("native_profiler");
        $__internal_7f1eda7a13e4aa49141205695a54ea6315820be049152f3799a0f1c043c02ec0->enter($__internal_7f1eda7a13e4aa49141205695a54ea6315820be049152f3799a0f1c043c02ec0_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "choice_widget_expanded"));

        // line 2
        echo "    ";
        ob_start();
        // line 3
        echo "        <div ";
        $this->displayBlock("widget_container_attributes", $context, $blocks);
        echo " class=\"btn-group\" data-toggle=\"buttons\">
            ";
        // line 4
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable((isset($context["form"]) ? $context["form"] : $this->getContext($context, "form")));
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
        foreach ($context['_seq'] as $context["_key"] => $context["child"]) {
            // line 5
            echo "                <label ";
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable($this->getAttribute($this->getAttribute($context["child"], "vars", array()), "attr", array()));
            foreach ($context['_seq'] as $context["k"] => $context["a"]) {
                echo " ";
                echo twig_escape_filter($this->env, $context["k"], "html", null, true);
                echo "=\"";
                echo twig_escape_filter($this->env, $context["a"], "html", null, true);
                echo "\" ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['k'], $context['a'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            echo " class=\"btn btn-sm btn-default ";
            if ($this->getAttribute($this->getAttribute($context["child"], "vars", array()), "checked", array())) {
                echo " active";
            }
            echo "\">
                    <input type=\"radio\" ";
            // line 6
            $this->displayBlock("widget_attributes", $context, $blocks);
            if ($this->getAttribute($this->getAttribute($context["child"], "vars", array(), "any", false, true), "value", array(), "any", true, true)) {
                echo " value=\"";
                echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute($context["child"], "vars", array()), "value", array()), "html", null, true);
                echo "\"";
            }
            if ($this->getAttribute($this->getAttribute($context["child"], "vars", array()), "checked", array())) {
                echo " checked=\"checked\"";
            }
            echo " />
                    ";
            // line 7
            echo twig_escape_filter($this->env, $this->env->getExtension('translator')->trans($this->getAttribute($this->getAttribute($context["child"], "vars", array()), "label", array())), "html", null, true);
            echo "
                </label>
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
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['child'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 10
        echo "        </div>
    ";
        echo trim(preg_replace('/>\s+</', '><', ob_get_clean()));
        
        $__internal_7f1eda7a13e4aa49141205695a54ea6315820be049152f3799a0f1c043c02ec0->leave($__internal_7f1eda7a13e4aa49141205695a54ea6315820be049152f3799a0f1c043c02ec0_prof);

    }

    // line 14
    public function block_form_widget_compound($context, array $blocks = array())
    {
        $__internal_e58012df5aa569a7f09e044350ed20923f1765f336c83e2a58aaf9838154ea3d = $this->env->getExtension("native_profiler");
        $__internal_e58012df5aa569a7f09e044350ed20923f1765f336c83e2a58aaf9838154ea3d->enter($__internal_e58012df5aa569a7f09e044350ed20923f1765f336c83e2a58aaf9838154ea3d_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "form_widget_compound"));

        // line 15
        echo "    <div ";
        $this->displayBlock("widget_container_attributes", $context, $blocks);
        echo ">
        ";
        // line 16
        if (twig_test_empty($this->getAttribute((isset($context["form"]) ? $context["form"] : $this->getContext($context, "form")), "parent", array()))) {
            // line 17
            echo "            ";
            if ((twig_length_filter($this->env, (isset($context["errors"]) ? $context["errors"] : $this->getContext($context, "errors"))) > 0)) {
                // line 18
                echo "                <div class=\"alert alert-danger global-errors\">";
                echo $this->env->getExtension('form')->renderer->searchAndRenderBlock((isset($context["form"]) ? $context["form"] : $this->getContext($context, "form")), 'errors');
                echo "</div>";
            }
            // line 19
            echo "        ";
        }
        // line 20
        echo "
        ";
        // line 21
        $context["groups"] = array("form.main.group" => twig_get_array_keys_filter($this->getAttribute((isset($context["form"]) ? $context["form"] : $this->getContext($context, "form")), "children", array())));
        // line 22
        echo "        ";
        if ($this->getAttribute($this->getAttribute((isset($context["form"]) ? $context["form"] : null), "vars", array(), "any", false, true), "groups", array(), "any", true, true)) {
            // line 23
            echo "            ";
            $context["groups"] = $this->getAttribute($this->getAttribute((isset($context["form"]) ? $context["form"] : $this->getContext($context, "form")), "vars", array()), "groups", array());
            // line 24
            echo "        ";
        }
        // line 25
        echo "
        ";
        // line 26
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable((isset($context["groups"]) ? $context["groups"] : $this->getContext($context, "groups")));
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
        foreach ($context['_seq'] as $context["group"] => $context["items"]) {
            // line 27
            echo "
            <div class=\"box box-default box-solid\">

                <div class=\"box-header with-border\">
                    <h3 class=\"box-title\">";
            // line 31
            echo twig_escape_filter($this->env, $this->env->getExtension('translator')->trans($context["group"]), "html", null, true);
            echo "</h3>

                    <div class=\"box-tools pull-right\">
                        <button class=\"btn btn-box-tool form-group-collapse\" data-widget=\"collapse\"
                                id=\"";
            // line 35
            echo twig_escape_filter($this->env, (isset($context["id"]) ? $context["id"] : $this->getContext($context, "id")), "html", null, true);
            echo "-group-";
            echo twig_escape_filter($this->env, $this->getAttribute($context["loop"], "index", array()), "html", null, true);
            echo "\"><i class=\"fa fa-minus\"></i></button>
                    </div>
                </div>

                <div class=\"box-body\">
                    ";
            // line 40
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable($context["items"]);
            foreach ($context['_seq'] as $context["_key"] => $context["item"]) {
                // line 41
                echo "                        ";
                echo $this->env->getExtension('form')->renderer->searchAndRenderBlock($this->getAttribute((isset($context["form"]) ? $context["form"] : $this->getContext($context, "form")), $context["item"], array(), "array"), 'row');
                echo "
                    ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['item'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 43
            echo "                </div>
            </div>
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
        unset($context['_seq'], $context['_iterated'], $context['group'], $context['items'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 46
        echo "
        ";
        // line 47
        echo $this->env->getExtension('form')->renderer->searchAndRenderBlock((isset($context["form"]) ? $context["form"] : $this->getContext($context, "form")), 'rest');
        echo "
    </div>
";
        
        $__internal_e58012df5aa569a7f09e044350ed20923f1765f336c83e2a58aaf9838154ea3d->leave($__internal_e58012df5aa569a7f09e044350ed20923f1765f336c83e2a58aaf9838154ea3d_prof);

    }

    // line 51
    public function block_form_row($context, array $blocks = array())
    {
        $__internal_e13860fee1dea0ffbdf9c3112735243ee1aafa42686d7dc4a90590cb1a053c9f = $this->env->getExtension("native_profiler");
        $__internal_e13860fee1dea0ffbdf9c3112735243ee1aafa42686d7dc4a90590cb1a053c9f->enter($__internal_e13860fee1dea0ffbdf9c3112735243ee1aafa42686d7dc4a90590cb1a053c9f_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "form_row"));

        // line 52
        echo "    ";
        ob_start();
        // line 53
        echo "        <div class=\"form-group ";
        if ((twig_length_filter($this->env, (isset($context["errors"]) ? $context["errors"] : $this->getContext($context, "errors"))) > 0)) {
            echo " has-error";
        }
        echo "\">
            ";
        // line 54
        echo $this->env->getExtension('form')->renderer->searchAndRenderBlock((isset($context["form"]) ? $context["form"] : $this->getContext($context, "form")), 'label');
        echo "

            <div class=\"col-sm-6\">
                ";
        // line 57
        echo $this->env->getExtension('form')->renderer->searchAndRenderBlock((isset($context["form"]) ? $context["form"] : $this->getContext($context, "form")), 'widget');
        echo "
                ";
        // line 58
        if ( !(null === (isset($context["help"]) ? $context["help"] : $this->getContext($context, "help")))) {
            // line 59
            echo "                    <span class=\"help-block\"><small>";
            echo $this->env->getExtension('translator')->trans((isset($context["help"]) ? $context["help"] : $this->getContext($context, "help")));
            echo "</small></span>
                ";
        }
        // line 61
        echo "            </div>
            <div class=\"col-sm-4\">
                ";
        // line 63
        if ((twig_length_filter($this->env, (isset($context["errors"]) ? $context["errors"] : $this->getContext($context, "errors"))) > 0)) {
            // line 64
            echo "                    <span class=\"text-danger text-left input-errors\">";
            echo $this->env->getExtension('form')->renderer->searchAndRenderBlock((isset($context["form"]) ? $context["form"] : $this->getContext($context, "form")), 'errors');
            echo "</span>
                ";
        }
        // line 66
        echo "            </div>
        </div>
    ";
        echo trim(preg_replace('/>\s+</', '><', ob_get_clean()));
        
        $__internal_e13860fee1dea0ffbdf9c3112735243ee1aafa42686d7dc4a90590cb1a053c9f->leave($__internal_e13860fee1dea0ffbdf9c3112735243ee1aafa42686d7dc4a90590cb1a053c9f_prof);

    }

    // line 71
    public function block_datetime_widget($context, array $blocks = array())
    {
        $__internal_b797b82610a73f0c1b02cce3b824c7b7156fe201cc6151b2fe240ac2717089ac = $this->env->getExtension("native_profiler");
        $__internal_b797b82610a73f0c1b02cce3b824c7b7156fe201cc6151b2fe240ac2717089ac->enter($__internal_b797b82610a73f0c1b02cce3b824c7b7156fe201cc6151b2fe240ac2717089ac_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "datetime_widget"));

        // line 72
        if (((isset($context["widget"]) ? $context["widget"] : $this->getContext($context, "widget")) == "single_text")) {
            // line 73
            $this->displayBlock("form_widget_simple", $context, $blocks);
        } else {
            // line 75
            echo "<div ";
            $this->displayBlock("widget_container_attributes", $context, $blocks);
            echo ">";
            // line 76
            echo $this->env->getExtension('form')->renderer->searchAndRenderBlock($this->getAttribute((isset($context["form"]) ? $context["form"] : $this->getContext($context, "form")), "date", array()), 'errors');
            // line 77
            echo $this->env->getExtension('form')->renderer->searchAndRenderBlock($this->getAttribute((isset($context["form"]) ? $context["form"] : $this->getContext($context, "form")), "time", array()), 'errors');
            // line 78
            echo $this->env->getExtension('form')->renderer->searchAndRenderBlock($this->getAttribute((isset($context["form"]) ? $context["form"] : $this->getContext($context, "form")), "date", array()), 'widget');
            // line 79
            echo "<div class=\"bootstrap-timepicker\">";
            echo $this->env->getExtension('form')->renderer->searchAndRenderBlock($this->getAttribute((isset($context["form"]) ? $context["form"] : $this->getContext($context, "form")), "time", array()), 'widget');
            echo "</div>
        </div>";
        }
        
        $__internal_b797b82610a73f0c1b02cce3b824c7b7156fe201cc6151b2fe240ac2717089ac->leave($__internal_b797b82610a73f0c1b02cce3b824c7b7156fe201cc6151b2fe240ac2717089ac_prof);

    }

    // line 84
    public function block_form_label($context, array $blocks = array())
    {
        $__internal_0cb059382d8f9cf4e0275a0fae8d9b919f9ad21dac0ddcf46af7fa1d946e8b42 = $this->env->getExtension("native_profiler");
        $__internal_0cb059382d8f9cf4e0275a0fae8d9b919f9ad21dac0ddcf46af7fa1d946e8b42->enter($__internal_0cb059382d8f9cf4e0275a0fae8d9b919f9ad21dac0ddcf46af7fa1d946e8b42_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "form_label"));

        // line 85
        echo "    ";
        ob_start();
        // line 86
        echo "        ";
        if ( !((isset($context["label"]) ? $context["label"] : $this->getContext($context, "label")) === false)) {
            // line 87
            echo "            ";
            if ( !(isset($context["compound"]) ? $context["compound"] : $this->getContext($context, "compound"))) {
                // line 88
                echo "                ";
                $context["label_attr"] = twig_array_merge((isset($context["label_attr"]) ? $context["label_attr"] : $this->getContext($context, "label_attr")), array("for" => (isset($context["id"]) ? $context["id"] : $this->getContext($context, "id"))));
                // line 89
                echo "            ";
            }
            // line 90
            echo "            ";
            if ((isset($context["required"]) ? $context["required"] : $this->getContext($context, "required"))) {
                // line 91
                echo "                ";
                $context["label_attr"] = twig_array_merge((isset($context["label_attr"]) ? $context["label_attr"] : $this->getContext($context, "label_attr")), array("class" => trim(((($this->getAttribute((isset($context["label_attr"]) ? $context["label_attr"] : null), "class", array(), "any", true, true)) ? (_twig_default_filter($this->getAttribute((isset($context["label_attr"]) ? $context["label_attr"] : null), "class", array()), "")) : ("")) . " required"))));
                // line 92
                echo "            ";
            }
            // line 93
            echo "            ";
            if (twig_test_empty((isset($context["label"]) ? $context["label"] : $this->getContext($context, "label")))) {
                // line 94
                echo "                ";
                $context["label"] = $this->env->getExtension('form')->humanize((isset($context["name"]) ? $context["name"] : $this->getContext($context, "name")));
                // line 95
                echo "            ";
            }
            // line 96
            echo "            ";
            $context["label_attr"] = twig_array_merge((isset($context["label_attr"]) ? $context["label_attr"] : $this->getContext($context, "label_attr")), array("class" => trim(((($this->getAttribute((isset($context["label_attr"]) ? $context["label_attr"] : null), "class", array(), "any", true, true)) ? (_twig_default_filter($this->getAttribute((isset($context["label_attr"]) ? $context["label_attr"] : null), "class", array()), "")) : ("")) . " control-label col-sm-2"))));
            // line 97
            echo "
            <label ";
            // line 98
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable((isset($context["label_attr"]) ? $context["label_attr"] : $this->getContext($context, "label_attr")));
            foreach ($context['_seq'] as $context["attrname"] => $context["attrvalue"]) {
                echo " ";
                echo twig_escape_filter($this->env, $context["attrname"], "html", null, true);
                echo "=\"";
                echo twig_escape_filter($this->env, $context["attrvalue"], "html", null, true);
                echo "\"";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['attrname'], $context['attrvalue'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            echo ">";
            echo $this->env->getExtension('translator')->trans((isset($context["label"]) ? $context["label"] : $this->getContext($context, "label")), array(), (isset($context["translation_domain"]) ? $context["translation_domain"] : $this->getContext($context, "translation_domain")));
            if ((isset($context["required"]) ? $context["required"] : $this->getContext($context, "required"))) {
                echo "&nbsp;<span class=\"required-star text-danger\">*</span>";
            }
            echo "</label>
        ";
        }
        // line 100
        echo "    ";
        echo trim(preg_replace('/>\s+</', '><', ob_get_clean()));
        
        $__internal_0cb059382d8f9cf4e0275a0fae8d9b919f9ad21dac0ddcf46af7fa1d946e8b42->leave($__internal_0cb059382d8f9cf4e0275a0fae8d9b919f9ad21dac0ddcf46af7fa1d946e8b42_prof);

    }

    // line 103
    public function block_form_widget_simple($context, array $blocks = array())
    {
        $__internal_6b26d8cd1b841de7a9b9abec198480956506a8a0a74acae0a169fae832a988e6 = $this->env->getExtension("native_profiler");
        $__internal_6b26d8cd1b841de7a9b9abec198480956506a8a0a74acae0a169fae832a988e6->enter($__internal_6b26d8cd1b841de7a9b9abec198480956506a8a0a74acae0a169fae832a988e6_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "form_widget_simple"));

        // line 104
        echo "
    ";
        // line 105
        ob_start();
        // line 106
        echo "        ";
        $context["type"] = ((array_key_exists("type", $context)) ? (_twig_default_filter((isset($context["type"]) ? $context["type"] : $this->getContext($context, "type")), "text")) : ("text"));
        // line 107
        echo "        ";
        if (((isset($context["type"]) ? $context["type"] : $this->getContext($context, "type")) != "file")) {
            // line 108
            echo "            ";
            $context["attr"] = twig_array_merge((isset($context["attr"]) ? $context["attr"] : $this->getContext($context, "attr")), array("class" => trim(((($this->getAttribute((isset($context["attr"]) ? $context["attr"] : null), "class", array(), "any", true, true)) ? (_twig_default_filter($this->getAttribute((isset($context["attr"]) ? $context["attr"] : null), "class", array()), "")) : ("")) . " form-control input-sm"))));
            // line 109
            echo "        ";
        }
        // line 110
        echo "        ";
        if ((isset($context["addon"]) ? $context["addon"] : $this->getContext($context, "addon"))) {
            echo "<div class=\"input-group\">";
        }
        // line 111
        echo "        <input type=\"";
        echo twig_escape_filter($this->env, (isset($context["type"]) ? $context["type"] : $this->getContext($context, "type")), "html", null, true);
        echo "\" ";
        $this->displayBlock("widget_attributes", $context, $blocks);
        echo " ";
        if ( !twig_test_empty((isset($context["value"]) ? $context["value"] : $this->getContext($context, "value")))) {
            echo "value=\"";
            echo twig_escape_filter($this->env, (isset($context["value"]) ? $context["value"] : $this->getContext($context, "value")), "html", null, true);
            echo "\" ";
        }
        echo "/>
        ";
        // line 112
        if ((isset($context["addon"]) ? $context["addon"] : $this->getContext($context, "addon"))) {
            // line 113
            echo "            <span class=\"input-group-addon\"><i class=\"";
            echo twig_escape_filter($this->env, (isset($context["addon"]) ? $context["addon"] : $this->getContext($context, "addon")), "html", null, true);
            echo "\"></i></span>
            </div>";
        }
        // line 115
        echo "
    ";
        echo trim(preg_replace('/>\s+</', '><', ob_get_clean()));
        
        $__internal_6b26d8cd1b841de7a9b9abec198480956506a8a0a74acae0a169fae832a988e6->leave($__internal_6b26d8cd1b841de7a9b9abec198480956506a8a0a74acae0a169fae832a988e6_prof);

    }

    // line 119
    public function block_choice_widget_collapsed($context, array $blocks = array())
    {
        $__internal_6cef53667e6e2ecc016ebd4906d962d5ba8f1a7c9f81f02c5538e54e6218ded2 = $this->env->getExtension("native_profiler");
        $__internal_6cef53667e6e2ecc016ebd4906d962d5ba8f1a7c9f81f02c5538e54e6218ded2->enter($__internal_6cef53667e6e2ecc016ebd4906d962d5ba8f1a7c9f81f02c5538e54e6218ded2_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "choice_widget_collapsed"));

        // line 120
        echo "    ";
        ob_start();
        // line 121
        echo "        ";
        $context["attr"] = twig_array_merge((isset($context["attr"]) ? $context["attr"] : $this->getContext($context, "attr")), array("class" => trim(((($this->getAttribute((isset($context["attr"]) ? $context["attr"] : null), "class", array(), "any", true, true)) ? (_twig_default_filter($this->getAttribute((isset($context["attr"]) ? $context["attr"] : null), "class", array()), "")) : ("")) . " form-control input-sm"))));
        // line 122
        echo "        <select ";
        $this->displayBlock("widget_attributes", $context, $blocks);
        if ((isset($context["multiple"]) ? $context["multiple"] : $this->getContext($context, "multiple"))) {
            echo " multiple ";
        }
        echo ">
            ";
        // line 123
        if ( !(null === (isset($context["empty_value"]) ? $context["empty_value"] : $this->getContext($context, "empty_value")))) {
            // line 124
            echo "                <option ";
            if ((isset($context["required"]) ? $context["required"] : $this->getContext($context, "required"))) {
                echo " disabled=\"disabled\"";
                if (twig_test_empty((isset($context["value"]) ? $context["value"] : $this->getContext($context, "value")))) {
                    echo " selected=\"selected\"";
                }
            } else {
                echo " value=\"\"";
            }
            echo ">";
            echo twig_escape_filter($this->env, $this->env->getExtension('translator')->trans((isset($context["empty_value"]) ? $context["empty_value"] : $this->getContext($context, "empty_value")), array(), (isset($context["translation_domain"]) ? $context["translation_domain"] : $this->getContext($context, "translation_domain"))), "html", null, true);
            echo "</option>
            ";
        }
        // line 126
        echo "            ";
        if ((twig_length_filter($this->env, (isset($context["preferred_choices"]) ? $context["preferred_choices"] : $this->getContext($context, "preferred_choices"))) > 0)) {
            // line 127
            echo "                ";
            $context["options"] = (isset($context["preferred_choices"]) ? $context["preferred_choices"] : $this->getContext($context, "preferred_choices"));
            // line 128
            echo "                ";
            $this->displayBlock("choice_widget_options", $context, $blocks);
            echo "
                ";
            // line 129
            if (((twig_length_filter($this->env, (isset($context["choices"]) ? $context["choices"] : $this->getContext($context, "choices"))) > 0) &&  !(null === (isset($context["separator"]) ? $context["separator"] : $this->getContext($context, "separator"))))) {
                // line 130
                echo "                    <option disabled=\"disabled\">";
                echo twig_escape_filter($this->env, (isset($context["separator"]) ? $context["separator"] : $this->getContext($context, "separator")), "html", null, true);
                echo "</option>
                ";
            }
            // line 132
            echo "            ";
        }
        // line 133
        echo "            ";
        $context["options"] = (isset($context["choices"]) ? $context["choices"] : $this->getContext($context, "choices"));
        // line 134
        echo "            ";
        $this->displayBlock("choice_widget_options", $context, $blocks);
        echo "
        </select>
    ";
        echo trim(preg_replace('/>\s+</', '><', ob_get_clean()));
        
        $__internal_6cef53667e6e2ecc016ebd4906d962d5ba8f1a7c9f81f02c5538e54e6218ded2->leave($__internal_6cef53667e6e2ecc016ebd4906d962d5ba8f1a7c9f81f02c5538e54e6218ded2_prof);

    }

    // line 139
    public function block_textarea_widget($context, array $blocks = array())
    {
        $__internal_4be285b8be9e1a1de3d729d372b3da5ac806d553134e63a2bff34e0f9aff0de2 = $this->env->getExtension("native_profiler");
        $__internal_4be285b8be9e1a1de3d729d372b3da5ac806d553134e63a2bff34e0f9aff0de2->enter($__internal_4be285b8be9e1a1de3d729d372b3da5ac806d553134e63a2bff34e0f9aff0de2_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "textarea_widget"));

        // line 140
        echo "    ";
        ob_start();
        // line 141
        echo "        ";
        $context["attr"] = twig_array_merge((isset($context["attr"]) ? $context["attr"] : $this->getContext($context, "attr")), array("class" => trim(((($this->getAttribute((isset($context["attr"]) ? $context["attr"] : null), "class", array(), "any", true, true)) ? (_twig_default_filter($this->getAttribute((isset($context["attr"]) ? $context["attr"] : null), "class", array()), "")) : ("")) . " form-control input-sm"))));
        // line 142
        echo "        <textarea ";
        $this->displayBlock("widget_attributes", $context, $blocks);
        echo ">";
        echo twig_escape_filter($this->env, (isset($context["value"]) ? $context["value"] : $this->getContext($context, "value")), "html", null, true);
        echo "</textarea>
    ";
        echo trim(preg_replace('/>\s+</', '><', ob_get_clean()));
        
        $__internal_4be285b8be9e1a1de3d729d372b3da5ac806d553134e63a2bff34e0f9aff0de2->leave($__internal_4be285b8be9e1a1de3d729d372b3da5ac806d553134e63a2bff34e0f9aff0de2_prof);

    }

    // line 146
    public function block_form_errors($context, array $blocks = array())
    {
        $__internal_df28fe666e9aa35dd1b7747ad165c8da71ef37d66484870b24e311f667859c2f = $this->env->getExtension("native_profiler");
        $__internal_df28fe666e9aa35dd1b7747ad165c8da71ef37d66484870b24e311f667859c2f->enter($__internal_df28fe666e9aa35dd1b7747ad165c8da71ef37d66484870b24e311f667859c2f_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "form_errors"));

        // line 147
        echo "    ";
        ob_start();
        // line 148
        echo "        ";
        if ((twig_length_filter($this->env, (isset($context["errors"]) ? $context["errors"] : $this->getContext($context, "errors"))) > 0)) {
            // line 149
            echo "            <ul>
                ";
            // line 150
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable((isset($context["errors"]) ? $context["errors"] : $this->getContext($context, "errors")));
            foreach ($context['_seq'] as $context["_key"] => $context["error"]) {
                // line 151
                echo "                    <li>";
                echo $this->getAttribute($context["error"], "message", array());
                echo "</li>
                ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['error'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 153
            echo "            </ul>
        ";
        }
        // line 155
        echo "    ";
        echo trim(preg_replace('/>\s+</', '><', ob_get_clean()));
        
        $__internal_df28fe666e9aa35dd1b7747ad165c8da71ef37d66484870b24e311f667859c2f->leave($__internal_df28fe666e9aa35dd1b7747ad165c8da71ef37d66484870b24e311f667859c2f_prof);

    }

    // line 158
    public function block_roles_widget($context, array $blocks = array())
    {
        $__internal_db49d8f31e248e0dcb027c2560139eeabcb83708441a8645417a7a87c5857a98 = $this->env->getExtension("native_profiler");
        $__internal_db49d8f31e248e0dcb027c2560139eeabcb83708441a8645417a7a87c5857a98->enter($__internal_db49d8f31e248e0dcb027c2560139eeabcb83708441a8645417a7a87c5857a98_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "roles_widget"));

        // line 159
        echo "
    ";
        // line 160
        $context["exists"] = array();
        // line 161
        echo "
    ";
        // line 162
        if ($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute((isset($context["form"]) ? $context["form"] : null), "parent", array(), "any", false, true), "vars", array(), "any", false, true), "value", array(), "any", false, true), "groups", array(), "any", true, true)) {
            // line 163
            echo "        ";
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute((isset($context["form"]) ? $context["form"] : $this->getContext($context, "form")), "parent", array()), "vars", array()), "value", array()), "groups", array()));
            foreach ($context['_seq'] as $context["_key"] => $context["group"]) {
                // line 164
                echo "            ";
                $context["exists"] = twig_array_merge((isset($context["exists"]) ? $context["exists"] : $this->getContext($context, "exists")), $this->getAttribute($context["group"], "roles", array()));
                // line 165
                echo "        ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['group'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 166
            echo "    ";
        }
        // line 167
        echo "
    ";
        // line 168
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable($this->getAttribute($this->getAttribute((isset($context["form"]) ? $context["form"] : $this->getContext($context, "form")), "vars", array()), "choices", array()));
        foreach ($context['_seq'] as $context["_key"] => $context["parent"]) {
            // line 169
            echo "
        ";
            // line 170
            $context["label"] = twig_replace_filter($this->getAttribute($context["parent"], "label", array()), array("__GROUP" => ""));
            // line 171
            echo "
        <div class=\"box roles-widget collapsed-box\">
            <div class=\"box-header\" style=\"background-color: #efefef;\">
                <h3 class=\"box-title\">
                    <label style=\"cursor: pointer;\">
                        <input type=\"checkbox\" ";
            // line 176
            echo ((twig_in_filter((isset($context["label"]) ? $context["label"] : $this->getContext($context, "label")), $this->getAttribute($this->getAttribute((isset($context["form"]) ? $context["form"] : $this->getContext($context, "form")), "vars", array()), "data", array()))) ? ("checked=\"checked\"") : (""));
            echo " class=\"flat-green plain-html\" name=\"";
            echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute((isset($context["form"]) ? $context["form"] : $this->getContext($context, "form")), "vars", array()), "full_name", array()), "html", null, true);
            echo "\" value=\"";
            echo twig_escape_filter($this->env, (isset($context["label"]) ? $context["label"] : $this->getContext($context, "label")), "html", null, true);
            echo "\">
                        &nbsp;
                        <span class=\"";
            // line 178
            echo (((twig_in_filter((isset($context["label"]) ? $context["label"] : $this->getContext($context, "label")), (isset($context["exists"]) ? $context["exists"] : $this->getContext($context, "exists"))) || twig_in_filter((isset($context["label"]) ? $context["label"] : $this->getContext($context, "label")), $this->getAttribute($this->getAttribute((isset($context["form"]) ? $context["form"] : $this->getContext($context, "form")), "vars", array()), "data", array())))) ? ("text-info") : (""));
            echo "\">";
            echo twig_escape_filter($this->env, $this->env->getExtension('translator')->trans((isset($context["label"]) ? $context["label"] : $this->getContext($context, "label")), array(), $this->getAttribute($this->getAttribute((isset($context["form"]) ? $context["form"] : $this->getContext($context, "form")), "vars", array()), "translation_domain", array())), "html", null, true);
            echo "</span>
                    </label>
                </h3>
                <div class=\"box-tools pull-right\">
                    <button class=\"btn btn-box-tool form-group-expandable show-checkboxes\" id=\"";
            // line 182
            echo twig_escape_filter($this->env, (isset($context["label"]) ? $context["label"] : $this->getContext($context, "label")), "html", null, true);
            echo "\"  data-widget=\"collapse\"><i class=\"fa fa-plus\"></i></button>
                </div>
            </div>
            <div class=\"box-body\">
                ";
            // line 186
            if ($this->getAttribute($context["parent"], "choices", array(), "any", true, true)) {
                // line 187
                echo "                    ";
                $context['_parent'] = $context;
                $context['_seq'] = twig_ensure_traversable($this->getAttribute($context["parent"], "choices", array()));
                foreach ($context['_seq'] as $context["_key"] => $context["child"]) {
                    if (((isset($context["label"]) ? $context["label"] : $this->getContext($context, "label")) != $this->getAttribute($context["child"], "label", array()))) {
                        // line 188
                        echo "                        <div class=\"form-group\" style=\"padding-left: 50px;\">
                            <label style=\"cursor: pointer;\">
                                <input type=\"checkbox\"  ";
                        // line 190
                        echo ((twig_in_filter($this->getAttribute($context["child"], "label", array()), $this->getAttribute($this->getAttribute((isset($context["form"]) ? $context["form"] : $this->getContext($context, "form")), "vars", array()), "data", array()))) ? ("checked=\"checked\"") : (""));
                        echo " class=\"flat-green plain-html\" name=\"";
                        echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute((isset($context["form"]) ? $context["form"] : $this->getContext($context, "form")), "vars", array()), "full_name", array()), "html", null, true);
                        echo "\" value=\"";
                        echo twig_escape_filter($this->env, $this->getAttribute($context["child"], "label", array()), "html", null, true);
                        echo "\">
                                &nbsp;
                                <span class=\"";
                        // line 192
                        echo (((twig_in_filter($this->getAttribute($context["child"], "label", array()), (isset($context["exists"]) ? $context["exists"] : $this->getContext($context, "exists"))) || twig_in_filter($this->getAttribute($context["child"], "label", array()), $this->getAttribute($this->getAttribute((isset($context["form"]) ? $context["form"] : $this->getContext($context, "form")), "vars", array()), "data", array())))) ? ("text-info") : (""));
                        echo "\">";
                        echo twig_escape_filter($this->env, $this->env->getExtension('translator')->trans($this->getAttribute($context["child"], "label", array()), array(), $this->getAttribute($this->getAttribute((isset($context["form"]) ? $context["form"] : $this->getContext($context, "form")), "vars", array()), "translation_domain", array())), "html", null, true);
                        echo "</span>
                            </label>
                        </div>
                    ";
                    }
                }
                $_parent = $context['_parent'];
                unset($context['_seq'], $context['_iterated'], $context['_key'], $context['child'], $context['_parent'], $context['loop']);
                $context = array_intersect_key($context, $_parent) + $_parent;
                // line 196
                echo "                ";
            }
            // line 197
            echo "            </div>
        </div>
    ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['parent'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        
        $__internal_db49d8f31e248e0dcb027c2560139eeabcb83708441a8645417a7a87c5857a98->leave($__internal_db49d8f31e248e0dcb027c2560139eeabcb83708441a8645417a7a87c5857a98_prof);

    }

    public function getTemplateName()
    {
        return "MBHBaseBundle:Form:fields.html.twig";
    }

    public function getDebugInfo()
    {
        return array (  763 => 197,  760 => 196,  747 => 192,  738 => 190,  734 => 188,  728 => 187,  726 => 186,  719 => 182,  710 => 178,  701 => 176,  694 => 171,  692 => 170,  689 => 169,  685 => 168,  682 => 167,  679 => 166,  673 => 165,  670 => 164,  665 => 163,  663 => 162,  660 => 161,  658 => 160,  655 => 159,  649 => 158,  641 => 155,  637 => 153,  628 => 151,  624 => 150,  621 => 149,  618 => 148,  615 => 147,  609 => 146,  596 => 142,  593 => 141,  590 => 140,  584 => 139,  572 => 134,  569 => 133,  566 => 132,  560 => 130,  558 => 129,  553 => 128,  550 => 127,  547 => 126,  532 => 124,  530 => 123,  522 => 122,  519 => 121,  516 => 120,  510 => 119,  501 => 115,  495 => 113,  493 => 112,  480 => 111,  475 => 110,  472 => 109,  469 => 108,  466 => 107,  463 => 106,  461 => 105,  458 => 104,  452 => 103,  444 => 100,  423 => 98,  420 => 97,  417 => 96,  414 => 95,  411 => 94,  408 => 93,  405 => 92,  402 => 91,  399 => 90,  396 => 89,  393 => 88,  390 => 87,  387 => 86,  384 => 85,  378 => 84,  367 => 79,  365 => 78,  363 => 77,  361 => 76,  357 => 75,  354 => 73,  352 => 72,  346 => 71,  336 => 66,  330 => 64,  328 => 63,  324 => 61,  318 => 59,  316 => 58,  312 => 57,  306 => 54,  299 => 53,  296 => 52,  290 => 51,  280 => 47,  277 => 46,  261 => 43,  252 => 41,  248 => 40,  238 => 35,  231 => 31,  225 => 27,  208 => 26,  205 => 25,  202 => 24,  199 => 23,  196 => 22,  194 => 21,  191 => 20,  188 => 19,  183 => 18,  180 => 17,  178 => 16,  173 => 15,  167 => 14,  158 => 10,  141 => 7,  129 => 6,  109 => 5,  92 => 4,  87 => 3,  84 => 2,  78 => 1,  71 => 158,  68 => 157,  66 => 146,  63 => 145,  61 => 139,  58 => 138,  56 => 119,  53 => 118,  51 => 103,  48 => 102,  46 => 84,  44 => 71,  42 => 51,  39 => 50,  37 => 14,  34 => 13,  32 => 1,);
    }
}
/* {% block choice_widget_expanded %}*/
/*     {% spaceless %}*/
/*         <div {{ block('widget_container_attributes') }} class="btn-group" data-toggle="buttons">*/
/*             {% for child in form %}*/
/*                 <label {% for k, a in child.vars.attr %} {{ k }}="{{ a }}" {% endfor %} class="btn btn-sm btn-default {% if child.vars.checked %} active{% endif %}">*/
/*                     <input type="radio" {{ block('widget_attributes') }}{% if child.vars.value is defined %} value="{{ child.vars.value }}"{% endif %}{% if child.vars.checked %} checked="checked"{% endif %} />*/
/*                     {{ child.vars.label|trans }}*/
/*                 </label>*/
/*             {% endfor %}*/
/*         </div>*/
/*     {% endspaceless %}*/
/* {% endblock choice_widget_expanded %}*/
/* */
/* {% block form_widget_compound %}*/
/*     <div {{ block('widget_container_attributes') }}>*/
/*         {% if form.parent is empty %}*/
/*             {% if errors|length > 0 %}*/
/*                 <div class="alert alert-danger global-errors">{{ form_errors(form) }}</div>{% endif %}*/
/*         {% endif %}*/
/* */
/*         {% set groups = {'form.main.group': form.children|keys} %}*/
/*         {% if form.vars.groups is defined %}*/
/*             {% set groups = form.vars.groups %}*/
/*         {% endif %}*/
/* */
/*         {% for group, items in groups %}*/
/* */
/*             <div class="box box-default box-solid">*/
/* */
/*                 <div class="box-header with-border">*/
/*                     <h3 class="box-title">{{ group|trans }}</h3>*/
/* */
/*                     <div class="box-tools pull-right">*/
/*                         <button class="btn btn-box-tool form-group-collapse" data-widget="collapse"*/
/*                                 id="{{ id }}-group-{{ loop.index }}"><i class="fa fa-minus"></i></button>*/
/*                     </div>*/
/*                 </div>*/
/* */
/*                 <div class="box-body">*/
/*                     {% for item in items %}*/
/*                         {{ form_row(form[item]) }}*/
/*                     {% endfor %}*/
/*                 </div>*/
/*             </div>*/
/*         {% endfor %}*/
/* */
/*         {{ form_rest(form) }}*/
/*     </div>*/
/* {% endblock form_widget_compound %}*/
/* */
/* {% block form_row %}*/
/*     {% spaceless %}*/
/*         <div class="form-group {% if errors|length > 0 %} has-error{% endif %}">*/
/*             {{ form_label(form) }}*/
/* */
/*             <div class="col-sm-6">*/
/*                 {{ form_widget(form) }}*/
/*                 {% if help is not null %}*/
/*                     <span class="help-block"><small>{{ help|trans|raw }}</small></span>*/
/*                 {% endif %}*/
/*             </div>*/
/*             <div class="col-sm-4">*/
/*                 {% if errors|length > 0 %}*/
/*                     <span class="text-danger text-left input-errors">{{ form_errors(form) }}</span>*/
/*                 {% endif %}*/
/*             </div>*/
/*         </div>*/
/*     {% endspaceless %}*/
/* {% endblock form_row %}*/
/* */
/* {%- block datetime_widget -%}*/
/*     {% if widget == 'single_text' %}*/
/*         {{- block('form_widget_simple') -}}*/
/*     {%- else -%}*/
/*         <div {{ block('widget_container_attributes') }}>*/
/*             {{- form_errors(form.date) -}}*/
/*             {{- form_errors(form.time) -}}*/
/*             {{- form_widget(form.date) -}}*/
/*             <div class="bootstrap-timepicker">{{- form_widget(form.time) -}}</div>*/
/*         </div>*/
/*     {%- endif -%}*/
/* {%- endblock datetime_widget -%}*/
/* */
/* {% block form_label %}*/
/*     {% spaceless %}*/
/*         {% if label is not sameas(false) %}*/
/*             {% if not compound %}*/
/*                 {% set label_attr = label_attr|merge({'for': id}) %}*/
/*             {% endif %}*/
/*             {% if required %}*/
/*                 {% set label_attr = label_attr|merge({'class': (label_attr.class|default('') ~ ' required')|trim}) %}*/
/*             {% endif %}*/
/*             {% if label is empty %}*/
/*                 {% set label = name|humanize %}*/
/*             {% endif %}*/
/*             {% set label_attr = label_attr|merge({'class': (label_attr.class|default('') ~ ' control-label col-sm-2')|trim}) %}*/
/* */
/*             <label {% for attrname, attrvalue in label_attr %} {{ attrname }}="{{ attrvalue }}"{% endfor %}>{{ label|trans({}, translation_domain)|raw }}{% if required %}&nbsp;<span class="required-star text-danger">*</span>{% endif %}</label>*/
/*         {% endif %}*/
/*     {% endspaceless %}*/
/* {% endblock form_label %}*/
/* */
/* {% block form_widget_simple %}*/
/* */
/*     {% spaceless %}*/
/*         {% set type = type|default('text') %}*/
/*         {% if type != 'file' %}*/
/*             {% set attr = attr|merge({'class': (attr.class|default('') ~ ' form-control input-sm')|trim}) %}*/
/*         {% endif %}*/
/*         {% if addon %}<div class="input-group">{% endif %}*/
/*         <input type="{{ type }}" {{ block('widget_attributes') }} {% if value is not empty %}value="{{ value }}" {% endif %}/>*/
/*         {% if addon %}*/
/*             <span class="input-group-addon"><i class="{{ addon }}"></i></span>*/
/*             </div>{% endif %}*/
/* */
/*     {% endspaceless %}*/
/* {% endblock form_widget_simple %}*/
/* */
/* {% block choice_widget_collapsed %}*/
/*     {% spaceless %}*/
/*         {% set attr = attr|merge({'class': (attr.class|default('') ~ ' form-control input-sm')|trim}) %}*/
/*         <select {{ block('widget_attributes') }}{% if multiple %} multiple {% endif %}>*/
/*             {% if empty_value is not none %}*/
/*                 <option {% if required %} disabled="disabled"{% if value is empty %} selected="selected"{% endif %}{% else %} value=""{% endif %}>{{ empty_value|trans({}, translation_domain) }}</option>*/
/*             {% endif %}*/
/*             {% if preferred_choices|length > 0 %}*/
/*                 {% set options = preferred_choices %}*/
/*                 {{ block('choice_widget_options') }}*/
/*                 {% if choices|length > 0 and separator is not none %}*/
/*                     <option disabled="disabled">{{ separator }}</option>*/
/*                 {% endif %}*/
/*             {% endif %}*/
/*             {% set options = choices %}*/
/*             {{ block('choice_widget_options') }}*/
/*         </select>*/
/*     {% endspaceless %}*/
/* {% endblock choice_widget_collapsed %}*/
/* */
/* {% block textarea_widget %}*/
/*     {% spaceless %}*/
/*         {% set attr = attr|merge({'class': (attr.class|default('') ~ ' form-control input-sm')|trim}) %}*/
/*         <textarea {{ block('widget_attributes') }}>{{ value }}</textarea>*/
/*     {% endspaceless %}*/
/* {% endblock textarea_widget %}*/
/* */
/* {% block form_errors %}*/
/*     {% spaceless %}*/
/*         {% if errors|length > 0 %}*/
/*             <ul>*/
/*                 {% for error in errors %}*/
/*                     <li>{{ error.message|raw }}</li>*/
/*                 {% endfor %}*/
/*             </ul>*/
/*         {% endif %}*/
/*     {% endspaceless %}*/
/* {% endblock form_errors %}*/
/* */
/* {% block roles_widget %}*/
/* */
/*     {% set exists = {} %}*/
/* */
/*     {% if form.parent.vars.value.groups is defined %}*/
/*         {% for group in form.parent.vars.value.groups %}*/
/*             {% set exists = exists|merge(group.roles) %}*/
/*         {% endfor %}*/
/*     {% endif %}*/
/* */
/*     {% for parent in form.vars.choices %}*/
/* */
/*         {% set label = parent.label|replace({'__GROUP': ''}) %}*/
/* */
/*         <div class="box roles-widget collapsed-box">*/
/*             <div class="box-header" style="background-color: #efefef;">*/
/*                 <h3 class="box-title">*/
/*                     <label style="cursor: pointer;">*/
/*                         <input type="checkbox" {{ label in form.vars.data ? 'checked="checked"'}} class="flat-green plain-html" name="{{ form.vars.full_name }}" value="{{ label }}">*/
/*                         &nbsp;*/
/*                         <span class="{{ label in exists or label in form.vars.data ? 'text-info'}}">{{ label|trans({}, form.vars.translation_domain) }}</span>*/
/*                     </label>*/
/*                 </h3>*/
/*                 <div class="box-tools pull-right">*/
/*                     <button class="btn btn-box-tool form-group-expandable show-checkboxes" id="{{ label }}"  data-widget="collapse"><i class="fa fa-plus"></i></button>*/
/*                 </div>*/
/*             </div>*/
/*             <div class="box-body">*/
/*                 {% if parent.choices is defined%}*/
/*                     {% for child in parent.choices  if label != child.label %}*/
/*                         <div class="form-group" style="padding-left: 50px;">*/
/*                             <label style="cursor: pointer;">*/
/*                                 <input type="checkbox"  {{ child.label in form.vars.data ? 'checked="checked"'}} class="flat-green plain-html" name="{{ form.vars.full_name }}" value="{{ child.label }}">*/
/*                                 &nbsp;*/
/*                                 <span class="{{ child.label in exists or child.label in form.vars.data ? 'text-info'}}">{{ child.label|trans({}, form.vars.translation_domain) }}</span>*/
/*                             </label>*/
/*                         </div>*/
/*                     {% endfor %}*/
/*                 {% endif %}*/
/*             </div>*/
/*         </div>*/
/*     {% endfor %}*/
/* {% endblock %}*/
/* */

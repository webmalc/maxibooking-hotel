<?php

/* MBHBaseBundle:Menu:menu.html.twig */
class __TwigTemplate_1f19588e3d3d38942ea1f8386c2414edbfade3f5299e6ed4653cff4a3ebdeda5 extends MBH\Bundle\BaseBundle\Twig\Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        // line 1
        $this->parent = $this->loadTemplate("knp_menu.html.twig", "MBHBaseBundle:Menu:menu.html.twig", 1);
        $this->blocks = array(
            'item' => array($this, 'block_item'),
            'dividerElement' => array($this, 'block_dividerElement'),
            'dropdownHeader' => array($this, 'block_dropdownHeader'),
            'dropdownElement' => array($this, 'block_dropdownElement'),
            'linkElement' => array($this, 'block_linkElement'),
            'spanElement' => array($this, 'block_spanElement'),
            'label' => array($this, 'block_label'),
        );
    }

    protected function doGetParent(array $context)
    {
        return "knp_menu.html.twig";
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        $__internal_82d81be76da6d56014c2da9c283895880d99c99ebac04ee5102175ee80bc9579 = $this->env->getExtension("native_profiler");
        $__internal_82d81be76da6d56014c2da9c283895880d99c99ebac04ee5102175ee80bc9579->enter($__internal_82d81be76da6d56014c2da9c283895880d99c99ebac04ee5102175ee80bc9579_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "MBHBaseBundle:Menu:menu.html.twig"));

        $this->parent->display($context, array_merge($this->blocks, $blocks));
        
        $__internal_82d81be76da6d56014c2da9c283895880d99c99ebac04ee5102175ee80bc9579->leave($__internal_82d81be76da6d56014c2da9c283895880d99c99ebac04ee5102175ee80bc9579_prof);

    }

    // line 3
    public function block_item($context, array $blocks = array())
    {
        $__internal_c61d4c14952677bebdf7c9d7f4fca40486d0a898304aa015729a84ec70802415 = $this->env->getExtension("native_profiler");
        $__internal_c61d4c14952677bebdf7c9d7f4fca40486d0a898304aa015729a84ec70802415->enter($__internal_c61d4c14952677bebdf7c9d7f4fca40486d0a898304aa015729a84ec70802415_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "item"));

        // line 4
        echo "    ";
        $context["macros"] = $this->loadTemplate("knp_menu.html.twig", "MBHBaseBundle:Menu:menu.html.twig", 4);
        // line 6
        $context["attributes"] = $this->getAttribute((isset($context["item"]) ? $context["item"] : $this->getContext($context, "item")), "attributes", array());
        // line 7
        $context["header"] = (($this->getAttribute($this->getAttribute((isset($context["item"]) ? $context["item"] : null), "attributes", array(), "any", false, true), "header", array(), "any", true, true)) ? (_twig_default_filter($this->getAttribute($this->getAttribute((isset($context["item"]) ? $context["item"] : null), "attributes", array(), "any", false, true), "header", array()), false)) : (false));
        // line 8
        echo "
    ";
        // line 9
        if ((isset($context["header"]) ? $context["header"] : $this->getContext($context, "header"))) {
            // line 11
            $context["header_icon"] = (($this->getAttribute((isset($context["attributes"]) ? $context["attributes"] : null), "header_icon", array(), "any", true, true)) ? (_twig_default_filter($this->getAttribute((isset($context["attributes"]) ? $context["attributes"] : null), "header_icon", array()), false)) : (false));
            // line 12
            echo "        <li class=\"header\">
            ";
            // line 13
            if ((isset($context["header_icon"]) ? $context["header_icon"] : $this->getContext($context, "header_icon"))) {
                echo "<i class=\"";
                echo twig_escape_filter($this->env, (isset($context["header_icon"]) ? $context["header_icon"] : $this->getContext($context, "header_icon")), "html", null, true);
                echo "\"></i>&nbsp;";
            }
            echo twig_escape_filter($this->env, $this->env->getExtension('translator')->trans((isset($context["header"]) ? $context["header"] : $this->getContext($context, "header"))), "html", null, true);
            echo "
        </li>
    ";
        } elseif (($this->getAttribute(        // line 15
(isset($context["item"]) ? $context["item"] : $this->getContext($context, "item")), "displayed", array()) && twig_test_empty((isset($context["header"]) ? $context["header"] : $this->getContext($context, "header"))))) {
            // line 17
            $context["is_dropdown"] = (($this->getAttribute((isset($context["attributes"]) ? $context["attributes"] : null), "dropdown", array(), "any", true, true)) ? (_twig_default_filter($this->getAttribute((isset($context["attributes"]) ? $context["attributes"] : null), "dropdown", array()), false)) : (false));
            // line 18
            $context["is_dropdown_header"] = (($this->getAttribute((isset($context["attributes"]) ? $context["attributes"] : null), "dropdown_header", array(), "any", true, true)) ? (_twig_default_filter($this->getAttribute((isset($context["attributes"]) ? $context["attributes"] : null), "dropdown_header", array()), false)) : (false));
            // line 19
            $context["divider_prepend"] = (($this->getAttribute((isset($context["attributes"]) ? $context["attributes"] : null), "divider_prepend", array(), "any", true, true)) ? (_twig_default_filter($this->getAttribute((isset($context["attributes"]) ? $context["attributes"] : null), "divider_prepend", array()), false)) : (false));
            // line 20
            $context["divider_append"] = (($this->getAttribute((isset($context["attributes"]) ? $context["attributes"] : null), "divider_append", array(), "any", true, true)) ? (_twig_default_filter($this->getAttribute((isset($context["attributes"]) ? $context["attributes"] : null), "divider_append", array()), false)) : (false));
            // line 21
            echo "
        ";
            // line 23
            $context["attributes"] = twig_array_merge((isset($context["attributes"]) ? $context["attributes"] : $this->getContext($context, "attributes")), array("dropdown" => null, "dropdown_header" => null, "divider_prepend" => null, "divider_append" => null));
            // line 25
            if ((isset($context["divider_prepend"]) ? $context["divider_prepend"] : $this->getContext($context, "divider_prepend"))) {
                // line 26
                echo "            ";
                $this->displayBlock("dividerElement", $context, $blocks);
            }
            // line 28
            echo "
        ";
            // line 30
            $context["classes"] = (( !twig_test_empty($this->getAttribute((isset($context["item"]) ? $context["item"] : $this->getContext($context, "item")), "attribute", array(0 => "class"), "method"))) ? (array(0 => $this->getAttribute((isset($context["item"]) ? $context["item"] : $this->getContext($context, "item")), "attribute", array(0 => "class"), "method"))) : (array()));
            // line 31
            if ($this->getAttribute((isset($context["item"]) ? $context["item"] : $this->getContext($context, "item")), "current", array())) {
                // line 32
                $context["classes"] = twig_array_merge((isset($context["classes"]) ? $context["classes"] : $this->getContext($context, "classes")), array(0 => $this->getAttribute((isset($context["options"]) ? $context["options"] : $this->getContext($context, "options")), "currentClass", array())));
            } elseif ($this->getAttribute(            // line 33
(isset($context["matcher"]) ? $context["matcher"] : $this->getContext($context, "matcher")), "isAncestor", array(0 => (isset($context["item"]) ? $context["item"] : $this->getContext($context, "item")), 1 => $this->getAttribute((isset($context["options"]) ? $context["options"] : $this->getContext($context, "options")), "depth", array())), "method")) {
                // line 34
                $context["classes"] = twig_array_merge((isset($context["classes"]) ? $context["classes"] : $this->getContext($context, "classes")), array(0 => $this->getAttribute((isset($context["options"]) ? $context["options"] : $this->getContext($context, "options")), "ancestorClass", array())));
            }
            // line 36
            if ($this->getAttribute((isset($context["item"]) ? $context["item"] : $this->getContext($context, "item")), "actsLikeFirst", array())) {
                // line 37
                $context["classes"] = twig_array_merge((isset($context["classes"]) ? $context["classes"] : $this->getContext($context, "classes")), array(0 => $this->getAttribute((isset($context["options"]) ? $context["options"] : $this->getContext($context, "options")), "firstClass", array())));
            }
            // line 39
            if ($this->getAttribute((isset($context["item"]) ? $context["item"] : $this->getContext($context, "item")), "actsLikeLast", array())) {
                // line 40
                $context["classes"] = twig_array_merge((isset($context["classes"]) ? $context["classes"] : $this->getContext($context, "classes")), array(0 => $this->getAttribute((isset($context["options"]) ? $context["options"] : $this->getContext($context, "options")), "lastClass", array())));
            }
            // line 42
            echo "
        ";
            // line 44
            $context["childrenClasses"] = (( !twig_test_empty($this->getAttribute((isset($context["item"]) ? $context["item"] : $this->getContext($context, "item")), "childrenAttribute", array(0 => "class"), "method"))) ? (array(0 => $this->getAttribute((isset($context["item"]) ? $context["item"] : $this->getContext($context, "item")), "childrenAttribute", array(0 => "class"), "method"))) : (array()));
            // line 45
            $context["childrenClasses"] = twig_array_merge((isset($context["childrenClasses"]) ? $context["childrenClasses"] : $this->getContext($context, "childrenClasses")), array(0 => ("menu_level_" . $this->getAttribute((isset($context["item"]) ? $context["item"] : $this->getContext($context, "item")), "level", array()))));
            // line 46
            echo "
        ";
            // line 48
            if ((isset($context["is_dropdown"]) ? $context["is_dropdown"] : $this->getContext($context, "is_dropdown"))) {
                // line 49
                $context["classes"] = twig_array_merge((isset($context["classes"]) ? $context["classes"] : $this->getContext($context, "classes")), array(0 => "dropdown"));
                // line 50
                $context["childrenClasses"] = twig_array_merge((isset($context["childrenClasses"]) ? $context["childrenClasses"] : $this->getContext($context, "childrenClasses")), array(0 => "treeview-menu"));
            }
            // line 52
            echo "
        ";
            // line 54
            if ( !twig_test_empty((isset($context["classes"]) ? $context["classes"] : $this->getContext($context, "classes")))) {
                // line 55
                $context["attributes"] = twig_array_merge((isset($context["attributes"]) ? $context["attributes"] : $this->getContext($context, "attributes")), array("class" => twig_join_filter((isset($context["classes"]) ? $context["classes"] : $this->getContext($context, "classes")), " ")));
            }
            // line 57
            $context["listAttributes"] = twig_array_merge($this->getAttribute((isset($context["item"]) ? $context["item"] : $this->getContext($context, "item")), "childrenAttributes", array()), array("class" => twig_join_filter((isset($context["childrenClasses"]) ? $context["childrenClasses"] : $this->getContext($context, "childrenClasses")), " ")));
            // line 60
            if ((isset($context["is_dropdown_header"]) ? $context["is_dropdown_header"] : $this->getContext($context, "is_dropdown_header"))) {
                // line 61
                echo "            ";
                $this->displayBlock("dropdownHeader", $context, $blocks);
            } else {
                // line 63
                echo "            ";
                // line 64
                echo "            <li";
                echo $context["macros"]->getattributes((isset($context["attributes"]) ? $context["attributes"] : $this->getContext($context, "attributes")));
                echo ">";
                // line 65
                if ((isset($context["is_dropdown"]) ? $context["is_dropdown"] : $this->getContext($context, "is_dropdown"))) {
                    // line 66
                    echo "                    ";
                    if ($this->getAttribute((isset($context["item"]) ? $context["item"] : $this->getContext($context, "item")), "hasChildren", array())) {
                        echo " ";
                        $this->displayBlock("dropdownElement", $context, $blocks);
                    }
                } elseif (( !twig_test_empty($this->getAttribute(                // line 67
(isset($context["item"]) ? $context["item"] : $this->getContext($context, "item")), "uri", array())) && ( !$this->getAttribute((isset($context["item"]) ? $context["item"] : $this->getContext($context, "item")), "current", array()) || $this->getAttribute((isset($context["options"]) ? $context["options"] : $this->getContext($context, "options")), "currentAsLink", array())))) {
                    // line 68
                    echo "                    ";
                    $this->displayBlock("linkElement", $context, $blocks);
                } else {
                    // line 70
                    echo "                    ";
                    $this->displayBlock("linkElement", $context, $blocks);
                }
                // line 72
                echo "                ";
                // line 73
                echo "                ";
                $this->displayBlock("list", $context, $blocks);
                echo "
            </li>";
            }
            // line 77
            if ((isset($context["divider_append"]) ? $context["divider_append"] : $this->getContext($context, "divider_append"))) {
                // line 78
                echo "            ";
                $this->displayBlock("dividerElement", $context, $blocks);
            }
            // line 80
            echo "    ";
        }
        
        $__internal_c61d4c14952677bebdf7c9d7f4fca40486d0a898304aa015729a84ec70802415->leave($__internal_c61d4c14952677bebdf7c9d7f4fca40486d0a898304aa015729a84ec70802415_prof);

    }

    // line 83
    public function block_dividerElement($context, array $blocks = array())
    {
        $__internal_0b3c2a2427dce952d20162ce4fa8e123551cea9c5bb3a4114372e0e5fa978022 = $this->env->getExtension("native_profiler");
        $__internal_0b3c2a2427dce952d20162ce4fa8e123551cea9c5bb3a4114372e0e5fa978022->enter($__internal_0b3c2a2427dce952d20162ce4fa8e123551cea9c5bb3a4114372e0e5fa978022_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "dividerElement"));

        // line 84
        echo "    ";
        if (($this->getAttribute((isset($context["item"]) ? $context["item"] : $this->getContext($context, "item")), "level", array()) == 1)) {
            // line 85
            echo "        <li class=\"divider-vertical\"></li>
    ";
        } else {
            // line 87
            echo "        <li class=\"divider\"></li>
    ";
        }
        
        $__internal_0b3c2a2427dce952d20162ce4fa8e123551cea9c5bb3a4114372e0e5fa978022->leave($__internal_0b3c2a2427dce952d20162ce4fa8e123551cea9c5bb3a4114372e0e5fa978022_prof);

    }

    // line 91
    public function block_dropdownHeader($context, array $blocks = array())
    {
        $__internal_d761e4d9f5157f6b47372bef55d9bfaed0dae5ae427fc3e1add4fc15b1dc6fa8 = $this->env->getExtension("native_profiler");
        $__internal_d761e4d9f5157f6b47372bef55d9bfaed0dae5ae427fc3e1add4fc15b1dc6fa8->enter($__internal_d761e4d9f5157f6b47372bef55d9bfaed0dae5ae427fc3e1add4fc15b1dc6fa8_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "dropdownHeader"));

        // line 92
        echo "    <li role=\"presentation\" class=\"dropdown-header\">";
        echo twig_escape_filter($this->env, $this->getAttribute((isset($context["item"]) ? $context["item"] : $this->getContext($context, "item")), "label", array()), "html", null, true);
        echo "</li>
";
        
        $__internal_d761e4d9f5157f6b47372bef55d9bfaed0dae5ae427fc3e1add4fc15b1dc6fa8->leave($__internal_d761e4d9f5157f6b47372bef55d9bfaed0dae5ae427fc3e1add4fc15b1dc6fa8_prof);

    }

    // line 95
    public function block_dropdownElement($context, array $blocks = array())
    {
        $__internal_e660b038e47b81769941ac492af4f19374e57a3f906bc478a0000999d5a0b0ad = $this->env->getExtension("native_profiler");
        $__internal_e660b038e47b81769941ac492af4f19374e57a3f906bc478a0000999d5a0b0ad->enter($__internal_e660b038e47b81769941ac492af4f19374e57a3f906bc478a0000999d5a0b0ad_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "dropdownElement"));

        // line 96
        $context["classes"] = (( !twig_test_empty($this->getAttribute((isset($context["item"]) ? $context["item"] : $this->getContext($context, "item")), "linkAttribute", array(0 => "class"), "method"))) ? (array(0 => $this->getAttribute((isset($context["item"]) ? $context["item"] : $this->getContext($context, "item")), "linkAttribute", array(0 => "class"), "method"))) : (array()));
        // line 97
        $context["attributes"] = $this->getAttribute((isset($context["item"]) ? $context["item"] : $this->getContext($context, "item")), "linkAttributes", array());
        // line 98
        $context["attributes"] = twig_array_merge((isset($context["attributes"]) ? $context["attributes"] : $this->getContext($context, "attributes")), array("class" => twig_join_filter((isset($context["classes"]) ? $context["classes"] : $this->getContext($context, "classes")), " ")));
        // line 99
        echo "
    ";
        // line 100
        ob_start();
        // line 101
        echo "        <a href=\"#\"";
        echo $this->getAttribute((isset($context["macros"]) ? $context["macros"] : $this->getContext($context, "macros")), "attributes", array(0 => (isset($context["attributes"]) ? $context["attributes"] : $this->getContext($context, "attributes"))), "method");
        echo ">
            ";
        // line 102
        if ( !twig_test_empty($this->getAttribute((isset($context["item"]) ? $context["item"] : $this->getContext($context, "item")), "attribute", array(0 => "icon"), "method"))) {
            // line 103
            echo "                <i class=\"";
            echo twig_escape_filter($this->env, $this->getAttribute((isset($context["item"]) ? $context["item"] : $this->getContext($context, "item")), "attribute", array(0 => "icon"), "method"), "html", null, true);
            echo "\"></i>
            ";
        }
        // line 105
        echo "            ";
        $this->displayBlock("label", $context, $blocks);
        echo " <i class=\"fa fa-angle-left pull-right\"></i>
        </a>
    ";
        echo trim(preg_replace('/>\s+</', '><', ob_get_clean()));
        
        $__internal_e660b038e47b81769941ac492af4f19374e57a3f906bc478a0000999d5a0b0ad->leave($__internal_e660b038e47b81769941ac492af4f19374e57a3f906bc478a0000999d5a0b0ad_prof);

    }

    // line 110
    public function block_linkElement($context, array $blocks = array())
    {
        $__internal_c39961f3c65688136844d64952dfb8c25601aa2bbbe20a262e1b3206ed5b2c18 = $this->env->getExtension("native_profiler");
        $__internal_c39961f3c65688136844d64952dfb8c25601aa2bbbe20a262e1b3206ed5b2c18->enter($__internal_c39961f3c65688136844d64952dfb8c25601aa2bbbe20a262e1b3206ed5b2c18_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "linkElement"));

        // line 111
        echo "    <a href=\"";
        echo twig_escape_filter($this->env, $this->getAttribute((isset($context["item"]) ? $context["item"] : $this->getContext($context, "item")), "uri", array()), "html", null, true);
        echo "\"";
        echo $this->getAttribute((isset($context["macros"]) ? $context["macros"] : $this->getContext($context, "macros")), "attributes", array(0 => $this->getAttribute((isset($context["item"]) ? $context["item"] : $this->getContext($context, "item")), "linkAttributes", array())), "method");
        echo ">
        ";
        // line 112
        if ( !twig_test_empty($this->getAttribute((isset($context["item"]) ? $context["item"] : $this->getContext($context, "item")), "attribute", array(0 => "icon"), "method"))) {
            // line 113
            echo "            <i class=\"";
            echo twig_escape_filter($this->env, $this->getAttribute((isset($context["item"]) ? $context["item"] : $this->getContext($context, "item")), "attribute", array(0 => "icon"), "method"), "html", null, true);
            echo "\"></i>
        ";
        }
        // line 115
        echo "        ";
        $this->displayBlock("label", $context, $blocks);
        echo "
    </a>
";
        
        $__internal_c39961f3c65688136844d64952dfb8c25601aa2bbbe20a262e1b3206ed5b2c18->leave($__internal_c39961f3c65688136844d64952dfb8c25601aa2bbbe20a262e1b3206ed5b2c18_prof);

    }

    // line 119
    public function block_spanElement($context, array $blocks = array())
    {
        $__internal_b0550d626977e75b30eb47e6e5dc0ebeb184e1eb9a836463bc426e74d1407319 = $this->env->getExtension("native_profiler");
        $__internal_b0550d626977e75b30eb47e6e5dc0ebeb184e1eb9a836463bc426e74d1407319->enter($__internal_b0550d626977e75b30eb47e6e5dc0ebeb184e1eb9a836463bc426e74d1407319_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "spanElement"));

        // line 120
        echo "    <span>";
        echo $this->getAttribute((isset($context["macros"]) ? $context["macros"] : $this->getContext($context, "macros")), "attributes", array(0 => $this->getAttribute((isset($context["item"]) ? $context["item"] : $this->getContext($context, "item")), "labelAttributes", array())), "method");
        echo "
        ";
        // line 121
        if ( !twig_test_empty($this->getAttribute((isset($context["item"]) ? $context["item"] : $this->getContext($context, "item")), "attribute", array(0 => "icon"), "method"))) {
            // line 122
            echo "            <i class=\"";
            echo twig_escape_filter($this->env, $this->getAttribute((isset($context["item"]) ? $context["item"] : $this->getContext($context, "item")), "attribute", array(0 => "icon"), "method"), "html", null, true);
            echo "\"></i>
        ";
        }
        // line 124
        echo "        ";
        $this->displayBlock("label", $context, $blocks);
        echo "
    </span>
";
        
        $__internal_b0550d626977e75b30eb47e6e5dc0ebeb184e1eb9a836463bc426e74d1407319->leave($__internal_b0550d626977e75b30eb47e6e5dc0ebeb184e1eb9a836463bc426e74d1407319_prof);

    }

    // line 128
    public function block_label($context, array $blocks = array())
    {
        $__internal_d3d165af08ef0d8eada77b27c5bb14516c5e06f798ea9281040bea7adf63a5f4 = $this->env->getExtension("native_profiler");
        $__internal_d3d165af08ef0d8eada77b27c5bb14516c5e06f798ea9281040bea7adf63a5f4->enter($__internal_d3d165af08ef0d8eada77b27c5bb14516c5e06f798ea9281040bea7adf63a5f4_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "label"));

        echo "<span>";
        if (($this->getAttribute((isset($context["item"]) ? $context["item"] : $this->getContext($context, "item")), "label", array()) == "&nbsp;")) {
            echo "&nbsp;";
        } else {
            echo twig_escape_filter($this->env, $this->env->getExtension('translator')->trans($this->getAttribute((isset($context["item"]) ? $context["item"] : $this->getContext($context, "item")), "label", array())), "html", null, true);
            echo "</span>
    ";
            // line 129
            if ($this->getAttribute((isset($context["item"]) ? $context["item"] : null), "attribute", array(0 => "badge_left"), "method", true, true)) {
                // line 130
                echo "        <small ";
                if ($this->getAttribute((isset($context["item"]) ? $context["item"] : null), "attribute", array(0 => "badge_title_left"), "method", true, true)) {
                    echo "data-toggle=\"tooltip\" data-placement=\"top\" title=\"";
                    echo twig_escape_filter($this->env, $this->getAttribute((isset($context["item"]) ? $context["item"] : $this->getContext($context, "item")), "attribute", array(0 => "badge_title_left"), "method"), "html", null, true);
                    echo "\"";
                }
                echo "  ";
                if ($this->getAttribute((isset($context["item"]) ? $context["item"] : null), "attribute", array(0 => "badge_id_left"), "method", true, true)) {
                    echo "id=\"";
                    echo twig_escape_filter($this->env, $this->getAttribute((isset($context["item"]) ? $context["item"] : $this->getContext($context, "item")), "attribute", array(0 => "badge_id_left"), "method"), "html", null, true);
                    echo "\"";
                }
                // line 131
                echo "               class=\"label pull-right ";
                if ($this->getAttribute((isset($context["item"]) ? $context["item"] : null), "attribute", array(0 => "badge_class_left"), "method", true, true)) {
                    echo twig_escape_filter($this->env, $this->getAttribute((isset($context["item"]) ? $context["item"] : $this->getContext($context, "item")), "attribute", array(0 => "badge_class_left"), "method"), "html", null, true);
                }
                echo "\">";
                echo twig_escape_filter($this->env, $this->getAttribute((isset($context["item"]) ? $context["item"] : $this->getContext($context, "item")), "attribute", array(0 => "badge_value_left"), "method"), "html", null, true);
                echo "</small>
    ";
            }
            // line 133
            echo "    ";
            if ($this->getAttribute((isset($context["item"]) ? $context["item"] : null), "attribute", array(0 => "badge"), "method", true, true)) {
                // line 134
                echo "        <small ";
                if ($this->getAttribute((isset($context["item"]) ? $context["item"] : null), "attribute", array(0 => "badge_title_right"), "method", true, true)) {
                    echo "data-toggle=\"tooltip\" data-placement=\"top\" title=\"";
                    echo twig_escape_filter($this->env, $this->getAttribute((isset($context["item"]) ? $context["item"] : $this->getContext($context, "item")), "attribute", array(0 => "badge_title_right"), "method"), "html", null, true);
                    echo "\"";
                }
                echo " ";
                if ($this->getAttribute((isset($context["item"]) ? $context["item"] : null), "attribute", array(0 => "badge_id_right"), "method", true, true)) {
                    echo "id=\"";
                    echo twig_escape_filter($this->env, $this->getAttribute((isset($context["item"]) ? $context["item"] : $this->getContext($context, "item")), "attribute", array(0 => "badge_id_right"), "method"), "html", null, true);
                    echo "\"";
                }
                // line 135
                echo "               class=\"label pull-right ";
                if ($this->getAttribute((isset($context["item"]) ? $context["item"] : null), "attribute", array(0 => "badge_class_right"), "method", true, true)) {
                    echo twig_escape_filter($this->env, $this->getAttribute((isset($context["item"]) ? $context["item"] : $this->getContext($context, "item")), "attribute", array(0 => "badge_class_right"), "method"), "html", null, true);
                }
                echo "\">";
                echo twig_escape_filter($this->env, $this->getAttribute((isset($context["item"]) ? $context["item"] : $this->getContext($context, "item")), "attribute", array(0 => "badge_value_right"), "method"), "html", null, true);
                echo "</small>
    ";
            }
        }
        
        $__internal_d3d165af08ef0d8eada77b27c5bb14516c5e06f798ea9281040bea7adf63a5f4->leave($__internal_d3d165af08ef0d8eada77b27c5bb14516c5e06f798ea9281040bea7adf63a5f4_prof);

    }

    public function getTemplateName()
    {
        return "MBHBaseBundle:Menu:menu.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  393 => 135,  380 => 134,  377 => 133,  367 => 131,  354 => 130,  352 => 129,  339 => 128,  328 => 124,  322 => 122,  320 => 121,  315 => 120,  309 => 119,  298 => 115,  292 => 113,  290 => 112,  283 => 111,  277 => 110,  265 => 105,  259 => 103,  257 => 102,  252 => 101,  250 => 100,  247 => 99,  245 => 98,  243 => 97,  241 => 96,  235 => 95,  225 => 92,  219 => 91,  210 => 87,  206 => 85,  203 => 84,  197 => 83,  189 => 80,  185 => 78,  183 => 77,  177 => 73,  175 => 72,  171 => 70,  167 => 68,  165 => 67,  159 => 66,  157 => 65,  153 => 64,  151 => 63,  147 => 61,  145 => 60,  143 => 57,  140 => 55,  138 => 54,  135 => 52,  132 => 50,  130 => 49,  128 => 48,  125 => 46,  123 => 45,  121 => 44,  118 => 42,  115 => 40,  113 => 39,  110 => 37,  108 => 36,  105 => 34,  103 => 33,  101 => 32,  99 => 31,  97 => 30,  94 => 28,  90 => 26,  88 => 25,  86 => 23,  83 => 21,  81 => 20,  79 => 19,  77 => 18,  75 => 17,  73 => 15,  63 => 13,  60 => 12,  58 => 11,  56 => 9,  53 => 8,  51 => 7,  49 => 6,  46 => 4,  40 => 3,  11 => 1,);
    }
}
/* {% extends 'knp_menu.html.twig' %}*/
/* */
/* {% block item %}*/
/*     {% import "knp_menu.html.twig" as macros %}*/
/* */
/*     {%- set attributes = item.attributes %}*/
/*     {%- set header = item.attributes.header|default(false) %}*/
/* */
/*     {% if header %}*/
/* */
/*         {%- set header_icon = attributes.header_icon|default(false) %}*/
/*         <li class="header">*/
/*             {% if header_icon %}<i class="{{ header_icon }}"></i>&nbsp;{% endif %}{{ header|trans }}*/
/*         </li>*/
/*     {% elseif item.displayed and header is empty %}*/
/* */
/*         {%- set is_dropdown = attributes.dropdown|default(false) %}*/
/*         {%- set is_dropdown_header = attributes.dropdown_header|default(false) %}*/
/*         {%- set divider_prepend = attributes.divider_prepend|default(false) %}*/
/*         {%- set divider_append = attributes.divider_append|default(false) %}*/
/* */
/*         {# unset bootstrap specific attributes #}*/
/*         {%- set attributes = attributes|merge({'dropdown': null, 'dropdown_header': null, 'divider_prepend': null, 'divider_append': null }) %}*/
/* */
/*         {%- if divider_prepend %}*/
/*             {{ block('dividerElement') }}*/
/*         {%- endif %}*/
/* */
/*         {# building the class of the item #}*/
/*         {%- set classes = item.attribute('class') is not empty ? [item.attribute('class')] : [] %}*/
/*         {%- if item.current %}*/
/*             {%- set classes = classes|merge([options.currentClass]) %}*/
/*         {%- elseif matcher.isAncestor(item, options.depth) %}*/
/*             {%- set classes = classes|merge([options.ancestorClass]) %}*/
/*         {%- endif %}*/
/*         {%- if item.actsLikeFirst %}*/
/*             {%- set classes = classes|merge([options.firstClass]) %}*/
/*         {%- endif %}*/
/*         {%- if item.actsLikeLast %}*/
/*             {%- set classes = classes|merge([options.lastClass]) %}*/
/*         {%- endif %}*/
/* */
/*         {# building the class of the children #}*/
/*         {%- set childrenClasses = item.childrenAttribute('class') is not empty ? [item.childrenAttribute('class')] : [] %}*/
/*         {%- set childrenClasses = childrenClasses|merge(['menu_level_' ~ item.level]) %}*/
/* */
/*         {# adding classes for dropdown #}*/
/*         {%- if is_dropdown %}*/
/*             {%- set classes = classes|merge(['dropdown']) %}*/
/*             {%- set childrenClasses = childrenClasses|merge(['treeview-menu']) %}*/
/*         {%- endif %}*/
/* */
/*         {# putting classes together #}*/
/*         {%- if classes is not empty %}*/
/*             {%- set attributes = attributes|merge({'class': classes|join(' ')}) %}*/
/*         {%- endif %}*/
/*         {%- set listAttributes = item.childrenAttributes|merge({'class': childrenClasses|join(' ') }) %}*/
/* */
/* */
/*         {%- if is_dropdown_header %}*/
/*             {{ block('dropdownHeader') }}*/
/*         {%- else %}*/
/*             {# displaying the item #}*/
/*             <li{{ macros.attributes(attributes) }}>*/
/*                 {%- if is_dropdown %}*/
/*                     {% if item.hasChildren %} {{ block('dropdownElement') }}{% endif %}*/
/*                 {%- elseif item.uri is not empty and (not item.current or options.currentAsLink) %}*/
/*                     {{ block('linkElement') }}*/
/*                 {%- else %}*/
/*                     {{ block('linkElement') }}*/
/*                 {%- endif %}*/
/*                 {# render the list of children#}*/
/*                 {{ block('list') }}*/
/*             </li>*/
/*         {%- endif %}*/
/* */
/*         {%- if divider_append %}*/
/*             {{ block('dividerElement') }}*/
/*         {%- endif %}*/
/*     {% endif %}*/
/* {% endblock %}*/
/*  */
/* {% block dividerElement %}*/
/*     {% if item.level == 1 %}*/
/*         <li class="divider-vertical"></li>*/
/*     {% else %}*/
/*         <li class="divider"></li>*/
/*     {% endif %}*/
/* {% endblock %}*/
/* */
/* {% block dropdownHeader %}*/
/*     <li role="presentation" class="dropdown-header">{{ item.label }}</li>*/
/* {% endblock %}*/
/* */
/* {% block dropdownElement %}*/
/*     {%- set classes = item.linkAttribute('class') is not empty ? [item.linkAttribute('class')] : [] %}*/
/*     {%- set attributes = item.linkAttributes %}*/
/*     {%- set attributes = attributes|merge({'class': classes|join(' ')}) %}*/
/* */
/*     {% spaceless %}*/
/*         <a href="#"{{ macros.attributes(attributes) }}>*/
/*             {% if item.attribute('icon') is not empty %}*/
/*                 <i class="{{ item.attribute('icon') }}"></i>*/
/*             {% endif %}*/
/*             {{ block('label') }} <i class="fa fa-angle-left pull-right"></i>*/
/*         </a>*/
/*     {% endspaceless %}*/
/* {% endblock %}*/
/* */
/* {% block linkElement %}*/
/*     <a href="{{ item.uri }}"{{ macros.attributes(item.linkAttributes) }}>*/
/*         {% if item.attribute('icon') is not empty %}*/
/*             <i class="{{ item.attribute('icon') }}"></i>*/
/*         {% endif %}*/
/*         {{ block('label') }}*/
/*     </a>*/
/* {% endblock %}*/
/*  */
/* {% block spanElement %}*/
/*     <span>{{ macros.attributes(item.labelAttributes) }}*/
/*         {% if item.attribute('icon') is not empty %}*/
/*             <i class="{{ item.attribute('icon') }}"></i>*/
/*         {% endif %}*/
/*         {{ block('label') }}*/
/*     </span>*/
/* {% endblock %}*/
/*  */
/* {% block label %}<span>{% if item.label == '&nbsp;' %}&nbsp;{% else %}{{ item.label|trans }}</span>*/
/*     {% if item.attribute('badge_left') is defined %}*/
/*         <small {% if item.attribute('badge_title_left') is defined %}data-toggle="tooltip" data-placement="top" title="{{ item.attribute('badge_title_left') }}"{% endif %}  {% if item.attribute('badge_id_left') is defined %}id="{{ item.attribute('badge_id_left') }}"{% endif %}*/
/*                class="label pull-right {% if item.attribute('badge_class_left') is defined %}{{ item.attribute('badge_class_left') }}{% endif %}">{{ item.attribute('badge_value_left') }}</small>*/
/*     {% endif %}*/
/*     {% if item.attribute('badge') is defined %}*/
/*         <small {% if item.attribute('badge_title_right') is defined %}data-toggle="tooltip" data-placement="top" title="{{ item.attribute('badge_title_right') }}"{% endif %} {% if item.attribute('badge_id_right') is defined %}id="{{ item.attribute('badge_id_right') }}"{% endif %}*/
/*                class="label pull-right {% if item.attribute('badge_class_right') is defined %}{{ item.attribute('badge_class_right') }}{% endif %}">{{ item.attribute('badge_value_right') }}</small>*/
/*     {% endif %}*/
/* {% endif %}{% endblock %}*/
/* */

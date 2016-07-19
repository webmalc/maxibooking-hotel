<?php

/* @Swiftmailer/Collector/swiftmailer.html.twig */
class __TwigTemplate_cab693934d3b884ab0086906e7b7dfd0365c3e24b4e3bf119f3e1d14f2362e54 extends MBH\Bundle\BaseBundle\Twig\Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        // line 1
        $this->parent = $this->loadTemplate("WebProfilerBundle:Profiler:layout.html.twig", "@Swiftmailer/Collector/swiftmailer.html.twig", 1);
        $this->blocks = array(
            'toolbar' => array($this, 'block_toolbar'),
            'menu' => array($this, 'block_menu'),
            'panel' => array($this, 'block_panel'),
        );
    }

    protected function doGetParent(array $context)
    {
        return "WebProfilerBundle:Profiler:layout.html.twig";
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        $__internal_3778e91b81aef92641f93c4c41889711cdc0b1a9bf976ef0e8fd0977f3761c09 = $this->env->getExtension("native_profiler");
        $__internal_3778e91b81aef92641f93c4c41889711cdc0b1a9bf976ef0e8fd0977f3761c09->enter($__internal_3778e91b81aef92641f93c4c41889711cdc0b1a9bf976ef0e8fd0977f3761c09_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "@Swiftmailer/Collector/swiftmailer.html.twig"));

        $this->parent->display($context, array_merge($this->blocks, $blocks));
        
        $__internal_3778e91b81aef92641f93c4c41889711cdc0b1a9bf976ef0e8fd0977f3761c09->leave($__internal_3778e91b81aef92641f93c4c41889711cdc0b1a9bf976ef0e8fd0977f3761c09_prof);

    }

    // line 3
    public function block_toolbar($context, array $blocks = array())
    {
        $__internal_a7d616f670ce1423ceeec7d091a76d9d466b647652b63295bd4170ba1edca673 = $this->env->getExtension("native_profiler");
        $__internal_a7d616f670ce1423ceeec7d091a76d9d466b647652b63295bd4170ba1edca673->enter($__internal_a7d616f670ce1423ceeec7d091a76d9d466b647652b63295bd4170ba1edca673_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "toolbar"));

        // line 4
        echo "    ";
        if ($this->getAttribute((isset($context["collector"]) ? $context["collector"] : $this->getContext($context, "collector")), "messageCount", array())) {
            // line 5
            echo "        ";
            ob_start();
            // line 6
            echo "            <img width=\"23\" height=\"28\" alt=\"Swiftmailer\" src=\"data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABcAAAAcCAYAAACK7SRjAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyJpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuMC1jMDYwIDYxLjEzNDc3NywgMjAxMC8wMi8xMi0xNzozMjowMCAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENTNSBNYWNpbnRvc2giIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6N0NEOTU1MjM0OThFMTFFMDg3NzJBNTE2ODgwQzMxMzQiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6N0NEOTU1MjQ0OThFMTFFMDg3NzJBNTE2ODgwQzMxMzQiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlpZDoxMEQ5OTQ5QzQ5OEMxMUUwODc3MkE1MTY4ODBDMzEzNCIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDo3Q0Q5NTUyMjQ5OEUxMUUwODc3MkE1MTY4ODBDMzEzNCIvPiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6eG1wbWV0YT4gPD94cGFja2V0IGVuZD0iciI/PpkRnSAAAAJ0SURBVHjaYvz//z8DrQATAw3BqOFYAaO9vT1FBhw4cGCAXA5MipxBQUHT3r17l0AVAxkZ/wkLC89as2ZNIcjlYkALXKnlWqBZTH/+/PEDmQsynLW/v3+NoaHhN2oYDjJn8uTJK4BMNpDhPwsLCwOKiop2+fn5vafEYC8vrw8gc/Lz8wOB3B8gw/nev38vn5WV5eTg4LA/Ly/vESsrK2npmYmJITU19SnQ8L0gc4DxpwgyF2S4EEjB58+f+crLy31YWFjOt7S0XBYUFPxHjMEcHBz/6+rqboqJiZ0qKSnxBpkDlRICGc4MU/j792+2CRMm+L18+fLSxIkTDykoKPzBZ7CoqOi/np6eE8rKylvb29v9fvz4wYEkzYKRzjk5OX/LyMjcnDRpEkjjdisrK6wRraOj8wvokAMLFy788ejRoxcaGhrPCWai4ODgB8DUE3/mzBknYMToASNoMzAfvEVW4+Tk9LmhoWFbTU2NwunTpx2BjiiMjo6+hm4WCzJHUlLyz+vXrxkfP36sDOI/ffpUPikpibe7u3sLsJjQW7VqlSrQxe+Avjmanp7u9PbtWzGQOmCCkARmmu/m5uYfT548yY/V5UpKSl+2b9+uiiz26dMnIWBSDTp27NgdYGrYCIzwE7m5uR4wg2Hg/PnzSsDI/QlKOcjZ3wGUBLm5uf+DwLdv38gub4AG/xcSEvr35s0bZmCB5sgCE/z69SsjyDJKMtG/f/8YQQYD8wkoGf8H51AbG5sH1CpbQBnQ09PzBiiHgoIFFHlBQGwLxLxUMP8dqJgH4k3gIhfIkAKVYkDMTmmhCHIxEL8A4peMo02L4WU4QIABANxZAoDIQDv3AAAAAElFTkSuQmCC\" />
            <span class=\"sf-toolbar-status\">";
            // line 7
            echo twig_escape_filter($this->env, $this->getAttribute((isset($context["collector"]) ? $context["collector"] : $this->getContext($context, "collector")), "messageCount", array()), "html", null, true);
            echo "</span>
        ";
            $context["icon"] = ('' === $tmp = ob_get_clean()) ? '' : new Twig_Markup($tmp, $this->env->getCharset());
            // line 9
            echo "        ";
            ob_start();
            // line 10
            echo "            <div class=\"sf-toolbar-info-piece\">
                <b>Messages</b>
                <span>";
            // line 12
            echo twig_escape_filter($this->env, $this->getAttribute((isset($context["collector"]) ? $context["collector"] : $this->getContext($context, "collector")), "messageCount", array()), "html", null, true);
            echo "</span>
            </div>
            ";
            // line 14
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable($this->getAttribute((isset($context["collector"]) ? $context["collector"] : $this->getContext($context, "collector")), "mailers", array()));
            foreach ($context['_seq'] as $context["_key"] => $context["name"]) {
                // line 15
                echo "                <hr>
                <div class=\"sf-toolbar-info-piece\">
                    <b>";
                // line 17
                echo twig_escape_filter($this->env, $context["name"], "html", null, true);
                echo "</b>
                    <span>";
                // line 18
                echo twig_escape_filter($this->env, $this->getAttribute((isset($context["collector"]) ? $context["collector"] : $this->getContext($context, "collector")), "messageCount", array(0 => $context["name"]), "method"), "html", null, true);
                echo "</span>
                </div>
                <div class=\"sf-toolbar-info-piece\">
                    <b>Is spooled ?</b>
                    <span>";
                // line 22
                echo (($this->getAttribute((isset($context["collector"]) ? $context["collector"] : $this->getContext($context, "collector")), "isSpool", array(0 => $context["name"]), "method")) ? ("yes") : ("no"));
                echo "</span>
                </div>
            ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['name'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 25
            echo "
        ";
            $context["text"] = ('' === $tmp = ob_get_clean()) ? '' : new Twig_Markup($tmp, $this->env->getCharset());
            // line 27
            echo "        ";
            $this->loadTemplate("WebProfilerBundle:Profiler:toolbar_item.html.twig", "@Swiftmailer/Collector/swiftmailer.html.twig", 27)->display(array_merge($context, array("link" => (isset($context["profiler_url"]) ? $context["profiler_url"] : $this->getContext($context, "profiler_url")))));
            // line 28
            echo "    ";
        }
        
        $__internal_a7d616f670ce1423ceeec7d091a76d9d466b647652b63295bd4170ba1edca673->leave($__internal_a7d616f670ce1423ceeec7d091a76d9d466b647652b63295bd4170ba1edca673_prof);

    }

    // line 31
    public function block_menu($context, array $blocks = array())
    {
        $__internal_2f7842209bc41e13efed0ca22bf089edb6a0a980017637dd7533f3facd50187f = $this->env->getExtension("native_profiler");
        $__internal_2f7842209bc41e13efed0ca22bf089edb6a0a980017637dd7533f3facd50187f->enter($__internal_2f7842209bc41e13efed0ca22bf089edb6a0a980017637dd7533f3facd50187f_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "menu"));

        // line 32
        echo "<span class=\"label\">
    <span class=\"icon\"><img src=\"data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACoAAAAeCAYAAABaKIzgAAAEDElEQVR42u2XWUhUURjHy6isILAebO+lonpJ8qkHM3NFVFxAUVFM0RSXUKnwwdAQn3wQE0MyA1MwBEUQcxvHZTTTHNcZl9DUGqd0JBOzcsZ7+n8XJ0Z0GueOVwg68GfmnLn3O7/7Lee7s4cxZpHq6uos0bb3+Q+6DrG3u7v7RHt7u3tbW9uD5ubm8qamJnlDQ4NKIpG8HhkZOU3X7BYoD9Tb22sjk8mcWltb70ul0pcAegugBfzOjKmzs/MJ7j0kCujw8PBhADkAKAVAz+EZGYA+08bmCN79qdFo7sGmjaWg+wgIIUtoaWl5CqDmxsbGj7SJpYK3uYWFBRnsDmEfWyGg+zs6OkIBNEoGxVB9fT2bnZ1VIHff03xmZuY29rUyF9QWT6sWC5I0OTk5rVAo3unnSqXyEfa1Nhf0Kp5UKRYk8lsDD0oM1/r6+l5h32Pmgl5UqVTP5ubmlEgBHRlCobC8vDzm5eXFHB0dRZWzs/OXsLCwu5SCpkBPo4DaMVRI9rbp6Wk1vnP+/v5kaFfk4eGhAcdJU6Dn+/v7q9aTnpPL5YqVlRV5eXm5Fk+7Gx7lCgsL68Fx2RToWST7C8McQgr8yMrKWkLu/hQz/LDNIZojycnJb8BxwRTocYT8oSEoQs8bSkpK0k5MTGgiIiK4nYYMDg7mcBLIo6OjP9Ec44opUGvIF+eoTg/a1dX1x2BISMgyKncpLS1tbacgU1NT2djY2HxoaOi8fg3jhilQK+gmQvBVD4qQbzDs4+ND6bBWUFCgtRQyJyeHdwSKdcODY9zaTgu9jheMcWOgJFdXV1ZZWclqamp0bm5uQoqGVVRUrFHBuru782tCQC+hW0j/BkpCPlGXIY9wfn5+5hQN5T3dS+cz5btg0DNTU1NFpkBra2tZaWkpy8jIYOPj4ywmJmY7RcMGBwdZZmYmKykpoa7ELPGozeLiYrIetKenZ5OhuLg4ft3T05OfJyQk8LDp6el/LRq6JiUlheb8vXgzY7m5uYJBD0LeeDnRApQ8sKloqK3GxsZuWEPrYzhiWHFx8ZZFMzo6yiIjIw1DTTZ+qNXqMRcXF0GgVpADKltDoCisDcbj4+NZfn7+ll5D9fKeprYbFRXFwsPDWVVV1SodPwEBAVueEtnZ2QNIhTkhoKRrAxhb5WhRURFzcnIyGmIcX9rq6uoPq6urAzqdrresrGwIHtMZux62OOT6AD4FgZ5bXl5+DMhv6P16j/KhCwoK2vHO5O3t/SsxMfG7ENAjkAOUBUkMvMVDiiFKDSGge6Gj0CUoGmffpvwSEfg7dUch/0LtkWcdvr6+a2JDBgYG6tDt6DXPTgjoKegOVAo1QVKR1AgVQ8GQrRDQA+uw9ushuSWSyLYdQRr7K/JP6DcTwr8i7Fj8pwAAAABJRU5ErkJggg==\" alt=\"Configuration\" /></span>
    <strong>E-Mails</strong>
    <span class=\"count\">
        <span>";
        // line 36
        echo twig_escape_filter($this->env, $this->getAttribute((isset($context["collector"]) ? $context["collector"] : $this->getContext($context, "collector")), "messageCount", array()), "html", null, true);
        echo "</span>
    </span>
</span>
";
        
        $__internal_2f7842209bc41e13efed0ca22bf089edb6a0a980017637dd7533f3facd50187f->leave($__internal_2f7842209bc41e13efed0ca22bf089edb6a0a980017637dd7533f3facd50187f_prof);

    }

    // line 41
    public function block_panel($context, array $blocks = array())
    {
        $__internal_30f7f1b82a71ddf15ac54a678b996f6ac142e6ede6d9775ed03475f207538418 = $this->env->getExtension("native_profiler");
        $__internal_30f7f1b82a71ddf15ac54a678b996f6ac142e6ede6d9775ed03475f207538418->enter($__internal_30f7f1b82a71ddf15ac54a678b996f6ac142e6ede6d9775ed03475f207538418_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "panel"));

        // line 42
        echo "    <h2>Messages</h2>

    <table>
        <thead>
            <tr>
                <th scope=\"col\">Mailer</th>
                <th scope=\"col\">Messages</th>
            </tr>
        </thead>
        <tbody>
            ";
        // line 52
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable($this->getAttribute((isset($context["collector"]) ? $context["collector"] : $this->getContext($context, "collector")), "mailers", array()));
        foreach ($context['_seq'] as $context["_key"] => $context["name"]) {
            // line 53
            echo "                <tr>
                    <th>";
            // line 54
            echo twig_escape_filter($this->env, $context["name"], "html", null, true);
            echo (($this->getAttribute((isset($context["collector"]) ? $context["collector"] : $this->getContext($context, "collector")), "isDefaultMailer", array(0 => $context["name"]), "method")) ? (" (default mailer)") : (""));
            echo "</th>
                    <td>";
            // line 55
            echo twig_escape_filter($this->env, $this->getAttribute((isset($context["collector"]) ? $context["collector"] : $this->getContext($context, "collector")), "messageCount", array(0 => $context["name"]), "method"), "html", null, true);
            echo " ";
            echo (($this->getAttribute((isset($context["collector"]) ? $context["collector"] : $this->getContext($context, "collector")), "isSpool", array(0 => $context["name"]), "method")) ? ("spooled") : ("sent"));
            echo "</td>
                </tr>
            ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['name'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 58
        echo "        </tbody>
    </table>

    ";
        // line 61
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable($this->getAttribute((isset($context["collector"]) ? $context["collector"] : $this->getContext($context, "collector")), "mailers", array()));
        foreach ($context['_seq'] as $context["_key"] => $context["name"]) {
            // line 62
            echo "        <h3>Mailer <em>";
            echo twig_escape_filter($this->env, $context["name"], "html", null, true);
            echo "</em>";
            echo (($this->getAttribute((isset($context["collector"]) ? $context["collector"] : $this->getContext($context, "collector")), "isDefaultMailer", array(0 => $context["name"]), "method")) ? (" (default mailer)") : (""));
            echo "</h3>

        ";
            // line 64
            if ( !$this->getAttribute((isset($context["collector"]) ? $context["collector"] : $this->getContext($context, "collector")), "messages", array(0 => $context["name"]), "method")) {
                // line 65
                echo "            <p>
                <em>No message sent.</em>
            </p>
        ";
            } else {
                // line 69
                echo "            <ul class=\"alt\">
                ";
                // line 70
                $context['_parent'] = $context;
                $context['_seq'] = twig_ensure_traversable($this->getAttribute((isset($context["collector"]) ? $context["collector"] : $this->getContext($context, "collector")), "messages", array(0 => $context["name"]), "method"));
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
                foreach ($context['_seq'] as $context["_key"] => $context["message"]) {
                    // line 71
                    echo "                    <li class=\"";
                    echo twig_escape_filter($this->env, twig_cycle(array(0 => "odd", 1 => "even"), $this->getAttribute($context["loop"], "index", array())), "html", null, true);
                    echo "\">
                        ";
                    // line 72
                    $context['_parent'] = $context;
                    $context['_seq'] = twig_ensure_traversable($this->getAttribute($this->getAttribute($context["message"], "headers", array()), "all", array()));
                    foreach ($context['_seq'] as $context["_key"] => $context["header"]) {
                        // line 73
                        echo "                            <pre>";
                        echo twig_escape_filter($this->env, $context["header"], "html", null, true);
                        echo "</pre>
                        ";
                    }
                    $_parent = $context['_parent'];
                    unset($context['_seq'], $context['_iterated'], $context['_key'], $context['header'], $context['_parent'], $context['loop']);
                    $context = array_intersect_key($context, $_parent) + $_parent;
                    // line 75
                    echo "                        <pre>";
                    // line 76
                    if (($this->getAttribute((isset($context["messagePart"]) ? $context["messagePart"] : null), "charset", array(), "any", true, true) && $this->getAttribute($context["message"], "charset", array()))) {
                        // line 77
                        echo twig_escape_filter($this->env, twig_convert_encoding(twig_escape_filter($this->env, $this->getAttribute($context["message"], "body", array()), "html", $this->getAttribute($context["message"], "charset", array())), "UTF-8", $this->getAttribute($context["message"], "charset", array())), "html", null, true);
                    } else {
                        // line 79
                        echo twig_escape_filter($this->env, $this->getAttribute($context["message"], "body", array()), "html");
                    }
                    // line 81
                    echo "</pre>
                        ";
                    // line 82
                    $context['_parent'] = $context;
                    $context['_seq'] = twig_ensure_traversable($this->getAttribute($context["message"], "children", array()));
                    foreach ($context['_seq'] as $context["_key"] => $context["messagePart"]) {
                        if ((($this->getAttribute($context["messagePart"], "contentType", array()) == "text/plain") || ($this->getAttribute($context["messagePart"], "contentType", array()) == "text/html"))) {
                            // line 83
                            echo "                            <h4>Alternative part</h4>
                            <pre>";
                            // line 85
                            if ($this->getAttribute($context["messagePart"], "charset", array())) {
                                // line 86
                                echo twig_escape_filter($this->env, twig_convert_encoding(twig_escape_filter($this->env, $this->getAttribute($context["messagePart"], "body", array()), "html", $this->getAttribute($context["messagePart"], "charset", array())), "UTF-8", $this->getAttribute($context["messagePart"], "charset", array())), "html", null, true);
                            } else {
                                // line 88
                                echo twig_escape_filter($this->env, $this->getAttribute($context["messagePart"], "body", array()), "html");
                            }
                            // line 90
                            echo "</pre>
                        ";
                        }
                    }
                    $_parent = $context['_parent'];
                    unset($context['_seq'], $context['_iterated'], $context['_key'], $context['messagePart'], $context['_parent'], $context['loop']);
                    $context = array_intersect_key($context, $_parent) + $_parent;
                    // line 92
                    echo "                    </li>
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
                unset($context['_seq'], $context['_iterated'], $context['_key'], $context['message'], $context['_parent'], $context['loop']);
                $context = array_intersect_key($context, $_parent) + $_parent;
                // line 94
                echo "            </ul>
        ";
            }
            // line 96
            echo "    ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['name'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        
        $__internal_30f7f1b82a71ddf15ac54a678b996f6ac142e6ede6d9775ed03475f207538418->leave($__internal_30f7f1b82a71ddf15ac54a678b996f6ac142e6ede6d9775ed03475f207538418_prof);

    }

    public function getTemplateName()
    {
        return "@Swiftmailer/Collector/swiftmailer.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  294 => 96,  290 => 94,  275 => 92,  267 => 90,  264 => 88,  261 => 86,  259 => 85,  256 => 83,  251 => 82,  248 => 81,  245 => 79,  242 => 77,  240 => 76,  238 => 75,  229 => 73,  225 => 72,  220 => 71,  203 => 70,  200 => 69,  194 => 65,  192 => 64,  184 => 62,  180 => 61,  175 => 58,  164 => 55,  159 => 54,  156 => 53,  152 => 52,  140 => 42,  134 => 41,  123 => 36,  117 => 32,  111 => 31,  103 => 28,  100 => 27,  96 => 25,  87 => 22,  80 => 18,  76 => 17,  72 => 15,  68 => 14,  63 => 12,  59 => 10,  56 => 9,  51 => 7,  48 => 6,  45 => 5,  42 => 4,  36 => 3,  11 => 1,);
    }
}
/* {% extends 'WebProfilerBundle:Profiler:layout.html.twig' %}*/
/* */
/* {% block toolbar %}*/
/*     {% if collector.messageCount %}*/
/*         {% set icon %}*/
/*             <img width="23" height="28" alt="Swiftmailer" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABcAAAAcCAYAAACK7SRjAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyJpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuMC1jMDYwIDYxLjEzNDc3NywgMjAxMC8wMi8xMi0xNzozMjowMCAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENTNSBNYWNpbnRvc2giIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6N0NEOTU1MjM0OThFMTFFMDg3NzJBNTE2ODgwQzMxMzQiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6N0NEOTU1MjQ0OThFMTFFMDg3NzJBNTE2ODgwQzMxMzQiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlpZDoxMEQ5OTQ5QzQ5OEMxMUUwODc3MkE1MTY4ODBDMzEzNCIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDo3Q0Q5NTUyMjQ5OEUxMUUwODc3MkE1MTY4ODBDMzEzNCIvPiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6eG1wbWV0YT4gPD94cGFja2V0IGVuZD0iciI/PpkRnSAAAAJ0SURBVHjaYvz//z8DrQATAw3BqOFYAaO9vT1FBhw4cGCAXA5MipxBQUHT3r17l0AVAxkZ/wkLC89as2ZNIcjlYkALXKnlWqBZTH/+/PEDmQsynLW/v3+NoaHhN2oYDjJn8uTJK4BMNpDhPwsLCwOKiop2+fn5vafEYC8vrw8gc/Lz8wOB3B8gw/nev38vn5WV5eTg4LA/Ly/vESsrK2npmYmJITU19SnQ8L0gc4DxpwgyF2S4EEjB58+f+crLy31YWFjOt7S0XBYUFPxHjMEcHBz/6+rqboqJiZ0qKSnxBpkDlRICGc4MU/j792+2CRMm+L18+fLSxIkTDykoKPzBZ7CoqOi/np6eE8rKylvb29v9fvz4wYEkzYKRzjk5OX/LyMjcnDRpEkjjdisrK6wRraOj8wvokAMLFy788ejRoxcaGhrPCWai4ODgB8DUE3/mzBknYMToASNoMzAfvEVW4+Tk9LmhoWFbTU2NwunTpx2BjiiMjo6+hm4WCzJHUlLyz+vXrxkfP36sDOI/ffpUPikpibe7u3sLsJjQW7VqlSrQxe+Avjmanp7u9PbtWzGQOmCCkARmmu/m5uYfT548yY/V5UpKSl+2b9+uiiz26dMnIWBSDTp27NgdYGrYCIzwE7m5uR4wg2Hg/PnzSsDI/QlKOcjZ3wGUBLm5uf+DwLdv38gub4AG/xcSEvr35s0bZmCB5sgCE/z69SsjyDJKMtG/f/8YQQYD8wkoGf8H51AbG5sH1CpbQBnQ09PzBiiHgoIFFHlBQGwLxLxUMP8dqJgH4k3gIhfIkAKVYkDMTmmhCHIxEL8A4peMo02L4WU4QIABANxZAoDIQDv3AAAAAElFTkSuQmCC" />*/
/*             <span class="sf-toolbar-status">{{ collector.messageCount }}</span>*/
/*         {% endset %}*/
/*         {% set text %}*/
/*             <div class="sf-toolbar-info-piece">*/
/*                 <b>Messages</b>*/
/*                 <span>{{ collector.messageCount }}</span>*/
/*             </div>*/
/*             {% for name in collector.mailers %}*/
/*                 <hr>*/
/*                 <div class="sf-toolbar-info-piece">*/
/*                     <b>{{ name }}</b>*/
/*                     <span>{{ collector.messageCount(name) }}</span>*/
/*                 </div>*/
/*                 <div class="sf-toolbar-info-piece">*/
/*                     <b>Is spooled ?</b>*/
/*                     <span>{{ collector.isSpool(name) ? 'yes' : 'no' }}</span>*/
/*                 </div>*/
/*             {% endfor %}*/
/* */
/*         {% endset %}*/
/*         {% include 'WebProfilerBundle:Profiler:toolbar_item.html.twig' with { 'link': profiler_url } %}*/
/*     {% endif %}*/
/* {% endblock %}*/
/* */
/* {% block menu %}*/
/* <span class="label">*/
/*     <span class="icon"><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACoAAAAeCAYAAABaKIzgAAAEDElEQVR42u2XWUhUURjHy6isILAebO+lonpJ8qkHM3NFVFxAUVFM0RSXUKnwwdAQn3wQE0MyA1MwBEUQcxvHZTTTHNcZl9DUGqd0JBOzcsZ7+n8XJ0Z0GueOVwg68GfmnLn3O7/7Lee7s4cxZpHq6uos0bb3+Q+6DrG3u7v7RHt7u3tbW9uD5ubm8qamJnlDQ4NKIpG8HhkZOU3X7BYoD9Tb22sjk8mcWltb70ul0pcAegugBfzOjKmzs/MJ7j0kCujw8PBhADkAKAVAz+EZGYA+08bmCN79qdFo7sGmjaWg+wgIIUtoaWl5CqDmxsbGj7SJpYK3uYWFBRnsDmEfWyGg+zs6OkIBNEoGxVB9fT2bnZ1VIHff03xmZuY29rUyF9QWT6sWC5I0OTk5rVAo3unnSqXyEfa1Nhf0Kp5UKRYk8lsDD0oM1/r6+l5h32Pmgl5UqVTP5ubmlEgBHRlCobC8vDzm5eXFHB0dRZWzs/OXsLCwu5SCpkBPo4DaMVRI9rbp6Wk1vnP+/v5kaFfk4eGhAcdJU6Dn+/v7q9aTnpPL5YqVlRV5eXm5Fk+7Gx7lCgsL68Fx2RToWST7C8McQgr8yMrKWkLu/hQz/LDNIZojycnJb8BxwRTocYT8oSEoQs8bSkpK0k5MTGgiIiK4nYYMDg7mcBLIo6OjP9Ec44opUGvIF+eoTg/a1dX1x2BISMgyKncpLS1tbacgU1NT2djY2HxoaOi8fg3jhilQK+gmQvBVD4qQbzDs4+ND6bBWUFCgtRQyJyeHdwSKdcODY9zaTgu9jheMcWOgJFdXV1ZZWclqamp0bm5uQoqGVVRUrFHBuru782tCQC+hW0j/BkpCPlGXIY9wfn5+5hQN5T3dS+cz5btg0DNTU1NFpkBra2tZaWkpy8jIYOPj4ywmJmY7RcMGBwdZZmYmKykpoa7ELPGozeLiYrIetKenZ5OhuLg4ft3T05OfJyQk8LDp6el/LRq6JiUlheb8vXgzY7m5uYJBD0LeeDnRApQ8sKloqK3GxsZuWEPrYzhiWHFx8ZZFMzo6yiIjIw1DTTZ+qNXqMRcXF0GgVpADKltDoCisDcbj4+NZfn7+ll5D9fKeprYbFRXFwsPDWVVV1SodPwEBAVueEtnZ2QNIhTkhoKRrAxhb5WhRURFzcnIyGmIcX9rq6uoPq6urAzqdrresrGwIHtMZux62OOT6AD4FgZ5bXl5+DMhv6P16j/KhCwoK2vHO5O3t/SsxMfG7ENAjkAOUBUkMvMVDiiFKDSGge6Gj0CUoGmffpvwSEfg7dUch/0LtkWcdvr6+a2JDBgYG6tDt6DXPTgjoKegOVAo1QVKR1AgVQ8GQrRDQA+uw9ushuSWSyLYdQRr7K/JP6DcTwr8i7Fj8pwAAAABJRU5ErkJggg==" alt="Configuration" /></span>*/
/*     <strong>E-Mails</strong>*/
/*     <span class="count">*/
/*         <span>{{ collector.messageCount }}</span>*/
/*     </span>*/
/* </span>*/
/* {% endblock %}*/
/* */
/* {% block panel %}*/
/*     <h2>Messages</h2>*/
/* */
/*     <table>*/
/*         <thead>*/
/*             <tr>*/
/*                 <th scope="col">Mailer</th>*/
/*                 <th scope="col">Messages</th>*/
/*             </tr>*/
/*         </thead>*/
/*         <tbody>*/
/*             {% for name in collector.mailers %}*/
/*                 <tr>*/
/*                     <th>{{ name }}{{ collector.isDefaultMailer(name) ? ' (default mailer)' : '' }}</th>*/
/*                     <td>{{ collector.messageCount(name) }} {{ collector.isSpool(name) ? 'spooled' : 'sent' }}</td>*/
/*                 </tr>*/
/*             {% endfor %}*/
/*         </tbody>*/
/*     </table>*/
/* */
/*     {% for name in collector.mailers %}*/
/*         <h3>Mailer <em>{{ name }}</em>{{ collector.isDefaultMailer(name) ? ' (default mailer)' : '' }}</h3>*/
/* */
/*         {% if not collector.messages(name) %}*/
/*             <p>*/
/*                 <em>No message sent.</em>*/
/*             </p>*/
/*         {% else %}*/
/*             <ul class="alt">*/
/*                 {% for message in collector.messages(name) %}*/
/*                     <li class="{{ cycle(['odd', 'even'], loop.index) }}">*/
/*                         {% for header in message.headers.all %}*/
/*                             <pre>{{ header }}</pre>*/
/*                         {% endfor %}*/
/*                         <pre>*/
/*                             {%- if messagePart.charset is defined and message.charset %}*/
/*                                 {{- message.body|e('html', message.charset)|convert_encoding('UTF-8', message.charset) }}*/
/*                             {%- else %}*/
/*                                 {{- message.body|e('html') }}*/
/*                             {%- endif -%}*/
/*                         </pre>*/
/*                         {% for messagePart in message.children if messagePart.contentType == 'text/plain' or messagePart.contentType == 'text/html' %}*/
/*                             <h4>Alternative part</h4>*/
/*                             <pre>*/
/*                                 {%- if messagePart.charset %}*/
/*                                     {{- messagePart.body|e('html', messagePart.charset)|convert_encoding('UTF-8', messagePart.charset) }}*/
/*                                 {%- else %}*/
/*                                     {{- messagePart.body|e('html') }}*/
/*                                 {%- endif -%}*/
/*                             </pre>*/
/*                         {% endfor %}*/
/*                     </li>*/
/*                 {% endfor %}*/
/*             </ul>*/
/*         {% endif %}*/
/*     {% endfor %}*/
/* {% endblock %}*/
/* */

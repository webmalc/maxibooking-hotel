<?php

/* MisdGuzzleBundle:Collector:guzzle.html.twig */
class __TwigTemplate_4c50eaed4278e598e8e741a965191223ca4ff92190527034fd892c84c0b8d3a0 extends MBH\Bundle\BaseBundle\Twig\Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        // line 1
        $this->parent = $this->loadTemplate("WebProfilerBundle:Profiler:layout.html.twig", "MisdGuzzleBundle:Collector:guzzle.html.twig", 1);
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
        $__internal_4f77b61c8c72371114e193b1152e3e4526a98fa02db46bd01bba8f0b44f790cb = $this->env->getExtension("native_profiler");
        $__internal_4f77b61c8c72371114e193b1152e3e4526a98fa02db46bd01bba8f0b44f790cb->enter($__internal_4f77b61c8c72371114e193b1152e3e4526a98fa02db46bd01bba8f0b44f790cb_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "MisdGuzzleBundle:Collector:guzzle.html.twig"));

        $this->parent->display($context, array_merge($this->blocks, $blocks));
        
        $__internal_4f77b61c8c72371114e193b1152e3e4526a98fa02db46bd01bba8f0b44f790cb->leave($__internal_4f77b61c8c72371114e193b1152e3e4526a98fa02db46bd01bba8f0b44f790cb_prof);

    }

    // line 3
    public function block_toolbar($context, array $blocks = array())
    {
        $__internal_2e5826feff8ce665d2e2c621dc837513ee509652cc5b78ba662de1d8fc492e66 = $this->env->getExtension("native_profiler");
        $__internal_2e5826feff8ce665d2e2c621dc837513ee509652cc5b78ba662de1d8fc492e66->enter($__internal_2e5826feff8ce665d2e2c621dc837513ee509652cc5b78ba662de1d8fc492e66_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "toolbar"));

        // line 4
        echo "    ";
        ob_start();
        // line 5
        echo "    <img width=\"18\" height=\"28\" alt=\"Guzzle\"
         src=\"data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABIAAAAcCAYAAABsxO8nAAAAAXNSR0IArs4c6QAAAAZiS0dEAP8A/wD/oL2nkwAAAAlwSFlzAAAOxAAADsQBlSsOGwAAAAd0SU1FB9wJFA0kAmx9x5QAAAAdaVRYdENvbW1lbnQAAAAAAENyZWF0ZWQgd2l0aCBHSU1QZC5lBwAAAsRJREFUOMvdlL1vXUUQxc+ZXV/HJCSiQRRBdOHDEYVrR5Gf3vWHrMjPtHTQ5m9IRQtI/AE0NDS2sYSc5+cPCSwUKVKE5BRBKD1ShEgsEM593pmh2Wvte3kE6kxz9652f3PmzNwLvLLBdrGwsPAGyTkRaQAYACVZkUyDweDe/wJ1Op2eiGzFGEESIQSYGcwMAODup7u7u6/9J6jb7T6cmpq6XlXVdRG5TDK6u6rqM1X9xszmRGQ2pfRrVVX3qqr6dGNj4/gF0PLy8o/T09M3tre3OZ5pbW1tXVU3U0pz/X7/59XVVQ8hIMb44ebm5sP2nAAAyQsvUf02SZAc5vebqorhcHjc6/XeHQEBeK6qk2sn/zAzkEzZr5OU0klKCSml+yOglNJTd58IOjs7g6rC3U8BwMwigGMz+0VVL7fnIgCo6pOmabCysrKVWz/j7n8COBsOhx+TRIzxBAD6/f6DxcXF70h+nlLCOOiZmUFVeyTbks7bH2PEzs7OSXtpMBh80e12ZwB8NgIys6chBLg7Wq/cHdlkmBnquva8t7W3t/eRiNwv7Yj5kpsZRKQ0Ga26lBJIfk/yfQDrnU6n0zTNkxDCKKj8VEqYmSGEgJQSjo6ObuWv4CcAKyQPyk7HfPnvdqOUKyJQ1XNluYtBRC4AuFruS87srbHt8yXxG8lHAJpSfbu6KCIY96lVV8JJXnL3d0IId0miruvZc5CI/KuMcXgI4UqM8drh4eHvuaOzZWnPW0/GSyshOU4BXCwUflAq4qTs7g4RGW9AjDFWWd0PJN8rPZqZVJaIjMxTvvy6u7fz97W7r5dzdGmSNyGEF0DufiWEMJ3XjbtXpaLZcqJLv8wMMUbUdf1mVnQHwLW6rp3ktyLy6Hyi5+fnr4rIbRF5bGZ/5Z+YAJgRkbdCCGl/f//LNtnS0tJNVf3E3R8cHBx8hVc7/gEz5WHvMjpIQQAAAABJRU5ErkJggg==\"/>
    <span class=\"sf-toolbar-status";
        // line 7
        if ((10 < twig_length_filter($this->env, $this->getAttribute((isset($context["collector"]) ? $context["collector"] : $this->getContext($context, "collector")), "requests", array())))) {
            echo " sf-toolbar-status-yellow";
        }
        echo "\">";
        echo twig_escape_filter($this->env, twig_length_filter($this->env, $this->getAttribute((isset($context["collector"]) ? $context["collector"] : $this->getContext($context, "collector")), "requests", array())), "html", null, true);
        echo "</span>
    ";
        $context["icon"] = ('' === $tmp = ob_get_clean()) ? '' : new Twig_Markup($tmp, $this->env->getCharset());
        // line 9
        echo "    ";
        ob_start();
        // line 10
        echo "    <div class=\"sf-toolbar-info-piece\">
        <b>HTTP Requests</b>
        <span>";
        // line 12
        echo twig_escape_filter($this->env, twig_length_filter($this->env, $this->getAttribute((isset($context["collector"]) ? $context["collector"] : $this->getContext($context, "collector")), "requests", array())), "html", null, true);
        echo "</span>
    </div>
    ";
        $context["text"] = ('' === $tmp = ob_get_clean()) ? '' : new Twig_Markup($tmp, $this->env->getCharset());
        // line 15
        echo "    ";
        $this->loadTemplate("WebProfilerBundle:Profiler:toolbar_item.html.twig", "MisdGuzzleBundle:Collector:guzzle.html.twig", 15)->display(array_merge($context, array("link" => (isset($context["profiler_url"]) ? $context["profiler_url"] : $this->getContext($context, "profiler_url")))));
        
        $__internal_2e5826feff8ce665d2e2c621dc837513ee509652cc5b78ba662de1d8fc492e66->leave($__internal_2e5826feff8ce665d2e2c621dc837513ee509652cc5b78ba662de1d8fc492e66_prof);

    }

    // line 18
    public function block_menu($context, array $blocks = array())
    {
        $__internal_a718a9ce340d859358821fe5afc26b01656a142de53a71e255ed9c710bdc98f2 = $this->env->getExtension("native_profiler");
        $__internal_a718a9ce340d859358821fe5afc26b01656a142de53a71e255ed9c710bdc98f2->enter($__internal_a718a9ce340d859358821fe5afc26b01656a142de53a71e255ed9c710bdc98f2_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "menu"));

        // line 19
        echo "    <span class=\"label\">
    <span class=\"icon\"><img src=\"data:image/png;base64,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\" alt=\"\"/></span>
    <strong>Guzzle</strong>
    <span class=\"count\">
        <span>";
        // line 23
        echo twig_escape_filter($this->env, twig_length_filter($this->env, $this->getAttribute((isset($context["collector"]) ? $context["collector"] : $this->getContext($context, "collector")), "requests", array())), "html", null, true);
        echo "</span>
    </span>
</span>
";
        
        $__internal_a718a9ce340d859358821fe5afc26b01656a142de53a71e255ed9c710bdc98f2->leave($__internal_a718a9ce340d859358821fe5afc26b01656a142de53a71e255ed9c710bdc98f2_prof);

    }

    // line 28
    public function block_panel($context, array $blocks = array())
    {
        $__internal_362dbef9cfc5882831d0c3aeda0407f212da9728330e8eb0b98dda07119494b9 = $this->env->getExtension("native_profiler");
        $__internal_362dbef9cfc5882831d0c3aeda0407f212da9728330e8eb0b98dda07119494b9->enter($__internal_362dbef9cfc5882831d0c3aeda0407f212da9728330e8eb0b98dda07119494b9_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "panel"));

        // line 29
        echo "    <h2>HTTP requests</h2>

    ";
        // line 31
        if ((twig_length_filter($this->env, $this->getAttribute((isset($context["collector"]) ? $context["collector"] : $this->getContext($context, "collector")), "requests", array())) == 0)) {
            // line 32
            echo "        <p>
            <em>No requests sent.</em>
        </p>
    ";
        } else {
            // line 36
            echo "        <ul class=\"alt\">
            ";
            // line 37
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable($this->getAttribute((isset($context["collector"]) ? $context["collector"] : $this->getContext($context, "collector")), "requests", array()));
            foreach ($context['_seq'] as $context["i"] => $context["request"]) {
                // line 38
                echo "                <li class=\"";
                echo ((($context["i"] % 2 == 1)) ? ("odd") : ("even"));
                echo " ";
                if ($this->getAttribute($context["request"], "is_error", array())) {
                    echo "error";
                }
                echo "\">
                    <div>
                        <a href=\"#\" onclick=\"return explain(this);\" style=\"text-decoration: none;\" title=\"Explains\"
                           data-target-id=\"explain-";
                // line 41
                echo twig_escape_filter($this->env, $context["i"], "html", null, true);
                echo "\">
                            <img alt=\"+\" src=\"";
                // line 42
                echo twig_escape_filter($this->env, $this->env->getExtension('asset')->getAssetUrl("bundles/framework/images/blue_picto_more.gif"), "html", null, true);
                echo "\"
                                 style=\"display: inline;\"/>
                            <img alt=\"-\" src=\"";
                // line 44
                echo twig_escape_filter($this->env, $this->env->getExtension('asset')->getAssetUrl("bundles/framework/images/blue_picto_less.gif"), "html", null, true);
                echo "\"
                                 style=\"display: none;\"/>
                        </a>
                        <code>";
                // line 47
                echo twig_escape_filter($this->env, $this->getAttribute($context["request"], "message", array()), "html", null, true);
                echo " (";
                echo twig_escape_filter($this->env, twig_number_format_filter($this->env, $this->getAttribute($context["request"], "time", array())), "html", null, true);
                echo " ms)</code>
                    </div>

                    <div id=\"explain-";
                // line 50
                echo twig_escape_filter($this->env, $context["i"], "html", null, true);
                echo "\" style=\"display:none; padding-top: 1em;\">
                        <small>
                            <strong>Request:</strong>
                        </small>
                        <pre style=\"white-space: pre-wrap; white-space: -moz-pre-wrap !important; white-space: -pre-wrap; white-space: -o-pre-wrap; word-wrap: break-word;\">";
                // line 54
                echo twig_escape_filter($this->env, $this->getAttribute($context["request"], "request", array()), "html", null, true);
                echo "</pre>
                        <small>
                            <strong>Response:</strong>
                        </small>
                        <pre style=\"white-space: pre-wrap; white-space: -moz-pre-wrap !important; white-space: -pre-wrap; white-space: -o-pre-wrap; word-wrap: break-word;\">";
                // line 58
                echo twig_escape_filter($this->env, $this->getAttribute($context["request"], "response", array()), "html", null, true);
                echo "</pre>
                    </div>
                </li>
            ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['i'], $context['request'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 62
            echo "        </ul>
    ";
        }
        // line 64
        echo "
    <script type=\"text/javascript\">//<![CDATA[
    function explain(link) {
        \"use strict\";

        var imgs = link.children,
                target = link.getAttribute('data-target-id');

        Sfjs.toggle(target, imgs[0], imgs[1]);

        return false;
    }
    //]]></script>
";
        
        $__internal_362dbef9cfc5882831d0c3aeda0407f212da9728330e8eb0b98dda07119494b9->leave($__internal_362dbef9cfc5882831d0c3aeda0407f212da9728330e8eb0b98dda07119494b9_prof);

    }

    public function getTemplateName()
    {
        return "MisdGuzzleBundle:Collector:guzzle.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  189 => 64,  185 => 62,  175 => 58,  168 => 54,  161 => 50,  153 => 47,  147 => 44,  142 => 42,  138 => 41,  127 => 38,  123 => 37,  120 => 36,  114 => 32,  112 => 31,  108 => 29,  102 => 28,  91 => 23,  85 => 19,  79 => 18,  71 => 15,  65 => 12,  61 => 10,  58 => 9,  49 => 7,  45 => 5,  42 => 4,  36 => 3,  11 => 1,);
    }
}
/* {% extends 'WebProfilerBundle:Profiler:layout.html.twig' %}*/
/* */
/* {% block toolbar %}*/
/*     {% set icon %}*/
/*     <img width="18" height="28" alt="Guzzle"*/
/*          src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABIAAAAcCAYAAABsxO8nAAAAAXNSR0IArs4c6QAAAAZiS0dEAP8A/wD/oL2nkwAAAAlwSFlzAAAOxAAADsQBlSsOGwAAAAd0SU1FB9wJFA0kAmx9x5QAAAAdaVRYdENvbW1lbnQAAAAAAENyZWF0ZWQgd2l0aCBHSU1QZC5lBwAAAsRJREFUOMvdlL1vXUUQxc+ZXV/HJCSiQRRBdOHDEYVrR5Gf3vWHrMjPtHTQ5m9IRQtI/AE0NDS2sYSc5+cPCSwUKVKE5BRBKD1ShEgsEM593pmh2Wvte3kE6kxz9652f3PmzNwLvLLBdrGwsPAGyTkRaQAYACVZkUyDweDe/wJ1Op2eiGzFGEESIQSYGcwMAODup7u7u6/9J6jb7T6cmpq6XlXVdRG5TDK6u6rqM1X9xszmRGQ2pfRrVVX3qqr6dGNj4/gF0PLy8o/T09M3tre3OZ5pbW1tXVU3U0pz/X7/59XVVQ8hIMb44ebm5sP2nAAAyQsvUf02SZAc5vebqorhcHjc6/XeHQEBeK6qk2sn/zAzkEzZr5OU0klKCSml+yOglNJTd58IOjs7g6rC3U8BwMwigGMz+0VVL7fnIgCo6pOmabCysrKVWz/j7n8COBsOhx+TRIzxBAD6/f6DxcXF70h+nlLCOOiZmUFVeyTbks7bH2PEzs7OSXtpMBh80e12ZwB8NgIys6chBLg7Wq/cHdlkmBnquva8t7W3t/eRiNwv7Yj5kpsZRKQ0Ga26lBJIfk/yfQDrnU6n0zTNkxDCKKj8VEqYmSGEgJQSjo6ObuWv4CcAKyQPyk7HfPnvdqOUKyJQ1XNluYtBRC4AuFruS87srbHt8yXxG8lHAJpSfbu6KCIY96lVV8JJXnL3d0IId0miruvZc5CI/KuMcXgI4UqM8drh4eHvuaOzZWnPW0/GSyshOU4BXCwUflAq4qTs7g4RGW9AjDFWWd0PJN8rPZqZVJaIjMxTvvy6u7fz97W7r5dzdGmSNyGEF0DufiWEMJ3XjbtXpaLZcqJLv8wMMUbUdf1mVnQHwLW6rp3ktyLy6Hyi5+fnr4rIbRF5bGZ/5Z+YAJgRkbdCCGl/f//LNtnS0tJNVf3E3R8cHBx8hVc7/gEz5WHvMjpIQQAAAABJRU5ErkJggg=="/>*/
/*     <span class="sf-toolbar-status{% if 10 < collector.requests|length %} sf-toolbar-status-yellow{% endif %}">{{ collector.requests|length }}</span>*/
/*     {% endset %}*/
/*     {% set text %}*/
/*     <div class="sf-toolbar-info-piece">*/
/*         <b>HTTP Requests</b>*/
/*         <span>{{ collector.requests|length }}</span>*/
/*     </div>*/
/*     {% endset %}*/
/*     {% include 'WebProfilerBundle:Profiler:toolbar_item.html.twig' with { 'link': profiler_url } %}*/
/* {% endblock %}*/
/* */
/* {% block menu %}*/
/*     <span class="label">*/
/*     <span class="icon"><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAeCAYAAAA2Lt7lAAAAAXNSR0IArs4c6QAAAAZiS0dEAP8A/wD/oL2nkwAAAAlwSFlzAAAOxAAADsQBlSsOGwAAAAd0SU1FB9wJFA0tK/8N5LEAAAAdaVRYdENvbW1lbnQAAAAAAENyZWF0ZWQgd2l0aCBHSU1QZC5lBwAABa5JREFUSMeFVl2IXVcV/tZa+5x77swdpzMpBeOYamPTTEzBGgr+TAL5RRJShgkZGrQPAZ9ERB9L7IsBQQsiFn3JkyhcKswPQsjUjIS8DBQREqTYptC0wZKGJrVt4r1zzt57LR/m7NMzY9UFl3u55+z1rf1931p7E+o4cOAAmBlEBCKCmQEAiKj538ywuroKADh69CguX76M/xe0f/9+OOcec8793Dn3LWYeFRFPRBGAhhDEzHJm/pCI3iOi3w4Gg58loBTT09OYmZnBhQsXNgMcPHhwW5Zla8y8yzkHEQEzQ0QAACEExBjBzDAzEBHKsqSVlRWcPXuWnXM77969++bS0hIA4MyZM+j3+w0Ai8hzeZ7v6nQ6KIriByMjI2Ojo6NPdbvdb3a73a91u90niqKYds696ZwDM2NsbOxhAKiq6ntlWf5l27ZtP52fnwcA9Pt9zM7ObgLInXMoigJFUSz1+/0H09PT14qiWOv1eq/2+/0bU1NTr+d5/kNmBjNDVR8CAO/9IyGE8aqqnh8fH//J6dOnAQDLy8uYm5sDADgR+cA5h7q6DADOnTu3icebN2+i1+v9DUBUVfHeewCIMf4ewI8BEIAXxsbG3p+dnX1peXkZi4uLGzsgImdmUFXUwv5HmBnMbLeqSowRAAYzMzNYWFi4EWN82nuP9fV1VFX1q8nJyflNFAG4H2NEjBGpsq1R7/AtVU32XVdVnD9/HiEELcsS3nvU3y9PTk4eadZ67+8lx5hZBQDHjx9v+uDSpUswM4QQ3okxfqCqk+vr6/fX1tYwMTEB55wCuBVj3MHM8N6DiMabHXjv75Rl6estzs/Pz2e9Xm97r9fb2ev1Hj116tSEmX3uwYMHL1VVNRljbMAvXryI4XB4HcAfAUBVUdNVNTsws/e892+o6t4Y4y9F5LvM3APQVdWhmX2oql1VfbLu8qiqDX01bS8y80FV/XINTg1ACOGeiLxrZntDCLmIfJWINmlQC5u0+Lj9bHV1FceOHbvV6XSOm9k1EZkws24DAKAKIdxxzoGI4L1vKEgdnACZGQAmiqL4+4kTJzIAbzPzi1VVvTIYDG6Njo6uENEZIuo0ALU976tqaqLmkygQERBRchpUdXf9bCeAJ4lor6q+r6qv1zRmbZvCNqKpkpkTHakBQUSNiFVV/TqE8Ie6kEcAfKfu7I+892i73dVjWNu81lQ0TQYAIoIYI1T1dozx+8yMLMv+CuAFAEfzPP9zjHGybsomHxMRRKRsC1u/1CTfEh+pKq5evYoY48UY4+0QwvYY41MAHq3X6yaKVLXcmiVpkEZ38r9zrkiN6b0fjzFWMcbbqloR0YSIQEQ8AMzNzYHrLnUhhKbqJPhWu4oIzExatA0BvE1ErwG4AeBeW+TFxcUNAGYu0oHS1iCB1U5Lv6W1u5yZP2tmX1TVt5h5oab88U0a8EZ8csxtqbw1bUEbkQAK59xjWZZ9pdPpqIhcJyKEEPYcOnQovcMA4NuJU+emAz/1R9vG9W8hos8Q0Q5mniCiQc3C7pGRkQwAuE5WbbVlu/I0JlIBrVk0ADCszZIBKOtz+wvMvKu9g/9JTxoZdXJuayUikuc5dzqdoqqqf4nIkIjIzL7U1qDbrjglS3ek9n0prQGAPM+7IlLUtI7ULvuNmRXM/GzzMhEV/+2o3EpPexQT0cMtO9PKygpU9WVVRYzxG6nRcgCf/7Qx0R58KZGI9Fo2Xm+t+/qRI0cA4In6GQMAm9keM5v+NB1avDcgWZYVzrkEsMLMf6pd9os8z19j5t/VxVxPJ9o1M1tg5m8D+BjAP8xsCCCNj0JVHzKzKRHZISKvJBubmReR51T1gqo+o6p76sKWmPlHAED79u1Dt9sFEe0loncB/DNdEduRZdlUlmXbsyx7Zzgc3kl305MnT6IsSzjnTqjq02Z2qaqqV69cuYLDhw/j3+ulRXoDv+EnAAAAAElFTkSuQmCC" alt=""/></span>*/
/*     <strong>Guzzle</strong>*/
/*     <span class="count">*/
/*         <span>{{ collector.requests|length }}</span>*/
/*     </span>*/
/* </span>*/
/* {% endblock %}*/
/* */
/* {% block panel %}*/
/*     <h2>HTTP requests</h2>*/
/* */
/*     {% if collector.requests|length == 0 %}*/
/*         <p>*/
/*             <em>No requests sent.</em>*/
/*         </p>*/
/*     {% else %}*/
/*         <ul class="alt">*/
/*             {% for i, request in collector.requests %}*/
/*                 <li class="{{ i is odd ? 'odd' : 'even' }} {% if request.is_error %}error{% endif %}">*/
/*                     <div>*/
/*                         <a href="#" onclick="return explain(this);" style="text-decoration: none;" title="Explains"*/
/*                            data-target-id="explain-{{ i }}">*/
/*                             <img alt="+" src="{{ asset('bundles/framework/images/blue_picto_more.gif') }}"*/
/*                                  style="display: inline;"/>*/
/*                             <img alt="-" src="{{ asset('bundles/framework/images/blue_picto_less.gif') }}"*/
/*                                  style="display: none;"/>*/
/*                         </a>*/
/*                         <code>{{ request.message }} ({{ request.time|number_format }} ms)</code>*/
/*                     </div>*/
/* */
/*                     <div id="explain-{{ i }}" style="display:none; padding-top: 1em;">*/
/*                         <small>*/
/*                             <strong>Request:</strong>*/
/*                         </small>*/
/*                         <pre style="white-space: pre-wrap; white-space: -moz-pre-wrap !important; white-space: -pre-wrap; white-space: -o-pre-wrap; word-wrap: break-word;">{{ request.request }}</pre>*/
/*                         <small>*/
/*                             <strong>Response:</strong>*/
/*                         </small>*/
/*                         <pre style="white-space: pre-wrap; white-space: -moz-pre-wrap !important; white-space: -pre-wrap; white-space: -o-pre-wrap; word-wrap: break-word;">{{ request.response }}</pre>*/
/*                     </div>*/
/*                 </li>*/
/*             {% endfor %}*/
/*         </ul>*/
/*     {% endif %}*/
/* */
/*     <script type="text/javascript">//<![CDATA[*/
/*     function explain(link) {*/
/*         "use strict";*/
/* */
/*         var imgs = link.children,*/
/*                 target = link.getAttribute('data-target-id');*/
/* */
/*         Sfjs.toggle(target, imgs[0], imgs[1]);*/
/* */
/*         return false;*/
/*     }*/
/*     //]]></script>*/
/* {% endblock %}*/
/* */

<?php

/* MBHBaseBundle::navbar.html.twig */
class __TwigTemplate_e448cce4a64893bd5fe6df2f16f2aa2d67061c7c9ab8c3adb82e8e28fef2df2f extends MBH\Bundle\BaseBundle\Twig\Template
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
        $__internal_2c86dd0df9960b93ee0bbba3645bc6fe9773d624a137e3fbe12da1fe324ad534 = $this->env->getExtension("native_profiler");
        $__internal_2c86dd0df9960b93ee0bbba3645bc6fe9773d624a137e3fbe12da1fe324ad534->enter($__internal_2c86dd0df9960b93ee0bbba3645bc6fe9773d624a137e3fbe12da1fe324ad534_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "MBHBaseBundle::navbar.html.twig"));

        // line 1
        echo "<nav class=\"navbar navbar-static-top\" role=\"navigation\">
    <!-- Sidebar toggle button-->
    <a href=\"#\" class=\"sidebar-toggle\" data-toggle=\"offcanvas\" role=\"button\">
        <span class=\"sr-only\">Свернуть меню</span>
    </a>
    <!-- Navbar Right Menu -->
    <div class=\"navbar-custom-menu\">
        <ul class=\"nav navbar-nav\">

            ";
        // line 10
        $context["cash"] = $this->env->getExtension('mbh_twig_extension')->cashDocuments();
        // line 11
        echo "
            ";
        // line 12
        if ($this->getAttribute((isset($context["cash"]) ? $context["cash"] : $this->getContext($context, "cash")), "count", array(), "array")) {
            // line 13
            echo "            <!-- Cash docs notifications: begin -->
            <li class=\"dropdown messages-menu\">
                <a href=\"#\" class=\"dropdown-toggle\" data-toggle=\"dropdown\">
                    <i class=\"";
            // line 16
            echo twig_escape_filter($this->env, $this->getAttribute($this->env->getExtension('mbh_twig_extension')->currency(), "icon", array(), "array"), "html", null, true);
            echo "\"></i> Касса
                    <span class=\"label label-danger\">";
            // line 17
            echo twig_escape_filter($this->env, twig_number_format_filter($this->env, $this->getAttribute((isset($context["cash"]) ? $context["cash"] : $this->getContext($context, "cash")), "total", array(), "array"), 2), "html", null, true);
            echo "</span>
                    &nbsp;<i class=\"fa fa-caret-down\"></i>
                </a>
                <ul class=\"dropdown-menu\">
                    <li>
                        <ul class=\"menu\">
                            ";
            // line 23
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable($this->getAttribute((isset($context["cash"]) ? $context["cash"] : $this->getContext($context, "cash")), "docs", array(), "array"));
            foreach ($context['_seq'] as $context["_key"] => $context["doc"]) {
                // line 24
                echo "                                <li>
                                    <a href=\"";
                // line 25
                echo twig_escape_filter($this->env, (($this->env->getExtension('security')->isGranted("ROLE_CASH_EDIT")) ? ($this->env->getExtension('routing')->getPath("cash_edit", array("id" => $this->getAttribute($context["doc"], "id", array())))) : ("#")), "html", null, true);
                echo "\">
                                    <small>
                                        Документ #";
                // line 27
                echo twig_escape_filter($this->env, $this->getAttribute($context["doc"], "number", array()), "html", null, true);
                echo ", сумма: ";
                echo twig_escape_filter($this->env, twig_number_format_filter($this->env, $this->getAttribute($context["doc"], "total", array())), "html", null, true);
                echo " ";
                echo twig_escape_filter($this->env, $this->getAttribute($this->env->getExtension('mbh_twig_extension')->currency(), "text", array(), "array"), "html", null, true);
                echo "<br>
                                        ";
                // line 28
                echo twig_escape_filter($this->env, (($this->getAttribute($context["doc"], "payer", array())) ? ($this->getAttribute($this->getAttribute($context["doc"], "payer", array()), "shortName", array())) : (null)), "html", null, true);
                echo " не подтвержден
                                    </small>
                                    </a>
                                </li>
                            ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['doc'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 33
            echo "                        </ul>
                    </li>
                    ";
            // line 35
            if ($this->env->getExtension('security')->isGranted("ROLE_CASH_VIEW")) {
                // line 36
                echo "                    <li class=\"footer\">
                        <a href=\"";
                // line 37
                echo $this->env->getExtension('routing')->getPath("cash");
                echo "?user=";
                echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute((isset($context["app"]) ? $context["app"] : $this->getContext($context, "app")), "user", array()), "username", array()), "html", null, true);
                echo "\">Перейти в кассу</a>
                    </li>
                    ";
            }
            // line 40
            echo "                </ul>
            </li>
            <!-- Cash docs notifications: end -->
            ";
        }
        // line 44
        echo "

            ";
        // line 46
        if ((($this->getAttribute((isset($context["app"]) ? $context["app"] : $this->getContext($context, "app")), "user", array()) && $this->getAttribute($this->getAttribute((isset($context["app"]) ? $context["app"] : $this->getContext($context, "app")), "user", array()), "isEnabledWorkShift", array())) && $this->env->getExtension('mbh_twig_extension')->currentWorkShift())) {
            // line 47
            echo "                <li class=\"dropdown notifications-menu\" id=\"work-shift-menu\">
                    <a href=\"#\" class=\"dropdown-toggle\" data-toggle=\"dropdown\" aria-expanded=\"true\">
                        <i class=\"fa fa-clock-o\"></i>&nbsp;Смена <span class=\"label label-info\">";
            // line 49
            echo twig_escape_filter($this->env, $this->getAttribute($this->env->getExtension('mbh_twig_extension')->currentWorkShift(), "getPastHours", array()), "html", null, true);
            echo " ч.</span>
                        &nbsp;<i class=\"fa fa-caret-down\"></i>
                    </a>
                    <ul class=\"dropdown-menu\">
                        <li class=\"footer\">
                            <a id=\"work-shift-lock\" href=\"";
            // line 54
            echo $this->env->getExtension('routing')->getPath("work_shift_lock");
            echo "\">Заблокировать смену</a>
                        </li>
                    </ul>
                </li>
            ";
        }
        // line 59
        echo "

            <!-- Menu selected hotel: begin -->
            ";
        // line 62
        if (($this->env->getExtension('mbh_hotel_selector_extension')->getSelectedHotel() && $this->env->getExtension('security')->isGranted("ROLE_HOTEL_VIEW"))) {
            // line 63
            echo "
                <li class=\"dropdown notifications-menu\">
                    <a href=\"#\" class=\"dropdown-toggle\" data-toggle=\"dropdown\" aria-expanded=\"true\">
                        <i class=\"fa fa-home\"></i>&nbsp;";
            // line 66
            echo twig_escape_filter($this->env, $this->getAttribute($this->env->getExtension('mbh_hotel_selector_extension')->getSelectedHotel(), "name", array()), "html", null, true);
            echo "
                        &nbsp;<i class=\"fa fa-caret-down\"></i>
                    </a>
                    <ul class=\"dropdown-menu\">
                        <li class=\"header\">Выберите отель: </li>
                        <li>
                            <ul class=\"menu\">
                                ";
            // line 73
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable($this->env->getExtension('mbh_hotel_selector_extension')->getHotels());
            foreach ($context['_seq'] as $context["_key"] => $context["hotel"]) {
                // line 74
                echo "                                    <li>
                                        ";
                // line 75
                if (array_key_exists("title_url", $context)) {
                    $context["hotel_url"] = (isset($context["title_url"]) ? $context["title_url"] : $this->getContext($context, "title_url"));
                } else {
                    $context["hotel_url"] = null;
                }
                // line 76
                echo "                                        <a href=\"";
                echo twig_escape_filter($this->env, $this->env->getExtension('routing')->getPath("hotel_select", array("id" => $this->getAttribute($context["hotel"], "id", array()), "url" => (isset($context["hotel_url"]) ? $context["hotel_url"] : $this->getContext($context, "hotel_url")))), "html", null, true);
                echo "\">
                                            <i class=\"fa fa-home\"></i>&nbsp;";
                // line 77
                echo twig_escape_filter($this->env, $this->getAttribute($context["hotel"], "name", array()), "html", null, true);
                echo "
                                        </a>
                                    </li>
                                ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['hotel'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 81
            echo "                            </ul>
                        </li>
                        ";
            // line 83
            if ($this->env->getExtension('security')->isGranted("ROLE_ADMIN")) {
                // line 84
                echo "                            <li class=\"footer\">
                                <a href=\"";
                // line 85
                echo $this->env->getExtension('routing')->getPath("hotel");
                echo "\"><i class=\"fa fa-home\"></i>Список отелей</a>
                            </li>
                        ";
            }
            // line 88
            echo "                    </ul>
                </li>
            ";
        }
        // line 91
        echo "            <!-- Menu selected hotel: end -->

            <!-- User Account Menu -->
            <li class=\"dropdown user user-menu\">
                <!-- Menu Toggle Button -->
                <a href=\"#\" class=\"dropdown-toggle\" data-toggle=\"dropdown\">
                    <!-- The user image in the navbar-->
                    <img src=\"";
        // line 98
        echo twig_escape_filter($this->env, $this->env->getExtension('ornicar_gravatar')->getUrl($this->getAttribute($this->getAttribute((isset($context["app"]) ? $context["app"] : $this->getContext($context, "app")), "user", array()), "email", array())), "html", null, true);
        echo "\" class=\"user-image\" alt=\"";
        echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute((isset($context["app"]) ? $context["app"] : $this->getContext($context, "app")), "user", array()), "fullName", array(0 => true), "method"), "html", null, true);
        echo "\">
                    <!-- hidden-xs hides the username on small devices so only the image appears. -->
                    <span class=\"hidden-xs\">";
        // line 100
        echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute((isset($context["app"]) ? $context["app"] : $this->getContext($context, "app")), "user", array()), "fullName", array(0 => true), "method"), "html", null, true);
        echo "&nbsp;<i class=\"fa fa-caret-down\"></i></span>

                </a>
                <ul class=\"dropdown-menu\">
                    <!-- The user image in the menu -->
                    <li class=\"user-header\">
                        <img src=\"";
        // line 106
        echo twig_escape_filter($this->env, $this->env->getExtension('ornicar_gravatar')->getUrl($this->getAttribute($this->getAttribute((isset($context["app"]) ? $context["app"] : $this->getContext($context, "app")), "user", array()), "email", array())), "html", null, true);
        echo "\" class=\"img-circle\" alt=\"";
        echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute((isset($context["app"]) ? $context["app"] : $this->getContext($context, "app")), "user", array()), "fullName", array(0 => true), "method"), "html", null, true);
        echo "\">
                        <p>
                            ";
        // line 108
        echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute((isset($context["app"]) ? $context["app"] : $this->getContext($context, "app")), "user", array()), "username", array()), "html", null, true);
        echo " — ";
        echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute((isset($context["app"]) ? $context["app"] : $this->getContext($context, "app")), "user", array()), "fullName", array(0 => true), "method"), "html", null, true);
        echo "
                            <small>
                                Вошел ";
        // line 110
        echo $this->env->getExtension('mbh_twig_extension')->format($this->getAttribute($this->getAttribute((isset($context["app"]) ? $context["app"] : $this->getContext($context, "app")), "user", array()), "lastLogin", array()));
        echo " ";
        echo twig_escape_filter($this->env, twig_date_format_filter($this->env, $this->getAttribute($this->getAttribute((isset($context["app"]) ? $context["app"] : $this->getContext($context, "app")), "user", array()), "lastLogin", array()), "H:i"), "html", null, true);
        echo "
                                <br>v";
        // line 111
        echo twig_escape_filter($this->env, (isset($context["version"]) ? $context["version"] : $this->getContext($context, "version")), "html", null, true);
        echo "
                            </small>
                        </p>
                    </li>
                    <!-- Menu Footer-->
                    <li class=\"user-footer\">
                        ";
        // line 117
        if ($this->env->getExtension('security')->isGranted("ROLE_USER_PROFILE")) {
            // line 118
            echo "                        <div class=\"pull-left\">
                            <a href=\"";
            // line 119
            echo $this->env->getExtension('routing')->getPath("user_profile");
            echo "\" class=\"btn btn-default btn-flat\">
                                Профиль
                            </a>
                        </div>
                        ";
        }
        // line 124
        echo "                        <div class=\"pull-right\">
                            <a id=\"logout-btn\" href=\"";
        // line 125
        echo $this->env->getExtension('routing')->getPath("fos_user_security_logout");
        echo "\" class=\"btn btn-default btn-flat\">
                                <i class=\"\"></i>&nbsp;Выйти
                            </a>
                        </div>
                    </li>
                </ul>
            </li>
        </ul>
    </div>
</nav>";
        
        $__internal_2c86dd0df9960b93ee0bbba3645bc6fe9773d624a137e3fbe12da1fe324ad534->leave($__internal_2c86dd0df9960b93ee0bbba3645bc6fe9773d624a137e3fbe12da1fe324ad534_prof);

    }

    public function getTemplateName()
    {
        return "MBHBaseBundle::navbar.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  278 => 125,  275 => 124,  267 => 119,  264 => 118,  262 => 117,  253 => 111,  247 => 110,  240 => 108,  233 => 106,  224 => 100,  217 => 98,  208 => 91,  203 => 88,  197 => 85,  194 => 84,  192 => 83,  188 => 81,  178 => 77,  173 => 76,  167 => 75,  164 => 74,  160 => 73,  150 => 66,  145 => 63,  143 => 62,  138 => 59,  130 => 54,  122 => 49,  118 => 47,  116 => 46,  112 => 44,  106 => 40,  98 => 37,  95 => 36,  93 => 35,  89 => 33,  78 => 28,  70 => 27,  65 => 25,  62 => 24,  58 => 23,  49 => 17,  45 => 16,  40 => 13,  38 => 12,  35 => 11,  33 => 10,  22 => 1,);
    }
}
/* <nav class="navbar navbar-static-top" role="navigation">*/
/*     <!-- Sidebar toggle button-->*/
/*     <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">*/
/*         <span class="sr-only">Свернуть меню</span>*/
/*     </a>*/
/*     <!-- Navbar Right Menu -->*/
/*     <div class="navbar-custom-menu">*/
/*         <ul class="nav navbar-nav">*/
/* */
/*             {% set cash = user_cash() %}*/
/* */
/*             {% if cash['count'] %}*/
/*             <!-- Cash docs notifications: begin -->*/
/*             <li class="dropdown messages-menu">*/
/*                 <a href="#" class="dropdown-toggle" data-toggle="dropdown">*/
/*                     <i class="{{ currency()['icon'] }}"></i> Касса*/
/*                     <span class="label label-danger">{{ cash['total']|number_format(2) }}</span>*/
/*                     &nbsp;<i class="fa fa-caret-down"></i>*/
/*                 </a>*/
/*                 <ul class="dropdown-menu">*/
/*                     <li>*/
/*                         <ul class="menu">*/
/*                             {% for doc in cash['docs']  %}*/
/*                                 <li>*/
/*                                     <a href="{{ is_granted('ROLE_CASH_EDIT') ? path('cash_edit', {'id': doc.id}) : '#' }}">*/
/*                                     <small>*/
/*                                         Документ #{{ doc.number }}, сумма: {{ doc.total|number_format }} {{ currency()['text'] }}<br>*/
/*                                         {{ doc.payer ? doc.payer.shortName : null }} не подтвержден*/
/*                                     </small>*/
/*                                     </a>*/
/*                                 </li>*/
/*                             {% endfor %}*/
/*                         </ul>*/
/*                     </li>*/
/*                     {% if is_granted('ROLE_CASH_VIEW') %}*/
/*                     <li class="footer">*/
/*                         <a href="{{ path('cash') }}?user={{ app.user.username }}">Перейти в кассу</a>*/
/*                     </li>*/
/*                     {% endif %}*/
/*                 </ul>*/
/*             </li>*/
/*             <!-- Cash docs notifications: end -->*/
/*             {% endif %}*/
/* */
/* */
/*             {% if app.user and app.user.isEnabledWorkShift and currentWorkShift() %}*/
/*                 <li class="dropdown notifications-menu" id="work-shift-menu">*/
/*                     <a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="true">*/
/*                         <i class="fa fa-clock-o"></i>&nbsp;Смена <span class="label label-info">{{ currentWorkShift().getPastHours }} ч.</span>*/
/*                         &nbsp;<i class="fa fa-caret-down"></i>*/
/*                     </a>*/
/*                     <ul class="dropdown-menu">*/
/*                         <li class="footer">*/
/*                             <a id="work-shift-lock" href="{{ path('work_shift_lock') }}">Заблокировать смену</a>*/
/*                         </li>*/
/*                     </ul>*/
/*                 </li>*/
/*             {% endif %}*/
/* */
/* */
/*             <!-- Menu selected hotel: begin -->*/
/*             {% if selected_hotel() and is_granted('ROLE_HOTEL_VIEW') %}*/
/* */
/*                 <li class="dropdown notifications-menu">*/
/*                     <a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="true">*/
/*                         <i class="fa fa-home"></i>&nbsp;{{ selected_hotel().name }}*/
/*                         &nbsp;<i class="fa fa-caret-down"></i>*/
/*                     </a>*/
/*                     <ul class="dropdown-menu">*/
/*                         <li class="header">Выберите отель: </li>*/
/*                         <li>*/
/*                             <ul class="menu">*/
/*                                 {% for hotel in hotels() %}*/
/*                                     <li>*/
/*                                         {% if title_url is defined  %}{% set hotel_url = title_url %}{% else %}{% set hotel_url = null %}{% endif%}*/
/*                                         <a href="{{ path('hotel_select', {'id': hotel.id, 'url': hotel_url}) }}">*/
/*                                             <i class="fa fa-home"></i>&nbsp;{{ hotel.name }}*/
/*                                         </a>*/
/*                                     </li>*/
/*                                 {% endfor %}*/
/*                             </ul>*/
/*                         </li>*/
/*                         {% if is_granted('ROLE_ADMIN') %}*/
/*                             <li class="footer">*/
/*                                 <a href="{{ path('hotel') }}"><i class="fa fa-home"></i>Список отелей</a>*/
/*                             </li>*/
/*                         {% endif %}*/
/*                     </ul>*/
/*                 </li>*/
/*             {% endif %}*/
/*             <!-- Menu selected hotel: end -->*/
/* */
/*             <!-- User Account Menu -->*/
/*             <li class="dropdown user user-menu">*/
/*                 <!-- Menu Toggle Button -->*/
/*                 <a href="#" class="dropdown-toggle" data-toggle="dropdown">*/
/*                     <!-- The user image in the navbar-->*/
/*                     <img src="{{ gravatar(app.user.email) }}" class="user-image" alt="{{ app.user.fullName(true) }}">*/
/*                     <!-- hidden-xs hides the username on small devices so only the image appears. -->*/
/*                     <span class="hidden-xs">{{ app.user.fullName(true) }}&nbsp;<i class="fa fa-caret-down"></i></span>*/
/* */
/*                 </a>*/
/*                 <ul class="dropdown-menu">*/
/*                     <!-- The user image in the menu -->*/
/*                     <li class="user-header">*/
/*                         <img src="{{ gravatar(app.user.email) }}" class="img-circle" alt="{{ app.user.fullName(true) }}">*/
/*                         <p>*/
/*                             {{ app.user.username }} — {{ app.user.fullName(true) }}*/
/*                             <small>*/
/*                                 Вошел {{ app.user.lastLogin|mbh_format }} {{ app.user.lastLogin|date('H:i') }}*/
/*                                 <br>v{{ version }}*/
/*                             </small>*/
/*                         </p>*/
/*                     </li>*/
/*                     <!-- Menu Footer-->*/
/*                     <li class="user-footer">*/
/*                         {% if is_granted('ROLE_USER_PROFILE') %}*/
/*                         <div class="pull-left">*/
/*                             <a href="{{ path('user_profile') }}" class="btn btn-default btn-flat">*/
/*                                 Профиль*/
/*                             </a>*/
/*                         </div>*/
/*                         {% endif %}*/
/*                         <div class="pull-right">*/
/*                             <a id="logout-btn" href="{{ path('fos_user_security_logout') }}" class="btn btn-default btn-flat">*/
/*                                 <i class=""></i>&nbsp;Выйти*/
/*                             </a>*/
/*                         </div>*/
/*                     </li>*/
/*                 </ul>*/
/*             </li>*/
/*         </ul>*/
/*     </div>*/
/* </nav>*/

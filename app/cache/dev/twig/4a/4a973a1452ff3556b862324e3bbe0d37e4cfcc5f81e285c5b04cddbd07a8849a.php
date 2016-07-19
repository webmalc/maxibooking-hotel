<?php

/* MBHBaseBundle::meta.html.twig */
class __TwigTemplate_34ed6925a3ed18402dfdd1ff825e971803357b917e3b213d59b146bc24095576 extends MBH\Bundle\BaseBundle\Twig\Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = array(
            'meta_keywords' => array($this, 'block_meta_keywords'),
            'meta_description' => array($this, 'block_meta_description'),
            'title' => array($this, 'block_title'),
            'styles' => array($this, 'block_styles'),
            'body' => array($this, 'block_body'),
            'scripts' => array($this, 'block_scripts'),
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        $__internal_a49982b736a79224611bf6e09a5fbaa3f3ff1b64c49899507d5e560a4ad5075d = $this->env->getExtension("native_profiler");
        $__internal_a49982b736a79224611bf6e09a5fbaa3f3ff1b64c49899507d5e560a4ad5075d->enter($__internal_a49982b736a79224611bf6e09a5fbaa3f3ff1b64c49899507d5e560a4ad5075d_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "MBHBaseBundle::meta.html.twig"));

        // line 1
        ob_start();
        // line 2
        echo "    <!DOCTYPE html>
    <html lang=\"ru\">
        <head>
            <meta charset=\"utf-8\">
            <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\">
            <meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">
            <meta name=\"keywords\" content=\"";
        // line 8
        $this->displayBlock('meta_keywords', $context, $blocks);
        echo "\">
            <meta name=\"author\" content=\"webmalc\">
            <meta name=\"description\" content=\"";
        // line 10
        $this->displayBlock('meta_description', $context, $blocks);
        echo "\">
            <title>";
        // line 11
        $this->displayBlock('title', $context, $blocks);
        echo "</title>

            ";
        // line 13
        $this->displayBlock('styles', $context, $blocks);
        // line 45
        echo "        </head>

        <body class=\"hold-transition ";
        // line 47
        echo ((array_key_exists("loginPage", $context)) ? ("login-page") : ("skin-blue sidebar-mini fixed"));
        echo "\">
        <script type=\"text/javascript\">

            if (localStorage.getItem('sidebar-collapse') == 'close') {
                document.getElementsByTagName('body')[0].className+=' sidebar-collapse'
            }
        </script>

        ";
        // line 55
        $this->displayBlock('body', $context, $blocks);
        // line 56
        echo "
        ";
        // line 57
        $this->displayBlock('scripts', $context, $blocks);
        // line 127
        echo "
    </body>
</html>
";
        echo trim(preg_replace('/>\s+</', '><', ob_get_clean()));
        // line 131
        echo "
";
        
        $__internal_a49982b736a79224611bf6e09a5fbaa3f3ff1b64c49899507d5e560a4ad5075d->leave($__internal_a49982b736a79224611bf6e09a5fbaa3f3ff1b64c49899507d5e560a4ad5075d_prof);

    }

    // line 8
    public function block_meta_keywords($context, array $blocks = array())
    {
        $__internal_9b947683fb36d407b7dc311c14a1d035b8891f8517deb0fd7735df141ac26f03 = $this->env->getExtension("native_profiler");
        $__internal_9b947683fb36d407b7dc311c14a1d035b8891f8517deb0fd7735df141ac26f03->enter($__internal_9b947683fb36d407b7dc311c14a1d035b8891f8517deb0fd7735df141ac26f03_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "meta_keywords"));

        echo twig_escape_filter($this->env, (isset($context["project_title"]) ? $context["project_title"] : $this->getContext($context, "project_title")), "html", null, true);
        
        $__internal_9b947683fb36d407b7dc311c14a1d035b8891f8517deb0fd7735df141ac26f03->leave($__internal_9b947683fb36d407b7dc311c14a1d035b8891f8517deb0fd7735df141ac26f03_prof);

    }

    // line 10
    public function block_meta_description($context, array $blocks = array())
    {
        $__internal_88332b45cf79c51ad1c2fb29b6524f7acea9987f10657483b0fc7db5abc1802f = $this->env->getExtension("native_profiler");
        $__internal_88332b45cf79c51ad1c2fb29b6524f7acea9987f10657483b0fc7db5abc1802f->enter($__internal_88332b45cf79c51ad1c2fb29b6524f7acea9987f10657483b0fc7db5abc1802f_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "meta_description"));

        echo twig_escape_filter($this->env, (isset($context["project_title"]) ? $context["project_title"] : $this->getContext($context, "project_title")), "html", null, true);
        
        $__internal_88332b45cf79c51ad1c2fb29b6524f7acea9987f10657483b0fc7db5abc1802f->leave($__internal_88332b45cf79c51ad1c2fb29b6524f7acea9987f10657483b0fc7db5abc1802f_prof);

    }

    // line 11
    public function block_title($context, array $blocks = array())
    {
        $__internal_b9a6df3f17cec83ff73e6133f4fa8fd645e056f6df51f518af55d77ba2ec2164 = $this->env->getExtension("native_profiler");
        $__internal_b9a6df3f17cec83ff73e6133f4fa8fd645e056f6df51f518af55d77ba2ec2164->enter($__internal_b9a6df3f17cec83ff73e6133f4fa8fd645e056f6df51f518af55d77ba2ec2164_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "title"));

        echo twig_escape_filter($this->env, (isset($context["project_title"]) ? $context["project_title"] : $this->getContext($context, "project_title")), "html", null, true);
        if (array_key_exists("title", $context)) {
            echo ": ";
            echo twig_escape_filter($this->env, (isset($context["title"]) ? $context["title"] : $this->getContext($context, "title")), "html", null, true);
        }
        
        $__internal_b9a6df3f17cec83ff73e6133f4fa8fd645e056f6df51f518af55d77ba2ec2164->leave($__internal_b9a6df3f17cec83ff73e6133f4fa8fd645e056f6df51f518af55d77ba2ec2164_prof);

    }

    // line 13
    public function block_styles($context, array $blocks = array())
    {
        $__internal_a87cac9cf66c3abec6522396d4fe7f5cc3e26ec685b97f709ce16f2ac567bd16 = $this->env->getExtension("native_profiler");
        $__internal_a87cac9cf66c3abec6522396d4fe7f5cc3e26ec685b97f709ce16f2ac567bd16->enter($__internal_a87cac9cf66c3abec6522396d4fe7f5cc3e26ec685b97f709ce16f2ac567bd16_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "styles"));

        // line 14
        echo "
                ";
        // line 15
        if (isset($context['assetic']['debug']) && $context['assetic']['debug']) {
            // asset "c265bde_0"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('routing')->getPath("_assetic_c265bde_0") : $this->env->getExtension('asset')->getAssetUrl("css/c265bde_bootstrap.min_1.css");
            // line 33
            echo "                <link rel=\"stylesheet\" href=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : $this->getContext($context, "asset_url")), "html", null, true);
            echo "\"/>
                ";
            // asset "c265bde_1"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('routing')->getPath("_assetic_c265bde_1") : $this->env->getExtension('asset')->getAssetUrl("css/c265bde_font-awesome.min_2.css");
            echo "                <link rel=\"stylesheet\" href=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : $this->getContext($context, "asset_url")), "html", null, true);
            echo "\"/>
                ";
            // asset "c265bde_2"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('routing')->getPath("_assetic_c265bde_2") : $this->env->getExtension('asset')->getAssetUrl("css/c265bde_select2.min_3.css");
            echo "                <link rel=\"stylesheet\" href=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : $this->getContext($context, "asset_url")), "html", null, true);
            echo "\"/>
                ";
            // asset "c265bde_3"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('routing')->getPath("_assetic_c265bde_3") : $this->env->getExtension('asset')->getAssetUrl("css/c265bde_bootstrap-switch.min_4.css");
            echo "                <link rel=\"stylesheet\" href=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : $this->getContext($context, "asset_url")), "html", null, true);
            echo "\"/>
                ";
            // asset "c265bde_4"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('routing')->getPath("_assetic_c265bde_4") : $this->env->getExtension('asset')->getAssetUrl("css/c265bde_dataTables.bootstrap.min_5.css");
            echo "                <link rel=\"stylesheet\" href=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : $this->getContext($context, "asset_url")), "html", null, true);
            echo "\"/>
                ";
            // asset "c265bde_5"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('routing')->getPath("_assetic_c265bde_5") : $this->env->getExtension('asset')->getAssetUrl("css/c265bde_bootstrap-datepicker3.min_6.css");
            echo "                <link rel=\"stylesheet\" href=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : $this->getContext($context, "asset_url")), "html", null, true);
            echo "\"/>
                ";
            // asset "c265bde_6"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('routing')->getPath("_assetic_c265bde_6") : $this->env->getExtension('asset')->getAssetUrl("css/c265bde_jquery.fancybox_7.css");
            echo "                <link rel=\"stylesheet\" href=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : $this->getContext($context, "asset_url")), "html", null, true);
            echo "\"/>
                ";
            // asset "c265bde_7"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('routing')->getPath("_assetic_c265bde_7") : $this->env->getExtension('asset')->getAssetUrl("css/c265bde_jquery.bootstrap-touchspin.min_8.css");
            echo "                <link rel=\"stylesheet\" href=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : $this->getContext($context, "asset_url")), "html", null, true);
            echo "\"/>
                ";
            // asset "c265bde_8"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('routing')->getPath("_assetic_c265bde_8") : $this->env->getExtension('asset')->getAssetUrl("css/c265bde_ionicons.min_9.css");
            echo "                <link rel=\"stylesheet\" href=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : $this->getContext($context, "asset_url")), "html", null, true);
            echo "\"/>
                ";
            // asset "c265bde_9"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('routing')->getPath("_assetic_c265bde_9") : $this->env->getExtension('asset')->getAssetUrl("css/c265bde_AdminLTE.min_10.css");
            echo "                <link rel=\"stylesheet\" href=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : $this->getContext($context, "asset_url")), "html", null, true);
            echo "\"/>
                ";
            // asset "c265bde_10"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('routing')->getPath("_assetic_c265bde_10") : $this->env->getExtension('asset')->getAssetUrl("css/c265bde_skin-blue.min_11.css");
            echo "                <link rel=\"stylesheet\" href=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : $this->getContext($context, "asset_url")), "html", null, true);
            echo "\"/>
                ";
            // asset "c265bde_11"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('routing')->getPath("_assetic_c265bde_11") : $this->env->getExtension('asset')->getAssetUrl("css/c265bde_bootstrap-colorpicker.min_12.css");
            echo "                <link rel=\"stylesheet\" href=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : $this->getContext($context, "asset_url")), "html", null, true);
            echo "\"/>
                ";
            // asset "c265bde_12"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('routing')->getPath("_assetic_c265bde_12") : $this->env->getExtension('asset')->getAssetUrl("css/c265bde_bootstrap-timepicker.min_13.css");
            echo "                <link rel=\"stylesheet\" href=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : $this->getContext($context, "asset_url")), "html", null, true);
            echo "\"/>
                ";
            // asset "c265bde_13"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('routing')->getPath("_assetic_c265bde_13") : $this->env->getExtension('asset')->getAssetUrl("css/c265bde_daterangepicker_14.css");
            echo "                <link rel=\"stylesheet\" href=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : $this->getContext($context, "asset_url")), "html", null, true);
            echo "\"/>
                ";
            // asset "c265bde_14"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('routing')->getPath("_assetic_c265bde_14") : $this->env->getExtension('asset')->getAssetUrl("css/c265bde_part_15_001-fonts_1.css");
            echo "                <link rel=\"stylesheet\" href=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : $this->getContext($context, "asset_url")), "html", null, true);
            echo "\"/>
                ";
            // asset "c265bde_15"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('routing')->getPath("_assetic_c265bde_15") : $this->env->getExtension('asset')->getAssetUrl("css/c265bde_part_15_020-app_2.css");
            echo "                <link rel=\"stylesheet\" href=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : $this->getContext($context, "asset_url")), "html", null, true);
            echo "\"/>
                ";
            // asset "c265bde_16"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('routing')->getPath("_assetic_c265bde_16") : $this->env->getExtension('asset')->getAssetUrl("css/c265bde_part_15_030-navbar_3.css");
            echo "                <link rel=\"stylesheet\" href=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : $this->getContext($context, "asset_url")), "html", null, true);
            echo "\"/>
                ";
            // asset "c265bde_17"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('routing')->getPath("_assetic_c265bde_17") : $this->env->getExtension('asset')->getAssetUrl("css/c265bde_part_15_040-tables_4.css");
            echo "                <link rel=\"stylesheet\" href=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : $this->getContext($context, "asset_url")), "html", null, true);
            echo "\"/>
                ";
            // asset "c265bde_18"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('routing')->getPath("_assetic_c265bde_18") : $this->env->getExtension('asset')->getAssetUrl("css/c265bde_part_15_050-forms_5.css");
            echo "                <link rel=\"stylesheet\" href=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : $this->getContext($context, "asset_url")), "html", null, true);
            echo "\"/>
                ";
            // asset "c265bde_19"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('routing')->getPath("_assetic_c265bde_19") : $this->env->getExtension('asset')->getAssetUrl("css/c265bde_part_15_060-print_6.css");
            echo "                <link rel=\"stylesheet\" href=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : $this->getContext($context, "asset_url")), "html", null, true);
            echo "\"/>
                ";
            // asset "c265bde_20"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('routing')->getPath("_assetic_c265bde_20") : $this->env->getExtension('asset')->getAssetUrl("css/c265bde_styles_16.css");
            echo "                <link rel=\"stylesheet\" href=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : $this->getContext($context, "asset_url")), "html", null, true);
            echo "\"/>
                ";
        } else {
            // asset "c265bde"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('routing')->getPath("_assetic_c265bde") : $this->env->getExtension('asset')->getAssetUrl("css/c265bde.css");
            echo "                <link rel=\"stylesheet\" href=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : $this->getContext($context, "asset_url")), "html", null, true);
            echo "\"/>
                ";
        }
        unset($context["asset_url"]);
        // line 35
        echo "
                ";
        // line 36
        if (isset($context['assetic']['debug']) && $context['assetic']['debug']) {
            // asset "6c5c0ad_0"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('routing')->getPath("_assetic_6c5c0ad_0") : $this->env->getExtension('asset')->getAssetUrl("css/6c5c0ad_common_1.css");
            // line 41
            echo "                <link rel=\"stylesheet\" href=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : $this->getContext($context, "asset_url")), "html", null, true);
            echo "\"/>
                ";
            // asset "6c5c0ad_1"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('routing')->getPath("_assetic_6c5c0ad_1") : $this->env->getExtension('asset')->getAssetUrl("css/6c5c0ad_mixins_2.css");
            echo "                <link rel=\"stylesheet\" href=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : $this->getContext($context, "asset_url")), "html", null, true);
            echo "\"/>
                ";
            // asset "6c5c0ad_2"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('routing')->getPath("_assetic_6c5c0ad_2") : $this->env->getExtension('asset')->getAssetUrl("css/6c5c0ad_buttons.bootstrap_3.css");
            echo "                <link rel=\"stylesheet\" href=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : $this->getContext($context, "asset_url")), "html", null, true);
            echo "\"/>
                ";
        } else {
            // asset "6c5c0ad"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('routing')->getPath("_assetic_6c5c0ad") : $this->env->getExtension('asset')->getAssetUrl("css/6c5c0ad.css");
            echo "                <link rel=\"stylesheet\" href=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : $this->getContext($context, "asset_url")), "html", null, true);
            echo "\"/>
                ";
        }
        unset($context["asset_url"]);
        // line 43
        echo "                <link rel=\"stylesheet\" href=\"";
        echo twig_escape_filter($this->env, $this->env->getExtension('asset')->getAssetUrl("assets/vendor/admin-lte/plugins/iCheck/all.css"), "html", null, true);
        echo "\">
            ";
        
        $__internal_a87cac9cf66c3abec6522396d4fe7f5cc3e26ec685b97f709ce16f2ac567bd16->leave($__internal_a87cac9cf66c3abec6522396d4fe7f5cc3e26ec685b97f709ce16f2ac567bd16_prof);

    }

    // line 55
    public function block_body($context, array $blocks = array())
    {
        $__internal_89e452f1b8cf679d341d420f5b0f5ca930ecf7a239d568451093b0d8a8f90d7f = $this->env->getExtension("native_profiler");
        $__internal_89e452f1b8cf679d341d420f5b0f5ca930ecf7a239d568451093b0d8a8f90d7f->enter($__internal_89e452f1b8cf679d341d420f5b0f5ca930ecf7a239d568451093b0d8a8f90d7f_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "body"));

        
        $__internal_89e452f1b8cf679d341d420f5b0f5ca930ecf7a239d568451093b0d8a8f90d7f->leave($__internal_89e452f1b8cf679d341d420f5b0f5ca930ecf7a239d568451093b0d8a8f90d7f_prof);

    }

    // line 57
    public function block_scripts($context, array $blocks = array())
    {
        $__internal_18102bf41eeeaf7c690d89645feff84578ef5c139b83f55ccceb7439b792afa0 = $this->env->getExtension("native_profiler");
        $__internal_18102bf41eeeaf7c690d89645feff84578ef5c139b83f55ccceb7439b792afa0->enter($__internal_18102bf41eeeaf7c690d89645feff84578ef5c139b83f55ccceb7439b792afa0_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "scripts"));

        // line 58
        echo "
            <script>
                var mbh = {
                    currency: {
                        icon: \"";
        // line 62
        echo twig_escape_filter($this->env, $this->getAttribute($this->env->getExtension('mbh_twig_extension')->currency(), "icon", array()), "html", null, true);
        echo "\",
                        text: \"";
        // line 63
        echo twig_escape_filter($this->env, $this->getAttribute($this->env->getExtension('mbh_twig_extension')->currency(), "text", array()), "html", null, true);
        echo "\"
                    },
                    UTCHoursOffset: ";
        // line 65
        echo twig_escape_filter($this->env, ($this->env->getExtension('mbh_twig_extension')->timezoneOffsetGet() / 3600), "html", null, true);
        echo ",
                    utils: {}
                }
            </script>

            ";
        // line 70
        if (isset($context['assetic']['debug']) && $context['assetic']['debug']) {
            // asset "86b546c_0"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('routing')->getPath("_assetic_86b546c_0") : $this->env->getExtension('asset')->getAssetUrl("js/86b546c_jquery.min_1.js");
            // line 103
            echo "            <script type=\"text/javascript\" src=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : $this->getContext($context, "asset_url")), "html", null, true);
            echo "\"></script>
            ";
            // asset "86b546c_1"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('routing')->getPath("_assetic_86b546c_1") : $this->env->getExtension('asset')->getAssetUrl("js/86b546c_bootstrap.min_2.js");
            echo "            <script type=\"text/javascript\" src=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : $this->getContext($context, "asset_url")), "html", null, true);
            echo "\"></script>
            ";
            // asset "86b546c_2"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('routing')->getPath("_assetic_86b546c_2") : $this->env->getExtension('asset')->getAssetUrl("js/86b546c_select2.full.min_3.js");
            echo "            <script type=\"text/javascript\" src=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : $this->getContext($context, "asset_url")), "html", null, true);
            echo "\"></script>
            ";
            // asset "86b546c_3"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('routing')->getPath("_assetic_86b546c_3") : $this->env->getExtension('asset')->getAssetUrl("js/86b546c_ru_4.js");
            echo "            <script type=\"text/javascript\" src=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : $this->getContext($context, "asset_url")), "html", null, true);
            echo "\"></script>
            ";
            // asset "86b546c_4"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('routing')->getPath("_assetic_86b546c_4") : $this->env->getExtension('asset')->getAssetUrl("js/86b546c_bootstrap-switch.min_5.js");
            echo "            <script type=\"text/javascript\" src=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : $this->getContext($context, "asset_url")), "html", null, true);
            echo "\"></script>
            ";
            // asset "86b546c_5"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('routing')->getPath("_assetic_86b546c_5") : $this->env->getExtension('asset')->getAssetUrl("js/86b546c_jquery.dataTables.min_6.js");
            echo "            <script type=\"text/javascript\" src=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : $this->getContext($context, "asset_url")), "html", null, true);
            echo "\"></script>
            ";
            // asset "86b546c_6"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('routing')->getPath("_assetic_86b546c_6") : $this->env->getExtension('asset')->getAssetUrl("js/86b546c_dataTables.bootstrap.min_7.js");
            echo "            <script type=\"text/javascript\" src=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : $this->getContext($context, "asset_url")), "html", null, true);
            echo "\"></script>
            ";
            // asset "86b546c_7"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('routing')->getPath("_assetic_86b546c_7") : $this->env->getExtension('asset')->getAssetUrl("js/86b546c_bootstrap-datepicker.min_8.js");
            echo "            <script type=\"text/javascript\" src=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : $this->getContext($context, "asset_url")), "html", null, true);
            echo "\"></script>
            ";
            // asset "86b546c_8"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('routing')->getPath("_assetic_86b546c_8") : $this->env->getExtension('asset')->getAssetUrl("js/86b546c_bootstrap-datepicker.ru.min_9.js");
            echo "            <script type=\"text/javascript\" src=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : $this->getContext($context, "asset_url")), "html", null, true);
            echo "\"></script>
            ";
            // asset "86b546c_9"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('routing')->getPath("_assetic_86b546c_9") : $this->env->getExtension('asset')->getAssetUrl("js/86b546c_jquery.fancybox_10.js");
            echo "            <script type=\"text/javascript\" src=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : $this->getContext($context, "asset_url")), "html", null, true);
            echo "\"></script>
            ";
            // asset "86b546c_10"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('routing')->getPath("_assetic_86b546c_10") : $this->env->getExtension('asset')->getAssetUrl("js/86b546c_tinycolor-min_11.js");
            echo "            <script type=\"text/javascript\" src=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : $this->getContext($context, "asset_url")), "html", null, true);
            echo "\"></script>
            ";
            // asset "86b546c_11"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('routing')->getPath("_assetic_86b546c_11") : $this->env->getExtension('asset')->getAssetUrl("js/86b546c_jquery.cookie_12.js");
            echo "            <script type=\"text/javascript\" src=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : $this->getContext($context, "asset_url")), "html", null, true);
            echo "\"></script>
            ";
            // asset "86b546c_12"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('routing')->getPath("_assetic_86b546c_12") : $this->env->getExtension('asset')->getAssetUrl("js/86b546c_bootstrap-timepicker.min_13.js");
            echo "            <script type=\"text/javascript\" src=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : $this->getContext($context, "asset_url")), "html", null, true);
            echo "\"></script>
            ";
            // asset "86b546c_13"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('routing')->getPath("_assetic_86b546c_13") : $this->env->getExtension('asset')->getAssetUrl("js/86b546c_tinymce.min_14.js");
            echo "            <script type=\"text/javascript\" src=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : $this->getContext($context, "asset_url")), "html", null, true);
            echo "\"></script>
            ";
            // asset "86b546c_14"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('routing')->getPath("_assetic_86b546c_14") : $this->env->getExtension('asset')->getAssetUrl("js/86b546c_theme.min_15.js");
            echo "            <script type=\"text/javascript\" src=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : $this->getContext($context, "asset_url")), "html", null, true);
            echo "\"></script>
            ";
            // asset "86b546c_15"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('routing')->getPath("_assetic_86b546c_15") : $this->env->getExtension('asset')->getAssetUrl("js/86b546c_jquery.bootstrap-touchspin.min_16.js");
            echo "            <script type=\"text/javascript\" src=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : $this->getContext($context, "asset_url")), "html", null, true);
            echo "\"></script>
            ";
            // asset "86b546c_16"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('routing')->getPath("_assetic_86b546c_16") : $this->env->getExtension('asset')->getAssetUrl("js/86b546c_jquery.mask.min_17.js");
            echo "            <script type=\"text/javascript\" src=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : $this->getContext($context, "asset_url")), "html", null, true);
            echo "\"></script>
            ";
            // asset "86b546c_17"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('routing')->getPath("_assetic_86b546c_17") : $this->env->getExtension('asset')->getAssetUrl("js/86b546c_jquery.phoenix.min_18.js");
            echo "            <script type=\"text/javascript\" src=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : $this->getContext($context, "asset_url")), "html", null, true);
            echo "\"></script>
            ";
            // asset "86b546c_18"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('routing')->getPath("_assetic_86b546c_18") : $this->env->getExtension('asset')->getAssetUrl("js/86b546c_jquery.number.min_19.js");
            echo "            <script type=\"text/javascript\" src=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : $this->getContext($context, "asset_url")), "html", null, true);
            echo "\"></script>
            ";
            // asset "86b546c_19"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('routing')->getPath("_assetic_86b546c_19") : $this->env->getExtension('asset')->getAssetUrl("js/86b546c_bootstrap.file-input_20.js");
            echo "            <script type=\"text/javascript\" src=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : $this->getContext($context, "asset_url")), "html", null, true);
            echo "\"></script>
            ";
            // asset "86b546c_20"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('routing')->getPath("_assetic_86b546c_20") : $this->env->getExtension('asset')->getAssetUrl("js/86b546c_router_22.js");
            echo "            <script type=\"text/javascript\" src=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : $this->getContext($context, "asset_url")), "html", null, true);
            echo "\"></script>
            ";
            // asset "86b546c_21"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('routing')->getPath("_assetic_86b546c_21") : $this->env->getExtension('asset')->getAssetUrl("js/86b546c_options_23.js");
            echo "            <script type=\"text/javascript\" src=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : $this->getContext($context, "asset_url")), "html", null, true);
            echo "\"></script>
            ";
            // asset "86b546c_22"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('routing')->getPath("_assetic_86b546c_22") : $this->env->getExtension('asset')->getAssetUrl("js/86b546c_jquery.slimscroll.min_24.js");
            echo "            <script type=\"text/javascript\" src=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : $this->getContext($context, "asset_url")), "html", null, true);
            echo "\"></script>
            ";
            // asset "86b546c_23"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('routing')->getPath("_assetic_86b546c_23") : $this->env->getExtension('asset')->getAssetUrl("js/86b546c_app.min_25.js");
            echo "            <script type=\"text/javascript\" src=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : $this->getContext($context, "asset_url")), "html", null, true);
            echo "\"></script>
            ";
            // asset "86b546c_24"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('routing')->getPath("_assetic_86b546c_24") : $this->env->getExtension('asset')->getAssetUrl("js/86b546c_fastclick.min_26.js");
            echo "            <script type=\"text/javascript\" src=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : $this->getContext($context, "asset_url")), "html", null, true);
            echo "\"></script>
            ";
            // asset "86b546c_25"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('routing')->getPath("_assetic_86b546c_25") : $this->env->getExtension('asset')->getAssetUrl("js/86b546c_bootstrap-colorpicker.min_27.js");
            echo "            <script type=\"text/javascript\" src=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : $this->getContext($context, "asset_url")), "html", null, true);
            echo "\"></script>
            ";
            // asset "86b546c_26"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('routing')->getPath("_assetic_86b546c_26") : $this->env->getExtension('asset')->getAssetUrl("js/86b546c_icheck.min_28.js");
            echo "            <script type=\"text/javascript\" src=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : $this->getContext($context, "asset_url")), "html", null, true);
            echo "\"></script>
            ";
            // asset "86b546c_27"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('routing')->getPath("_assetic_86b546c_27") : $this->env->getExtension('asset')->getAssetUrl("js/86b546c_moment_29.js");
            echo "            <script type=\"text/javascript\" src=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : $this->getContext($context, "asset_url")), "html", null, true);
            echo "\"></script>
            ";
            // asset "86b546c_28"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('routing')->getPath("_assetic_86b546c_28") : $this->env->getExtension('asset')->getAssetUrl("js/86b546c_ru_30.js");
            echo "            <script type=\"text/javascript\" src=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : $this->getContext($context, "asset_url")), "html", null, true);
            echo "\"></script>
            ";
            // asset "86b546c_29"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('routing')->getPath("_assetic_86b546c_29") : $this->env->getExtension('asset')->getAssetUrl("js/86b546c_daterangepicker_31.js");
            echo "            <script type=\"text/javascript\" src=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : $this->getContext($context, "asset_url")), "html", null, true);
            echo "\"></script>
            ";
        } else {
            // asset "86b546c"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('routing')->getPath("_assetic_86b546c") : $this->env->getExtension('asset')->getAssetUrl("js/86b546c.js");
            echo "            <script type=\"text/javascript\" src=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : $this->getContext($context, "asset_url")), "html", null, true);
            echo "\"></script>
            ";
        }
        unset($context["asset_url"]);
        // line 105
        echo "
            <script src=\"https://cdn.datatables.net/buttons/1.1.2/js/dataTables.buttons.min.js\"></script>
            <script src=\"//cdn.datatables.net/buttons/1.1.2/js/buttons.flash.min.js\"></script>
            <script src=\"//cdnjs.cloudflare.com/ajax/libs/jszip/2.5.0/jszip.min.js\"></script>
            <script src=\"//cdn.rawgit.com/bpampuch/pdfmake/0.1.18/build/pdfmake.min.js\"></script>
            <script src=\"//cdn.rawgit.com/bpampuch/pdfmake/0.1.18/build/vfs_fonts.js\"></script>
            <script src=\"//cdn.datatables.net/buttons/1.1.2/js/buttons.html5.min.js\"></script>
            <script src=\"//cdn.datatables.net/buttons/1.1.2/js/buttons.print.min.js\"></script>

            ";
        // line 114
        if (isset($context['assetic']['debug']) && $context['assetic']['debug']) {
            // asset "5ae1e98_0"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('routing')->getPath("_assetic_5ae1e98_0") : $this->env->getExtension('asset')->getAssetUrl("js/5ae1e98_part_1_009-untils_1.js");
            // line 117
            echo "            <script type=\"text/javascript\" src=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : $this->getContext($context, "asset_url")), "html", null, true);
            echo "\"></script>
            ";
            // asset "5ae1e98_1"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('routing')->getPath("_assetic_5ae1e98_1") : $this->env->getExtension('asset')->getAssetUrl("js/5ae1e98_part_1_010-app_2.js");
            echo "            <script type=\"text/javascript\" src=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : $this->getContext($context, "asset_url")), "html", null, true);
            echo "\"></script>
            ";
            // asset "5ae1e98_2"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('routing')->getPath("_assetic_5ae1e98_2") : $this->env->getExtension('asset')->getAssetUrl("js/5ae1e98_part_1_020-forms_3.js");
            echo "            <script type=\"text/javascript\" src=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : $this->getContext($context, "asset_url")), "html", null, true);
            echo "\"></script>
            ";
            // asset "5ae1e98_3"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('routing')->getPath("_assetic_5ae1e98_3") : $this->env->getExtension('asset')->getAssetUrl("js/5ae1e98_part_1_030-tables_4.js");
            echo "            <script type=\"text/javascript\" src=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : $this->getContext($context, "asset_url")), "html", null, true);
            echo "\"></script>
            ";
        } else {
            // asset "5ae1e98"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('routing')->getPath("_assetic_5ae1e98") : $this->env->getExtension('asset')->getAssetUrl("js/5ae1e98.js");
            echo "            <script type=\"text/javascript\" src=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : $this->getContext($context, "asset_url")), "html", null, true);
            echo "\"></script>
            ";
        }
        unset($context["asset_url"]);
        // line 119
        echo "
            ";
        // line 120
        if (($this->getAttribute((isset($context["app"]) ? $context["app"] : $this->getContext($context, "app")), "environment", array()) == "dev")) {
            // line 121
            echo "                <script src=\"";
            echo $this->env->getExtension('routing')->getPath("fos_js_routing_js", array("callback" => "fos.Router.setData"));
            echo "\"></script>
            ";
        } else {
            // line 123
            echo "            <script src=\"";
            echo twig_escape_filter($this->env, $this->env->getExtension('asset')->getAssetUrl("js/fos_js_routes.js"), "html", null, true);
            echo "\"></script>
            ";
        }
        // line 125
        echo "
        ";
        
        $__internal_18102bf41eeeaf7c690d89645feff84578ef5c139b83f55ccceb7439b792afa0->leave($__internal_18102bf41eeeaf7c690d89645feff84578ef5c139b83f55ccceb7439b792afa0_prof);

    }

    public function getTemplateName()
    {
        return "MBHBaseBundle::meta.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  616 => 125,  610 => 123,  604 => 121,  602 => 120,  599 => 119,  567 => 117,  563 => 114,  552 => 105,  364 => 103,  360 => 70,  352 => 65,  347 => 63,  343 => 62,  337 => 58,  331 => 57,  320 => 55,  310 => 43,  284 => 41,  280 => 36,  277 => 35,  143 => 33,  139 => 15,  136 => 14,  130 => 13,  114 => 11,  102 => 10,  90 => 8,  82 => 131,  76 => 127,  74 => 57,  71 => 56,  69 => 55,  58 => 47,  54 => 45,  52 => 13,  47 => 11,  43 => 10,  38 => 8,  30 => 2,  28 => 1,);
    }
}
/* {% spaceless %}*/
/*     <!DOCTYPE html>*/
/*     <html lang="ru">*/
/*         <head>*/
/*             <meta charset="utf-8">*/
/*             <meta http-equiv="X-UA-Compatible" content="IE=edge">*/
/*             <meta name="viewport" content="width=device-width, initial-scale=1">*/
/*             <meta name="keywords" content="{% block meta_keywords %}{{project_title}}{% endblock %}">*/
/*             <meta name="author" content="webmalc">*/
/*             <meta name="description" content="{% block meta_description %}{{project_title}}{% endblock %}">*/
/*             <title>{% block title %}{{project_title}}{% if title is defined %}: {{ title }}{% endif %}{% endblock %}</title>*/
/* */
/*             {% block styles %}*/
/* */
/*                 {% stylesheets filter='cssrewrite, uglifycss'*/
/*                     'assets/vendor/admin-lte/bootstrap/css/bootstrap.min.css'*/
/*                     'assets/vendor/font-awesome/css/font-awesome.min.css'*/
/*                     'assets/vendor/select2/dist/css/select2.min.css'*/
/*                     'assets/vendor/bootstrap-switch/dist/css/bootstrap2/bootstrap-switch.min.css'*/
/*                     'assets/vendor/datatables/media/css/dataTables.bootstrap.min.css'*/
/*                     'assets/vendor/bootstrap-datepicker/dist/css/bootstrap-datepicker3.min.css'*/
/*                     'assets/vendor/fancybox/source/jquery.fancybox.css'*/
/*                     'assets/vendor/bootstrap-touchspin/dist/jquery.bootstrap-touchspin.min.css'*/
/*                     'assets/vendor/ionicons/css/ionicons.min.css'*/
/*                     'assets/vendor/admin-lte/dist/css/AdminLTE.min.css'*/
/*                     'assets/vendor/admin-lte/dist/css/skins/skin-blue.min.css'*/
/*                     'assets/vendor/admin-lte/plugins/colorpicker/bootstrap-colorpicker.min.css'*/
/*                     'assets/vendor/admin-lte/plugins/timepicker/bootstrap-timepicker.min.css'*/
/*                     'assets/vendor/bootstrap-daterangepicker/daterangepicker.css'*/
/*                     '@MBHBaseBundle/Resources/public/css/app/*'*/
/*                     '@MBHBaseBundle/Resources/public/css/mbsuperfont/styles.less'*/
/*                 %}*/
/*                 <link rel="stylesheet" href="{{ asset_url }}"/>*/
/*                 {% endstylesheets %}*/
/* */
/*                 {% stylesheets filter='cssrewrite, uglifycss, scssphp'*/
/*                     'assets/vendor/datatables-buttons/css/common.scss'*/
/*                     'assets/vendor/datatables-buttons/css/mixins.scss'*/
/*                     'assets/vendor/datatables-buttons/css/buttons.bootstrap.scss'*/
/*                 %}*/
/*                 <link rel="stylesheet" href="{{ asset_url }}"/>*/
/*                 {% endstylesheets %}*/
/*                 <link rel="stylesheet" href="{{ asset('assets/vendor/admin-lte/plugins/iCheck/all.css') }}">*/
/*             {% endblock %}*/
/*         </head>*/
/* */
/*         <body class="hold-transition {{ loginPage is defined ? 'login-page' : 'skin-blue sidebar-mini fixed' }}">*/
/*         <script type="text/javascript">*/
/* */
/*             if (localStorage.getItem('sidebar-collapse') == 'close') {*/
/*                 document.getElementsByTagName('body')[0].className+=' sidebar-collapse'*/
/*             }*/
/*         </script>*/
/* */
/*         {% block body %}{% endblock %}*/
/* */
/*         {% block scripts %}*/
/* */
/*             <script>*/
/*                 var mbh = {*/
/*                     currency: {*/
/*                         icon: "{{ currency().icon }}",*/
/*                         text: "{{ currency().text }}"*/
/*                     },*/
/*                     UTCHoursOffset: {{ mbh_timezone_offset_get() / 3600 }},*/
/*                     utils: {}*/
/*                 }*/
/*             </script>*/
/* */
/*             {% javascripts filter='uglifyjs2'*/
/*                 'assets/vendor/jquery/dist/jquery.min.js'*/
/*                 'assets/vendor/bootstrap/dist/js/bootstrap.min.js'*/
/*                 'assets/vendor/select2/dist/js/select2.full.min.js'*/
/*                 'assets/vendor/select2/dist/js/i18n/ru.js'*/
/*                 'assets/vendor/bootstrap-switch/dist/js/bootstrap-switch.min.js'*/
/*                 'assets/vendor/datatables/media/js/jquery.dataTables.min.js'*/
/*                 'assets/vendor/datatables/media/js/dataTables.bootstrap.min.js'*/
/*                 'assets/vendor/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js'*/
/*                 'assets/vendor/bootstrap-datepicker/dist/locales/bootstrap-datepicker.ru.min.js'*/
/*                 'assets/vendor/fancybox/source/jquery.fancybox.js'*/
/*                 'assets/vendor/tinycolor/dist/tinycolor-min.js'*/
/*                 'assets/vendor/jquery.cookie/jquery.cookie.js'*/
/*                 'assets/vendor/admin-lte/plugins/timepicker/bootstrap-timepicker.min.js'*/
/*                 'assets/vendor/tinymce/tinymce.min.js'*/
/*                 'assets/vendor/tinymce/themes/modern/theme.min.js'*/
/*                 'assets/vendor/bootstrap-touchspin/dist/jquery.bootstrap-touchspin.min.js'*/
/*                 'assets/vendor/jquery-mask-plugin/dist/jquery.mask.min.js'*/
/*                 'assets/vendor/phoenix/jquery.phoenix.min.js'*/
/*                 'assets/vendor/jquery-number/jquery.number.min.js'*/
/*                 'assets/vendor/bootstrap-file-input/bootstrap.file-input.js'*/
/*                 'assets/vendor/admin-lte/plugins/timepicker/bootstrap-timepicker.min.js'*/
/*                 'bundles/fosjsrouting/js/router.js'*/
/*                 '@MBHBaseBundle/Resources/public/js/lte/options.js'*/
/*                 'assets/vendor/admin-lte/plugins/slimScroll/jquery.slimscroll.min.js'*/
/*                 'assets/vendor/admin-lte/dist/js/app.min.js'*/
/*                 'assets/vendor/admin-lte/plugins/fastclick/fastclick.min.js'*/
/*                 'assets/vendor/admin-lte/plugins/colorpicker/bootstrap-colorpicker.min.js'*/
/*                 'assets/vendor/admin-lte/plugins/iCheck/icheck.min.js'*/
/*                 'assets/vendor/moment/moment.js'*/
/*                 'assets/vendor/moment/locale/ru.js'*/
/*                 'assets/vendor/bootstrap-daterangepicker/daterangepicker.js'*/
/*             %}*/
/*             <script type="text/javascript" src="{{ asset_url }}"></script>*/
/*             {% endjavascripts %}*/
/* */
/*             <script src="https://cdn.datatables.net/buttons/1.1.2/js/dataTables.buttons.min.js"></script>*/
/*             <script src="//cdn.datatables.net/buttons/1.1.2/js/buttons.flash.min.js"></script>*/
/*             <script src="//cdnjs.cloudflare.com/ajax/libs/jszip/2.5.0/jszip.min.js"></script>*/
/*             <script src="//cdn.rawgit.com/bpampuch/pdfmake/0.1.18/build/pdfmake.min.js"></script>*/
/*             <script src="//cdn.rawgit.com/bpampuch/pdfmake/0.1.18/build/vfs_fonts.js"></script>*/
/*             <script src="//cdn.datatables.net/buttons/1.1.2/js/buttons.html5.min.js"></script>*/
/*             <script src="//cdn.datatables.net/buttons/1.1.2/js/buttons.print.min.js"></script>*/
/* */
/*             {% javascripts filter='uglifyjs2'*/
/*             '@MBHBaseBundle/Resources/public/js/app/*'*/
/*             %}*/
/*             <script type="text/javascript" src="{{ asset_url }}"></script>*/
/*             {% endjavascripts %}*/
/* */
/*             {% if app.environment == 'dev' %}*/
/*                 <script src="{{ path('fos_js_routing_js', {"callback": "fos.Router.setData"}) }}"></script>*/
/*             {% else %}*/
/*             <script src="{{ asset('js/fos_js_routes.js') }}"></script>*/
/*             {% endif %}*/
/* */
/*         {% endblock %}*/
/* */
/*     </body>*/
/* </html>*/
/* {% endspaceless %}*/
/* */
/* */

<?php
/**
 * Created by PhpStorm.
 * User: mb3
 * Date: 20.03.18
 * Time: 11:01
 */

namespace MBH\Bundle\BaseBundle\Twig;


class TwigWrapInLineNode extends \Twig_Node
{
    public function __construct(\Twig_Node $body, $lineno, $tag)
    {
        parent::__construct(['body' => $body], [], $lineno, $tag);
    }

    public function compile(\Twig_Compiler $compiler)
    {
        $compiler
            ->addDebugInfo($this)
            ->write("ob_start();\n")
            ->subcompile($this->getNode('body'))
            ->write("echo str_replace([\"\r\n\",\"\n\",\"\r\"],'',ob_get_clean());\n")
        ;
    }
}
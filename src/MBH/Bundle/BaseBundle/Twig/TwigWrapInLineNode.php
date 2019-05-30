<?php

namespace MBH\Bundle\BaseBundle\Twig;

use Twig\Compiler;
use Twig\Node\Node;

class TwigWrapInLineNode extends Node
{
    public function __construct(Node $body, $lineno, $tag)
    {
        parent::__construct(['body' => $body], [], $lineno, $tag);
    }

    public function compile(Compiler $compiler)
    {
        $compiler
            ->addDebugInfo($this)
            ->write("ob_start();\n")
            ->subcompile($this->getNode('body'))
            ->write("echo str_replace([\"\r\n\",\"\n\",\"\r\"],'',ob_get_clean());\n")
        ;
    }
}

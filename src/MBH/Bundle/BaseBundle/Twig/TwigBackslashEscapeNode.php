<?php

namespace MBH\Bundle\BaseBundle\Twig;


use Twig\Compiler;
use Twig\Node\Node;

class TwigBackslashEscapeNode extends Node
{
    public function __construct(Node $body, int $lineno = 0, string $tag = null)
    {
        parent::__construct(['value' => $body], [], $lineno, $tag);
    }

    public function compile(Compiler $compiler)
    {
        $compiler
            ->addDebugInfo($this)
            ->write("ob_start();\n")
            ->subcompile($this->getNode('value'))
            ->write("echo str_replace('\\\', '&#92', ob_get_clean() );\n");
    }
}

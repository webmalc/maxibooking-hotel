<?php
/**
 * Created by PhpStorm.
 * User: mb3
 * Date: 26.03.18
 * Time: 17:54
 */

namespace Tests\Bundle\BaseBundle\Twig;


use MBH\Bundle\BaseBundle\Twig\TwigWrapInLineNode;
use Twig_Node;
use Twig_Node_Text;

class TwigWrapInLineNodeTest extends \Twig_Test_NodeTestCase
{
    private $string = <<<HTML
<div>
    <div>
        foo
    </div>
</div>
HTML;

    public function getString()
    {
        return $this->string;
    }


    public function getTests()
    {

        $body = new Twig_Node([new Twig_Node_Text($this->getString(), 1)]);
        $node = new TwigWrapInLineNode($body, 1, 'wrapinline');


        return [
            [
                $node,
                <<<EOF
// line 1
ob_start();
echo "{$this->getString()}";
echo str_replace(["\r\n","\n","\r"],'',ob_get_clean());
EOF
                ,
            ]
        ];
    }

    public function testConstructor()
    {
        $body = new Twig_Node([new Twig_Node_Text($this->getString(), 1)]);
        $node = new TwigWrapInLineNode($body, 1, 'wrapinline');

        $this->assertEquals($body, $node->getNode('body'));
    }
}
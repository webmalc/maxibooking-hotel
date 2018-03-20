<?php
/**
 * Created by PhpStorm.
 * User: mb3
 * Date: 20.03.18
 * Time: 10:59
 */

namespace MBH\Bundle\BaseBundle\Twig;

use \Twig_Token;

/**
 * Удаление переноса строк (\r\n)
 *
 * <pre>
 * {% wrapinline %}
 *      <div class="my_class">
 *          Hello World!
 *          <strong>foo</strong>
 *      </div>
 * {% endwrapinline %}
 *
 * {# output will be <div class="my_class">Hello World!<strong id="my_id">foo</strong></div> #}
 * </pre>
 */

class TwigWrapInLineTokenParser extends \Twig_TokenParser
{
    public function parse(Twig_Token $token)
    {
        $lineno = $token->getLine();

        $this->parser->getStream()->expect(Twig_Token::BLOCK_END_TYPE);
        $body = $this->parser->subparse([$this, 'decideWrapinlineEnd'], true);
        $this->parser->getStream()->expect(Twig_Token::BLOCK_END_TYPE);

        return new TwigWrapInLineNode($body, $lineno, $this->getTag());
    }

    public function decideWrapinlineEnd(Twig_Token $token)
    {
        return $token->test('endwrapinline');
    }

    public function getTag()
    {
        return 'wrapinline';
    }
}
<?php

namespace MBH\Bundle\BaseBundle\Twig;

use Twig\Token;
use Twig\TokenParser\AbstractTokenParser;

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

class TwigWrapInLineTokenParser extends AbstractTokenParser
{
    public function parse(Token $token)
    {
        $lineno = $token->getLine();

        $this->parser->getStream()->expect(Token::BLOCK_END_TYPE);
        $body = $this->parser->subparse([$this, 'decideWrapinlineEnd'], true);
        $this->parser->getStream()->expect(Token::BLOCK_END_TYPE);

        return new TwigWrapInLineNode($body, $lineno, $this->getTag());
    }

    public function decideWrapinlineEnd(Token $token)
    {
        return $token->test('endwrapinline');
    }

    public function getTag()
    {
        return 'wrapinline';
    }
}

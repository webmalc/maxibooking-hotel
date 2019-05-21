<?php

namespace MBH\Bundle\BaseBundle\Twig;

use Twig\Token;
use Twig\TokenParser\AbstractTokenParser;

class TwigBackslashEscapeTokenParser extends AbstractTokenParser
{

    /**
     * Parses a token and returns a node.
     *
     * @param Token $token
     * @return TwigBackslashEscapeNode
     *
     */
    public function parse(Token $token)
    {
        $this->parser->getStream()->expect(Token::BLOCK_END_TYPE);
        $body = $this->parser->subparse([$this, 'backslashEscapeEnd'], true);
        $this->parser->getStream()->expect(Token::BLOCK_END_TYPE);

        return new TwigBackslashEscapeNode($body, $token->getLine(), $this->getTag());
    }

    public function backslashEscapeEnd(Token $token)
    {
        return $token->test('endescapebackslash');
    }

    /**
     * Gets the tag name associated with this token parser.
     *
     * @return string The tag name
     */
    public function getTag()
    {
        return 'escapebackslash';
    }
}

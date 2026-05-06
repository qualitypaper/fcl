<?php

namespace App\Service;

use App\Enum\TokenType;
use App\Service\Node\AssigmentNode;
use App\Service\Node\BinaryOpNode;
use App\Service\Node\BlockNode;
use App\Service\Node\FormNode;
use App\Service\Node\FormStatementNode;
use App\Service\Node\KeywordNode;
use App\Service\Node\Node;
use App\Service\Node\NumberNode;
use App\Service\Node\StringNode;
use App\Service\Node\VariableNode;
use Exception;

/*
 * Parser's grammar:
 *  program     -> statement*
 *  statement   -> expression | assigment | form
 *  form      -> IDENTIFIER: INDENT (form_statement)* DEDENT
 *  form_statement -> assigment (identifier (,) )*
 *  callable    -> IDENTIFIER "(" IDENTIFIER* ")"
 *  identifier  -> assigment | keyword
 *  assignment  -> IDENTIFIER "=" expression
 *  expression  -> term ((+ | -) term)*
 *  term        -> factor ((* | /) factor)*
 *  factor      -> NUMBER | STRING | IDENTIFIER | "(" expression ")"
*/

class ASTParser
{
    private ?Token $curr;

    /**
     * @throws Exception
     */
    public function __construct(private readonly ASTLexer $lexer)
    {
        $this->curr = $this->lexer->nextToken();
    }

    /**
     * @throws Exception
     */
    public function parse(): ?array
    {
        $statements = [];

        while ($this->curr !== null) {
            if ($this->curr->type === TokenType::NEWLINE) {
                $this->eat(TokenType::NEWLINE);
                continue;
            }

            $statements[] = $this->statement();
        }

        return $statements;
    }

    /**
     * @throws Exception
     */
    public function statement(): ?Node
    {
        return match ($this->curr->type) {
            TokenType::IDENTIFIER => $this->assigment(),
            TokenType::FORM => $this->form(),
            default => $this->expression()
        };
    }

    /**
     * @throws Exception
     */
    private function assigment(): AssigmentNode
    {
        $node = new VariableNode($this->curr->value);
        $this->eat(TokenType::IDENTIFIER);

        if ($this->curr->type === TokenType::NEWLINE) {
            return new AssigmentNode($node, null);
        } elseif ($this->curr->type === TokenType::COMMA) {
            $this->eat(TokenType::COMMA);
            return new AssigmentNode($node, null);
        }

        $this->eat(TokenType::EQUALS);

        return new AssigmentNode($node, $this->expression());
    }

    /**
     * @throws Exception
     */
    private function form(): FormNode
    {
        $this->eat(TokenType::FORM);

        $node = new KeywordNode($this->curr->value);
        $this->eat(TokenType::IDENTIFIER);
        $this->eat(TokenType::COLON);

        $this->eat(TokenType::NEWLINE);
        $this->eat(TokenType::INDENT);

        $statements = [];

        while ($this->curr !== null && $this->curr->type !== TokenType::DEDENT) {
            $statements[] = $this->formStatement();
            if ($this->curr) $this->eat(TokenType::NEWLINE);
        }
        if ($this->curr !== null) $this->eat(TokenType::DEDENT);

        $block = new BlockNode($statements);

        return new FormNode($node, $block);
    }

    /**
     * @throws Exception
     */
    private function formStatement(): FormStatementNode
    {
        $node = $this->assigment();

        $statements = [];
        while ($this->curr !== null && $this->curr !== TokenType::NEWLINE) {
            $statements[] = $this->statement();
        }

        return new FormStatementNode($node, $statements);
    }

    /**
     * @throws Exception
     */
    private function expression(): ?Node
    {
        $node = $this->term();

        while ($this->curr && in_array($this->curr->type, [TokenType::PLUS, TokenType::MINUS])) {
            $type = $this->curr->type;
            $this->eat($type);
            $node = new BinaryOpNode($node, $type, $this->term());
        }

        return $node;
    }

    /**
     * @throws Exception
     */
    private function term(): ?Node
    {
        $node = $this->factor();

        while ($this->curr && ($this->curr->type == TokenType::DIV || $this->curr->type == TokenType::MUL)) {
            $type = $this->curr->type;
            $this->eat($type);
            $node = new BinaryOpNode($node, $type, $this->term());
        }

        return $node;
    }

    /**
     * @throws Exception
     */
    private function factor(): ?Node
    {
        $token = $this->curr;
        if (!$token) return null;

        switch ($token->type) {
            case TokenType::NUMBER:
                $this->eat(TokenType::NUMBER);
                return new NumberNode((int)$token->value);
            case TokenType::LPAREN:
                $this->eat(TokenType::LPAREN);
                $node = $this->expression();
                $this->eat(TokenType::RPAREN);
                return $node;
            case TokenType::STRING:
                $this->eat(TokenType::STRING);
                return new StringNode($token->value);
            case TokenType::IDENTIFIER:
                $this->eat(TokenType::IDENTIFIER);
                return new VariableNode($token->value);
            default:
                throw new Exception("Unexpected token: " . $token);
        }
    }

    /**
     * @throws Exception
     */
    private function eat(TokenType $tokenType): void
    {
        if ($this->curr !== null && $this->curr->type === $tokenType) {
            $this->curr = $this->lexer->nextToken();
        } else {
            throw new Exception("Expected " . $tokenType->name . ", but received: " . $this->curr->type->name);
        }
    }
}

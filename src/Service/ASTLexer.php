<?php

namespace App\Service;

use App\Enum\TokenType;
use Exception;
use Psr\Log\LoggerInterface;

class ASTLexer
{
    private string $sourceCode = "";
    private int $position = 0;
    private array $tokenBuffer = [];
    private array $indentStack = [0];

    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function setSourceCode(string $sourceCode): void
    {
        $this->sourceCode = $sourceCode;
        $this->position = 0;
    }

    /**
     * @throws Exception
     */
    public function nextToken(): ?Token
    {
        if (!empty($this->tokenBuffer)) {
            return array_shift($this->tokenBuffer);
        }

        while ($this->position < strlen($this->sourceCode)) {
            $char = $this->peek();

            if ($char === ' ' || $char === '\s') {
                $this->advance();
                continue;
            }
            $this->logger->info("Char token: " . $char);

            // handle new lines seperately
            if ($char === "\n" || $char === "\r\n") {
                $tokens = $this->handleNewLine();

                $this->tokenBuffer = array_merge($this->tokenBuffer, $tokens);

                return array_shift($this->tokenBuffer);
            }

            $parsedToken = match ($char) {
                "+" => new Token(TokenType::PLUS),
                "-" => new Token(TokenType::MINUS),
                "(" => new Token(TokenType::LPAREN),
                ")" => new Token(TokenType::RPAREN),
                "*" => new Token(TokenType::MUL),
                "/" => new Token(TokenType::DIV),
                ";" => new Token(TokenType::SEMICOLON),
                ":" => new Token(TokenType::COLON),
                "=" => new Token(TokenType::EQUALS),
                default => null
            };

            if ($parsedToken != null) {
                $this->logger->info("Parsed token: " . $parsedToken);
                $this->advance();
                return $parsedToken;
            }

            $parsedToken = match (true) {
                ctype_digit($char) => new Token(
                    TokenType::NUMBER,
                    $this->parseNumber(),
                ),
                ctype_alpha($char) => $this->handleIdentifiers(),
                $char === "\"" => new Token(
                    TokenType::STRING,
                    $this->parseString()
                ),
                default => null
            };

            if ($parsedToken != null) {
                $this->logger->info("Parsed token: " . $parsedToken);
                return $parsedToken;
            }

            throw new Exception("Unexpected token: " . $char);
        }

        return null;
    }

    private function handleNewLine(): array
    {
        $tokens = [];
        $tokens[] = new Token(TokenType::NEWLINE);
        $this->advance();

        $spaces = 0;

        while ($this->isInBounds() && ctype_space($this->peek())) {
            $spaces++;
            $this->advance();
        }

        $currentIndent = end($this->indentStack);

        if ($spaces > $currentIndent) {
            $this->indentStack[] = $spaces;
            $tokens[] = new Token(TokenType::INDENT);
        } elseif ($spaces < $currentIndent) {
            while ($spaces < end($this->indentStack)) {
                array_pop($this->indentStack);
                $tokens[] = new Token(TokenType::DEDENT);
            }
        }

        return $tokens;
    }

    private function parseNumber(): int
    {
        $token = "";

        while (
            $this->isInBounds() &&
            ctype_digit($this->peek())
        ) {
            $token .= $this->next();
        }
        return (int)$token;
    }

    private function parseIdentifier(): string
    {
        $token = "";
        while (
            $this->isInBounds() &&
            self::isIdentifierChar($this->peek())
        ) {
            $token .= $this->next();
        }
        return $token;
    }


    private function handleIdentifiers(): Token
    {
        $identifier = $this->parseIdentifier();

        return match ($identifier) {
            strtolower(TokenType::FORM->name) => new Token(TokenType::FORM, strtolower(TokenType::FORM->name)),
            default => new Token(
                TokenType::IDENTIFIER,
                $identifier
            ),
        };
    }

    private static function isIdentifierChar(string $char): bool
    {
        return ctype_alpha($char) || in_array($char, ["_", "-"]);
    }

    private function parseString(): string
    {
        $this->logger->info("Entered parseString()");
        $token = "";
        $this->advance();

        while ($this->isInBounds() && $this->peek() != "\"") {
            $token .= $this->next();
        }
        $this->advance();

        return $token;
    }

    private function skipToNextToken(): void
    {
        while (
            $this->isInBounds() &&
            ctype_space($this->peek())
        ) {
            $this->advance();
        }
    }

    private function next(): string
    {
        $char = $this->peek();
        $this->advance();
        return $char;
    }

    private function peek(): string
    {
        if ($this->position >= strlen($this->sourceCode)) {
            return "";
        }

        return $this->sourceCode[$this->position];
    }

    private function advance(): void
    {
        $this->position++;
    }

    private function isInBounds()
    {
        return $this->position < strlen($this->sourceCode);
    }
}

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

            $tokenType = TokenType::tryFrom($char);

            if ($tokenType != null) {
                $parsedToken = new Token($tokenType);
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
                $this->logger->info("Parsed token: $parsedToken");
                return $parsedToken;
            }

            throw new Exception("Unexpected token: $char");
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
        $tokenBytes = [];

        while (
            $this->isInBounds() &&
            ctype_digit($this->peek())
        ) {
            $tokenBytes[] = $this->next();
        }

        $number = 0;
        $powerOfTen = 1;

        for ($i = 0; $i < count($tokenBytes); $i++) {
            $number += $powerOfTen * $tokenBytes[$i];
            $powerOfTen *= 10;
        }

        return $number;
    }

    private function parseIdentifier(): string
    {
        $tokenBytes = [];

        while (
            $this->isInBounds() &&
            self::isIdentifierChar($this->peek())
        ) {
            $tokenBytes[] = $this->next();
        }

        return implode("", $tokenBytes);
    }


    private function handleIdentifiers(): Token
    {
        $identifier = $this->parseIdentifier();

        $tokenType = TokenType::tryFrom($identifier);

        if ($tokenType !== null) {
            if ($tokenType === TokenType::FORM) {
                return new Token(TokenType::FORM, $identifier);
            } elseif ($tokenType->isNumberKeyword() || $tokenType->isBooleanKeyword()) {
                return new Token($tokenType, $identifier);
            }
        }


        return new Token(TokenType::IDENTIFIER, $identifier);
    }

    private static function isIdentifierChar(string $char): bool
    {
        return ctype_alpha($char) || in_array($char, ["_", "-"]);
    }

    private function parseString(): string
    {
        $this->logger->info("Entered parseString()");
        $tokenBytes = [];
        $this->advance();

        while ($this->isInBounds() && $this->peek() != "\"") {
            $tokenBytes[] = $this->next();
        }
        $this->advance();

        return implode("", $tokenBytes);
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

    private function isInBounds(): bool
    {
        return $this->position < strlen($this->sourceCode);
    }
}

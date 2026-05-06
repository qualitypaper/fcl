<?php

namespace App\Enum;

enum TokenType: string
{
    case NEWLINE = 'NEWLINE';
    case INDENT = 'INDENT';
    case DEDENT = 'DEDENT';

    case NUMBER = 'number';
    case STRING = 'string';
    case BOOLEAN = 'boolean';

    case IDENTIFIER = 'identifier';
    case FORM = 'form';

    case MAX_SIZE = 'max-size';
    case REQUIRED = 'required';

    case LPAREN = '(';
    case RPAREN = ')';
    case PLUS = '+';
    case MINUS = '-';
    case EQUALS = '=';
    case MUL = '*';
    case DIV = '/';
    case SEMICOLON = ';';
    case COLON = ':';
    case COMMA = ',';

    public const array KEYWORDS = [
        self::MAX_SIZE, self::REQUIRED
    ];

    public function isKeyword() {
        return in_array($this, self::KEYWORDS);
    }
}

<?php

namespace App\Enum;

enum TokenType: string
{
    case NEWLINE='NEWLINE';
    case INDENT='INDENT';
    case DEDENT='DEDENT';

    case NUMBER = 'NUMBER';
    case STRING = 'STRING';
    case IDENTIFIER = 'IDENTIFIER';
    case FORM = 'FORM';

    case LPAREN = '(';
    case RPAREN = ')';
    case PLUS = '+';
    case MINUS = '-';
    case EQUALS = '=';
    case MUL = '*';
    case DIV = '/';
    case SEMICOLON = ';';
    case COLON = ':';
}

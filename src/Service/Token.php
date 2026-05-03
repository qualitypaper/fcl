<?php

namespace App\Service;

use App\Enum\TokenType;

class Token
{
    public function __construct(
        public TokenType $type,
        public mixed  $value = null,
    )
    {
    }

    public function __toString(): string
    {
        if ($this->value == null)
            return "Type: " . $this->type->name;
        else
            return "Type: " . $this->type->name . ", Value: " . $this->value;
    }
}

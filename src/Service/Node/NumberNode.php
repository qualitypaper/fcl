<?php

namespace App\Service\Node;

class NumberNode extends Node
{
    public function __construct(public int $value)
    {
    }

    public function __toString(): string
    {
        return $this->value;
    }
}

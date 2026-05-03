<?php

namespace App\Service\Node;

use App\Service\Node\Node;

class BlockNode extends Node
{
    public function __construct(public array $statements) {}

    public function __toString(): string
    {
        return "Statements: { " . implode("\n\t", $this->statements) . " }";
    }
}

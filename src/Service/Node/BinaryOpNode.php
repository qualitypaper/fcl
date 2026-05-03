<?php

namespace App\Service\Node;

use App\Enum\TokenType;

class BinaryOpNode extends Node
{
    public function __construct(public Node $left, public TokenType $op, public Node $right)
    {
    }

    public function __toString()
    {
        return "Left: " . $this->left . ", op: " . $this->op->name . ", right: " . $this->right;
    }
}

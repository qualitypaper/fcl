<?php

namespace App\Service\Node;

use App\Service\Node\Node;

class AssigmentNode extends Node
{
    public function __construct(public VariableNode $variable, public Node $expression)
    {

    }

    public function __toString()
    {
        return $this->variable . " with value: " . $this->expression;
    }
}

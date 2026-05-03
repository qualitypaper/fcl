<?php

namespace App\Service\Node;

use App\Service\Node\Node;

class VariableNode extends Node
{
    public function __construct(public string $variable) {

    }

    public function __toString() {
        return "Variable: " . $this->variable;
    }
}

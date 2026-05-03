<?php

namespace App\Service\Node;

use App\Service\Node\Node;

class StringNode extends Node
{
    public function __construct(public string $value) {

    }

    public function __toString() {
        return "String: " . $this->value;
    }
}

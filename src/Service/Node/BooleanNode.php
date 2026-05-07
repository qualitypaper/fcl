<?php

namespace App\Service\Node;

use App\Service\Node\Node;

class BooleanNode extends Node
{

    public function __construct(public bool $value) {

    }

    public function __toString() {
        return "Boolean: " . $this->value;
    }
}

<?php

namespace App\Service\Node;

use App\Service\Node\Node;

class FormNode extends Node
{
    /*
     * @param array<Node> statements
     */
    public function __construct(public VariableNode $variable, public BlockNode $block)
    {
    }

    public function __toString()
    {
        return $this->variable . $this->block;
    }
}

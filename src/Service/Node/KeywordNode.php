<?php

namespace App\Service\Node;

use App\Service\Node\Node;

class KeywordNode extends Node
{
    public function __construct(public string $keyword)
    {

    }

    public function __toString()
    {
        return "Keyword: $this->keyword";
    }
}

<?php

namespace App\Service\Node;

use App\Service\Node\Node;

class FormStatementNode extends Node
{
    /**
     * @param array<KeywordNode | AssigmentNode> $statements
     */
    public function __construct(public AssigmentNode | KeywordNode $assigment, public array $statements) {

    }

    public function __toString() {
        return "";
    }
}

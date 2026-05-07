<?php

namespace App\Controller;

use App\Service\ASTLexer;
use App\Service\ASTParser;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class ASTController extends AbstractController
{
    public function __construct(private readonly ASTLexer $lexer)
    {
    }

    // accepts plain text and returns a JSON response with the tokens
    /**
     * @throws \Exception
     */
    #[Route("/lexer", name: "lexer", methods: ["POST"], format: "text")]
    public function lexer(Request $request, LoggerInterface $logger): JsonResponse
    {
        $text = $request->getContent();
        $this->lexer->setSourceCode($text);
        $parser = new ASTParser($this->lexer, $logger);
        $node = $parser->parse();

        $logger->info("Node: " . implode(" , ", $node));

        return new JsonResponse($node);
    }
}

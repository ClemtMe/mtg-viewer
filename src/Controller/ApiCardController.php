<?php

namespace App\Controller;

use App\Entity\Artist;
use App\Entity\Card;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use OpenApi\Attributes as OA;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/card', name: 'api_card_')]
#[OA\Tag(name: 'Card', description: 'Routes for all about cards')]
class ApiCardController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly LoggerInterface $logger
    ) {
    }
    #[Route('/all', name: 'List all cards', methods: ['GET'])]
    #[OA\Put(description: 'Return all cards in the database')]
    #[OA\Response(response: 200, description: 'List all cards')]
    public function cardAll(Request $request): Response
    {

        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 10);

        $page = max(1, $page);
        $limit = max(1, min(100, $limit));

        $cards = $this->entityManager->getRepository(Card::class)
            ->createQueryBuilder('c')
            ->setMaxResults($limit)
            ->setFirstResult(($page - 1) * $limit)
            ->getQuery()
            ->getResult();
        $this->logger->debug('Fetch ' . count($cards) . ' cards');
        return $this->json($cards);
    }

    #[Route('/{uuid}', name: 'Show card', methods: ['GET'])]
    #[OA\Parameter(name: 'uuid', description: 'UUID of the card', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\Put(description: 'Get a card by UUID')]
    #[OA\Response(response: 200, description: 'Show card')]
    #[OA\Response(response: 404, description: 'Card not found')]
    public function cardShow(string $uuid): Response
    {
        $card = $this->entityManager->getRepository(Card::class)->findOneBy(['uuid' => $uuid]);
        if (!$card) {
            return $this->json(['error' => 'Card not found'], 404);
        }
        $this->logger->debug('Fetch card ' . $uuid);
        return $this->json($card);
    }

    #[Route('/search/{name}', name: 'Search card', methods: ['GET'])]
    #[OA\Parameter(name: 'name', description: 'Name of the card', in: 'path', required: true, schema: new OA\Schema(type: 'string'))]
    #[OA\Put(description: 'Get a card by name')]
    #[OA\Response(response: 200, description: 'Show card')]
    #[OA\Response(response: 404, description: 'Card not found')]
    public function searchCard(string $name): Response
    {
        $cards = $this->entityManager->getRepository(Card::class)
            ->createQueryBuilder('c')
            ->where('c.name LIKE :name')
            ->setParameter('name', '%' . $name . '%')
            ->setMaxResults(1)
            ->getQuery()
            ->getResult();

        if (empty($cards)) {
            return $this->json(['error' => 'Card not found'], 404);
        }
        $card = $cards[0];
        $this->logger->debug('Fetch card ' . $card->getUuid());
        return $this->json($card);
    }
}

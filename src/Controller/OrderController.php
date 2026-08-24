<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\User;
use App\Repository\LoyaltyAccountRepository;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class OrderController extends AbstractController
{
    #[Route('/api/orders', name: 'api_orders_get', methods: ['GET'])]
    public function getOrders(OrderRepository $repository): JsonResponse
    {
        $orders = $repository->findBy(
            ['user' => $this->getAuthenticatedUser()],
            ['createdAt' => 'DESC'],
        );

        return $this->json(array_map($this->serializeOrder(...), $orders));
    }

    #[Route('/api/orders', name: 'api_orders_create', methods: ['POST'])]
    public function createOrder(
        Request $request,
        ProductRepository $productRepository,
        LoyaltyAccountRepository $loyaltyRepository,
        EntityManagerInterface $entityManager,
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (!is_array($data)) {
            return $this->json(['message' => 'Le contenu JSON est invalide.'], 400);
        }

        if (!$this->isValidCardNumber($data['cardNumber'] ?? null)) {
            return $this->json(['message' => 'Le numéro de carte est invalide.'], 422);
        }

        if (!$this->isValidExpirationDate($data['expirationDate'] ?? null)) {
            return $this->json(['message' => "La date d'expiration est invalide."], 422);
        }

        $requestedItems = $data['items'] ?? null;

        if (!is_array($requestedItems) || $requestedItems === []) {
            return $this->json(['message' => 'Le panier est vide.'], 422);
        }

        $quantitiesByProduct = [];

        foreach ($requestedItems as $requestedItem) {
            if (!is_array($requestedItem)) {
                return $this->json(['message' => 'Une ligne du panier est invalide.'], 422);
            }

            $productId = $requestedItem['productId'] ?? null;
            $quantity = $requestedItem['quantity'] ?? null;

            if (!is_int($productId) || !is_int($quantity) || $quantity < 1) {
                return $this->json(['message' => 'Une ligne du panier est invalide.'], 422);
            }

            $quantitiesByProduct[$productId] = ($quantitiesByProduct[$productId] ?? 0) + $quantity;
        }

        $user = $this->getAuthenticatedUser();
        $order = (new Order())->setUser($user);
        $totalInCents = 0;

        foreach ($quantitiesByProduct as $productId => $quantity) {
            $product = $productRepository->find($productId);

            if (!$product || !$product->isAvailable()) {
                return $this->json(['message' => 'Un produit est indisponible.'], 422);
            }

            $unitPriceInCents = (int) round(((float) $product->getPrice()) * 100);
            $totalInCents += $unitPriceInCents * $quantity;

            $item = (new OrderItem())
                ->setProduct($product)
                ->setProductName((string) $product->getName())
                ->setUnitPrice($this->formatCents($unitPriceInCents))
                ->setQuantity($quantity);

            $order->addItem($item);
        }

        $order->setTotal($this->formatCents($totalInCents));
        $pointsEarned = intdiv($totalInCents, 100);
        $loyaltyAccount = $loyaltyRepository->findOneBy(['user' => $user]);

        if ($loyaltyAccount) {
            $loyaltyAccount->addPoints($pointsEarned);
        }

        $entityManager->persist($order);
        $entityManager->flush();

        return $this->json([
            ...$this->serializeOrder($order),
            'pointsEarned' => $loyaltyAccount ? $pointsEarned : 0,
        ], 201);
    }

    private function getAuthenticatedUser(): User
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        return $user;
    }

    /**
     * @return array{
     *     id: int|null,
     *     date: string,
     *     total: float,
     *     status: string,
     *     items: list<array{id: int|null, productName: string, unitPrice: float, quantity: int, subtotal: float}>
     * }
     */
    private function serializeOrder(Order $order): array
    {
        return [
            'id' => $order->getId(),
            'date' => $order->getCreatedAt()->format(\DateTimeInterface::ATOM),
            'total' => (float) $order->getTotal(),
            'status' => $order->getStatus(),
            'items' => array_values(array_map(
                static fn (OrderItem $item): array => [
                    'id' => $item->getId(),
                    'productName' => $item->getProductName(),
                    'unitPrice' => (float) $item->getUnitPrice(),
                    'quantity' => $item->getQuantity(),
                    'subtotal' => (float) $item->getUnitPrice() * $item->getQuantity(),
                ],
                $order->getItems()->toArray(),
            )),
        ];
    }

    private function formatCents(int $cents): string
    {
        return sprintf('%d.%02d', intdiv($cents, 100), $cents % 100);
    }

    private function isValidCardNumber(mixed $value): bool
    {
        if (!is_string($value)) {
            return false;
        }

        $cardNumber = preg_replace('/\s+/', '', $value);

        if (!is_string($cardNumber) || !preg_match('/^\d{13,19}$/', $cardNumber)) {
            return false;
        }

        $sum = 0;
        $shouldDouble = false;

        for ($index = strlen($cardNumber) - 1; $index >= 0; --$index) {
            $digit = (int) $cardNumber[$index];

            if ($shouldDouble) {
                $digit *= 2;
                $digit = $digit > 9 ? $digit - 9 : $digit;
            }

            $sum += $digit;
            $shouldDouble = !$shouldDouble;
        }

        return 0 === $sum % 10;
    }

    private function isValidExpirationDate(mixed $value): bool
    {
        if (!is_string($value) || !preg_match('/^(\d{4})-(\d{2})$/', $value, $matches)) {
            return false;
        }

        $year = (int) $matches[1];
        $month = (int) $matches[2];
        $currentYear = (int) date('Y');
        $currentMonth = (int) date('n');

        return $month >= 1 && $month <= 12
            && ($year > $currentYear || ($year === $currentYear && $month >= $currentMonth));
    }
}

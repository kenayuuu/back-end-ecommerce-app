<?php

$router->get('/cart', function () use ($router) {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    return 'Cart Service is running';
});

##Dummy cart data
$carts = [
    'items' => [
        [
            'id' => 1,
            'name' => 'Laptop',
            'quantity' => 1,
            'price' => 999.99
        ],
        [
            'id' => 2,
            'name' => 'Smartphone',
            'quantity' => 1,
            'price' => 499.99
        ],
        [
            'id' => 3,
            'name' => 'Tablet',
            'quantity' => 1,
            'price' => 299.99
        ]
    ],
    'total' => 1798.97
];

//get all charts
$router->get('/carts', function () use ($carts) {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    header('Content-Type: application/json');
    echo json_encode($carts);
});

//get cart by id
$router->get('/carts/{id}', function ($id) use ($carts) {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    foreach ($carts['items'] as $item) {
        if ($item['id'] == $id) {
            header('Content-Type: application/json');
            echo json_encode($item);
            return;
        }
    }
    header('Content-Type: application/json');
    http_response_code(404);
    echo json_encode(['message' => 'Item not found']);
});

//add item to cart
$router->post('/carts', function () use (&$carts) {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');

    $data = json_decode(file_get_contents('php://input'), true);
    $productId = $data['product_id'] ?? null;
    $quantity = $data['quantity'] ?? 1;

    if (!$productId) {
        header('Content-Type: application/json');
        http_response_code(400);
        echo json_encode(['message' => 'Product ID is required']);
        return;
    }

    // Fetch product details from product service
    $productServiceUrl = 'http://product-services:3000/products/' . $productId;
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => 'Content-Type: application/json',
        ]
    ]);
    $productResponse = file_get_contents($productServiceUrl, false, $context);
    if ($productResponse === false) {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode(['message' => 'Failed to fetch product details']);
        return;
    }
    $product = json_decode($productResponse, true);
    if (!$product || isset($product['message'])) {
        header('Content-Type: application/json');
        http_response_code(404);
        echo json_encode(['message' => 'Product not found']);
        return;
    }

    // Check if item already in cart
    $existingItem = null;
    foreach ($carts['items'] as &$item) {
        if ($item['id'] == $productId) {
            $existingItem = &$item;
            break;
        }
    }

    if ($existingItem) {
        $existingItem['quantity'] += $quantity;
    } else {
        $carts['items'][] = [
            'id' => $product['id'],
            'name' => $product['name'],
            'quantity' => $quantity,
            'price' => $product['price']
        ];
    }

    // Recalculate total
    $carts['total'] = 0;
    foreach ($carts['items'] as $item) {
        $carts['total'] += $item['quantity'] * $item['price'];
    }

    return response()->json(['message' => 'Item added to cart']);
});

//delete item from cart
$router->delete('/carts/{id}', function ($id) use (&$carts) {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');

    $cartId = (int) $id;
    $exists = array_filter($carts['items'], fn($c) => $c['id'] === $cartId);
    if (empty($exists)) {
        header('Content-Type: application/json');
        http_response_code(404);
        echo json_encode(['message' => 'Item not found']);
        return;
    }
    // Remove item
    $carts['items'] = array_filter($carts['items'], fn($c) => $c['id'] !== $cartId);
    // Recalculate total
    $carts['total'] = 0;
    foreach ($carts['items'] as $item) {
        $carts['total'] += $item['quantity'] * $item['price'];
    }
    header('Content-Type: application/json');
    echo json_encode(['message' => 'Item deleted successfully']);
});

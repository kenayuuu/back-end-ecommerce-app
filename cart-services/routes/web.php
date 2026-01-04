<?php
require __DIR__ . '/../vendor/autoload.php';
// pastikan sudah install mongodb/mongodb via composer

use MongoDB\Client;

// =====================
// CORS
// =====================
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// =====================
// Connect MongoDB
// =====================
$mongoUri = getenv('MONGO_URI') ?: 'mongodb://admin:admin123@mongo-db:27017/cartdb?authSource=admin';
$client = new Client($mongoUri);
$collection = $client->cartdb->carts;

// =====================
// Router sederhana
// =====================
$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];
$uri = parse_url($uri, PHP_URL_PATH);

// =====================
// OPTIONS preflight untuk CORS
// =====================
if ($method === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// =====================
// GET all cart items
// =====================
if ($method === 'GET' && $uri === '/carts') {
    $carts = iterator_to_array($collection->find());
    foreach ($carts as &$c) {
        $c['_id'] = (string) $c['_id'];
    }

    $total = array_sum(array_map(fn($c) => $c['quantity'] * $c['price'], $carts));

    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'data' => [
            'items' => $carts,
            'total' => $total
        ]
    ]);
    exit;
}

// =====================
// GET cart item by product_id
// =====================
if ($method === 'GET' && preg_match('#/carts/(\d+)#', $uri, $matches)) {
    $productId = (int)$matches[1];
    $cart = $collection->findOne(['product_id' => $productId]);

    header('Content-Type: application/json');
    if ($cart) {
        $cart['_id'] = (string) $cart['_id'];
        echo json_encode(['success' => true, 'data' => $cart]);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Item not found']);
    }
    exit;
}

// =====================
// POST add item to cart
// =====================
if ($method === 'POST' && $uri === '/carts') {
    $data = json_decode(file_get_contents('php://input'), true);
    $productId = (int)($data['product_id'] ?? 0);
    $quantity = $data['quantity'] ?? 1;

    if (!$productId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Product ID is required']);
        exit;
    }

    // cek jika item sudah ada
    $existing = $collection->findOne(['product_id' => $productId]);
    if ($existing) {
        $collection->updateOne(
            ['product_id' => $productId],
            ['$inc' => ['quantity' => $quantity]]
        );
    } else {
        // ambil data produk dari product service
        $productUrl = 'http://product-services:3000/products/' . $productId;
        $productJson = @file_get_contents($productUrl);
        if (!$productJson) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Product not found']);
            exit;
        }
        $productResponse = json_decode($productJson, true);
        if (!$productResponse || !$productResponse['success']) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Product not found']);
            exit;
        }
        $product = $productResponse['data'];

        $collection->insertOne([
            'product_id' => $product['id'],
            'name' => $product['name'],
            'price' => $product['price'],
            'quantity' => $quantity
        ]);
    }

    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Item added to cart']);
    exit;
}

// =====================
// DELETE item from cart
// =====================
if ($method === 'DELETE' && preg_match('#/carts/(\d+)#', $uri, $matches)) {
    $productId = (int)$matches[1];
    $result = $collection->deleteOne(['product_id' => $productId]);

    header('Content-Type: application/json');
    if ($result->getDeletedCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Item deleted']);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Item not found']);
    }
    exit;
}

// =====================
// Default fallback
// =====================
http_response_code(404);
echo json_encode(['success' => false, 'message' => 'Endpoint not found']);

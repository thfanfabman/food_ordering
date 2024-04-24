<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $requestData = json_decode(file_get_contents('php://input'), true);
    error_log('Received POST Data: ' . print_r($requestData, true)); // Log POST data for debugging

    if (isset($requestData['cartItems'])) {
        $_SESSION['cartItems'] = $requestData['cartItems'];
        error_log('Stored Cart Items in Session: ' . print_r($_SESSION['cartItems'], true)); // Log stored cart items
        // Redirect to order confirmation page
        header('Location: order.php');
        exit;
    } else {
        error_log('Cart Items Not Found in POST Data');
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h1>Order Confirmation</h1>

    <?php if (isset($_SESSION['cartItems']) && !empty($_SESSION['cartItems'])) : ?>
        <h2>Ordered Items:</h2>
        <ul>
            <?php foreach ($_SESSION['cartItems'] as $item) : ?>
                <li><?php echo htmlspecialchars($item['itemName']); ?> - Quantity: <?php echo $item['quantity']; ?></li>
            <?php endforeach; ?>
        </ul>
    <?php else : ?>
        <p>No items in the cart.</p>
    <?php endif; ?>

    <!-- Additional HTML content for order confirmation page -->
</body>
</html>

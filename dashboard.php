<?php
session_start();

// Check if user is not logged in, redirect to index.php
if (!isset($_SESSION['email'])) {
    header('Location: index.php');
    exit;
}

// Check if 'name' key is set in $_SESSION
if (isset($_SESSION['name'])) {
    $userName = $_SESSION['name'];
} else {
    $userName = "Guest";
}

// Database connection settings
$servername = "localhost";
$username = "root"; // Default username for XAMPP MySQL
$password = ""; // Leave password blank if not set during XAMPP setup
$dbname = "food_ordering"; // Database name (change to 'food_ordering')

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch food items from the database along with category names
$sql = "SELECT f.id, f.name, f.description, f.category_id, f.price, f.image_filename, c.category_name
        FROM food_items f
        INNER JOIN categories c ON f.category_id = c.id";
$result = $conn->query($sql);

// Initialize an empty array to store food items
$foodItems = [];

if ($result->num_rows > 0) {
    // Store food items in an array
    while ($row = $result->fetch_assoc()) {
        $foodItems[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Food Ordering Dashboard</title>
    <link rel="stylesheet" href="styles.css">
    <!-- Font Awesome CDN for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Custom CSS for positioning user name and cart icon */
        body {
            margin: 0;
            font-family: Arial, sans-serif;
        }

        .top-bar {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            background-color: #333;
            color: #fff;
            padding: 10px 20px;
            box-sizing: border-box;
            z-index: 1000;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .top-bar .user-info {
            display: flex;
            align-items: center;
        }

        .top-bar .user-info .user-name {
            font-weight: bold;
            cursor: pointer;
            color: #fff;
            text-decoration: none; /* Remove underline */
            margin-right: 20px;
        }

        .top-bar .user-info .cart-icon {
            color: #fff;
            font-size: 1.5rem;
            margin-right: 10px;
            cursor: pointer;
        }

        .dashboard-container {
            padding-top: 60px; /* Adjust spacing for fixed top bar */
            padding: 20px;
            margin-top: 40px; /* Adjust margin for content to avoid overlap with fixed top bar */
        }

        .food-item {
            border: 1px solid #ccc;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
            background-color: #f9f9f9;
        }

        /* Style for quantity input */
        .quantity-input {
            width: 60px;
            padding: 5px;
            text-align: center;
            margin-right: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        /* Style for cart overlay */
        .cart-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 2000;
        }

        .cart-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
        }

        .food-item img {
            max-width: 200px;
            height: auto;
        }
    </style>
</head>
<body>
    <!-- Fixed top bar -->
    <div class="top-bar">
        <div class="user-info">
            <a href="edit_profile.php" class="user-name"><?php echo htmlspecialchars($userName); ?></a>
            <i class="fas fa-shopping-cart cart-icon" onclick="openCart()"></i> <!-- Cart icon with click event -->
        </div>
    </div>

    <div class="dashboard-container">
        <div class="dashboard-content">
            <h2>Welcome to the Food Ordering Dashboard, <?php echo htmlspecialchars($userName); ?>!</h2>

            <h3>Available Food Items</h3>

            <div class="food-items">
                <?php foreach ($foodItems as $item) : ?>
                    <div class="food-item">
                        <h4><?php echo htmlspecialchars($item['name']); ?></h4>
                        <p>Category: <?php echo htmlspecialchars($item['category_name']); ?></p>
                        <p>Price: $<?php echo number_format($item['price'], 2); ?></p>
                        <p>Description: <?php echo htmlspecialchars($item['description']); ?></p>
                        <img class="food-item-image" src="images/<?php echo htmlspecialchars($item['image_filename']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                        <form onsubmit="addToCart(event, <?php echo $item['id']; ?>, '<?php echo htmlspecialchars($item['name']); ?>')">
                            <input type="number" name="quantity" class="quantity-input" value="1" min="1" max="10" required>
                            <button type="submit">Add to Cart</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Cart overlay -->
    <div class="cart-overlay" id="cartOverlay" onclick="closeCart()">
        <div class="cart-container">
            <h2>Your Cart</h2>
            <div id="cartItems"></div>
            <button onclick="closeCart()">Close</button> <!-- Close button for cart overlay -->
        </div>
    </div>

    <script>
        let cartItems = []; // Array to store cart items

        function addToCart(event, itemId, itemName) {
            event.preventDefault(); // Prevent form submission

            const quantity = parseInt(event.target.elements.quantity.value); // Get quantity from form

            // Find item in cart
            const itemIndex = cartItems.findIndex(item => item.itemId === itemId);

            if (itemIndex !== -1) {
                // Update quantity if item already exists in cart
                cartItems[itemIndex].quantity += quantity;
            } else {
                // Add new item to cart
                cartItems.push({ itemId, itemName, quantity });
            }

            updateCartDisplay(); // Update cart display

            // Notify user that item was added to cart
            showNotification(`Added ${quantity} ${quantity > 1 ? 'items' : 'item'} to cart`);
        }

        function updateCartDisplay() {
            const cartItemsElement = document.getElementById('cartItems');

            // Clear previous cart items
            cartItemsElement.innerHTML = '';

            // Display cart items
            cartItems.forEach(item => {
                const itemElement = document.createElement('div');
                itemElement.textContent = `${item.itemName} - Quantity: ${item.quantity}`;
                cartItemsElement.appendChild(itemElement);
            });
        }

        function openCart() {
            updateCartDisplay(); // Update cart display
            document.getElementById('cartOverlay').style.display = 'flex'; // Display cart overlay
        }

        function closeCart() {
            document.getElementById('cartOverlay').style.display = 'none'; // Hide cart overlay
        }
        function showNotification(message) {
            // Create notification element
            const notificationElement = document.createElement('div');
            notificationElement.textContent = message;
            notificationElement.style.position = 'fixed';
            notificationElement.style.top = '40px';
            notificationElement.style.left = '20px';
            notificationElement.style.padding = '10px';
            notificationElement.style.backgroundColor = '#4CAF50';
            notificationElement.style.color = '#fff';
            notificationElement.style.borderRadius = '5px';
            notificationElement.style.zIndex = '3000';

            // Append notification element to body
            document.body.appendChild(notificationElement);

            // Automatically remove notification after 3 seconds
            setTimeout(() => {
                document.body.removeChild(notificationElement);
            }, 3000);
        }
    </script>
</body>
</html>

<?php
// Close database connection
$conn->close();
?>

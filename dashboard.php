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
    <style>/* Styles for sidebar */
    /* Styles for sidebar */
        .cart-sidebar {
            position: fixed;
            top: 40px; /* Position below the top bar */
            bottom: 0; /* Stretch sidebar to the bottom of the viewport */
            right: -300px; /* Start with sidebar hidden */
            width: 300px;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
            transition: right 0.3s ease; /* Smooth transition for sidebar animation */
            z-index: 900; /* Ensure sidebar is above the overlay */
            overflow-y: auto; /* Enable vertical scrolling if needed */
        }

        .cart-sidebar-content {
            padding: 20px;
            height: 100%; /* Fill the entire height of the sidebar container */
        }

        /* Overlay styles */
        .cart-overlay {
            position: fixed;
            top: 60px; /* Position below the top bar */
            bottom: 0; /* Stretch overlay to the bottom of the viewport */
            left: 0;
            width: 100%;
            background-color: rgba(0, 0, 0, 0.5); /* Semi-transparent black overlay */
            display: none;
            z-index: 800; /* Ensure overlay is below sidebar */
        }

        .overlay-active {
            display: block; /* Display overlay when sidebar is open */
        }


        /* Additional styles for top bar and content */
        .top-bar {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            background-color: #333;
            color: #fff;
            padding: 20px 20px;
            box-sizing: border-box;
            z-index: 1000;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 18px;
        }

        .top-bar .user-info {
            display: flex;
            align-items: center;
        }

        .top-bar .user-info .user-name {
            font-weight: bold;
            cursor: pointer;
            color: #fff;
            text-decoration: none;
            margin-right: 20px;
        }

        .top-bar .cart-info {
            display: flex;
            align-items: center;
        }

        .top-bar .cart-info #totalItems {
            margin-right: 20px;
        }

        .dashboard-container {
            padding-top: 80px; /* Adjust spacing for fixed top bar and sidebar */
            padding: 20px;
            margin-top: 40px; /* Adjust margin for content to avoid overlap with fixed elements */
        }

        .food-items {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-start; /* Start items from the left */
            gap: 20px; /* Spacing between items */
        }

        .food-item {
            width: calc(25% - 20px); /* Initially show 4 items per row */
            margin-bottom: 20px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #f9f9f9;
            box-sizing: border-box; /* Include padding in width calculation */
        }

        @media (max-width: 1200px) {
            .food-item {
                width: calc(33.33% - 20px); /* Show 3 items per row on medium screens */
            }
        }

        @media (max-width: 768px) {
            .food-item {
                width: calc(50% - 20px); /* Show 2 items per row on smaller screens */
            }
        }

        @media (max-width: 480px) {
            .food-item {
                width: 100%; /* Show 1 item per row on narrow screens */
            }
        }

        .food-item img {
            max-width: 100%;
            height: auto;
        }
    </style>
</head>
<body>
    <!-- Fixed top bar -->
    <div class="top-bar">
        <div class="user-info">
            <a href="edit_profile.php" class="user-name"><?php echo htmlspecialchars($userName); ?></a>
        </div>
        <div class="cart-info">
            <span id="totalItems">Total Items in Cart: 0</span> <!-- Display total items count -->
            <i class="fas fa-shopping-cart cart-icon" onclick="toggleCartSidebar()"></i> <!-- Cart icon with click event -->
        </div>
    </div>

    <!-- Sidebar for cart items -->
    <div class="cart-sidebar" id="cartSidebar">
        <div class="cart-sidebar-content">
            <h3>Your Cart</h3>
            <div id="cartItemsContainer"></div>
            <button onclick="closeCartSidebar()">Close</button> <!-- Button to close sidebar -->
        </div>
    </div>

    <!-- Overlay for dimming effect -->
    <div class="cart-overlay" id="cartOverlay" onclick="closeCartSidebar()"></div>

    <div class="dashboard-container">
        <div class="dashboard-content">
            <h2>Welcome to the [insert restaurant name], <?php echo htmlspecialchars($userName); ?>!</h2>

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

    <script>
        window.addEventListener('resize', adjustFoodItemWidth);

        function adjustFoodItemWidth() {
        const containerWidth = document.querySelector('.food-items').offsetWidth;
        const numItemsPerRow = Math.floor(containerWidth / 300); // Adjust item width based on desired item width (e.g., 300px)

        const foodItems = document.querySelectorAll('.food-item');
        foodItems.forEach(item => {
            item.style.width = `calc(${100 / numItemsPerRow}% - 20px)`;
        });
        }

        // Call the function initially and on window resize
        adjustFoodItemWidth();

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
            toggleCartSidebar(); // Open cart sidebar

            // Notify user that item was added to cart
            showNotification(`Added ${quantity} ${quantity > 1 ? 'items' : 'item'} to cart`);
        }

        function updateCartDisplay() {
            const cartItemsContainer = document.getElementById('cartItemsContainer');
            cartItemsContainer.innerHTML = ''; // Clear previous items

            // Display cart items
            cartItems.forEach(item => {
                const itemElement = document.createElement('div');
                itemElement.textContent = `${item.itemName} - Quantity: ${item.quantity}`;
                cartItemsContainer.appendChild(itemElement);
            });

            // Update total items count
            document.getElementById('totalItems').textContent = `Total Items in Cart: ${cartItems.length}`;
        }

        function toggleCartSidebar() {
            const cartSidebar = document.getElementById('cartSidebar');
            const cartOverlay = document.getElementById('cartOverlay');

            // Toggle sidebar visibility
            if (cartSidebar.style.right === '0px') {
                cartSidebar.style.right = '-300px'; // Hide sidebar
                cartOverlay.style.display = 'none'; // Hide overlay
            } else {
                cartSidebar.style.right = '0px'; // Show sidebar
                cartOverlay.style.display = 'block'; // Show overlay
            }
        }

        function closeCartSidebar() {
            const cartSidebar = document.getElementById('cartSidebar');
            const cartOverlay = document.getElementById('cartOverlay');

            cartSidebar.style.right = '-300px'; // Hide sidebar
            cartOverlay.style.display = 'none'; // Hide overlay
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

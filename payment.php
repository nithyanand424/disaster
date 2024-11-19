<?php
session_start();
include 'db.php'; // Include your database connection

// Check if necessary data is passed via GET method
if (isset($_GET['amount']) && isset($_GET['showtime_id']) && isset($_GET['seats'])) {
    $amount = floatval($_GET['amount']);
    $showtime_id = intval($_GET['showtime_id']);
    $selected_seats = explode(',', $_GET['seats']); // Convert comma-separated seats to an array
    $user_id = $_SESSION['user_id']; // Assuming the user is logged in

    // Fetch movie details to display (optional)
    $sql_movie = "SELECT * FROM Movies WHERE movie_id = ?";
    $stmt_movie = $pdo->prepare($sql_movie);
    $stmt_movie->execute([$showtime_id]);
    $movie = $stmt_movie->fetch();

    // Display booking confirmation
    echo '<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Booking Confirmation</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                background-color: #f4f4f4;
                margin: 0;
                padding: 0;
            }
            .container {
                max-width: 600px;
                margin: 50px auto;
                padding: 20px;
                background: white;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
                border-radius: 8px;
            }
            h1 {
                margin-bottom: 20px;
            }
            h3 {
                margin-top: 20px;
            }
            p {
                margin: 10px 0;
            }
            ul {
                margin: 10px 0;
                padding-left: 20px;
            }
            .btn {
                display: inline-block;
                margin-top: 20px;
                padding: 10px 20px;
                color: white;
                background-color: #28a745;
                border: none;
                border-radius: 5px;
                text-decoration: none;
                text-align: center;
            }
            .btn.cancel {
                background-color: #dc3545;
            }
            .btn:hover {
                opacity: 0.9;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>Booking Confirmation</h1>';
    
    if ($movie) {
        echo "<p><strong>Movie:</strong> " . htmlspecialchars($movie['title']) . "</p>";
    } else {
        echo "<p>Movie not found.</p>";
    }

    echo "<p><strong>Showtime ID:</strong> " . htmlspecialchars($showtime_id) . "</p>";
    echo "<p><strong>Selected Seats:</strong> " . htmlspecialchars(implode(', ', $selected_seats)) . "</p>";
    echo "<p><strong>Ticket Rate:</strong> $" . number_format($amount, 2) . "</p>";

    // Display payment options
    echo "<h3>Payment Options:</h3>";
    echo "<ul>";
    echo "<li>Credit Card</li>";
    echo "<li>Debit Card</li>";
    echo "<li>PayPal</li>";
    echo "<li>Bank Transfer</li>";
    echo "</ul>";

    // Proceed and Cancel buttons
    echo "<form method='POST' action='process_payment.php'>";
    echo "<input type='hidden' name='amount' value='" . htmlspecialchars($amount) . "'>";
    echo "<input type='hidden' name='showtime_id' value='" . htmlspecialchars($showtime_id) . "'>";
    echo "<input type='hidden' name='seats' value='" . htmlspecialchars(implode(',', $selected_seats)) . "'>";
    echo "<button type='submit' class='btn'>Proceed to Payment</button>";
    echo "<a href='index.php'><button type='button' class='btn cancel'>Cancel</button></a>"; // Redirect to home page or wherever you'd like
    echo "</form>";
    echo '</div></body></html>';
} else {
    echo "<div class='container'>Required data not provided!</div>";
    exit;
}
?>

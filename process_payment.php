<?php
session_start();
require 'db.php'; // Include the database connection file

// Check if booking_id is set in the session
if (!isset($_SESSION['booking_id'])) {
    echo "Booking ID is missing.";
    exit();
}

// Get user ID and booking ID from session
$user_id = $_SESSION['user_id']; // Assuming user_id is stored in session
$booking_id = $_SESSION['booking_id']; // Get booking ID from session

// Fetch seat numbers for this booking
$sql = "SELECT seat_number FROM bookings WHERE booking_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$booking_id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if ($row) {
    $seat_numbers = explode(",", $row['seat_number']); // Convert comma-separated seat numbers to an array

    // Prepare placeholders for seat numbers
    $placeholders = implode(',', array_fill(0, count($seat_numbers), '?'));

    // Fetch prices for the selected seat numbers
    $sql = "SELECT price FROM ticketrates WHERE seat_number IN ($placeholders)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($seat_numbers);
    $seat_prices = $stmt->fetchAll(PDO::FETCH_COLUMN); // Fetch prices as an array

    // Calculate total price based on selected seats
    $total_price = array_sum($seat_prices); // Sum up the prices of the selected seats

    // Insert payment details into the 'payments' table
    $payment_method = 'Credit Card'; // Set the payment method (adjust as necessary)
    $payment_status = 'Completed'; // Set payment status

    $sql = "INSERT INTO payments (user_id, booking_id, amount, payment_method, payment_status, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id, $booking_id, $total_price, $payment_method, $payment_status]);

    // Redirect to ticket confirmation page with booking ID in the URL
    header("Location: ticket_confirmation.php?booking_id=" . $booking_id);
    exit();
} else {
    echo "No booking found for the provided booking ID.";
}
?>

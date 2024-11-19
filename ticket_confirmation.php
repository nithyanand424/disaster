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

// Fetch seat numbers and related details for this booking
$sql = "
    SELECT 
        s.seat_number,
        tr.seat_type,
        tr.price,
        p.payment_method,
        p.payment_status,
        p.amount AS total_amount,
        p.created_at
    FROM bookings b
    JOIN seats s ON b.showtime_id = s.showtime_id -- Ensure this relationship is valid
    JOIN ticketrates tr ON tr.showtime_id = b.showtime_id AND s.showtime_id = tr.showtime_id -- Match seat_type with ticketrates
    JOIN payments p ON b.booking_id = p.booking_id
    WHERE b.booking_id = ?
";

$stmt = $pdo->prepare($sql);
$stmt->execute([$booking_id]);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Check if any results were returned
if (!$results) {
    echo "No booking found for the provided booking ID.";
    exit();
}

// Display the ticket confirmation
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket Confirmation</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center">Ticket Confirmation</h2>
        <table class="table table-bordered">
            <thead class="thead-dark">
                <tr>
                    <th>Seat Number</th>
                    <th>Seat Type</th>
                    <th>Seat Price</th>
                    <th>Payment Method</th>
                    <th>Payment Status</th>
                    <th>Total Amount</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($results as $row): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['seat_number']); ?></td>
                        <td><?php echo htmlspecialchars($row['seat_type']); ?></td>
                        <td><?php echo htmlspecialchars($row['price']); ?></td>
                        <td><?php echo htmlspecialchars($row['payment_method']); ?></td>
                        <td><?php echo htmlspecialchars($row['payment_status']); ?></td>
                        <td><?php echo htmlspecialchars($row['total_amount']); ?></td>
                        <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <h4 class="text-right">Total: <?php echo htmlspecialchars($results[0]['total_amount']); ?></h4>
    </div>
</body>
</html>

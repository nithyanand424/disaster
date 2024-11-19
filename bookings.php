<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['movie_id']) && isset($_POST['showtime_id']) && isset($_POST['seats'])) {
        $movie_id = intval($_POST['movie_id']);
        $showtime_id = intval($_POST['showtime_id']);
        $seats = $_POST['seats']; // Array of selected seats

        // Assuming you have a users table and the user is logged in
        $user_id = $_SESSION['user_id']; // Get the logged-in user's ID

        // Insert booking into the database (bookings table)
        foreach ($seats as $seat) {
            $sql = "INSERT INTO Bookings (user_id, movie_id, showtime_id, seat_number) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iiis", $user_id, $movie_id, $showtime_id, $seat);
            $stmt->execute();
        }

        // Optionally, redirect to a confirmation page or display a success message
        echo "Booking successful! You have booked the following seats: " . implode(', ', $seats);
    } else {
        echo "Please select a movie, showtime, and seats to book.";
    }
} else {
    echo "Invalid request method.";
}

$conn->close();
?>

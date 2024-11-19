<?php
session_start();
include 'db.php';

// Check if movie_id and showtime_id are provided
if (isset($_GET['movie_id']) && isset($_GET['showtime_id'])) {
    $movie_id = $_GET['movie_id'];
    $showtime_id = $_GET['showtime_id'];

    // Fetch available seats for the selected showtime (no movie_id in the Seats table)
    $sql = "SELECT * FROM Seats WHERE showtime_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$showtime_id]);
    $seats = $stmt->fetchAll();
} else {
    echo "Movie or showtime not selected!";
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Handle seat selection and proceed to payment
    if (!isset($_POST['selected_seats'])) {
        echo "No seats selected!";
        exit;
    }

    $selected_seats = explode(',', $_POST['selected_seats']);

    // Fetch ticket rate for the selected showtime
    $sql_price = "SELECT price FROM TicketRates WHERE showtime_id = ? LIMIT 1";
    $stmt_price = $pdo->prepare($sql_price);
    $stmt_price->execute([$showtime_id]);
    $ticket_rate = $stmt_price->fetchColumn();

    // Calculate total amount
    $total_amount = count($selected_seats) * $ticket_rate;

    // Redirect to payment page with total amount
    header("Location: payment.php?amount=$total_amount&movie_id=$movie_id&showtime_id=$showtime_id&seats=" . urlencode($_POST['selected_seats']));
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Seats</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Seat grid and styling */
        .seat-grid {
            display: grid;
            grid-template-columns: repeat(10, 50px);
            gap: 10px;
            justify-content: center;
        }
        .seat {
            width: 50px;
            height: 50px;
            border: 1px solid #ccc;
            text-align: center;
            line-height: 50px;
            cursor: pointer;
        }
        .seat.available { background-color: #28a745; }
        .seat.booked { background-color: #dc3545; cursor: not-allowed; }
        .seat.selected { background-color: #ffc107; }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            let selectedSeats = [];

            document.querySelectorAll('.seat.available').forEach(seat => {
                seat.addEventListener('click', function () {
                    const seatNumber = this.dataset.seatNumber;
                    if (selectedSeats.includes(seatNumber)) {
                        selectedSeats = selectedSeats.filter(num => num !== seatNumber);
                        this.classList.remove('selected');
                    } else {
                        selectedSeats.push(seatNumber);
                        this.classList.add('selected');
                    }

                    document.getElementById('selectedSeats').value = selectedSeats.join(',');
                    document.getElementById('seatCount').innerText = selectedSeats.length;
                });
            });
        });
    </script>
</head>
<body>
    <h1>Select Your Seats</h1>

    <div class="seat-grid">
        <?php foreach ($seats as $seat): ?>
            <div class="seat <?php echo ($seat['status'] === 'booked') ? 'booked' : 'available'; ?>"
                 data-seat-number="<?php echo htmlspecialchars($seat['seat_number']); ?>">
                <?php echo htmlspecialchars($seat['seat_number']); ?>
            </div>
        <?php endforeach; ?>
    </div>

    <p>Selected Seats: <span id="seatCount">0</span></p>

    <form method="POST" action="seats.php?movie_id=<?php echo $movie_id; ?>&showtime_id=<?php echo $showtime_id; ?>">
        <input type="hidden" id="selectedSeats" name="selected_seats" value="">
        <button type="submit">Proceed to Payment</button>
    </form>
</body>
</html>

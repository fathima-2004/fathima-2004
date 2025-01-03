<?php
session_start();

$conn = mysqli_connect("localhost", "root", "", "driver_booking_system");
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

$driver_id = $_GET['driver_id'] ?? null;
$driver_info = null;
$error_message = '';

if ($driver_id) {
    $driver_sql = "SELECT * FROM drivers WHERE driver_id = '$driver_id'";
    $driver_result = mysqli_query($conn, $driver_sql);
    if ($driver_result && mysqli_num_rows($driver_result) > 0) {
        $driver_info = mysqli_fetch_assoc($driver_result);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $driver_info) {
    $booking_date = $_POST['booking_date'];
    $booking_time = $_POST['booking_time'];
    $from_place = $_POST['from_place'];
    $destination = $_POST['destination'];

    // Check if the driver is already booked at the specified date and time
    $booking_check_sql = "SELECT * FROM bookings WHERE driver_id = '$driver_id' AND booking_date = '$booking_date'";
    $booking_check_result = mysqli_query($conn, $booking_check_sql);

    if (mysqli_num_rows($booking_check_result) > 0) {
        $error_message = "The driver is already booked on this date and time. Please select another date or time.";
    } else {
        // Insert booking details into the requests table
        $user_id = $_SESSION['user_id'];  // Assuming user is logged in and user ID is stored in the session
        $price = 0;  // Set price to 0 initially
        $status = 'pending';  // Set the initial status to 'pending'

        $insert_sql = "INSERT INTO requests (user_id, driver_id, from_place, destination, booking_date, booking_time, price, status) 
                       VALUES ('$user_id', '$driver_id', '$from_place', '$destination', '$booking_date', '$booking_time', '$price', '$status')";

        if (mysqli_query($conn, $insert_sql)) {
            // Show alert and stay on the same page
            echo "<script>
                    alert('Booking request sent successfully. Status is pending.');
                    window.location.href = 'userdashboard.php?driver_id=" . $driver_id . "';
                  </script>";
        } else {
            $error_message = "Error occurred while processing your booking. Please try again.";
        }
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="bookdriver.css">
    <title>Book Driver</title>
</head>
<body>
<nav class="nanbar">
    <div class="navbar">
        <h2 class="hdng">DRIVER BOOKING SYSTEM</h2>
        <div class="link">
            <a href="userdashboard.php">Home</a>
            <a href="">My bookings</a>
            <a href="view_drivers.php">View Drivers</a>
            <a href="feedback.php">Feedback</a>
            <a href="">Logout</a>
        </div>
    </div>
</nav>

<div class="container">
    <h1>Book Driver</h1>

    <?php if ($driver_info): ?>
        <h2>Driver Details</h2>
        <p><strong>Username:</strong> <?php echo htmlspecialchars($driver_info['username']); ?></p>
        <p><strong>Phone Number:</strong> <?php echo htmlspecialchars($driver_info['phno']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($driver_info['email']); ?></p>

        <h3 class="hg">Select Booking Details</h3>
        
        <?php if ($error_message): ?>
            <p style="color: red;"><?php echo htmlspecialchars($error_message); ?></p>
        <?php endif; ?>

        <form class="input" action="" method="POST">
            <input type="hidden" name="driver_id" value="<?php echo htmlspecialchars($driver_info['driver_id']); ?>">

            <label for="from_place">From:</label>
            <input type="text" name="from_place" required placeholder="Enter starting location">

            <label for="destination">Destination:</label>
            <input type="text" name="destination" required placeholder="Enter destination">

            <label for="booking_date">Date:</label>
            <input type="date" name="booking_date" required min="<?php echo date('Y-m-d'); ?>" max="<?php echo date('Y-m-d', strtotime('+1 year')); ?>">

            <label for="booking_time">Time:</label>
            <input type="time" name="booking_time" required>

            <input type="submit" value="Submit Booking">
        </form>

    <?php else: ?>
        <p>Driver not found.</p>
    <?php endif; ?>
</div>
</body>
</html>

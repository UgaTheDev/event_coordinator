<!DOCTYPE html>
<html>
<head>
    <?php
    session_start();
    ?>
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="sidebar.css">
    <link rel="stylesheet" href="optimaldisplay.css">
    <title>Optimal Day</title>
</head>
<body>
    <div class="sidebar">
        <a href="home.php">Home</a>
        <a href="eligibleevents.php">Eligible Events</a>
        <a href="registeredevents.php">Registered Events</a>
        <a href="proposeevents.html">Propose Event</a>
        <a href="optimaldisplay.php"><u>Available Times</u></a>
        <a href="api.html">Weather Forecast</a>
        <a href="logout.php">Logout</a>
    </div>
    <div class="container">
        <div class="current-entries">
            <h2>Current Entries:</h2>
            <?php
            include 'config.php';
            $currentusername = $_SESSION["username"];

            //initialises variables for each date field input
            $day1 = "";
            $day2 = "";
            $day3 = "";
            $day4 = "";
            $day5 = "";
            
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                //if variables exist, assign value from the POST request; if not, set as an empty string to avoid null errors
                $day1 = isset($_POST['day1']) ? $_POST['day1'] : '';
                $day2 = isset($_POST['day2']) ? $_POST['day2'] : '';
                $day3 = isset($_POST['day3']) ? $_POST['day3'] : '';
                $day4 = isset($_POST['day4']) ? $_POST['day4'] : '';
                $day5 = isset($_POST['day5']) ? $_POST['day5'] : '';
            }
                $conn = new mysqli($servername, $username, $password, $dbname);
                if ($conn->connect_error) {
                    die("Connection failed: " . $conn->connect_error);
                }
                
                $availabletimes = array();
                $available_sql = "SELECT available FROM users WHERE username = '$currentusername'";
                $result = $conn->query($available_sql);
            
                if ($result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    $availabletimes = json_decode($row['available'], true);
                    if ($availabletimes == null) {
                        $availabletimes = array();
                    }
                } else {
                    $availabletimes = array();
                }
            
                function addavailable($day1, $day2, $day3, $day4, $day5, $conn, &$availabletimes, $currentusername) {
                    if (!empty($day1)) {
                        $availabletimes[] = $day1;
                    }
                    if (!empty($day2)) {
                        $availabletimes[] = $day2;
                    }
                    if (!empty($day3)) {
                        $availabletimes[] = $day3;
                    }
                    if (!empty($day4)) {
                        $availabletimes[] = $day4;
                    }
                    if (!empty($day5)) {
                        $availabletimes[] = $day5;
                    }
                    $availabletimes = array_unique($availabletimes);
                    $availabletimes_json = json_encode($availabletimes);
                    $sql5 = "UPDATE users SET available = '$availabletimes_json' WHERE username = '$currentusername'";
                    $conn->query($sql5);
                }
                
            addavailable($day1, $day2, $day3, $day4, $day5, $conn, $availabletimes, $currentusername);
            
            $sql = "SELECT available FROM users WHERE username = '$currentusername'";
            $result = $conn->query($sql);
            
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $availabletimes = json_decode($row['available'], true);
                if ($availabletimes == null) {
                    $availabletimes = array();
                }
            } else {
                $availabletimes = array();
            }
            
            asort($availabletimes);
            $current_datetime = date("Y-m-d H:i:s");
            foreach ($availabletimes as $date) {
                $date_parts = explode(': ', $date);
                $output_date = end($date_parts);
                $formatted_date = date("d-m-y", strtotime($output_date));
                
                //checks if date has not already passed
                if (strtotime($output_date) >= strtotime($current_datetime)) {
                    echo $formatted_date . "<br>";
                }
            }
            
            function calculate_optimal($conn) {
                $all_available_dates = [];
                $available_dates_sql = "SELECT available FROM users";
                $result = $conn->query($available_dates_sql);
                
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $availabletimes = json_decode($row['available'], true);
                        if ($availabletimes != null) {
                            foreach ($availabletimes as $date) {
                                $date_parts = explode(': ', $date);
                                $formatted_date = end($date_parts);
                                $all_available_dates[] = $formatted_date;  //stores date in yyyy-mm-dd format
                            }
                        }
                    }
                }
                $date_counts = array_count_values($all_available_dates); //counts how many instances of each element is present in the array
                $optimal_dates = [];
                foreach ($date_counts as $date => $count) {
                    if ($count >= 3) { //stores if three or more users have the same date entered
                        $optimal_dates[] = $date;
                    }
                }
                return $optimal_dates;
            }
            ?>
        </div>
        <div class="optimal-input-container">
            <form action="optimaldisplay.php" method="post">
                <input type="date" name="day1" placeholder="Day 1"><br>
                <input type="date" name="day2" placeholder="Day 2"><br>
                <input type="date" name="day3" placeholder="Day 3"><br>
                <input type="date" name="day4" placeholder="Day 4"><br>
                <input type="date" name="day5" placeholder="Day 5"><br>
                <input type="hidden" name="username" value="<?php echo $currentusername; ?>">
                <button type="submit" name="submit">Confirm</button>
            </form>
        </div>
    </div>
    <div class="optimal-dates-container">
        <h3>Optimal Dates:</h3>
        <?php
           $optimal_dates = calculate_optimal($conn);
           if (!empty($optimal_dates)) {
               foreach ($optimal_dates as $optimal_date) {
                   //checks if the optimal date is not in the past
                   if (strtotime($optimal_date . ' 00:00:00') >= strtotime($current_datetime)) {
                       echo htmlspecialchars($optimal_date) . "<br>";
                   }
               }
           } else {
               echo "No optimal dates found.";
           }
            $_SESSION['optimal_dates'] = $optimal_dates;
        ?>
        <br>
        <button onclick="document.getElementById('id01').style.display='block'" style="width:auto;">Propose Event</button>

<div id="id01" class="modal">
    <form class="modal-content animate" action="proposeevents.php" method="post">
        <div class="modal_form_container">
            <label for="event_name"><b>Event Name</b></label>
            <input type="text" placeholder="Event Name" name="event_name" required>
            <label for="event_date"><b>Event Date</b></label>
            <input type="date" placeholder="Event Date" name="event_date" required>
            <label for="event_details"><b>Event Details</b></label>
            <input type="text" placeholder="Event Details" name="event_details" required>
            <label for="event_starttime"><b>Start Time</b></label>
            <input type="time" name="event_starttime" placeholder="Start Time" required>
            <label for="event_endtime"><b>End Time</b></label>
            <input type="time" name="event_endtime" placeholder="End Time" required>
            <input type="hidden" name="event_organiser" value="<?php echo $currentusername; ?>">
            <input type="hidden" name="completed" value="0">
            <button type="button" onclick="document.getElementById('id01').style.display='none'" class="cancelbtn">Cancel</button>
            <button type="submit" class="confirmbtn">Confirm</button>
        </div>
    </form>
</div>
    <script>
        var modal = document.getElementById('id01');
        //if the area outside the modal form is clicked, it will exit
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    </script>
</div>
</body>
</html>
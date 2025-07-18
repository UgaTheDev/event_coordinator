<?php session_start();
include 'config.php';

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

global $userID;
//stores username inputted from login process as session variable
$currentusername = $_SESSION["username"];
$registered_events_sql = "SELECT registered_events_dates FROM users WHERE id = '$userID'";
$result = $conn->query($registered_events_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="sidebar.css">
    <link rel="stylesheet" href="home.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
</head>
<body>
    <div class="sidebar">
    <a href="home.php"> <u>Home</u></a>
    <a href="eligibleevents.php">Eligible Events</a>
    <a href="registeredevents.php">Registered Events</a>
    <a href="proposeevents.html">Propose Event</a>
    <a href="optimaldisplay.php">Available Times</a>
    <a href="api.html">Weather Forecast</a>
    <a href="logout.php">Logout</a>
    </div>
    <div class="calendar">
        <div class="btn-container">
            <span class="arrow" id="prevMonth">&#60;</span>
            <h2 id="monthYear"></h2>
            <span class="arrow" id="nextMonth">&#62;</span>
        </div>
        <table id="calendarTable">
            <thead>
                <tr>
                    <th>Sun</th>
                    <th>Mon</th>
                    <th>Tue</th>
                    <th>Wed</th>
                    <th>Thu</th>
                    <th>Fri</th>
                    <th>Sat</th>
                </tr>
            </thead>
            <tbody id="calendarBody"></tbody>
        </table>
        </div>  
        <script>
function calendar() {
    const registeredEventsDatesArray = [
        <?php
        $sql = "SELECT registered_events_dates FROM users WHERE username = '$currentusername'";
        $result = $conn->query($sql);
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $registered_events_dates_list = json_decode($row['registered_events_dates'], true);
            if ($registered_events_dates_list) {
                $output = [];
                foreach ($registered_events_dates_list as $event_date) { //iterate through reach date in the list
                    $date_parts = explode('-', $event_date);
                    if (count($date_parts) === 3) {
                        $date_year = $date_parts[0];
                        $date_month = $date_parts[1];
                        $date_date = $date_parts[2];
                        $output[] = "\"$date_year $date_month $date_date\"";
                    }
                }
                echo implode(', ', $output);
            } else {
                echo '""'; //if no dates are found, use an empty string as a default
            }
        } else {
            echo '""';
        }
        ?>
    ];

    console.log(registeredEventsDatesArray); 
    //splits each date string into year, month, and date
    registeredEventsDatesArray.forEach(eventDate => {
        const dateParts = eventDate.split(' ');
        if (dateParts.length === 3) {
            const date_year = dateParts[0];
            const date_month = dateParts[1];
            const date_date = dateParts[2];
            console.log(`Year: ${date_year}, Month: ${date_month}, Date: ${date_date}`);
        }
    });

    const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    let currentDate = new Date(); //new date object
    let currentMonth = currentDate.getMonth();
    let currentYear = currentDate.getFullYear();

    function updateCalendar() {
        const calendarBody = document.getElementById('calendarBody');
        const monthYear = document.getElementById('monthYear');
        monthYear.textContent = `${months[currentMonth]} ${currentYear}`;

        const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();
        let date = 1;
        let calendarHTML = '';

        for (let i = 0; i < 6; i++) {
            calendarHTML += '<tr>';
            for (let j = 0; j < 7; j++) {
                const dayIndex = (j + 7 - new Date(currentYear, currentMonth, 1).getDay()) % 7;
                if (i === 0 && j < dayIndex) {
                    calendarHTML += '<td></td>';
                } else if (date > daysInMonth) {
                    break;
                } else {
                    let cellStyle = '';
                    //checks if each date before creating the table cell to see if it is in the list, using separate variables for year, month and date
                    registeredEventsDatesArray.forEach(eventDate => {
                        const parts = eventDate.split(' ');
                        if (parts.length === 3) {
                            const [year, month, day] = parts;
                            if (currentYear == year && currentMonth + 1 == month && date == day) {
                                cellStyle = ' style="background-color: #ddd5f3;"'; //changes the colour to this specific shade if the date is in the list
                            }
                        }
                    });
                    calendarHTML += `<td${cellStyle}>${date}</td>`;
                    date++;
                }
            }
            calendarHTML += '</tr>';
        }

        calendarBody.innerHTML = calendarHTML;
    }

    document.getElementById('prevMonth').addEventListener('click', function() {
        currentMonth = (currentMonth - 1 + 12) % 12;
        if (currentMonth === 11) {
            currentYear--;
        }
        updateCalendar();
    });

    document.getElementById('nextMonth').addEventListener('click', function() {
        currentMonth = (currentMonth + 1) % 12;
        if (currentMonth === 0) {
            currentYear++;
        }
        updateCalendar();
    });

    updateCalendar();
}

calendar();
</script>
</body>
</html>

<?php
if (!isset($_SESSION["username"])) {
    header("Location: loginhtml.html");
} else {
    echo "Login Successful! Welcome, ", $currentusername;
}
?>
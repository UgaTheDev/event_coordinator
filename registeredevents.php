<!DOCTYPE html>
<html lang="en">
<head>
    <title>Registered Events</title>
    <link rel="stylesheet" href="sidebar.css">
    <link rel="stylesheet" href="eventspages.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
</head>
<body>
    <div class="sidebar">
        <a href="home.php">Home</a>
        <a href="eligibleevents.php">Eligible Events</a>
        <a href="registeredevents.php"><u>Registered Events</u></a>
        <a href="proposeevents.html">Propose Event</a>
        <a href="optimaldisplay.php">Available Times</a>
        <a href="api.html">Weather Forecast</a>
        <a href="logout.php">Logout</a>
    </div>
    <div class="container">
        <h2>Registered Events Table</h2>
        <table>
            <tr>
                <th>Event Name</th>
                <th>Date</th>
                <th>Start Time</th>
                <th>End Time</th>
                <th>Details</th>
                <th>Action</th>
            </tr>

            <?php
            session_start();
            include 'config.php';
            date_default_timezone_set('Asia/Singapore');
            $currentusername = $_SESSION["username"];

            function check_admin($conn, $currentusername) {
                //checks whether the user is an admin
                $admin_check = "SELECT admin FROM users WHERE username = '$currentusername'";
                $admin_result = $conn->query($admin_check);
                if ($admin_result->num_rows > 0) {
                    $row = $admin_result->fetch_assoc();
                    $_SESSION["admin"] = $row["admin"] == 1 ? "True" : "False"; //Stores admin status as a session variable based on database value
                }
            }
            check_admin($conn, $currentusername);

            $conn = new mysqli($servername, $username, $password, $dbname);
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            $unregister_success = false;
            if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["unregister_event"])) {
                $eventID = $_POST["event_id"];
                $unregister_success = unregister($conn, $currentusername, $eventID);
            }

            function weather_notification($conn) {
                $notifs = [];
                $notif_dates = []; 
                
                $weather_sql = "SELECT date, rain, cold, hot FROM weather_info WHERE rain = 1 OR cold = 1 OR hot = 1";
                $weather_result = $conn->query($weather_sql);
                
                if ($weather_result->num_rows > 0) {
                    while ($row = $weather_result->fetch_assoc()) {
                        $date = $row['date'];
                        $conditions = [];
            
                        //compares database-stored weather conditions for the day, and inserts a corresponding string into the array
                        //corresponding string used for notification message
                        if ($row['rain'] == 1) {
                            $conditions[] = "rainy";
                        }
                        if ($row['cold'] == 1) {
                            $conditions[] = "cold";
                        }
                        if ($row['hot'] == 1) {
                            $conditions[] = "hot";
                        }
                        //join conditions with a comma if there are multiple
                        //2d array containing date and weather condition-related message to output for the notification
                        $notifs[] = [$date, implode(", ", $conditions)];
                        //1d array containing only the dates
                        $notif_dates[] = $date; 
                    }
                }
                
                return [$notifs, $notif_dates];
            }
            list($notif_dates, $only_dates) = weather_notification($conn);

            $registered_events_sql = "SELECT registered_events FROM users WHERE username = '$currentusername'";
            $result = $conn->query($registered_events_sql);

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $eventslist = json_decode($row['registered_events'], true);

                if (!empty($eventslist)) {
                    $eventIDs = implode(",", $eventslist);
                    
                    $event_details_sql = "SELECT event_id, event_name, event_date, event_starttime, event_endtime, event_details FROM events WHERE event_id IN ($eventIDs) AND completed = 0 ORDER BY event_date";
                    $event_result = $conn->query($event_details_sql);
                    if ($event_result->num_rows > 0) {
                        while ($eventRow = $event_result->fetch_assoc()) {
                            echo "<tr>";
                            $exclamationMark = '';
                            
                            if (in_array($eventRow["event_date"], $only_dates)) {
                                //find the corresponding weather conditions
                                foreach ($notif_dates as $notif) {
                                    if ($notif[0] === $eventRow["event_date"]) {
                                        //notification symbol, with the notification message outputted using the corresponding weather-dependent message
                                        $exclamationMark = "<span class='exclamation-mark' title='It is forecasted to be: " . $notif[1] . "'>&#9888;</span>";
                                        break;
                                    }
                                }
                            }
                            echo "<td>" . $eventRow["event_name"] . " " . $exclamationMark . "</td>";
                            echo "<td>" . $eventRow["event_date"] . "</td>";
                            echo "<td>" . $eventRow["event_starttime"] . "</td>";
                            echo "<td>" . $eventRow["event_endtime"] . "</td>";
                            echo "<td>" . $eventRow["event_details"] . "</td>";
                            echo "<td>
                                    <form method='post'>
                                        <input type='hidden' name='event_id' value='" . $eventRow["event_id"] . "'>
                                        <button class='btn-submit' type='submit' name='unregister_event'>Unregister</button>
                                    </form>
                                  </td>";
                            //add a delete button if they are an admin
                            if (isset($_SESSION["admin"]) && $_SESSION["admin"] == "True") {
                                echo "<td>
                                        <form method='post' onsubmit='return confirm_delete();'>
                                            <input type='hidden' name='delete_event_id' value='" . $eventRow["event_id"] . "'>
                                            <button class='btn-delete' type='submit' name='delete_event'>Delete</button>
                                        </form>
                                      </td>";
                            }
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6'>No registered events found.</td></tr>";
                    }
                } else {
                    echo "<tr><td colspan='6'>No events registered currently.</td></tr>";
                }
            } else {
                echo "<tr><td colspan='6'>User not found or no events registered.</td></tr>";
            }

            function delete_event($conn) {
                if (isset($_POST['delete_event']) && isset($_POST['delete_event_id'])) {
                    $delete_event_ID = $_POST['delete_event_id'];
                    $delete_sql = "DELETE FROM events WHERE event_id = '$delete_event_ID'";
                    if ($conn->query($delete_sql) === TRUE) {
                        echo "<script>alert('Event deleted successfully.');</script>";
                    } else {
                        echo "Error deleting event: " . $conn->error;
                    }
                }
            }
            
            delete_event($conn);
            function unregister($conn, $currentusername, $eventID) {
                //fetches date for the event, using its id
                $sql = "SELECT event_date FROM events WHERE event_id = $eventID";
                $event_date_result = $conn->query($sql);
                
                if ($event_date_result && $event_date_result->num_rows > 0) {
                    $eventDateRow = $event_date_result->fetch_assoc();
                    $just_unregistered_event_date = $eventDateRow['event_date'];
            
                    //fetches all the events the user has registered for, and their dates
                    $sql = "SELECT registered_events, registered_events_dates FROM users WHERE username = '$currentusername'";
                    $result = $conn->query($sql);
            
                    if ($result->num_rows > 0) {
                        $row = $result->fetch_assoc();
                        $eventslist = json_decode($row['registered_events'], true);
                        $registered_events_dates_list = json_decode($row['registered_events_dates'], true);
                        $unregister = array_search($eventID, $eventslist);
                        if ($unregister !== false) {
                            unset($eventslist[$unregister]); //unregisters the event
            
                            //remove the corresponding event date
                            if (($dateIndex = array_search($just_unregistered_event_date, $registered_events_dates_list)) !== false) {
                                unset($registered_events_dates_list[$dateIndex]);
                            }
            
                            //update events list and events date list in the database
                            $updated_events_list = json_encode(array_values($eventslist)); 
                            $updated_registered_events_dates_list = json_encode(array_values($registered_events_dates_list));
                            $updateSql = "UPDATE users SET registered_events = '$updated_events_list', registered_events_dates = '$updated_registered_events_dates_list' WHERE username = '$currentusername'";
                            if ($conn->query($updateSql) === TRUE) {
                                echo "Event unregistered successfully.";
                                return true;
                            } else {
                                echo "Error updating participation record: " . $conn->error;
                            }
                        } else {
                            echo "Event not found in registered events.";
                        }
                    } else {
                        echo "User not found.";
                    }
                } else {
                    echo "Event not found.";
                }
                return false;
            }
            $conn->close();
            ?>
        </table>
    </div>
</body>
<script>
    function confirm_delete() {
        return confirm("Are you sure you want to delete this event? This is irreversible and permanent.");
    }
    window.onload = function() {
        <?php if ($unregister_success): ?>
            window.location.href = 'registeredevents.php';
        <?php endif; ?>
    };
</script>
</html>
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
        $_SESSION["admin"] = $row["admin"] == 1 ? "True" : "False"; //stores admin status in session var
    }
}
check_admin($conn, $currentusername);
$register_success = false;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="sidebar.css?v=1.1">
    <link rel="stylesheet" href="eventspages.css?v=1.1">
    <title>Events Table</title>
</head>
<body>
    <div class="sidebar">
        <a href="home.php">Home</a>
        <a href="eligibleevents.php"><u>Eligible Events </u></a>
        <a href="registeredevents.php">Registered Events</a>
        <a href="proposeevents.html">Propose Event</a>
        <a href="optimaldisplay.php">Available Times</a>
        <a href="api.html">Weather Forecast</a>
        <a href="logout.php">Logout</a>
    </div>
    <div class="container">
        <h2>Events Table</h2>
        <table>
            <tr>
                <th>Event Name</th>
                <th>Date</th>
                <th>Start Time</th>
                <th>End Time</th>
                <th>Details</th>
                <th>Action</th>
                <?php if ($_SESSION["admin"] == "True"): ?>
                    <th>Delete</th>
                <?php endif; ?>
            </tr>

            <?php

            $current_datetime = date("Y-m-d H:i:s");
            $updatesql = "UPDATE events SET completed = 1 WHERE completed = 0 AND CONCAT(event_date, ' ', event_endtime) < '$current_datetime'";
            $conn->query($updatesql);

            $recent_update_sql = "UPDATE events SET recent = 1 WHERE recent = 0 AND creation_time > NOW() - INTERVAL 2 DAY";
            $conn->query($recent_update_sql); 

            $reset_recent_sql = "UPDATE events SET recent = 0 WHERE creation_time <= NOW() - INTERVAL 2 DAY";
            $conn->query($reset_recent_sql); //removes recent status from events created less than two days ago
            $recent_update_sql = "UPDATE events SET recent = 1 WHERE recent = 0 AND creation_time > NOW() - INTERVAL 2 DAY";
            $conn->query($recent_update_sql); //sets recent status to event created within two days

            $registered_events_sql = "SELECT registered_events FROM users WHERE username = '$currentusername'";
            $result = $conn->query($registered_events_sql);

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $eventslist = json_decode($row['registered_events'], true);
                if (!empty($eventslist)) {
                    $eventIDs = implode(",", $eventslist);
                    $sql = "SELECT event_id, event_name, event_date, event_starttime, event_endtime, event_details, recent FROM events WHERE completed = 0 AND event_id NOT IN ($eventIDs) ORDER BY event_date"; 
                } else {
                    $sql = "SELECT event_id, event_name, event_date, event_starttime, event_endtime, event_details, recent FROM events WHERE completed = 0 ORDER BY event_date"; 
                }
                $result = $conn->query($sql);
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $row["event_name"] . "</td>";
                        echo "<td>" . $row["event_date"] . "</td>";
                        echo "<td>" . $row["event_starttime"] . "</td>";
                        echo "<td>" . $row["event_endtime"] . "</td>";
                        echo "<td>" . $row["event_details"] . "</td>";
                        if ($row["recent"] == 1) {
                            echo '<td>
                                <form method="post" style="position: relative; display: inline-block;">
                                    <input type="hidden" name="event_id" value="' . $row["event_id"] . '">
                                    <button class="btn-submit" type="submit" name="register_event">
                                        Register
                                    </button>
                                    <span class="ribbon">NEW</span>
                                </form>
                            </td>';
                        } else {
                            echo '<td>
                                    <form method="post">
                                        <input type="hidden" name="event_id" value="' . $row["event_id"] . '">
                                        <button class="btn-submit" type="submit" name="register_event">Register</button>
                                    </form>
                                    </td>';
                        }

                        //add a delete button if they are an admin
                        if (isset($_SESSION["admin"]) && $_SESSION["admin"] == "True") {
                            echo "<td>
                                    <form method='post' onsubmit='return confirm_delete();'>
                                        <input type='hidden' name='delete_event_id' value='" . $row["event_id"] . "'>
                                        <button class='btn-delete' type='submit' name='delete_event'>Delete</button>
                                    </form>
                                    </td>";
                        }
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='7'>No events proposed currently.</td></tr>";
                }
            }
            
            function delete_event($conn) {
                if (isset($_POST['delete_event']) && isset($_POST['delete_event_id'])) {
                    $delete_event_ID = $_POST['delete_event_id'];
                    //sql statement to delete the event from database
                    $delete_sql = "DELETE FROM events WHERE event_id = '$delete_event_ID'";
                    if ($conn->query($delete_sql) === TRUE) {
                        echo "<script>alert('Event deleted successfully.');</script>";
                    } else {
                        echo "Error deleting event: " . $conn->error;
                    }
                }
            }
            
            delete_event($conn);
            function register_event($eventID, $conn, $currentusername) {
                $user_id_sql = "SELECT id FROM users WHERE username = '$currentusername'";
                $user_id_result = $conn->query($user_id_sql);
            
                if ($user_id_result && $user_id_result->num_rows > 0) {
                    $user_row = $user_id_result->fetch_assoc();
                    $userID = $user_row['id']; 
            
                    $event_date_sql = "SELECT event_date FROM events WHERE event_id = $eventID";
                    $event_date_sql_result = $conn->query($event_date_sql);
            
                    if ($event_date_sql_result && $event_date_sql_result->num_rows > 0) {
                        $event_date_row = $event_date_sql_result->fetch_assoc();
                        $just_registered_event_date = $event_date_row['event_date']; 
            
                        $registered_event_dates_sql = "SELECT registered_events_dates FROM users WHERE username = '$currentusername'";
                        $registered_event_dates_result = $conn->query($registered_event_dates_sql);
            
                        if ($registered_event_dates_result && $registered_event_dates_result->num_rows > 0) {
                            $row = $registered_event_dates_result->fetch_assoc();
                            $registered_events_dates_list = json_decode($row['registered_events_dates'], true); 
                            if ($registered_events_dates_list === null) {
                                $registered_events_dates_list = [];
                            }
            
                            if (!in_array($just_registered_event_date, $registered_events_dates_list)) {
                                $registered_events_dates_list[] = $just_registered_event_date; 
                            }
                            $updated_registered_events_dates_list = json_encode($registered_events_dates_list);
                            //updates the list of registered events dates for the user
                            $sql_update = "UPDATE users SET registered_events_dates = '$updated_registered_events_dates_list' WHERE username = '$currentusername'";
                            
                            if ($conn->query($sql_update) === TRUE) {
                                echo "Registered event date updated successfully.";
                            } else {
                                echo "Error updating registered event dates: " . $conn->error;
                            }
                        } else {
                            echo "No registered events found.";
                        }
                    } else {
                        echo "No event date found for event ID: $eventID.";
                    }
                    echo "<script>window.location.href = 'eligibleevents.php';</script>";
                }
            }
            
            function registeredevents($eventID, $conn, &$eventslist, $currentusername) {
                $registered_events_sql = "SELECT registered_events FROM users WHERE username = '$currentusername'";
                $result = $conn->query($registered_events_sql);
                if ($result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    $eventslist = json_decode($row['registered_events'], true);
                }
                if (!in_array($eventID, $eventslist)) {
                    $eventslist[] = $eventID;
                }
                //convert $eventslist from a php array back to a json array
                $eventslist_json = json_encode($eventslist);
                $sql5 = "UPDATE users SET registered_events = '$eventslist_json' WHERE username = '$currentusername'";
                $conn->query($sql5);
            }
            
        
            if (isset($_POST['register_event']) && isset($_POST['event_id'])) {
                $eventID = $_POST['event_id']; 
                $currentusername = $_SESSION["username"]; 
                register_event($eventID, $conn, $currentusername, $result, $row);
                registeredevents($eventID, $conn, $eventslist, $currentusername);
                }
            
            ?>
        </table>
        </div>
            <script>
                function confirm_delete() {
                    return confirm("Are you sure you want to delete this event? This is irreversible and permanent.");
                }
                window.onload = function() {
                    <?php if ($register_success): ?>
                        window.location.href = 'eligbleevents.php';
                    <?php endif; ?>
                };
            </script>
    </body>
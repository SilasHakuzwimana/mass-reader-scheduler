<?php 
// session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
include '../templates/header.php';

// Query to fetch events and reader assignments
$stmt = $conn->prepare("
    SELECT e.id, e.event_date, e.title, e.event_time, u.names, a.role 
    FROM events e
    LEFT JOIN assignments a ON e.id = a.event_id
    LEFT JOIN users u ON a.reader_id = u.id
    ORDER BY e.event_date
");
$stmt->execute();
$events_result = $stmt->get_result();
$events = [];

// Group readers by event
while ($event = $events_result->fetch_assoc()) {
    $event_id = $event['id'];

    // Format the time to be in a more readable format (removing seconds)
    $formatted_time = date("h:i A", strtotime($event['event_time']));

    if (!isset($events[$event_id])) {
        $events[$event_id] = [
            'id' => $event['id'],
            'title' => $event['title'],
            'start' => $event['event_date'],
            'time' => $formatted_time,
            'assigned_readers' => []
        ];
    }

    if ($event['names'] && $event['role']) {
        $events[$event_id]['assigned_readers'][] = [
            'names' => $event['names'],
            'role' => $event['role']
        ];
    }
}

// Convert the events array to a simple list for JavaScript usage
$events_for_js = [];
foreach ($events as $event) {
    $events_for_js[] = [
        'id' => $event['id'],
        'title' => $event['title'],
        'start' => $event['start'],
        'time' => $event['time'],
        'assigned_readers' => $event['assigned_readers']
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Mass Calendar | St. Basile</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- FullCalendar CSS -->
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/index.global.min.js'></script>

    <style>
        .fc-event {
            cursor: pointer;
        }
    </style>
</head>
<body class="bg-light">

<div class="container mt-5">
    <h2 class="text-center mb-4">Mass Calendar</h2>
    <a href="coordinator_dashboard.php" class="btn btn-secondary mb-3"><i class="fa-solid fa-arrow-left"></i> Back to Dashboard</a>

    <div class="card shadow rounded-4 p-4">
        <div id="calendar"></div>
    </div>
</div>

<!-- Modal for Event Details -->
<div class="modal fade" id="eventModal" tabindex="-1" aria-labelledby="eventModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="eventModalLabel">Event Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h5 id="eventTitle"></h5>
                <p><strong>Event Date:</strong> <span id="eventDate"></span></p>
                <p><strong>Event Time:</strong> <span id="eventTime"></span></p>

                <p><strong>Assigned Readers by Role:</strong></p>
                <div id="assignedReadersList"></div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap Bundle JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        
        // Initialize FullCalendar
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            events: <?php echo json_encode($events_for_js); ?>,
            eventClick: function(info) {
                var event = info.event;

                // Set event details in the modal
                document.getElementById('eventTitle').innerText = event.title;
                document.getElementById('eventDate').innerText = event.start.toLocaleDateString();
                document.getElementById('eventTime').innerText = event.extendedProps.time;

                var readersList = document.getElementById('assignedReadersList');
                readersList.innerHTML = ''; // Clear previous readers list

                // Check if 'assigned_readers' is an array before using .forEach
                if (Array.isArray(event.extendedProps.assigned_readers) && event.extendedProps.assigned_readers.length > 0) {
                    // Group readers by role
                    let readersByRole = {};
                    event.extendedProps.assigned_readers.forEach(function(reader) {
                        if (!readersByRole[reader.role]) {
                            readersByRole[reader.role] = [];
                        }
                        readersByRole[reader.role].push(reader.names);
                    });

                    // Display readers grouped by role
                    for (let role in readersByRole) {
                        var roleSection = document.createElement('div');
                        var roleHeader = document.createElement('h6');
                        roleHeader.innerText = role;
                        roleSection.appendChild(roleHeader);

                        var readerList = document.createElement('ul');
                        readersByRole[role].forEach(function(readerName) {
                            var listItem = document.createElement('li');
                            listItem.innerText = readerName;
                            readerList.appendChild(listItem);
                        });

                        roleSection.appendChild(readerList);
                        readersList.appendChild(roleSection);
                    }
                } else {
                    var listItem = document.createElement('p');
                    listItem.innerText = 'No readers assigned';
                    readersList.appendChild(listItem);
                }

                // Show the modal
                var eventModal = new bootstrap.Modal(document.getElementById('eventModal'));
                eventModal.show();
            }
        });

        calendar.render();
    });
</script>

<?php include '../templates/footer.php'; ?>
</body>
</html>

<?php
// pages/calendar.php
require '../config/db.php';
include '../includes/header.php';

$isAdmin = (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'Admin') ? 'true' : 'false';
$events = [];

try {
    // 1. Follow-Ups
    $stmt = $pdo->query("SELECT f.id, f.lead_id, f.follow_up_date, f.status, l.title as lead_title FROM FollowUps f JOIN Leads l ON f.lead_id = l.id");
    while ($row = $stmt->fetch()) {
        $color = ($row['status'] == 'Completed') ? '#198754' : (($row['status'] == 'Cancelled') ? '#dc3545' : '#ffc107');
        $textColor = ($row['status'] == 'Pending') ? '#000' : '#fff';
        
        $events[] = [
            'id' => 'f_'.$row['id'],
            'title' => 'Follow-up: ' . $row['lead_title'],
            'start' => $row['follow_up_date'],
            'url' => 'followup_edit.php?id=' . $row['id'],
            'backgroundColor' => $color,
            'borderColor' => $color,
            'textColor' => $textColor,
            'allDay' => true,
            'editable' => false // System events are not draggable on calendar
        ];
    }

    // 2. Deals Expected Close Dates
    $stmt = $pdo->query("SELECT id, title, expected_close_date, stage FROM Deals WHERE expected_close_date IS NOT NULL");
    while ($row = $stmt->fetch()) {
        $color = ($row['stage'] == 'Closed Won') ? '#198754' : (($row['stage'] == 'Closed Lost') ? '#dc3545' : '#0d6efd');
        $events[] = [
            'id' => 'd_'.$row['id'],
            'title' => 'Closing Deal: ' . $row['title'],
            'start' => $row['expected_close_date'],
            'url' => 'deal_edit.php?id=' . $row['id'],
            'backgroundColor' => $color,
            'borderColor' => $color,
            'allDay' => true,
            'editable' => false
        ];
    }

    // 3. Custom Calendar Events (Admin Managed)
    $stmt = $pdo->query("SELECT id, title, event_type, start_date, end_date FROM CalendarEvents");
    while ($row = $stmt->fetch()) {
        $color = '#6c757d'; // default secondary
        if ($row['event_type'] == 'Holiday') $color = '#dc3545'; // danger
        elseif ($row['event_type'] == 'Meeting') $color = '#0dcaf0'; // info
        elseif ($row['event_type'] == 'Deadline') $color = '#fd7e14'; // orange
        elseif ($row['event_type'] == 'Project') $color = '#6610f2'; // purple

        $events[] = [
            'id' => 'c_'.$row['id'],
            'title' => $row['title'],
            'start' => $row['start_date'],
            'end' => $row['end_date'],
            'backgroundColor' => $color,
            'borderColor' => $color,
            'allDay' => true,
            'extendedProps' => [
                'isCustom' => true,
                'db_id' => $row['id'],
                'event_type' => $row['event_type']
            ]
        ];
    }
} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>Error loading calendar: " . $e->getMessage() . "</div>";
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Task & Event Calendar</h2>
    <a href="../dashboard.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back to Dashboard</a>
</div>

<?php if($isAdmin === 'true'): ?>
<div class="alert alert-info py-2 shadow-sm">
    <i class="bi bi-info-circle me-2"></i> <strong>Admin Mode:</strong> You can click on any date to create custom events, or drag and drop existing custom events to reschedule them.
</div>
<?php endif; ?>

<div class="card shadow-sm border-top border-primary border-4 mb-4">
    <div class="card-body p-4">
        <div id="calendar"></div>
    </div>
</div>

<div class="modal fade" id="eventModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="eventModalTitle">Manage Event</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="eventForm">
            <input type="hidden" id="eventId">
            <div class="mb-3">
                <label class="form-label">Event Title</label>
                <input type="text" class="form-control" id="eventTitle" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Event Type</label>
                <select class="form-select" id="eventType">
                    <option value="Meeting">Meeting</option>
                    <option value="Holiday">Holiday</option>
                    <option value="Deadline">Deadline</option>
                    <option value="Project">New Project</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Start Date & Time</label>
                <input type="datetime-local" class="form-control" id="eventStartDate" required>
            </div>
            <div class="mb-3">
                <label class="form-label">End Date & Time</label>
                <input type="datetime-local" class="form-control" id="eventEndDate">
                <div class="form-text">For continuous multi-day events, select an end date.</div>
            </div>
        </form>
      </div>
      <div class="modal-footer d-flex justify-content-between">
        <button type="button" class="btn btn-danger d-none" id="btnDeleteEvent">Delete</button>
        <div>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="button" class="btn btn-primary" id="btnSaveEvent">Save Event</button>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
<script>
// Format date string for datetime-local input
function formatForInput(dateObj) {
    if(!dateObj) return '';
    const d = new Date(dateObj);
    if(isNaN(d)) return '';
    // offset to local time
    const tzOffset = d.getTimezoneOffset() * 60000;
    const localISOTime = (new Date(d - tzOffset)).toISOString().slice(0, 16);
    return localISOTime;
}

document.addEventListener('DOMContentLoaded', function() {
    const isAdmin = <?= $isAdmin ?>;
    var calendarEl = document.getElementById('calendar');
    var eventModal = new bootstrap.Modal(document.getElementById('eventModal'));

    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        themeSystem: 'bootstrap5',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,listWeek'
        },
        events: <?= json_encode($events) ?>,
        selectable: isAdmin,
        editable: isAdmin,
        
        select: function(info) {
            if (!isAdmin) return;
            document.getElementById('eventId').value = '';
            document.getElementById('eventTitle').value = '';
            document.getElementById('eventType').value = 'Meeting';
            document.getElementById('eventStartDate').value = formatForInput(info.start);
            document.getElementById('eventEndDate').value = info.end ? formatForInput(info.end) : '';
            
            document.getElementById('eventModalTitle').innerText = 'Add New Event';
            document.getElementById('btnDeleteEvent').classList.add('d-none');
            eventModal.show();
        },
        
        eventClick: function(info) {
            if (info.event.url) {
                window.location.href = info.event.url;
                info.jsEvent.preventDefault();
                return;
            }

            if (isAdmin && info.event.extendedProps.isCustom) {
                document.getElementById('eventId').value = info.event.extendedProps.db_id;
                document.getElementById('eventTitle').value = info.event.title;
                document.getElementById('eventType').value = info.event.extendedProps.event_type;
                document.getElementById('eventStartDate').value = formatForInput(info.event.start);
                document.getElementById('eventEndDate').value = document.getElementById('eventEndDate').value = info.event.end ? formatForInput(info.event.end) : '';
                
                document.getElementById('eventModalTitle').innerText = 'Edit Event';
                document.getElementById('btnDeleteEvent').classList.remove('d-none');
                eventModal.show();
            }
        },
        
        eventDrop: function(info) {
            if (!isAdmin || !info.event.extendedProps.isCustom) {
                info.revert();
                return;
            }
            let formData = new URLSearchParams();
            formData.append('action', 'update');
            formData.append('id', info.event.extendedProps.db_id);
            formData.append('start_date', formatForInput(info.event.start));
            if(info.event.end) formData.append('end_date', formatForInput(info.event.end));

            fetch('../api/manage_event.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: formData.toString()
            })
            .then(res => res.json())
            .then(data => {
                if(data.error) {
                    alert('Error: ' + data.error);
                    info.revert();
                } else {
                    location.reload();
                }
            });
        }
    });
    calendar.render();

    document.getElementById('btnSaveEvent').addEventListener('click', function() {
        let id = document.getElementById('eventId').value;
        let title = document.getElementById('eventTitle').value;
        let eventType = document.getElementById('eventType').value;
        let start = document.getElementById('eventStartDate').value;
        let end = document.getElementById('eventEndDate').value;
        
        if (!title.trim() || !start.trim()) {
            alert('Title and Start Date are required');
            return;
        }

        let formData = new URLSearchParams();
        formData.append('action', id ? 'update' : 'create');
        formData.append('title', title);
        formData.append('event_type', eventType);
        formData.append('start_date', start);
        if (end) formData.append('end_date', end);
        if (id) formData.append('id', id);

        fetch('../api/manage_event.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: formData.toString()
        }).then(res => res.json()).then(data => {
            if(data.error) alert('Error: ' + data.error);
            else location.reload();
        });
    });

    document.getElementById('btnDeleteEvent').addEventListener('click', function() {
        if(!confirm('Delete this event?')) return;
        let id = document.getElementById('eventId').value;
        let formData = new URLSearchParams();
        formData.append('action', 'delete');
        formData.append('id', id);
        fetch('../api/manage_event.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: formData.toString()
        }).then(res => res.json()).then(data => {
            if(data.error) alert(data.error);
            else location.reload();
        });
    });
});
</script>

<style>
/* Fullcalendar Custom Adjustments */
.fc-event { cursor: pointer; border-radius: 4px; padding: 2px 4px; box-shadow: 0 1px 2px rgba(0,0,0,0.1); }
.fc-toolbar-title { font-weight: 700 !important; color: #333; }
.fc .fc-button-primary { background-color: #0d6efd; border-color: #0d6efd; }
.fc .fc-button-primary:hover { background-color: #0b5ed7; border-color: #0a58ca; }
</style>

<?php include '../includes/footer.php'; ?>

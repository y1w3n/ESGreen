<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Kanban Dashboard</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
</head>
<body>
    <div class="container">
        <aside class="sidebar">
		<ul class="sidebar-menu">
                <li><a href="1_main.html">Overview</a></li>
                <li><a href="2_kb.php">Tasks</a></li>
				<li><a href="../end_user/1_overview_flow.php">Client Portal</a></li>
                <li><a href="3_user.php">Users Management</a></li>
                <li><a href="4_geninfo_mt.php">Generic Information Master Data</a></li>
x                <li><a href="6_ma_mt.php">Materiality Assessment Questions</a></li>
                <li><a href="7_hc_mt.php">Health Check Assessment Questions</a></li>
                <li><a href="8_qs_tr.php">Users Submissions Status</a></li>
                <li><a href="9_rp_vis.html">Reports</a></li>
                <li><a href="10_dev.html">Development Portal</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </aside>
        <div class="main-content">
            <header class="header">
                <img src="assets/images/esgreen.png" alt="Logo 1" class="logo1">
                <h1>Tasks</h1>
                <div class="logo-container">
                    <img src="assets/images/sunway-logo.png" alt="Logo 1" class="logo">
                </div>
            </header>
            <div class="content">
                <h2>To-do-list</h2>
                <div class="task-count-cards">
                    <div class="task-count-card" id="todo-count-card">
                        <span class="task-count-label">Pending Tasks:</span>
                        <span class="task-count">0</span>
                    </div>
                    <div class="task-count-card" id="inprogress-count-card">
                        <span class="task-count-label">In Progress Tasks:</span>
                        <span class="task-count">0</span>
                    </div>
                    <div class="task-count-card" id="done-count-card">
                        <span class="task-count-label">Completed Tasks:</span>
                        <span class="task-count">0</span>
                    </div>
                </div>
                <div class="kanban-board">
                    <div class="kanban-column" id="todo-column">
                        <h3 class="column-header" data-status="pending">Pending</h3>
                        <div class="column-content">
                            <p>Waiting for consultation</p>
                        </div>
                    </div>
                    <div class="kanban-column" id="inprogress-column">
                        <h3 class="column-header" data-status="in_progress">In Progress</h3>
                        <div class="column-content">
                            <p>Consultation in progress</p>
                        </div>
                    </div>
                    <div class="kanban-column" id="done-column">
                        <h3 class="column-header" data-status="completed">Done</h3>
                        <div class="column-content">
                            <p>Finished consultation</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
		$(document).ready(function() {
			function fetchConsultations() {
				$.ajax({
					url: '2_kb_fetch_tasks.php',
					type: 'GET',
					dataType: 'json',
					success: function(response) {
						let totalTasks = response.length;
						let pendingTasks = response.filter(task => task.progress === 'pending').length;
						let inProgressTasks = response.filter(task => task.progress === 'in_progress').length;
						let completedTasks = response.filter(task => task.progress === 'completed').length;

						$('#todo-count-card .task-count').text(pendingTasks);
						$('#inprogress-count-card .task-count').text(inProgressTasks);
						$('#done-count-card .task-count').text(completedTasks);

						$('#todo-column .column-content').find('.kanban-item').remove();
						$('#inprogress-column .column-content').find('.kanban-item').remove();
						$('#done-column .column-content').find('.kanban-item').remove();

						$.each(response, function(index, consultation) {
							var optionsHtml = '<a href="#" class="view-details">View Details</a>';

							if (consultation.progress === 'pending') {
								optionsHtml += '<a href="#" class="change-status" data-status="in_progress">Mark as In Progress</a>';
								optionsHtml += '<a href="#" class="change-status" data-status="completed">Mark as Completed</a>';
							} else if (consultation.progress === 'in_progress') {
								optionsHtml += '<a href="#" class="change-status" data-status="pending">Mark as Pending</a>';
								optionsHtml += '<a href="#" class="change-status" data-status="completed">Mark as Completed</a>';
							} else if (consultation.progress === 'completed') {
								optionsHtml += '<a href="#" class="change-status" data-status="pending">Mark as Pending</a>';
								optionsHtml += '<a href="#" class="change-status" data-status="in_progress">Mark as In Progress</a>';
							}

							optionsHtml += '<a href="#" class="delete-consultation">Delete</a>';

							var consultationHtml = '<div class="kanban-item" data-consultation-id="' + consultation.consultation_id + '" data-assessment-id="' + consultation.assessment_id + '">' +
												'Company Name: ' + consultation.company_name + '<br>' +
												'Assessment ID: ' + consultation.assessment_id +
												'<div class="dropdown" style="position: absolute; top: 10px; right: 10px;">' +
													'<button class="dropbtn">&#8942;</button>' +
													'<div class="dropdown-content">' +
														optionsHtml +
													'</div>' +
												'</div>' +
											'</div>';

							if (consultation.progress === 'pending') {
								$('#todo-column .column-content').append(consultationHtml);
							} else if (consultation.progress === 'in_progress') {
								$('#inprogress-column .column-content').append(consultationHtml);
							} else if (consultation.progress === 'completed') {
								$('#done-column .column-content').append(consultationHtml);
							}
						});
					},
					error: function(xhr, status, error) {
						console.error('Error fetching consultations:', error);
					}
				});
			}

			function changeConsultationStatus(consultationId, newStatus) {
				$.ajax({
					url: '2_kb_update_stat.php',
					type: 'POST',
					data: {
						consultation_id: consultationId,
						new_status: newStatus
					},
					success: function(response) {
						fetchConsultations();
					},
					error: function(xhr, status, error) {
						console.error('Error updating consultation status:', error);
					}
				});
			}

			function deleteConsultation(consultationId) {
				$.ajax({
					url: '2_kb_del.php',
					type: 'POST',
					data: {
						consultation_id: consultationId
					},
					success: function(response) {
						fetchConsultations();
					},
					error: function(xhr, status, error) {
						console.error('Error deleting consultation:', error);
					}
				});
			}

			$(document).on('click', '.change-status', function(e) {
				e.preventDefault();
				var consultationId = $(this).closest('.kanban-item').data('consultation-id');
				var newStatus = $(this).data('status');
				changeConsultationStatus(consultationId, newStatus);
			});

			$(document).on('click', '.view-details', function(e) {
				e.preventDefault();
				var assessmentId = $(this).closest('.kanban-item').data('assessment-id');
				viewDetails(assessmentId);
			});

			$(document).on('click', '.delete-consultation', function(e) {
				e.preventDefault();
				var consultationId = $(this).closest('.kanban-item').data('consultation-id');
				if (confirm('Are you sure you want to delete this consultation?')) {
					deleteConsultation(consultationId);
				}
			});

			function viewDetails(assessmentId) {
				window.open('8_qs_det.php?assessment_id=' + assessmentId, '_blank');
			}

			$(document).on('click', '.column-header', function() {
				$(this).next('.column-content').toggle();
			});

			fetchConsultations();
		});

    </script>
</body>
</html>

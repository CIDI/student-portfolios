<?php
	session_start();
	if ($_SESSION['allowed']){
		$courseID = $_SESSION['courseID'];
	} else {
		echo "Sorry, you are not authorized to view this content or your session has expired. Please relaunch this tool from Canvas.";
		return false;
	}
?>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
	<title>Create Student Portfolios</title>
	<link href="//netdna.bootstrapcdn.com/twitter-bootstrap/2.3.1/css/bootstrap-combined.min.css" media="all" rel="stylesheet" type="text/css" />
</head>
<body>
	<div class="container-fluid">
		<h1>Teaching Portfolios</h1>
		<p class="lead">The following courses have been created:</p>
		<?php
			ini_set('display_errors', 1);
			error_reporting(E_ALL ^ E_NOTICE);
			include 'portfoliosAPI.php';
			include 'dbconnect.php';

			// Originating Course Info
				$courseInfo = getCourse($courseID);
				date_default_timezone_set('UTC');
				$createdDate = date("Y-m-d");
				$workingList = json_decode($courseInfo,true);
				$courseName = $workingList['name'];
				$term = $workingList['term']['name'];
			// Get student enrollments (account for paginated)
				echo "<ol>";
				$pageNum = 1;
				$perPage = 50;
				for ($resultsPage=0; $resultsPage<5; $resultsPage++){
					$studentEnrollments = getStudentsPaged($courseID, $pageNum);
					$workingEnrollmentList = json_decode($studentEnrollments,true);
					$enrollmentCount = count($workingEnrollmentList);
					for ($i=0; $i<count($workingEnrollmentList); $i++){
						// Information for the new course
						$studentID = $workingEnrollmentList[$i]['id'];
						$studentName = $workingEnrollmentList[$i]['name'];
						// Pull data from database for this user
						$existingPortfolios = mysqli_query($GLOBALS['conn'], "SELECT * FROM student_portfolios WHERE student_canvas_id = $studentID");
						$num_rows = mysqli_num_rows($existingPortfolios);
						// If user exists
						if($num_rows>0){
							while($row = mysqli_fetch_array($existingPortfolios)) {
								echo '<li><a href="'.$_SESSION["canvasURL"].'/courses/'.$row['portfolio_course_id'].'" target="_blank">'.$row['student_name'].'</a> - Portfolio already exists</li>';
							}
						} else {
							$newCourseName = $studentName." Professional Teaching Portfolio";
							// Create a new course
							$addCourseParam = "account_id=".$_SESSION['accountID']."&course[name]=".$newCourseName."&offer=true&default_view=wiki&course[is_public]=true";
							$newCourseID = createNewCourse($_SESSION['accountID'], $addCourseParam);
							// Enroll the student as a teacher for the new course
							$enrollmentType =  "TeacherEnrollment"; 
							enrollUser($studentID, $newCourseID, $enrollmentType);
							// Add content from Portfolio Template Course
							copyCourseContent($_SESSION['templateCourseID'], $newCourseID);
							// Add to database
							$studentName = mysqli_real_escape_string($GLOBALS['conn'], $studentName);
							$sql = "INSERT INTO student_portfolios (portfolio_course_id, student_canvas_id, student_name, parent_course_id, parent_course_name, term, created) VALUES ($newCourseID, $studentID, '$studentName', $courseID, '$courseName', '$term', '$createdDate')";
							if(mysqli_query($GLOBALS['conn'], $sql)){
								echo '<li><a href="'.$_SESSION["canvasURL"].'/courses/'.$newCourseID.'" target="_blank">'.$studentName.'</a> - Portfolio Created</li>'; 
							}else{
								echo '<li>Error adding course to db.<br />'.mysqli_error($GLOBALS["conn"]).'</li>';
							}
						}
					}
					// This is the second part of page control. It will exit the loop when all records have been returned 
					if ($enrollmentCount < $perPage){
						break;
					}
					// If the loop is to continue, this will set up for the next page of results
					$pageNum++;
				}
				echo "</ol>";
		?>
		<a href="index.php" class="btn">Back to list</a>
	</div>
</body>
</html>
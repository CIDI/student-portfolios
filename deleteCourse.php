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
	<title>Delete Student Portfolio</title>
	<link href="//netdna.bootstrapcdn.com/twitter-bootstrap/2.3.1/css/bootstrap-combined.min.css" media="all" rel="stylesheet" type="text/css" />
</head>
<body>
		<?php
			ini_set('display_errors', 1);
			error_reporting(E_ALL ^ E_NOTICE);
			include 'portfoliosAPI.php';
			include 'dbconnect.php';
			$courseID = $_POST['courseID'];
			$sql = mysqli_query($GLOBALS['conn'], "DELETE FROM student_portfolios WHERE portfolio_course_id = $courseID");
			$deleteFromCanvas = deleteCourse($courseID);
			echo 'Course Deleted';
		?>
</body>
</html>
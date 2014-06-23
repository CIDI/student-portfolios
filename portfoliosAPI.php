<?php
	// This page contains a variety of Canvas API calls, it is not exhaustive as calls have been added by need
	// Caution: Not all calls have been tested, some were setup but the approach changed before they were fully tested


/********************************************/
/*********  REQUIRED INFORMATION ************/
/********************************************/

	// Root url for all api calls
	$canvasURL = 'https://usu.instructure.com/api/v1/';
	// This is the header containing the authorization token from Canvas, depending on the features you use, 
	// this needs to be an admin token
	$token = "###################";
	
/********************************************/
/********************************************/


	$tokenHeader = array("Authorization: Bearer ".$token);

	// Display any php errors (for development purposes)
		error_reporting(E_ALL);
		ini_set('display_errors', '1');

	// the following functions run the GET, POST and DELETE calls
		function curlPost($url, $data) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $GLOBALS['canvasURL'].$url);
			curl_setopt ($ch, CURLOPT_HTTPHEADER, $GLOBALS['tokenHeader']);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // ask for results to be returned

			// Send to remote and return data to caller.
			$response = curl_exec($ch);
			curl_close($ch);
			return $response;
		}

		function curlGet($url) {
			$ch = curl_init($url);
			curl_setopt ($ch, CURLOPT_URL, $GLOBALS['canvasURL'].$url);
			curl_setopt ($ch, CURLOPT_HTTPHEADER, $GLOBALS['tokenHeader']);
			curl_setopt ($ch, CURLOPT_HEADER, false);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // ask for results to be returned

			// Send to remote and return data to caller.
			$response = curl_exec($ch);
			curl_close($ch);
			return $response;
		}

		function curlDelete($url)
		{
		    $ch = curl_init();
		    curl_setopt($ch, CURLOPT_URL, $GLOBALS['canvasURL'].$url);
		    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
			curl_setopt ($ch, CURLOPT_HTTPHEADER, $GLOBALS['tokenHeader']);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			
			// Send to remote and return data to caller.
		    $result = curl_exec($ch);
		    curl_close($ch);
		    return $result;
		}


// Canvas API Functions
// Unless otherwise noted, the responses are in JSON format
		function createNewCourse($accountID, $addCourseParam){
			$addCourseUrl = "accounts/".$accountID."/courses";
			$response = curlPost($addCourseUrl, $addCourseParam);
			$responseData = json_decode($response, true);
			$courseID = $responseData['id'];
			return $courseID;
		}
		function copyCourseContent($courseCopyFromID, $courseCopyToID){
			$copyCourseURL = "courses/".$courseCopyToID."/course_copy";
			$copyCourseParam = "source_course=".$courseCopyFromID;
			$response = curlPost($copyCourseURL, $copyCourseParam);
			return $response;
		}
		function deleteCourse($courseID) {
			$deleteCourseUrl = "courses/".$courseID."?event=delete";
			$response = curlDelete($deleteCourseUrl);
			return $response;
		}
		function enrollUser($userID, $courseID, $enrollmentType){
			$enrollUserUrl = "courses/".$courseID."/enrollments";
			$enrollUserParam = "enrollment[user_id]=".$userID."&enrollment[type]=".$enrollmentType."&enrollment[enrollment_state]=active";
			$response = curlPost($enrollUserUrl, $enrollUserParam);
			return $response;
		}
		function getCourse($courseID){
			$apiUrl = "courses/".$courseID."?include[]=term";
			$response = curlGet($apiUrl);
			return $response;
		}

		function getTeacher($courseID){
			$getTeacherUrl = "courses/".$courseID."/users/?enrollment_type=teacher";
			$response = curlGet($getTeacherUrl);
			$responseData = json_decode($response, true);
			return $responseData;
		}
		function getStudentsPaged($courseID, $pageNum){
			$getStudentUrl = "courses/".$courseID."/users/?enrollment_type=student&per_page=50&page=".$pageNum;
			$response = curlGet($getStudentUrl);
			return $response;
		}
?>
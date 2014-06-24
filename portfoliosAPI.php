<?php
// This program will create a Canvas course for each enrolled student based
// off a seperate template course
// Copyright (C) 2014  Kenneth Larsen - Center for Innovative Design and Instruction
// Utah State University

// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU Affero General Public License as
// published by the Free Software Foundation, either version 3 of the
// License, or (at your option) any later version.

// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU Affero General Public License for more details.
// http://www.gnu.org/licenses/agpl-3.0.html


/********************************************/
/*********  REQUIRED INFORMATION ************/
/********************************************/

	// Root url for all api calls
	$canvasURL = 'https://<your institution>.instructure.com/api/v1/';
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
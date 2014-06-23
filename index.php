<?php
    session_start();
    if(strpos($_POST["roles"],'Instructor') !== false || strpos($_POST["roles"],'Administrator') !== false) {
        $_SESSION['allowed'] = true;
        $_SESSION['courseID'] = $_POST['custom_canvas_course_id'];
    } else if ($_SESSION['allowed'] !== true) {
        var_dump($_SESSION['allowed']);
        echo "<br>Sorry, you are not authorized to view this content";
        return false;
    }
    // Get the POST information and turn it into variables for future use
    if (isset($_POST["custom_canvas_course_id"])){
		// Canvas Sub-Account where portfolios will be created
		$_SESSION['accountID'] = "######";
		// Canvas course ID for template course
		$_SESSION['templateCourseID'] = "######";

        $_SESSION['courseID'] = $_POST['custom_canvas_course_id'];
        $_SESSION['courseName'] = $_POST["context_title"];
        $_SESSION['userID'] = $_POST["custom_canvas_user_login_id"];
        $_SESSION['apiDomain'] = $_POST["custom_canvas_api_domain"];
        $_SESSION["canvasURL"] = 'https://'.$_SESSION['apiDomain'];
    }

    ini_set('display_errors', 1);
    error_reporting(E_ALL ^ E_NOTICE);

    include 'dbconnect.php';
    include 'portfoliosAPI.php';
?>
<!DOCTYPE html>
<head>
    <meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
    <title>Student Portfolios</title>
    <link rel="stylesheet" href="//netdna.bootstrapcdn.com/twitter-bootstrap/2.3.1/css/bootstrap-combined.min.css">
    <link rel="stylesheet" href="//netdna.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.css">
    <script type="text/javascript" language="javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.8/jquery.min.js"></script>
    <script type="text/javascript" language="javascript" src="//cdn.datatables.net/1.10.0/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="//netdna.bootstrapcdn.com/twitter-bootstrap/2.3.1/js/bootstrap.min.js"></script>
    <script type="text/javascript" charset="utf-8">
        /* Modify dataTable classes to match Bootstrap */
        $.extend( $.fn.dataTableExt.oStdClasses, {
            "sSortAsc": "header headerSortDown",
            "sSortDesc": "header headerSortUp",
            "sSortable": "header"
        } );

        $(document).ready(function () {
            /* dataTable initialisation */
            var allCoursesTable = $('#allCourses').dataTable({
                    "bLengthChange": false,
                    "bPaginate": false,
                    "order": [[ 1, "asc" ]]
                });
            var currentCourseTable = $('#currentCourse').dataTable({
                    "bLengthChange": false,
                    "bPaginate": false,
                    "order": [[ 1, "asc" ]]
                });
            // toggle visibility when current course btn clicked
            $('.currentCourse').click(function (e) {
                e.preventDefault();
                $('.filterSelect').removeClass('active');
                $(this).addClass('active');
                $('#allCourses_wrapper, .adminTrigger, adminAlert').hide();
                $('#currentCourse_wrapper').show();
            });
            // toggle visibility when current all courses btn clicked
            $('.allCourses').click(function (e) {
                e.preventDefault();
                $('.filterSelect').removeClass('active');
                $(this).addClass('active');
                $('#allCourses_wrapper, .adminTrigger').show();
                $('#currentCourse_wrapper').hide();
            });
            // Show delete buttons when admin button is clicked
            $('.adminTrigger').click(function (e) {
                e.preventDefault();
                $('.deleteCourse, .adminAlert').toggle();
                $(this).toggleClass('active');
            });
            // Move the item count from the bottom of the table to the top
            $("#allCourses_wrapper .dataTables_info").appendTo("#allCourses_wrapper .dataTables_filter");
            $("#allCourses_wrapper").hide();
            $("#currentCourse_wrapper .dataTables_info").appendTo("#currentCourse_wrapper .dataTables_filter");

            // Create Portfolios button
            $(".createCourses").click(function(e){
                e.preventDefault();
                // Provide visual feedback that something is happening
                $(this).html('<i class="fa fa-spinner fa-spin fa-large"></i> Creating Portfolios').addClass("disabled");
                // in order for the icon above to display, there needs to be a slight delay
                var myUrl = $(this).attr("href");
                setTimeout(function(){
                    document.location.href=myUrl;
                }, 5);
            });
            // Handle deleting courses through ajax
            $('.deleteCourse').click(function (e) {
                e.preventDefault();
                console.log('clicked');
                var canvasCourseID = $(this).attr('rel');
                $(this).addClass('deletedCourse');
                $.post("deleteCourse.php", { courseID: canvasCourseID })
                    .done(function(data) {
                        $(".deletedCourse").addClass('disabled').html(data).removeClass('deletedCourse, btn-danger');
                    });
            });
        });
    </script>
    <style>
        .courseName {font-style: italic; font-weight: bold; font-size: 20px;}
        .dataTables_filter label {float:right;}
        .dataTables_filter label input {margin-left: 5px;}
        .help-block { display: inline-block;}
        .alert {margin-top: 10px;}
        #myModal {width: 600px;}
        .alert {text-align: center;}
        .deleteCourse, .adminAlert, .enrolledCourseID {display: none;}
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row-fluid">
            <div class="message"></div> 
            <h2>Student Portfolios 
                <div class="pull-right">
                    <a href="#" role="button" class="btn btn-mini adminTrigger" style="display:none;"><i class="fa fa-gear"></i> Admin</a>
                </div>
            </h2>
            <div class="well">
                <p>Below is a list of student portfolios.</p>
                <div class="btn-group" data-toggle="buttons-radio">
                    <div class="btn btn-small btn-inverse disabled">Show</div>
                    <button type="button" class="btn btn-small filterSelect currentCourse active" rel="this">This Course</button>
                    <button type="button" class="btn btn-small filterSelect allCourses" rel="all">All Courses</button>
                </div>
            </div>
            <div class="alert alert-danger adminAlert">Deleting a course will remove all of the student's course information. Only do so if you are positive you are removing the correct course.</div>
            <table cellpadding="0" cellspacing="0" border="0" class="table table-bordered table-striped" id="allCourses">
                <thead>
                    <tr>
                        <th>Term</th>
                        <th>Student Name</th>
                        <th>Parent Course</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                    $sql = mysqli_query($GLOBALS['conn'], "SELECT * FROM student_portfolios");
                    $num_rows = mysqli_num_rows($sql);
                    if ($num_rows > 0) {
                        while($row = mysqli_fetch_array($sql)) {
                            echo '<tr class="course_'.$row['parent_course_id'].'">
                                    <td>'.$row['term'].'</td>
                                    <td><a href="'.$_SESSION["canvasURL"].'/courses/'.$row['portfolio_course_id'].'" target="_blank">'.$row['student_name'].'</a>
                                    <a href="#" class="btn btn-danger btn-mini deleteCourse pull-right" style="display:none;" rel="'.$row['portfolio_course_id'].'"><i class="fa fa-times-circle"></i> Delete Course</a></td>
                                    <td><a href="'.$_SESSION["canvasURL"].'/courses/'.$row['parent_course_id'].'" target="_blank">'.$row['parent_course_name'].'</a><span class="enrolledCourseID">'.$row['parent_course_id'].'</span></td>
                                    <td>'.$row['created'].' </td>
                                </tr>';
                        }
                    } else {
                        echo '<tr colspan="4"><td><span class="text-error"> No portfolios found</span></td></tr>';
                    }
                ?>
                </tbody>
            </table>
            <table cellpadding="0" cellspacing="0" border="0" class="table table-bordered table-striped" id="currentCourse">
                <thead>
                    <tr>
                        <th>Term</th>
                        <th>Student Name</th>
                        <th>Parent Course</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                	// Handle paged results if needed
                    $pageNum = 1;
                    $perPage = 50;
                    for ($resultsPage=0; $resultsPage<5; $resultsPage++){
                        $studentEnrollments = getStudentsPaged($_SESSION['courseID'], $pageNum);
                        $workingEnrollmentList = json_decode($studentEnrollments,true);
                        $enrollmentCount = count($workingEnrollmentList);

                        for ($i=0; $i<count($workingEnrollmentList); $i++){
                            // Information for the new course
                            $studentID = $workingEnrollmentList[$i]['id'];
                            $studentName = $workingEnrollmentList[$i]['name'];

                            $existingPortfolios = mysqli_query($GLOBALS['conn'], "SELECT * FROM student_portfolios WHERE student_canvas_id = $studentID");
                            $num_rows = mysqli_num_rows($existingPortfolios);
                            // If user exists
                            if($num_rows>0){
                                while($row = mysqli_fetch_array($existingPortfolios)) {
                                    echo '<tr>
                                    <td>'.$row['term'].'</td>
                                    <td><a href="'.$_SESSION["canvasURL"].'/courses/'.$row['portfolio_course_id'].'" target="_blank">'.$row['student_name'].'</a></td>
                                    <td><a href="'.$_SESSION["canvasURL"].'/courses/'.$row['parent_course_id'].'" target="_blank">'.$row['parent_course_name'].'</a><span class="enrolledCourseID">'.$row['parent_course_id'].'</span></td>
                                    <td>'.$row['created'].'</td>
                                    </tr>';
                                }
                            } else {
                                echo '<tr>
                                    <td>&nbsp;</td>
                                    <td>'.$studentName.'</td>
                                    <td>'.$_SESSION["courseName"].'</td>
                                    <td><a href="#myModal" role="button" class="btn btn-small btn-info createPortfolioTrigger" data-toggle="modal"><i class="fa fa-plus"></i> Create Portfolio</a></td>
                                    </tr>';
                            }
                        }
                        // This is the second part of page control. It will exit the loop when all records have been returned 
                        if ($enrollmentCount < $perPage){
                            break;
                        }
                        // If the loop is to continue, this will set up for the next page of results
                        $pageNum++;
                    }
                ?>
                </tbody>
            </table>
            <div class="status_message"></div>
        </div>
    </div>
    <!-- New Course Modal -->
    <div id="myModal" class="modal hide fade">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h3>Attention</h3>
        </div>
            <div class="modal-body">
                <p>This will create a portfolio course for each student enrolled in:</p>
                <p class="courseName"><?php echo $_SESSION['courseName'] ?></p>
                <p>Please proceed only if you know you should be doing this.</p>
                <div class="alert alert-info">Portfolios will only be created for students who do not already have one.</div>
            </div>
            <div class="modal-footer">
            <a href="#" class="btn" data-dismiss="modal">Cancel</a>
            <a href="createPortfolios.php" class="btn createCourses btn-primary">Create Portfolios</a>
            </div>
    </div>
</body>
</html>
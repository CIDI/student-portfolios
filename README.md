student-portfolios
==================

This code is designed to create an Instructure Canvas course for each student enrolled in a given course

Requirements:
* PHP server version 5.3 or later
* MySQL Database
* Canvas OAuth token with permissions to create/delete courses

File Description:
* student_portfolios.sql
  - Database table information
* portfoliosAPI.php
  - Holds Canvas API calls
  - You will need to edit lines 24 and 27
* ltiTool.xml
  - This is the code to create the left navigation LTI tool
  - You will need to edit lines 14 and 20 to point to where you place this tool
  - You may want to look into CIDI's bulk LTI tool
* index.php
  - line 30 needs to be updated with the sub-account where the courses will be created
  - line 32 needs to be updated with the Canvas course ID for the template course
* dbconnect.php
  - update this file with the database credentials

License information:

This program will create a Canvas course for each enrolled student based
off a seperate template course
Copyright (C) 2014  Kenneth Larsen - Center for Innovative Design and Instruction
Utah State University

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU Affero General Public License as
published by the Free Software Foundation, either version 3 of the
License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU Affero General Public License for more details.
http://www.gnu.org/licenses/agpl-3.0.html

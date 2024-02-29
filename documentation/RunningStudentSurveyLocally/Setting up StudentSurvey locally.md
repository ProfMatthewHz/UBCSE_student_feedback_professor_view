  

**Welcome to StudentSurvey!**

  

**Verify you have the following! **

  

**Since we are working in master branch to start I advise you navigate to https://github.com/ProfMatthewHz/UBCSE_student_feedback_professor_view/tree/2024Sprint1-addDocumentationStudentSurveyRunningLocally and use the Setting Up StudentSurvey Locally.md on a seperate page to follow along while you change things inside of your ide**

1. Verify that you have XAMPP

2. Verify that you have Node.js

3. Verify that you have cloned Repo in htdocs within XAMPP

4. Rename repo to StudentSurvey

5. Verify that you have database.php file provided within StudentSurvey/documentation/RunningStudentSurveyLocally

6. Verify that you are currently within master branch

7. Verify that database.php file field within is as follows $DATABASE_NAME = 'test'

8. Verify that within StudentSurvey/react-frontend/package.json "homepage": "https://cse.buffalo.edu/teamwork/instructor" is converted to http://localhost/StudentSurvey/react-frontend/build

9. Place database.php file within the following path within StudentSurvey : StudentSurvey/backend/lib

10. In package.json within path StudentSurvey/react-frontend/ change "homepage" field to http://localhost/StudentSurvey/react-frontend/build

11.  **PLEASE REMEMBER DO NOT COMMIT TO MASTER, STASH CHANGES AFTER THEY ARE MADE. NEVER DIRECTLY COMMIT TO MASTER BRANCH**

12.  **INTIAL SETUP WILL HAPPEN IN MASTER BUT NEVER DIRECT COMMITS**

  

**Getting StudentSurvey up and running locally **

  

1. Locate XAMPP and double click on manager-osx = (mac) or xampp-control = (windows)

2. Verify a pop up is shown

3. Click on manage servers and verify MySql Database, ProFTPD, and Apache Web Server are running by either clicking on each and pressing start or clicking restart all

4. Verify a pop up saying Welcome to XAMPP is shown

5. Click on phpMyAdmin in the navbar

6. Verify you are in phpMyAdmin

7. Verify that to the left you have a **test** under the list of clickable items

8. Click on **test**

9. Open StudentSurvey within your ide and go to path StudentSurvey/db_creation_files/onboarding_script.sql

10. Copy all contents in onboarding_script.sql

11. Go back to phpMyAdmin and click on **test**

12. Verify and click on SQL on the top most navbar

13. Paste code from onboarding_script.sql into the field and click **go** on middle right of the page

14. Verify that **test** is now populated with the following fields within

- courses

- course_instructors

- enrollments

- evals

- freeforms

- instructors

- reviews

- rubrics

- rubric_responses

- rubric_scores

- rubric_topics

- scores

- students

- surveys

- survey_types

15. Within StudentSurvey within your ide and go to path StudentSurvey/db_creation_files/PHPDummyDataFinal.sql

16. Copy the contents of PHPDummyDataFinal.sql

17. Repeat Steps 11 through 12

18. Repeat step 13 with the exception of uncliking **Enable foreign key checks** and then click **go**

19. Verify that fields 15-29 are now populated with data

20. Open terminal and move to StudentSurvey/react-frontend directory

21. Install the node dependencies by running npm install

22. In the terminal while in the react-frontend directory, run npm run build to build to project

23. If npm run build does not work do npm start

24. Navigate to the website: http://localhost/StudentSurvey/backend/instructor/fake_shibboleth.php

25. Enter in hartloff in the input field and click Pretend Login

26. Then navigate to this website: http://localhost/StudentSurvey/react-frontend/build/

27. Make sure in your specific url that you are not on a specific port. For example make sure url is exactly http://localhost/StudentSurvey/react-frontend/build/ and not http://localhost:3000/StudentSurvey/react-frontend/build

28. Click on **history** in the nav bar

29. Click on **fall 2023** under terms to the left of the page

30. Verify that you see **CSE 199,404,305,116,115** and surveys under each course

  
  
  

**After Getting StudentSurvey up and running locally **
** Adding another Pairing Mode and Database Table **

1. After verifying that you have gotten StudentSurvey up and running locally, along with XAMPP running you are going to need to do the following. 

	2. Inside of StudentSurvey/db_creation_files within your local repo you will find a file called student_visit_data_table.sql
	3. Click on this file 
	4. Copy the contents of this file 
	5. While XAMPP is open, navigate to phpMyAdmin
	6. Click on "test" database on the left hand side 
	7. At the top of the navbar you will the the following "SQL" tab
	8. Click on this tab 
	9. Paste the contents of student_visit_data_table.sql
	10. Scroll down and make sure "Enable foreign key checks" is unchecked 
	11. To the right of that click "Go"
	12. Verify that a new table under the "test" database called "student_visit_data" is created with the following record inside
	13. student_id = 50243490
	14. survey_id = 42
	15. visit_count = 11
	16. last_visit = 2024-02-29 14:30:06
2. After you have completed this, we have to update the survey_types aka ("Pairing Modes")
3.  Inside of StudentSurvey/db_creation_files within your local repo you will find a file called survey_Types_Pairing_Mode.sql
	4. Click on this file 
	5. Copy the contents of this file 
	6. While XAMPP is open, navigate to phpMyAdmin
	7. Click on "test" database on the left hand side 
	8. At the top of the navbar you will the the following "SQL" tab
	9. Click on this tab 
	10. Paste the contents of survey_Types_Pairing_Mode.sql
	11. Scroll down and make sure "Enable foreign key checks" is unchecked 
		12. To the right of that click "Go"
	12. Under the "test" database click on "survey_types" and verify that the following record is available 
		13. id = 5 
		14. description = Single Pairs 
		15. file_organization  = One row per review. Each row has 2 columns: email of the reviewer, email of the person being reviewed.
		16. display_multiplier = 0
		17. URL = blank (as of 02/29/2024)

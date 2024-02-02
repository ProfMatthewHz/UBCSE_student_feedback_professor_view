**Welcome to StudentSurvey!**

**Verify you have the following!**

 1. Verify that you have XAMPP 
 2. Verify that you have Node.js
 3. Verify that you have database.php file provided within StudentSurvey/documentation/RunningStudentSurveyLocally
 4. Verify that database.php file field within is as follows $DATABASE_NAME = 'test'
 5. Locate htdocs within XAMPP and clone repo within this directory
 6. Rename repo to StudentSurvey
 7. Place database.php file within the following path within StudentSurvey : StudentSurvey/backend/lib

**Getting StudentSurvey up and running locally**

 1. Locate XAMPP and double click on manager-osx
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
	 15. courses
	 16. course_instructors
	 17. enrollments
	 18. evals
	 19. freeforms
	 20. instructors
	 21. reviews
	 22. rubrics
	 23. rubric_responses
	 24. rubric_scores
	 25. rubric_topics
	 26. scores
	 27.  students
	 28. surveys
	 29. survey_types
30. Within StudentSurvey within your ide and go to path StudentSurvey/db_creation_files/PHPDummyDataFinal.sql
31. Copy the contents of PHPDummyDataFinal.sql
32. Repeat Steps 11 through 12
33. Repeat step 13 with the exception of uncliking **Enable foreign key checks** and then click **go**
34. Verify that fields 15-29 are now populated with data
35. Open terminal and move to StudentSurvey/react-frontend directory 
36. Install the node dependencies by running npm install
37. In the terminal while in the react-frontend directory, run npm run build to build to project
38. Navigate to the website: [http://localhost/StudentSurvey/backend/instructor/fake_shibboleth.php](http://localhost/StudentSurvey/instructor/fake_shibboleth.php)
39. Enter in hartloff in the input field and click Pretend Login
40. Then navigate to this website: [http://localhost/StudentSurvey/react-frontend/build/](http://localhost/StudentSurvey/react-frontend/build/)
41. Click on **history** in the nav bar 
42. Click on **fall 2023** under terms to the left of the page 
43. Verify that you see **CSE 199,404,305,116,115** and surveys under each course

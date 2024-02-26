import React, {useEffect, useState} from "react";
import SideBar from "../Components/Sidebar";
import "../styles/home.css";
import "../styles/course.css";
import Course from "../Components/Course";

/**
 * The Home component is the main component that users see when they visit the home page.
 * It displays a list of courses for the current semester and year, fetched from an external API.
 */
const Home = () => {
  // State to store the list of courses
  const [courses, setCourses] = useState([]);

  const getCurrentYear = () => {
    const date = new Date();
    return date.getFullYear();
  };

  /**
   * Determines the current semester based on the current date.
   * Semesters are determined by specific date ranges within the year.
   * @returns {number} The current semester encoded as an integer (1 for Winter, 2 for Spring, 3 for Summer, 4 for Fall).
   */
  const getCurrentSemester = () => {
    const date = new Date();
    const month = date.getMonth(); // 0 for January, 1 for February, etc.
    const day = date.getDate();

    // Summer Sessions (May 30 to Aug 18)
    if (
        (month === 4 && day >= 30) ||
        (month > 4 && month < 7) ||
        (month === 7 && day <= 18)
    ) {
      return 3; // Summer
    }

    // Fall Semester (Aug 28 to Dec 20)
    if (
        (month === 7 && day >= 28) ||
        (month > 7 && month < 11) ||
        (month === 11 && day <= 20)
    ) {
      return 4; // Fall
    }

    // Winter Session (Dec 28 to Jan 19)
    if ((month === 11 && day >= 28) || (month === 0 && day <= 19)) {
      return 1; // Winter
    }

    // If none of the above conditions are met, it must be Spring (Jan 24 to May 19)
    return 2; // Spring
  };

  /**
   * Fetches the list of courses for the current semester and year from an external API.
    */
  const fetchCourses = () => {
    fetch(
        process.env.REACT_APP_API_URL + "instructorCoursesInTerm.php",
        {
          method: "POST",
          headers: {
            "Content-Type": "application/x-www-form-urlencoded",
          },
          body: new URLSearchParams({
            semester: getCurrentSemester(),
            year: getCurrentYear(),
          }),
        }
    )
        .then((res) => res.json())
        .then((result) => {
          setCourses(result);
        })
        .catch((err) => {
          console.log(err);
        });
  };

  // Fetch courses when the component mounts
  useEffect(() => {
    fetchCourses()
  }, []);

  // Preparing content for the Sidebar component
  const sidebar_content = {
    Courses: courses ? courses.map((course) => course.code) : [],
  };

  /**
   * The Home component renders a SideBar component and a list of Course components.
   */
  return (
    <>
      <SideBar route="/" content_dictionary={sidebar_content} getCourses={fetchCourses} />
      <div className="home--container">
        <div className="containerOfCourses">
          <div id="Open Surveys" class="courseContainer">
          <div className="courseContent">
                <div className="courseHeader">
                    <h2>
                        Open Surveys
                    </h2>
                </div>
                <table className="surveyTable">
                    <thead>
                    <tr>
                        <th>Survey Closes</th>
                        <th>Course Name</th>
                        <th>Survey Name</th>
                        <th>Completion Rate</th>
                        <th>Survey Actions</th>
                    </tr>
                    </thead>




                </table>
                
                
            </div>
          </div>

          <div id="Future Surveys" class="courseContainer">
          <div className="courseContent">
                <div className="courseHeader">
                    <h2>
                        Future Surveys
                    </h2>
                </div>
                <table className="surveyTable">
                    <thead>
                    <tr>
                        <th>Survey Closes</th>
                        <th>Course Name</th>
                        <th>Survey Name</th>
                    </tr>
                    </thead>




                </table>
                
                
            </div>
          </div>


          <div id="Closed Surveys" class="courseContainer">
          <div className="courseContent">
                <div className="courseHeader">
                    <h2>
                        Closed Surveys
                    </h2>
                </div>
                <table className="surveyTable">
                    <thead>
                    <tr>
                        <th>Survey Closed</th>
                        <th>Course Name</th>
                        <th>Survey Name</th>
                        <th>Survey Actions</th>
                    </tr>
                    </thead>

                 



                </table>
                
                
            </div>
          </div>



        </div>
      </div>
    </>
  );
};

export default Home;

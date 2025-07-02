import React, { useCallback, useEffect, useState } from "react";
import SideBar from "../Components/Sidebar";
import "../styles/home.css";
import Course from "../Components/Course";

const Home = () => {
  const [courses, setCourses] = useState([]);
  const [sidebar_content, setSidebarContent] = useState({});
  const [rubrics, setRubrics] = useState([]);
  const [pairingModes, setPairingModes] = useState([]);


  const getCurrentYear = () => {
    const date = new Date();
    return date.getFullYear();
  };

  // Using 2023-2024 course schedule
  const getCurrentSemester = () => {
    const date = new Date();
    const month = date.getMonth(); // 0 for January, 1 for February, etc.
    const day = date.getDate();

    // Summer Sessions (May 23 to Aug 18)
    if (
      (month === 4 && day >= 23) ||
      (month > 4 && month < 7) ||
      (month === 7 && day <= 18)
    ) {
      return 3; // Summer
    }

    // Fall Semester (Aug 19 to Dec 31)
    if (
      (month === 7 && day > 18) ||
      (month > 7 && month <= 11)
    ) {
      return 4; // Fall
    }

    // Winter Session (Jan 1 to Jan 23)
    if (month === 0 && day <= 23) {
      return 1; // Winter
    }

    // If none of the above conditions are met, it must be Spring (Jan 24 to May 19)
    return 2; // Spring
  };

  const fetchCourses = useCallback(() => {
    fetch(
      process.env.REACT_APP_API_URL + "getInstructorCoursesInTerm.php",
      {
        method: "POST",
        credentials: "include",
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
      .then((result) => setCourses(result))
      .catch((err) => {
        console.log(err);
      });
  }, []);

  useEffect(() => {
    if (pairingModes.length !== 0 && rubrics.length !== 0) {
      fetchCourses()
    }
  }, [fetchCourses, pairingModes, rubrics]);
  
  useEffect(() => {
    setSidebarContent({
      Courses: courses.length > 0 ? courses.map((course) => course.code) : [],
    })
  }, [courses]);


    /**
     * Create the effect which loads all of the potential rubrics from the system 
     */
    useEffect(() => {
        fetch(process.env.REACT_APP_API_URL + "getInstructorRubrics.php", {
            method: "GET",
            credentials: "include",
        })
            .then((res) => res.json())
            .then((result) => {
                //this is an array of objects of example elements {id: 1, description: 'exampleDescription'}
                let rubricIDandDescriptions = result.rubrics.map((element) => element);
                // An array of just the descriptions of the rubrics
                setRubrics(rubricIDandDescriptions);
            })
            .catch((err) => {
                console.log(err);
            });
    }, []);

    /**
     * Get all of the pairing modes we might need to use
     */
    useEffect(() => {
        // Fetch the survey types from the API
        fetch(process.env.REACT_APP_API_URL + "getSurveyTypes.php", {
            method: "GET",
            credentials: "include"
        })
            .then((res) => res.json())
            .then((result) => {
                setPairingModes(result.survey_types);
            })
            .catch((err) => {
                console.log(err);
            });
    }, []);
  
  return (
    <>
      <SideBar route="/" content_dictionary={sidebar_content} getCourses={fetchCourses} />
      <div className="home--container">
        <div className="containerOfCourses">
          {courses.length > 0 ? (
            courses.map((course) => (
              <Course key={course.id} course={course} rubricList={rubrics} pairingModes={pairingModes} page="home" />
            ))
          ) : (
            <div className="no-course">
              <h1>No Courses Yet</h1>
            </div>
          )}
        </div>
      </div>
    </>
  );
};

export default Home;
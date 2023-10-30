import React, { useState, useEffect } from "react";
import SideBar from "../Components/Sidebar";
import Course from "../Components/Course";
import "../styles/home.css";

const History = () => {
  const [courses, setCourses] = useState([]);
  const [terms, setTerms] = useState([]);

  const getCurrentYear = () => {
    const date = new Date();
    return date.getFullYear();
  };

  // Using 2023-2024 course schedule
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

  const getSemestermAsInt = (semester) => {
    if (semester == 'fall') {
      return 4;
    } else if (semester == 'summer') {
      return 3;
    } else if (semester == 'spring') {
      return 2;
    } else {
      return 1; // winter
    }
  }

  useEffect(() => {
    fetch(
      "http://localhost/StudentSurvey/backend/instructor/instructorTermsPost.php",
      {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: new URLSearchParams({
          currentYear: getCurrentYear(),
          currentSemester: getCurrentSemester(),
        }),
      }
    )
      .then((res) => res.json())
      .then((result) => {
        setTerms(result)

        const all_courses = []

        const fetchCourses = result.map((term) => {
          
          return fetch(
            "http://localhost/StudentSurvey/backend/instructor/instructorCoursesInTerm.php",
            {
              method: "POST",
              headers: {
                "Content-Type": "application/x-www-form-urlencoded",
              },
              body: new URLSearchParams({
                semester: getSemestermAsInt(term.semester),
                year: parseInt(term.year),
              }),
            }
          )
            .then((res2) => res2.json())
            .then((result2) => {
              all_courses.push(...result2)
            })
            .catch(err => {
              console.log(err)
            })

        });

        Promise.all(fetchCourses)
        .then(() => {
          console.log("All Courses", all_courses)
          setCourses(all_courses); // Update the courses state with all courses
        })
        .catch(err => {
          console.log(err);
        });
        console.log("Fetched Courses", fetchCourses)
   

      })
      .catch(err => {
        console.log(err)
      })
  }, []);

  console.log(terms)
  const sidebar_content = {
    Terms: terms.length > 0 ? terms.map((term) => term.semester.charAt(0).toUpperCase() + term.semester.slice(1) + " " + term.year) : [],
    Courses: courses.length > 0 ? courses.map((course) => course.code) : [],
  };

  return (
    <>
      <SideBar route="/history" content_dictionary={sidebar_content} />
      <div className="container home--container">
        {courses.length > 0 ? (

          courses.map((course) => (
            <Course key={course.id} course={course} page="history" />
          ))
          ) : (
            <div className="no-course">
              <h1>No Courses Found</h1>
            </div>
          )
        }
      </div>
    </>
  );
};

export default History;

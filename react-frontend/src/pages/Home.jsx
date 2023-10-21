import React, { useEffect, useState } from "react";
import SideBar from "../Components/Sidebar";
import "../styles/home.css";
import Course from "../Components/Course";

const Home = () => {
  const [courses, setCourses] = useState([]);

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

  useEffect(() => {
    fetch(
      "http://localhost/StudentSurvey/backend/instructor/instructorCoursesInTerm.php",
      {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: new URLSearchParams({
          instructor_id: 1,
          semester: getCurrentSemester(),
          year: getCurrentYear(),
        }),
      }
    )
      .then((res) => res.json())
      .then((result) => {
        setCourses(result);
        console.log(result);
      });
  }, []);

  const sidebar_content = {
    Courses: courses ? courses.map((course) => course.code) : [],
  };

  return (
    <>
      <SideBar route="/" content_dictionary={sidebar_content} />
      <div className="container home--container">
        {courses.length > 0 ? (
          courses.map((course) => (
            <Course key={course.id} course={course} page="home" />
          ))
        ) : (
          <div className="no-course">
            <h1>No courses yet.</h1>
          </div>
        )}
      </div>
    </>
  );
};

export default Home;

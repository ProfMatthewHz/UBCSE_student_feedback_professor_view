import React, { useState, useEffect } from "react";
import SideBar from "../Components/Sidebar";
import Course from "../Components/Course";
import "../styles/home.css";

const History = () => {
  const [courses, setCourses] = useState([]);

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
          semester: 2,
          year: 2024,
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
    Terms: [],
    Courses: courses ? courses.map((course) => course.code) : [],
  };

  return (
    <>
      <SideBar route="/history" content_dictionary={sidebar_content} />
      <div className="container home--container">
        {courses ? (
          courses.map((course) => (
            <Course key={course.id} course={course} page="history" />
          ))
        ) : (
          <div className="no-course">
            <h1>No courses found.</h1>
          </div>
        )}
      </div>
    </>
  );
};

export default History;

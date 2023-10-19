import React, { useEffect, useState } from "react";
import SideBar from "../Components/Sidebar";
import "../styles/home.css";
import Course from "../Components/Course";

const Home = () => {
  const [courses, setCourses] = useState([]);

  useEffect(() => {
    fetch("http://localhost/StudentSurvey/backend/instructor/instructorCoursesInTerm.php",
      {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: new URLSearchParams({
          'instructor_id': 1,
          'semester': 2,
          'year': 2024
        }),
      })
      .then((res) => res.json())
      .then((result) => {
        setCourses(result)
        console.log(result)
      });
  }, []);

  const sidebar_content = {
    Courses: courses.length > 0 ? courses.map((course) => course.code) : [],
  };

  return (
    <>
      <SideBar route="/" content_dictionary={sidebar_content} />
      <div className="container">
        {courses.length > 0 ? (
          courses.map((course) => <Course key={course.id} course={course} />)
        ) : (
          <h1>No courses yet.</h1>
        )}
      </div>
    </>
  );
};

export default Home;

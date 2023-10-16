import React from "react";
import SideBar from "../Components/Sidebar";
import "../styles/home.css";
import Course from "../Components/Course";

const Home = () => {
  const courses = [
    {
      id: "1",
      code: "CSE199",
      name: "Course Name",
      surveys: [
        {
          id: "1",
          startDate: "2023-09-19 08:31:19",
          endDate: "2023-09-19 08:31:19",
          name: "Dummy Name 1",
          completion: 67,
        },
        {
          id: "2",
          startDate: "2023-09-19 08:31:19",
          endDate: "2023-09-19 08:31:19",
          name: "Dummy Name 2",
          completion: 67,
        },
      ],
    },
    {
      id: "2",
      code: "CSE999",
      name: "Course Name 2",
      surveys: [
        {
          id: "1",
          startDate: "2023-09-19 08:31:19",
          endDate: "2023-09-19 08:31:19",
          name: "Dummy Name 1",
          completion: 67,
        },
      ],
    },
    {
      id: "3",
      code: "CSE199",
      name: "Course Name",
      surveys: [
        {
          id: "1",
          startDate: "2023-09-25 08:31:19",
          endDate: "2023-09-31 08:31:19",
          name: "Dummy Name 1",
          completion: 67,
        },
      ],
    },
    {
      id: "4",
      code: "CSE999",
      name: "Course Name 2",
      surveys: [
        {
          id: "1",
          startDate: "2023-09-25 08:31:19",
          endDate: "2023-09-31 08:31:19",
          name: "Dummy Name 1",
          completion: 67,
        },
      ],
    },
    {
      id: "5",
      code: "CSE115",
      name: "Intro to Computer Science",
      surveys: [
        {
          id: "1",
          startDate: "2023-09-19 08:31:19",
          endDate: "2023-09-21 08:31:19",
          name: "Dummy Name 1",
          completion: 67,
        },
      ],
    },
    {
      id: "6",
      code: "CSE115",
      name: "Intro to Computer Science",
      surveys: [
        {
          id: "1",
          startDate: "2023-09-07 08:31:19",
          endDate: "2023-09-08 08:31:19",
          name: "Dummy Name 1",
          completion: 67,
        },
      ],
    },
  ];
  
  const sidebar_content = {"Courses(Fall 2023)": courses.length > 0 ? (
    courses.map((course) => course.code
  )): (
    []
  )}

  return (
    <>
      <SideBar route="/" content_dictionary={sidebar_content}/>
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

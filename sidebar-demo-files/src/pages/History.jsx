import React from "react";
import SideBar from "../Components/Sidebar";
import Course from "../Components/Course";

const History = () => {
  const courses = [
    {
      id: "1",
      code: "HIS123",
      name: "Introduction to Computer Science II",
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
      id: "2",
      code: "Pizza",
      name: "Introduction to Computer Science II",
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
      code: "Pizza2",
      name: "Introduction to Computer Science II",
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
      id: "4",
      code: "Pizza3",
      name: "Introduction to Computer Science II",
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
  ];

  const sidebar_content = {
    Terms: [],
    Courses: courses ? courses.map((course) => course.code) : [],
  };
  return (
    <>
      <SideBar route="/history" content_dictionary={sidebar_content}/>
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

export default History;

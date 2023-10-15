import React from "react";
import SideBar from "../Components/Sidebar";
import Course from "../Components/Course";

const History = () => {
  const courses = [
    {
      id: "1",
      code: "CSE116",
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

  return (
    <>
      <SideBar />
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

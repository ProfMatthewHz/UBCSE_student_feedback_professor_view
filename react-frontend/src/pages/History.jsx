import React from "react";
import SideBar from "../Components/Sidebar";
import Course from "../Components/Course";

const History = ({ courses }) => {

  const sidebar_content = {
    "Terms": []
    ,
    "Courses": courses ? (courses.map((course) => course.code)): ([])
  }

  return (
    <>
      <SideBar route="/history" content_dictionary={sidebar_content}/>
      <div className="container">
        {courses ? (
          courses.map((course) => <Course key={course.id} course={course} page="history" />)
        ) : (
          <h1>No courses yet.</h1>
        )}
      </div>
    </>
  );
};

export default History;

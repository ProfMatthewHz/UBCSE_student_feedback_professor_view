import React, { useEffect, useState } from "react";
import SideBar from "../Components/Sidebar";
import "../styles/home.css";
import Course from "../Components/Course";

const Home = ({ courses }) => {

  const sidebar_content = {
    Courses: courses ? courses.map((course) => course.code) : [],
  };

  return (
    <>
      <SideBar route="/" content_dictionary={sidebar_content} />
      <div className="container">
        {courses ? (
          courses.map((course) => <Course key={course.id} course={course} page="home" />)
        ) : (
          <h1>No courses yet.</h1>
        )}
      </div>
    </>
  );
};

export default Home;

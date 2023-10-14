import React from "react";
import SideBar from "../Components/Sidebar";


const Home = () => {
  return (
    <>
      <SideBar route="/" content_dictionary={{
        Courses: ["CSE 789", "CSE 987", "CSE 404", "CSE312", "CSE302", "CSE 789", "CSE 987", "CSE 404", "CSE312"]
      }}/>
      <div>Home</div>
    </>
  );
};

export default Home;

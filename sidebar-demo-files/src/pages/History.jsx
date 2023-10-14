import React from "react";
import SideBar from "../Components/Sidebar";

const History = () => {
  return (
    <>
      <SideBar route="/history" content_dictionary={{
        Terms: ["Fall 2023", "Spring 2023", "Fall 2022", "Spring 2022", "Fall 2021", "Spring 2021", "Fall 2020", "Spring 2020", "Fall 2019"],
        Courses: ["CSE 789", "CSE 987", "CSE 404", "CSE312", "CSE302"]
      }}/>
      <div>History</div>
    </>
  );
};

export default History;

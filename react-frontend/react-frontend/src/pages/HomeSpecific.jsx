import "../styles/home.css";
import React from "react";
import SideBar from "../Components/Sidebar";
import Course from "../Components/Course";


const currentUrl = window.location.href;
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


// example url is http://localhost:3000/history/Spring2023/CSE116 or  http://localhost:3000/history/Spring2023
// or http://localhost:3000/history/CSE116

let coursesToUse = [];
console.log("on the home specific page")

let splitUrl = currentUrl.split("/");
//4 and above is the useful part
let length = splitUrl.length;
let term = splitUrl[3];
console.log(term);

courses.forEach((course) => {
    
        
        
    if(course.code === term ){
        console.log(course.code);
        coursesToUse.push(course);
        } 
    });



    const HomeSpecific = () => {
        
        const sidebar_content = {
            
            "Courses": coursesToUse.length > 0 ? (coursesToUse.map((course) => course.code)): ([])
          }
          return (
            <>
              <SideBar route="/" content_dictionary={sidebar_content}/>
              <div className="container">
                {coursesToUse.length > 0 ? (
                  coursesToUse.map((course) => <Course key={course.id} course={course} />)
                ) : (
                  <h1>No courses yet.</h1>
                )}
              </div>
            </>
          ); 
    }


  export default HomeSpecific;






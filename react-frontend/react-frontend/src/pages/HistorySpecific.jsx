import { BrowserRouter as Router, Route, Routes } from 'react-router-dom'
import React from "react";
import SideBar from "../Components/Sidebar";
import Course from "../Components/CourseHistory";

//below are all the courses in history. Every term included. Goal is to wittle down based on the url
const courses = [
    {
      id: "1",
      code: "CSE116",
      name: "Introduction to Computer Science II",
      surveys: [
        {
          id: "cse116SampleSurvey1",
          startDate: "2023-04-19 08:31:19",
          endDate: "2023-04-22 08:31:19",
          name: "Sample Survey",
          completion: 67,
        },
        {
          id: "cse116TeamworkSurvey1",
          startDate: "2023-04-19 08:31:19",
          endDate: "2023-04-23 08:31:19",
          name: "Teamwork Survey",
          completion: 67,
        },
      ],
    },
    {
      id: "2",
      code: "CSE312",
      name: "Introduction to Web Developement",
      surveys: [
        {
          id: "cse312TeamworkSurvey2",
          startDate: "2023-04-19 08:31:19",
          endDate: "2023-04-24 08:31:19",
          name: "Teamwork Survey",
          completion: 67,
        },
      ]
      },
        {
          id: "3",
          code: "CSE116",
          name: "Introduction to Computer Science II",
          surveys: [
            {
              id: "cse116SampleSurvey1",
              startDate: "2022-09-19 08:31:19",
              endDate: "2022-09-22 08:31:19",
              name: "Sample Survey",
              completion: 67,
            },
            {
              id: "cse116TeamworkSurvey1",
              startDate: "2022-09-19 08:31:19",
              endDate: "2022-09-23 08:31:19",
              name: "Teamwork Survey",
              completion: 67,
            },
          ],
        },
        {
          id: "4",
          code: "CSE312",
          name: "Introduction to Web Developement",
          surveys: [
            {
              id: "cse312TeamworkSurvey3",
              startDate: "2022-09-19 08:31:19",
              endDate: "2022-09-24 08:31:19",
              name: "Teamwork Survey",
              completion: 67,
            },
      ],
    },
    {
      id: "5",
      code: "CSE116",
      name: "Introduction to Computer Science II",
      surveys: [
        {
          id: "cse116SampleSurvey1",
          startDate: "2022-04-19 08:31:19",
          endDate: "2022-04-22 08:31:19",
          name: "Sample Survey",
          completion: 67,
        },
        {
          id: "cse116TeamworkSurvey1",
          startDate: "2022-04-19 08:31:19",
          endDate: "2022-04-23 08:31:19",
          name: "Teamwork Survey",
          completion: 67,
        },
      ],
    },
    {
      id: "5",
      code: "CSE312",
      name: "Introduction to Web Developement",
      surveys: [
        {
          id: "cse312TeamworkSurvey3",
          startDate: "2022-04-19 08:31:19",
          endDate: "2022-04-24 08:31:19",
          name: "Teamwork Survey",
          completion: 67,
        },
  ],
},
  ];

//url would be in form '/history/term' or '/history/term/course

const currentUrl = window.location.href;
console.log(currentUrl);


// example url is http://localhost:3000/history/Spring2023/CSE116 or  http://localhost:3000/history/Spring2023
// or http://localhost:3000/history/CSE116

let splitUrl = currentUrl.split("/");
//4 and above is the useful part
let length = splitUrl.length;
console.log(length);
let coursesToUse = [];

//this is the http://localhost:3000/history/Spring2023 or http://localhost:3000/history/CSE116 example url
if(length<6){
    //could be course or a term
    let termOrCourse = splitUrl[4];
    console.log(termOrCourse);
    let withPadding = termOrCourse + "randomstringforpadding"
    let subString1 = "Fall";
    let subString2 = "Spring";
    let isTerm = false;
    if(withPadding.includes(subString1) || withPadding.includes(subString2)){
        isTerm = true;

    }
    if(isTerm){
        //if its just a term without a a course then list all the courses for that term
        // termAndYear is a list in the form [Spring/Fall,20xx] 
        let termAndYear = termOrCourse.split("_");
        let term = termAndYear[0];
        let year = termAndYear[1];
        
        courses.forEach((course) => {
            let yearAndDateCourse = course.surveys[0].startDate.split("-");
            //yearAndDateCourse is in example form [2022,04,19 08:31:19]
            let number = Number(yearAndDateCourse[1]);
            let numberTermRepresentation = "";
            if(number>=9){
                numberTermRepresentation = "Fall";
            }
            if(number>1 && number<6){
                numberTermRepresentation = "Spring";
            }
            
            if(year === yearAndDateCourse[0] && numberTermRepresentation === term ){
                coursesToUse.push(course);
            } 
        });
        }
        console.log("stops here");
        
    if(isTerm==false){
         //http://localhost:3000/history/CSE116 example url. Print all courses in history regardless of term or year.
         courses.forEach((course) => {
            console.log(termOrCourse)
                
                
            if(course.code === termOrCourse ){
                console.log(course.code);
                coursesToUse.push(course);
                } 
            });
        
    }
        

    }
    if(length==6){
        //this is the http://localhost:3000/history/Spring2023/course or http://localhost:3000/history/CSE116/term example url

        let termOrCourse = splitUrl[4];
        let withPadding = termOrCourse + "randomstringforpadding"
        let subString1 = "Fall";
        let subString2 = "Spring";
        let isTerm = false;
        if(withPadding.includes(subString1) || withPadding.includes(subString2)){
            isTerm = true;

        }
        if(isTerm){
            let termAndYear = termOrCourse.split("_");
            let term = termAndYear[0];
            let year = termAndYear[1];
        
            courses.forEach((course) => {
                let yearAndDateCourse = course.surveys[0].startDate.split("-");
                //yearAndDateCourse is in example form [2022,04,19 08:31:19]
                let number = Number(yearAndDateCourse[1]);
                let numberTermRepresentation = "";
                if(number>=9){
                    numberTermRepresentation = "Fall";
                }
                if(number>1 && number<6){
                    numberTermRepresentation = "Spring";
                }
                
                if(year === yearAndDateCourse[0] && numberTermRepresentation === term ){
                    let courseForTheTerm = splitUrl[5];
                    if(course.code == courseForTheTerm){
                        coursesToUse.push(course);
                    }
                } 
            });

        }
        if(isTerm == false){
            let termAndYear = splitUrl[5];
            termAndYear = termOrCourse.split("_");
            let term = termAndYear[0];
            let year = termAndYear[1];
        
            courses.forEach((course) => {
                let yearAndDateCourse = course.surveys[0].startDate.split("-");
                //yearAndDateCourse is in example form [2022,04,19 08:31:19]
                let number = Number(yearAndDateCourse[1]);
                let numberTermRepresentation = "";
                if(number>=9){
                    numberTermRepresentation = "Fall";
                }
                if(number>1 && number<6){
                    numberTermRepresentation = "Spring";
                }
                
                if(year === yearAndDateCourse[0] && numberTermRepresentation === term ){
                    let courseForTheTerm = termOrCourse;
                    if(course.code == courseForTheTerm){
                        coursesToUse.push(course);
                    }
                } 
            });

        }


    }
    
    


    

   






  //courses.forEach((course) => {
    //console.log(score);
    //});

    const HistorySpecific = () => {
        console.log(coursesToUse);
        const sidebar_content = {
            "Terms": []
            ,
            
            "Courses": coursesToUse.length > 0 ? (coursesToUse.map((course) => course.code)): ([])
          }
          return (
            <>
              <SideBar route="/history" content_dictionary={sidebar_content}/>
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


  export default HistorySpecific;
import React from "react";
import SideBar from "../Components/Sidebar";
import Course from "../Components/CourseHistory";

const History = () => {
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

  const sidebar_content = {
    "Terms": ["Spring_2023", "Fall_2022", "Spring_2022"]
    ,
    
    "Courses": courses.length > 0 ? (courses.map((course) => course.code)): ([])
  }

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

import React, { useEffect, useState } from "react";
import "../styles/course.css";

// Our formatDate function
// function formatDate(dateStr) {
//   const date = new Date(dateStr);
//   const month = date.getMonth() + 1;
//   const day = date.getDate();
//   const year = date.getFullYear().toString().substr(-2);
//   const hour = date.getHours() % 12 || 12;
//   const minute = date.getMinutes().toString().padStart(2, "0");
//   const meridiem = date.getHours() >= 12 ? "pm" : "am";

//   return `${month}/${day}/${year} at ${hour}:${minute}${meridiem}`;
// }

const Course = ({ course, page }) => {
  const [surveys, setSurveys] = useState([]);

  useEffect(() => {
    fetch(
      "http://localhost/StudentSurvey/backend/instructor/courseSurveysQueries.php",
      {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: new URLSearchParams({
          "course-id": course.id,
        }),
      }
    )
      .then((res) => res.json())
      .then((result) => {
        console.log(result);
        if (page === "home") {
          setSurveys(result.active);
        } else if (page === "history") {
          setSurveys(result.expired);
        }
      });
    console.log(surveys);
  }, []);

  return (
    <div className="courseContainer">
      <div className="courseContent">
        <div className="courseHeader">
          <h2>
            {course.code}: {course.name}
          </h2>
          <div className="courseHeader-btns">
            <button className="btn add-btn">+ Add Survey</button>
            <button className="btn update-btn">Update Roster</button>
          </div>
        </div>
        {surveys.length > 0 ? (
          <table className="surveyTable">
            <thead>
              <tr>
                <th>Survey Name</th>
                <th>Dates Available</th>
                <th>Completion Rate</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              {surveys.map((survey) => (
                <tr key={survey.id}>
                  <td>{survey.name}</td>
                  <td>
                    Begins: {survey.start_date}
                    <br />
                    Ends: {survey.end_date}
                  </td>
                  <td>{survey.completion}</td>
                  <td>{/* actions button goes here */}</td>
                </tr>
              ))}
            </tbody>
          </table>
        ) : (
          <div className="no-surveys">
            <h1>
              {page === "home"
                ? `No surveys yet!`
                : `No surveys for this course!`}
            </h1>
          </div>
        )}
      </div>
    </div>
  );
};

export default Course;

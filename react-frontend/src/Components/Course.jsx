import React, { useEffect, useState } from "react";
import "../styles/course.css";

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
        setSurveys([...result.active, ...result.expired]);
      })
      .catch(err => {
        console.log(err)
      })

  }, []);

  return (
    <div id={course.code} className="courseContainer">
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
                  <td><button>Actions</button></td>
                </tr>
              ))}
            </tbody>
          </table>
        ) : (
          <div className="no-surveys">
            <h1>
              {page === "home"
                ? `No Surveys Yet!`
                : `No Surveys for This Course!`}
            </h1>
          </div>
        )}
      </div>
    </div>
  );
};

export default Course;

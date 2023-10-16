import React from "react";
import "../styles/course.css";
import { Link, NavLink } from "react-router-dom";

// Our formatDate function
function formatDate(dateStr) {
    const date = new Date(dateStr);
    const month = date.getMonth() + 1;
    const day = date.getDate();
    const year = date.getFullYear().toString().substr(-2);
    const hour = date.getHours() % 12 || 12;
    const minute = date.getMinutes().toString().padStart(2, '0');
    const meridiem = date.getHours() >= 12 ? 'pm' : 'am';
    
    return `${month}/${day}/${year} at ${hour}:${minute}${meridiem}`;
  }



const Course = ({ course }) => {

  return (
    <div className="courseContainer">
      <div className="courseContent">
        <div className="courseHeader">
          <h2>
            {course.code}: {course.name}
          </h2>
        </div>
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
            {course.surveys ? (
              course.surveys.map((survey) => (
                <tr key={survey.id}>
                  <td>{survey.name}</td>
                  <td>
                    Begins: {formatDate(survey.startDate)}
                    <br />
                    Ends: {formatDate(survey.endDate)}
                  </td>
                  <td>{survey.completion}% Completed</td>
                  <td>  
                        
                        <NavLink to={survey.id} style={{ textDecoration: 'none' }} > 
                        <div className = "viewResultsHistory"> View Results</div>
                        </NavLink> 
                        
                 </td>
                </tr>
              ))
            ) : (
              <div></div>
            )}
          </tbody>
        </table>
      </div>
    </div>
  );
};

export default Course;
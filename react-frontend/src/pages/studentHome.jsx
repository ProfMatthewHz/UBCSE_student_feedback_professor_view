import React, {useEffect, useState} from "react";
import { useNavigate } from 'react-router-dom';
import StudentSideBar from "../Components/studentSidebar";
import "../styles/home.css";
import "../styles/rubricCourse.css";
import Modal from "../Components/RubricModal";


/**
 * The Home component is the main component that users see when they visit the home page.
 * It displays a list of courses for the current semester and year, fetched from an external API.
 */
const StudentHome = () => {
  // State to store the list of courses
  const [rubricCurrent, setRubricCurrent] = useState([]);
  const [rubricPast, setRubricPast] = useState([]);
  const [rubricFuture, setRubricFuture] = useState([]);
  const [openModal,setOpenModal] = useState(false);
  
  const navigate = useNavigate();

  //redirects to temporary page for open action buton
  const comingSoon = () => {
    // Navigate to the 'comingsoon' page
    navigate('/SurveyForm');
  };

  //reformat time to 00:00:00 PM/AM
  const reformatTime = (data) => {
    const dataList = data.split(' ');
    const timeString = dataList[1];
    const date = new Date(`1970-01-01T${timeString}Z`);
    const formattedTime = date.toLocaleTimeString('en-US', {
      hour12: true,
      hour: 'numeric',
      minute: '2-digit',
      second: '2-digit',
    });

    return formattedTime;
  };

  //reformat date to month/day/yr
  const reformatDate = (data) => {
    const dataList = data.split(' ');
    const originalDate= dataList[0];
    const dateParts = originalDate.split('-');
    const year = dateParts[0];
    const month = dateParts[1];
    const day = dateParts[2];
    // Create a new Date object with the parts
    const reformattedDate = new Date(`${month}/${day}/${year}`);

    // Use the toLocaleDateString method to format the date as MM/DD/YYYY
    const formattedDateString = reformattedDate.toLocaleDateString('en-US');

    return formattedDateString;
  }

  //sort surveys from soonest closed date to latest
  const sortByDate= (dataArray) => {
    return [...dataArray].sort((a, b) => {
      const dateA = new Date(a.closingDate.date);
      const dateB = new Date(b.closingDate.date);
      return dateA - dateB;
    });
  };

  //choose action name for open survey button 
  const openChooseAction = (amtCompleted) => {
    if (amtCompleted === 0) {
      return "Start";
    } else if (amtCompleted === 100) {
      return "Revise";
    } else {
      return "Continue";
    }

  }

/**  Checks how far away the due date is from the current date.
* Return 0 if due date > 3 days away
* Return 1 if due date < 3 days away
*/
  const dateWarning = (date) => {
    
      const currentDate = new Date();
      const inputDate = new Date(date);
      const threeDaysAhead = new Date();
      threeDaysAhead.setDate(currentDate.getDate() + 3);
    
      const result = inputDate <= threeDaysAhead ? 1 : 0;
    
      return result;
    
  }


  const fetchCurrent = () => {
      // Adjust the URL to point to your surveys endpoint and include the survey type query parameter
      const url = `${process.env.REACT_APP_API_URL_STUDENT}endpoints.php?type=current`;

      fetch(url, {
          method: "GET",
          // Note: Removed the 'Content-Type' header and 'body' since it's a GET request
      })
          .then((res) => res.json())
          .then((result) => {
              // Assuming you have a way to set the surveys in your state or UI, similar to how courses were set
              setRubricCurrent(result); // Consider renaming this function to reflect that it now sets surveys, not courses
          })
          .catch((err) => {
              console.log(err);
          });
  };

  useEffect(() => {
      fetchCurrent()
  }, []);

  console.log("CURRENT");
  console.log(rubricCurrent);

  //past evals
  const fetchPast = () => {
      // Adjust the URL to point to your surveys endpoint and include the survey type query parameter
      const url = `${process.env.REACT_APP_API_URL_STUDENT}endpoints.php?type=past`;

      fetch(url, {
          method: "GET",
          // Note: Removed the 'Content-Type' header and 'body' since it's a GET request
      })
          .then((res) => res.json())
          .then((result) => {
              // Assuming you have a way to set the surveys in your state or UI, similar to how courses were set
              setRubricPast(result); // Consider renaming this function to reflect that it now sets surveys, not courses
          })
          .catch((err) => {
              console.log(err);
          });
  };

  useEffect(() => {
      fetchPast()
  }, []);
  console.log("PAST");
  console.log(rubricPast);


  const fetchFuture = () => {
      // Adjust the URL to point to your surveys endpoint and include the survey type query parameter
      const url = `${process.env.REACT_APP_API_URL_STUDENT}endpoints.php?type=upcoming`;

      fetch(url, {
          method: "GET",
          // Note: Removed the 'Content-Type' header and 'body' since it's a GET request
      })
          .then((res) => res.json())
          .then((result) => {
              // Assuming you have a way to set the surveys in your state or UI, similar to how courses were set
              setRubricFuture(result); // Consider renaming this function to reflect that it now sets surveys, not courses
          })
          .catch((err) => {
              console.log(err);
          });
  };

  useEffect(() => {
      fetchFuture()
  }, []);
console.log("Future Surveys")
console.log(rubricFuture)

  //API call to update view count in database for the specified student
const fetchFeedbackCount = (student_id, survey_id) => {
      // Send student_id and survey_id back to student
      const url = `${process.env.REACT_APP_API_URL}studentSurveyCount.php?survey_id=${survey_id}&student_id=${student_id}`;

      return fetch(url, {
          method: "GET",
      })
      .then((res) => {
          if (!res.ok) {
              throw new Error('HTTP Response error');
          }
          return res.json();
      })
      .catch((err) => {
          console.error('There was a problem with your fetch operation:', err);
          return "Not Available"; 
      });
  };


  //When the "View Feedback " button is clicked: update feedback count, then open the feedback modal
  const combinedClickHandler = (postData) => { //updates feedback count and opens feedback modal
    const { student_id, survey_id } = postData; 
    fetchFeedbackCount(student_id, survey_id); 
    console.log("View Feedback Clicked")
    setOpenModal(true);
  };


  return (
    <>
    <Modal open={openModal} onClose={()=>setOpenModal(false)}/>
      <StudentSideBar />
      <div className="home--container">
        <div className="containerOfCourses">
          <div id="Open Surveys" class="courseContainer">
          <div className="courseContent">
                <div className="courseHeader">
                    <h2>
                        Open Surveys
                    </h2>
                </div>
                {rubricCurrent.length > 0 ? (
                        <table className="surveyTable">
                          <thead>
                            <tr>
                              <th>Survey Closes</th>
                              <th>Course Name</th>
                              <th>Survey Name</th>
                              <th>Completion Rate</th>
                              <th>Action</th>


                            </tr>
                          </thead>

                          <tbody>
                            {sortByDate(rubricCurrent).map((item, index) => (
                              <tr key={index} className="survey-row">

                                {/* if the date is < 3 days away, make the text red */}
                                {dateWarning(item.closingDate.date) > 0?(
                                    <td><div className="warning">
                                    {reformatDate(item.closingDate.date)}
                                    <br />
                                    {reformatTime(item.closingDate.date)}
                                    </div> </td>


                                  ):(
                                    <td>
                                    {reformatDate(item.closingDate.date)}
                                    <br />
                                    {reformatTime(item.closingDate.date)}
                                  </td>
                                ) }
                                
                                <td>{item.courseName}</td>
                                <td>{item.surveyName}</td>
                                <td>{item.completionRate*100}% Completed</td>
                                <td><button onClick={comingSoon}>{openChooseAction(item.completionRate*100)}</button></td>
                              </tr>
                            ))}
                          </tbody>
                        </table>
                      ) : (
                        <table className="surveyTable">
                        <thead>
                          <tr>
                            <th>No Open Surveys</th>
                            
                          </tr>
                        </thead>
                        </table>
                      )}
                
                
            </div>
          </div>

          <div id="Future Surveys" class="courseContainer">
          <div className="courseContent">
                <div className="courseHeader">
                    <h2>
                        Future Surveys
                    </h2>
                </div>
                 {rubricFuture.length > 0 ? (
                        <table className="surveyTable">
                          <thead>
                            <tr>
                              <th>Survey Closes</th>
                              <th>Course Name</th>
                              <th>Survey Name</th>
                            </tr>
                          </thead>

                          <tbody>
                            {sortByDate(rubricFuture).map((item, index) => (
                              <tr key={index} className="survey-row">
                                <td>
                                  {reformatDate(item.closingDate.date)}
                                  <br />
                                  {reformatTime(item.closingDate.date)}
                                </td>
                                <td>{item.courseName}</td>
                                <td>{item.surveyName}</td>
                              
                              </tr>
                            ))}
                          </tbody>
                        </table>
                      ) : (
                        <table className="surveyTable">
                        <thead>
                          <tr>
                            <th>No Future Surveys</th>
                            
                          </tr>
                        </thead>
                        </table>
                      )}
            </div>
          </div>


          <div id="Closed Surveys" class="courseContainer">
          <div className="courseContent">
                <div className="courseHeader">
                    <h2>
                        Closed Surveys
                    </h2>
                </div>
               
                

                {rubricPast.length > 0 ? (
                        <table className="surveyTable">
                          <thead>
                            <tr>
                                <th>Survey Closed</th>
                                <th>Course Name</th>
                                <th>Survey Name</th>
                                <th>Submission</th>
                                <th>Feedback</th>
                            </tr>
                          </thead>

                          <tbody>
                            {sortByDate(rubricPast).map((item, index) => (
                              <tr key={index} className="survey-row">
                                <td>
                                  {reformatDate(item.closingDate.date)}
                                  <br />
                                  {reformatTime(item.closingDate.date)}
                                </td>
                                <td>{item.courseName}</td>
                                <td>{item.surveyName}</td>
                                {/* <td><button>View Submission</button></td> */}
                                <td></td>
                               <td><button onClick={() => combinedClickHandler({"student_id":item.student_id,"survey_name":item.survey_name,"survey_id":item.surveyID})}>View Feedback</button></td>
                               
                              </tr>
                            ))}
                          </tbody>
                        </table>
                      ) : (
                        <table className="surveyTable">
                        <thead>
                          <tr>
                            <th>No Closed Surveys</th>
                            
                          </tr>
                        </thead>
                        </table>
                      )}



            </div>
            
          </div>



        </div>
      </div>
    </>
  );
};

export default StudentHome;

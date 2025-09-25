import React, {useEffect, useState, useCallback} from "react";
import "../styles/home.css";
import "../styles/rubricCourse.css";
import "../styles/sidebar.css";
import StudentSurveyFeedbackModal from "./StudentSurveyFeedbackModal";
import SurveyFormModal from "./SurveyFormModal";


/**
 * This will be rendered for students
 */
const SurveyListing = (props) => {
 // State to store the list of courses
 const [surveyFeedbackModal, setSurveyFeedbackModal] = useState(false);
 const [feedbackData, setFeedbackData] = useState(null); 
 const [surveyCurrent, setSurveyCurrent] = useState([]);
 const [surveyPast, setSurveyPast] = useState([]);
 const [surveyFuture, setSurveyFuture] = useState([]);
 const [surveyFormModal, setSurveyFormModal] = useState(false);
 const [surveyFormData, setSurveyFormData] = useState(null);

  //redirects to temporary page for open action buton
  const showSurveyForm = (surveyData) => {
    setSurveyFormData(surveyData);
    setSurveyFormModal(true);
  };

  const closeSurveyFormModal = () => {
    setSurveyFormModal(false);
    setSurveyFormData(null);
    fetchSurveys();
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

    //sort surveys from soonest closed date to latest
    const sortByDateReverse= (dataArray) => {
      return [...dataArray].sort((a, b) => {
        const dateA = new Date(a.closingDate.date);
        const dateB = new Date(b.closingDate.date);
        return dateB - dateA;
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

  const fetchSurveys = useCallback(() => {
      fetch(process.env.REACT_APP_API_URL_STUDENT + "endpoints.php", {
          method: "GET",
          credentials: "include",
      })
          .then((result) => {
            if (result.ok) {
              return result.json();
            } else if (result.status === 511) {
              // If we get a 511 error, we have been logged out of shibboleth and need to redirect to the starting page
              window.location.href = `${process.env.REACT_APP_API_START}`;
            } else {
              throw new Error('Network response was not ok');
            }
          })
          .then((result) => {
              setSurveyCurrent(sortByDate(result.current)); 
              setSurveyPast(sortByDateReverse(result.past)); 
              setSurveyFuture(sortByDate(result.future)); 
          })
          .catch((err) => {
            if (err.name === 'TypeError') {
              // If we get a TypeError, we have been logged out of shibboleth and need to redirect to the starting page
              window.location.href = `${process.env.REACT_APP_API_START}`;
            }
            console.error('There was a problem with your fetch operation:', err);
          });
  }, []);

  useEffect(() => {
      fetchSurveys()
  }, [fetchSurveys]);

  //Send JSONIFY version of {"student_id":id, "survey_name":surveyName, "survey_id":surveyID} to api for feedback to be updated
  const updateFeedbackCount = (survey_id) => {
        let formData = new FormData();
        formData.append("survey-id", survey_id);
        
        // Send student_id and survey_id back to student
        const url = process.env.REACT_APP_API_URL_STUDENT + "surveyVisitData.php";
    
        return fetch(url, {
            method: "POST",
            credentials: "include",
            body: formData
        })
        .catch((err) => {
            console.error('There was a problem with your fetch operation:', err);
            return "Not Available"; 
        });
    };

    const showSurveyFeedback = (postData) => { //updates feedback count and opens feedback modal
      updateFeedbackCount(postData["survey_id"])
      setSurveyFeedbackModal(true); 
      setFeedbackData(postData); //sends postData to rubric modal
    };

    const closeSurveyFeedback = () => {
      setSurveyFeedbackModal(false);
      setFeedbackData(null);
    };

    return (
      <>
        {surveyFeedbackModal &&
          <StudentSurveyFeedbackModal onClose={closeSurveyFeedback} modalData={feedbackData} />
        }
        {surveyFormModal &&
          <SurveyFormModal closeModal={closeSurveyFormModal} surveyInfo={surveyFormData} />
        }
        <div className="home--container">
          <div className="containerOfCourses">
            <div id="Open Surveys" className="courseContainer">
            <div className="courseContent">
                  <div className="courseHeader">
                      <h2>
                          Open Surveys
                      </h2>
                  </div>
                  {surveyCurrent.length > 0 ? (
                          <table className="surveyTable">
                            <thead>
                              <tr>
                                <th>Survey Closes</th>
                                <th>Course Name</th>
                                <th>Survey Name</th>
                                <th>Survey Completion</th>
                                <th>Action</th>
                              </tr>
                            </thead>
  
                            <tbody>
                              {surveyCurrent.map((item, index) => (
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
                                  <td><button onClick={() => showSurveyForm({course: item.courseName, survey_name: item.surveyName, survey_id: item.surveyID})}>{openChooseAction(item.completionRate*100)}</button></td>
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
  
            <div id="Future Surveys" className="courseContainer">
            <div className="courseContent">
                  <div className="courseHeader">
                      <h2>
                          Future Surveys
                      </h2>
                  </div>
                   {surveyFuture.length > 0 ? (
                          <table className="surveyTable">
                            <thead>
                              <tr>
                              <th>Survey Opens</th>
                              <th>Survey Closes</th>
                                <th>Course Name</th>
                                <th>Survey Name</th>
                              </tr>
                            </thead>
  
                            <tbody>
                              {surveyFuture.map((item, index) => (
                                <tr key={index} className="survey-row">
                                  <td>
                                    {reformatDate(item.openingDate.date)}
                                    <br />
                                    {reformatTime(item.openingDate.date)}
                                  </td>
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
  
  
            <div id="Closed Surveys" className="courseContainer">
            <div className="courseContent">
                  <div className="courseHeader">
                      <h2>
                          Closed Surveys
                      </h2>
                  </div>
                 
                  {surveyPast.length > 0 ? (
                          <table className="surveyTable">
                            <thead>
                              <tr>
                                  <th>Survey Closed</th>
                                  <th>Course Name</th>
                                  <th>Survey Name</th>
                                  {/* <th>Submission</th> */}
                                  <th>Feedback</th>
                              </tr>
                            </thead>
  
                            <tbody>
                              {surveyPast.map((item, index) => (
                                <tr key={index} className="survey-row">
                                  <td>
                                    {reformatDate(item.closingDate.date)}
                                    <br />
                                    {reformatTime(item.closingDate.date)}
                                  </td>
                                  <td>{item.courseName}</td>
                                  <td>{item.surveyName}</td>
                                  {/* <td><button>View Submission</button></td> */}
                                  {/* <td></td> */}
                                 <td><button onClick={() => showSurveyFeedback({"survey_name":item.surveyName,"survey_id":item.surveyID})}>View Feedback</button></td>
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

  export default SurveyListing;
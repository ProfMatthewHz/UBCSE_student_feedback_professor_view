import React, {useState} from "react";
import "../styles/modal.css";
import "../styles/extendsurvey.css";

const SurveyTeamAssignmentReview = ({modalClose, course, survey_data}) => {
  const [survey_id,] = useState(survey_data.id);
  const [survey_name,] = useState(survey_data.name);
  const [surveyAssigns, setSurveyAssigns] = useState([]);


  async function updateAssignments(formdata) {
    let fetchHTTP =
        process.env.REACT_APP_API_URL + "updateAssignments.php";
    
    try {
        const response = await fetch(fetchHTTP, {
            method: "POST",
            credentials: "include",
            body: formdata,
        });
        const result = await response.json();
        return result; // Return the result directly
    } catch (err) {
        throw err; // Re-throw to be handled by the caller
    }
  }

  async function verifyAndSubmit() {
    let surveyId = survey_id;
    let formData = new FormData();

    formData.append('survey-id', surveyId);
    formData.append('assignments', JSON.stringify(surveyAssigns));
    let post = await updateAssignments();
    if (post.errors) {
        modalClose([post.errors]);
    } else {
      modalClose([]);
    }
}

return (
    <div className="modal">
      <div style={{ width: "650px", maxWidth: "90vw" }}className="modal-content modal-phone">
        <div className="CancelContainer">
            <button className="CancelButton" onClick={modalClose}>
                ×
            </button>
        </div>
        <div className="modal--contents-container">
            <h2 className="modal--main-title">
                Review Assignments {course.code}: {survey_name} 
            </h2>
            <div className="extend-survey--boxes-container">
                <div className="extend-survey--top-box-container">
                </div>
            </div>
            <div className="form__item--confirm-btn-container">
            <button className="form__item--confirm-btn" onClick={verifyAndSubmit}>
             Update Assignments
            </button>
          </div>
        </div>
      </div>
    </div>
  );
}

export default SurveyTeamAssignmentReview;
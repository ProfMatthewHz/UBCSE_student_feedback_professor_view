import React, { useCallback, useState, useEffect } from "react";
import SurveyFormRows from "./SurveyFormRows";
import "../styles/surveyForm.css";
import "../styles/modal.css";

const SurveyFormModal = ({surveyInfo, closeModal}) => {
  const [surveyID, ] = useState(surveyInfo.survey_id);
  const [courseName, ] = useState(surveyInfo.course);
  const [surveyName, ] = useState(surveyInfo.survey_name);
  const [surveyTopics, setSurveyTopics] = useState(null);
  const [groupMembers, setGroupMembers] = useState([]);
  const [numberTopics, setNumberTopics] = useState(0);
  const [groupMemberIndex, setGroupMemberIndex] = useState(0);
  const [evalIDs, setEvalIDs] = useState(null);
  const [buttonText, setButtonText] = useState('NEXT');
  const [showPrevious, setShowPrevious] = useState(false)
  const [surveyResults, setSurveyResults] = useState([]);
  const [advanceButtonState, setAdvanceButtonState] = useState('red');
  const [showToast, setShowToast] = useState(false);
  const [prompt, setPrompt] = useState('');

  const sendSurveyDataToBackend = async () => {
    if (!evalIDs) {
      return true;
    }
    const formdata = new FormData();
    formdata.append('eval_id', evalIDs[groupMemberIndex]);
    formdata.append('responses', JSON.stringify(surveyResults));
    
    const response = await fetch(process.env.REACT_APP_API_URL_STUDENT + 'submitEvalForm.php', {
      method: 'POST',
      credentials: "include",
      body: formdata
    }).then(res => res.json())
      .catch((err) => {
        console.error('There was a problem with your fetch operation:', err);
        return false;
      });
    return response.success;
  };

  const nextButtonClickHandler = async () => {
    let response = true;
    // Only send results if the survey was completely filled out
    if (Object.keys(surveyResults).length === numberTopics) {
      response = await sendSurveyDataToBackend();
    }
    if (response === false) {
      setShowToast(true);
    } else {
      if (groupMemberIndex >= (groupMembers.length - 1)) {
        closeModal();
      } else {
        setShowToast(false);
        setSurveyResults([]);
        setGroupMemberIndex(groupMemberIndex + 1);
        setShowPrevious(true);
      }
    }
  }

  const previousButtonClickHandler = async () => {
    let response = true;
    // Only send results if the survey was completely filled out
    if (Object.keys(surveyResults).length === numberTopics) {
      response = await sendSurveyDataToBackend();
    }
    if (response === false) {
      setShowToast(true);
    } else {
      setShowToast(false);
      if (groupMemberIndex === 1) {
        setShowPrevious(false);
        setGroupMemberIndex(0);
      } else {
        setGroupMemberIndex(groupMemberIndex - 1);
      }
    }
  }

  useEffect(() => {
    if (groupMembers && (Object.keys(surveyResults).length === numberTopics)) {
      setAdvanceButtonState('green');
      if (groupMemberIndex >= (groupMembers.length - 1))  {
        setButtonText('FINISH');
      } else {
        setButtonText('NEXT');
      }
    } else {
      setAdvanceButtonState('red');
      if (!groupMembers || (groupMemberIndex >= (groupMembers.length - 1)))  {
        setButtonText('CLOSE');
      } else {
        setButtonText('SKIP');
      }
    }
  }, [groupMemberIndex, groupMembers, numberTopics, surveyResults]);

const fetchData = useCallback((surveyid) => {
  let formData = new FormData();
  formData.append("survey", surveyid);
  fetch(process.env.REACT_APP_API_URL_STUDENT + 'startSurvey.php', {
        method: 'POST',
        credentials: 'include',
        body: formData
    }).then((result) => {
      if (result.ok) {
        return result.json();
      } else if (result.status === 511) {
          // If we get a 511 error, we have been logged out of shibboleth and need to redirect to the starting page
          window.location.href = `${process.env.REACT_APP_API_START}`;
      } else {
        throw new Error('Network response was not ok');
      }
    })
    .then((jsonData) => {
        setSurveyTopics(jsonData.topics);
        setNumberTopics(Object.keys(jsonData.topics).length);
        setGroupMembers(Object.values(jsonData.group_members));
        setPrompt(jsonData.prompt);
        setEvalIDs(Object.keys(jsonData.group_members));
    })
    .catch((error) => {
        console.error('Error:', error);
    });
  }, []);

  useEffect(() => {
    if (surveyID) {
      // Fetch data when the component mounts or when surveyID changes
      fetchData(surveyID);
    }
  }, [surveyID, fetchData]);

  return (
    <div className="modal">
      <div style={{ minHeight: "98&", maxWidth: "100%" }} className="modal-content modal-phone">
        <div>
          {showToast && (
              <div className="top-error-bar">
                Could not submit evaluation! Please try again later.
              </div>
          )}
          <div className="Header">
              <h1 className="Survey-Name">{courseName} {surveyName}</h1>
              { groupMembers && (groupMembers.length > 1) &&
                  (<h2 className="Evaluation-Name">Evaluating {prompt} {groupMemberIndex+1}/{groupMembers.length}: {groupMembers[groupMemberIndex]}</h2> )
              }
              { groupMembers && (groupMembers.length === 1) &&
                  (<h2 className="Evaluation-Name">Evaluating: {groupMembers[groupMemberIndex]}</h2> )
              }
              { !groupMembers &&
                (<h2 className="Evaluation-Name">Loading Evaluations</h2>)
              }
          </div>
          <div>
            {evalIDs != null ? (
              <SurveyFormRows
                topicData={surveyTopics}
                setSurveyResults={setSurveyResults}
                survey_id={evalIDs[groupMemberIndex]}/>
              ) : (<div>Survey loading...</div>)
            }
           </div>
          {showPrevious && (
            <button className="directional green previous" onClick={previousButtonClickHandler}>PREVIOUS</button>
          )}
          {groupMembers != null && (
            <button className={'directional next ' + advanceButtonState} onClick={nextButtonClickHandler}> {buttonText} </button>
          )}
        </div>
      </div>
    </div>
  );
}

export default SurveyFormModal;
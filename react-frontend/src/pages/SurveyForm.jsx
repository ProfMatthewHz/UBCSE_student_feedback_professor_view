import React, { useState, useEffect } from "react";
import SurveyFormRow from "../Components/SurveyFormRow";
import Toast from "./Toast";
import "../styles/surveyForm.css";
import { useLocation, useNavigate } from "react-router-dom";

const SurveyForm = () => {
  const [surveyData, setSurveyData] = useState(null);
  const [groupMembers, setGroupMembers] = useState([]);
  const [groupMemberIndex, setGroupMemberIndex] = useState(0);
  const [reviewIDs, setReviewIDs] = useState(null);
  const [buttonText, setButtonText] = useState('NEXT');
  const [showPrevious, setShowPrevious] = useState(false)
  const [surveyResults, setSurveyResults] = useState([]);
  const [advanceButtonState, setAdvanceButtonState] = useState('red');
  const [showToast, setShowToast] = useState(false);
  const location = useLocation();
  const navigate = useNavigate();

  const sendSurveyDataToBackend = async () => {
    const formdata = new FormData();
    formdata.append('review_id', reviewIDs[groupMemberIndex]);
    formdata.append('responses', JSON.stringify(surveyResults));
    
    const response = await fetch(process.env.REACT_APP_API_URL_STUDENT + 'buildPeerEvalForm.php', {
      method: 'POST',
      credentials: "include",
      body: formdata
    });
    resolve(response.ok);
  };

  const nextButtonClickHandler = async () => {
    let response = true;
    // Only send results if the survey was completely filled out
    if (Object.keys(surveyResults).length === Object.keys(surveyData.topics).length) {
      response = await sendSurveyDataToBackend();
    }
    if (response === false) {
      setShowToast(true);
    } else {
      if (groupMemberIndex >= (groupMembers.length - 1)) {
        setSurveyResults([]);
        navigate(location.state.return_to);
      } else {
        setSurveyResults([]);
        setGroupMemberIndex(groupMemberIndex + 1);
        setShowPrevious(true);
      }
    }
  }

  const previousButtonClickHandler = async () => {
    let response = true;
    // Only send results if the survey was completely filled out
    if (Object.keys(surveyResults).length === Object.keys(surveyData.topics).length) {
      response = await sendSurveyDataToBackend();
    }
    if (response === false) {
      setShowToast(true);
    } else {
      if (groupMemberIndex === 1) {
        setShowPrevious(false);
        setGroupMemberIndex(0);
      } else {
        setGroupMemberIndex(groupMemberIndex - 1);
      }
    }
  }

  useEffect(() => {
    if (surveyData && (Object.keys(surveyResults).length === Object.keys(surveyData.topics).length)) {
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
  }, [groupMemberIndex, groupMembers, surveyData, surveyResults]);


  useEffect(() => {
    const fetchData = async () => {
        try {
            const response = await fetch(process.env.REACT_APP_API_URL + '../startSurvey.php?survey=' + location.state.survey_id, {
                method: 'GET',
                credentials: 'include'
            });

            if (!response.ok) {
                throw new Error('Network response was not ok');
            }

            const jsonData = await response.json();
            setSurveyData(jsonData);
            setGroupMembers(Object.values(jsonData.group_members));
            setReviewIDs(Object.keys(jsonData.group_members));
        } catch (error) {
            console.error('Error:', error);
        }
    };
    fetchData();
  }, [location.state.survey_id]);

  return (
    <div>
      <Toast
        message={`Could not submit evaluation! Please try again later.`}
        isVisible={showToast}
        onClose={() => setShowToast(false)}
      />
     {surveyData != null && groupMembers != null ? (
      <div className="Header">
        <h1 className="Survey-Name">{location.state.course} {location.state.survey_name}</h1>
        { groupMembers.length > 1 ? 
          (<h2 className="Evaluation-Name">Evaluating Team Member {groupMemberIndex+1}/{groupMembers.length}: {groupMembers[groupMemberIndex]}</h2> ) :
          (<h2 className="Evaluation-Name">Evaluating: {groupMembers[groupMemberIndex]}</h2> )
        }
      </div> ) : 
      (<div className="Header">
        <h1 className="Survey-Name">{location.state.course} {location.state.survey_name}</h1>
        <h2 className="Evaluation-Name">Evaluating Team Member</h2>
      </div>)
      }
      <div>
        {surveyData != null && groupMembers != null ? (
        <SurveyFormRow
            rubricData={surveyData}
            surveyResults={surveyResults}
            setSurveyResults={setSurveyResults}
            survey_id={reviewIDs[groupMemberIndex]}/>
        ) : (<div>Survey loading...</div> )
        }
      </div>
      {showPrevious && (
        <button className="directional green previous" onClick={previousButtonClickHandler}>PREVIOUS</button>
      )}
      {surveyData != null && groupMembers != null && (
      <button 
        className={'directional next ' + advanceButtonState}
        onClick={nextButtonClickHandler}>
        {buttonText}
      </button>)}
    </div>
  )
}

export default SurveyForm;
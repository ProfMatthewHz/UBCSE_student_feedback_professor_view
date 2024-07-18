import React, { useState, useEffect } from "react";
import SurveyFormRow from "../Components/SurveyFormRow";
import "../styles/surveyForm.css";
import { useLocation, useNavigate } from "react-router-dom";

const SurveyForm = () => {
  const [surveyData, setSurveyData] = useState(null);
  const [groupMembers, setGroupMembers] = useState(null);
  const [groupMemberIndex, setGroupMemberIndex] = useState(0);
  const [reviewIDs, setReviewIDs] = useState(null);
  const [buttonText, setButtonText] = useState('NEXT');
  const [showPrevious, setShowPrevious] = useState(false)
  const [surveyResults, setSurveyResults] = useState("");
  const [refreshKey, setRefreshKey] = useState(0);
  const location = useLocation();
  const navigate = useNavigate();

  const sendSurveyDataToBackend = async () => {
    const requestData = {
      review_id: reviewIDs[groupMemberIndex],
      responses: surveyResults
    };
    
    try {
      const response = await fetch(process.env.REACT_APP_API_URL_STUDENT + 'buildPeerEvalForm.php', {
        method: 'POST',
        credentials: "include",
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(requestData)
      });
  
      if (!response.ok) {
        throw new Error('Network response was not ok');
      }
  
    } catch (error) {
      console.error('Error:', error);
    }
  };

  const nextButtonClickHandler = async () => {
    setSurveyResults([]);

    if (buttonText === 'FINISH') {
      await sendSurveyDataToBackend();
      navigate(location.state.return_to);
      return; // Return early if the button text is already 'FINISH'
    } 

    setGroupMemberIndex(groupMemberIndex + 1);
    setShowPrevious(true);
    
    if (groupMemberIndex >= groupMembers.length - 2) {
      setButtonText('FINISH');
    }
    
    setRefreshKey(prevKey => prevKey + 1);
    await sendSurveyDataToBackend();
  }

  const previousButtonClickHandler = async () => {
    await sendSurveyDataToBackend();

    setButtonText('NEXT');
    if (groupMemberIndex === 1) {
      setShowPrevious(false);
      setGroupMemberIndex(0);
    } else {
      setGroupMemberIndex(groupMemberIndex - 1);
    }
    setRefreshKey(prevKey => prevKey + 1);
  }

  useEffect(() => {
    // Check if groupMembers has been set
    if (groupMembers && groupMembers.length === 1) {
      setButtonText('FINISH');
    }
  }, [groupMembers]); // Run the effect whenever groupMembers changes

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
     {surveyData != null && groupMembers != null ? (
      <div className="Header">
        <h1 className="Survey-Name">{location.state.course} {location.state.survey_name}</h1>
        <h2 className="Evaluation-Name">Evaluating Team Member {groupMemberIndex+1}/{groupMembers.length}: {groupMembers[groupMemberIndex]}</h2>
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
            survey_id={reviewIDs[groupMemberIndex]}
            key={refreshKey}/>
        ) : (<div>Survey loading...</div> )
        }
      </div>
      {showPrevious && (
        <button className="previousButton" onClick={previousButtonClickHandler}>PREVIOUS</button>
      )}
      {surveyData != null && groupMembers != null && (
      <button 
        className={Object.keys(surveyResults).length === Object.keys(surveyData.topics).length ? 'nextFinishButtonGreen': 'nextFinishButtonRed' }
        onClick={nextButtonClickHandler}>
        {Object.keys(surveyResults).length === Object.keys(surveyData.topics).length ? buttonText: buttonText === 'FINISH' ? 'FINISH' : 'SKIP'}
      </button>)}
    </div>
  )
}

export default SurveyForm
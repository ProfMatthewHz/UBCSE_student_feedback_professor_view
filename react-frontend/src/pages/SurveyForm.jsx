import React, { useState, useEffect } from "react";
import SurveyFormRow from "../Components/SurveyFormRow";
import { json, useLocation } from "react-router-dom";
import "../styles/surveyForm.css";

const SurveyForm = () => {
  const location = useLocation();
  const [surveyData, setSurveyData] = useState(null);
  const [groupMembers, setGroupMembers] = useState(null);
  const [groupMemberIndex, setGroupMemberIndex] = useState(0);
  const [reviewIDs, setReviewIDs] = useState(null);
  const [buttonText, setButtonText] = useState('NEXT');
  const [showPrevious, setShowPrevious] = useState(false)
  const [surveyResults, setSurveyResults] = useState("");
  const survey_id = location.state.survey_id + "";
  const [refreshKey, setRefreshKey] = useState(0);
  const [surveyLengthPerTopic, setSurveyLengthPerTopic] = useState(null);
  
  const sendSurveyDataToBackend = async () => {
    const requestData = {
      review_id: reviewIDs[groupMemberIndex],
      responses: surveyResults
    };
    
    try {
      const response = await fetch(process.env.REACT_APP_API_URL_STUDENT + 'buildPeerEvalForm.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(requestData)
      });
  
      if (!response.ok) {
        throw new Error('Network response was not ok');
      }
  
      // Handle successful response here if needed
  
    } catch (error) {
      console.error('Error:', error);
    }
  };

  const nextButtonClickHandler = async () => {
    setSurveyResults([]);
    if (buttonText === 'FINISH') {
      await sendSurveyDataToBackend();
      return; // Return early if the button text is already 'FINISH'
    }  
    setGroupMemberIndex(groupMemberIndex + 1);
    setShowPrevious(true);
    
    if (groupMemberIndex >= groupMembers.length - 2) {
      setButtonText('FINISH');
    }
    console.log(surveyLengthPerTopic);
    setRefreshKey(prevKey => prevKey + 1);
    
    await sendSurveyDataToBackend();
    
  }

  const previousButtonClickHandler = () => {
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
            const response = await fetch(process.env.REACT_APP_API_URL + '../startSurvey.php?survey=' +survey_id, {
                method: 'GET',
                credentials: 'include'
            });

            if (!response.ok) {
                throw new Error('Network response was not ok');
            }

            const jsonData = await response.json();
            console.log(jsonData); // Handle the response data here
            setSurveyData(jsonData);
            setGroupMembers(Object.values(jsonData.group_members));
            //setGroupMembers(jsonData.group_members);
            setReviewIDs(Object.keys(jsonData.group_members));
            console.log((jsonData.topics.responses).length);
            setSurveyLengthPerTopic(Object.keys(jsonData.topics.responses));
            console.log(Object.keys(jsonData.group_members));
        } catch (error) {
            console.error('Error:', error);
        }
    };

    fetchData();
}, []);

  // Render null if rubricData is not set, otherwise render the page content
  if (surveyData === null && groupMembers === null) {
    return null;
}

  return (
    <div>
      {console.log(surveyResults)}
      <div className="Header">
        <h1 className="Survey-Name">{location.state.course} {location.state.survey_name}</h1>
        <h2 className="Evaluation-Name">Evaluating Team Member {groupMemberIndex+1}/{groupMembers.length}: {groupMembers[groupMemberIndex]}</h2>
      </div>
      <div>
        <SurveyFormRow
            x={surveyData}
            surveyResults={surveyResults}
            setSurveyResults={setSurveyResults}
            survey_id={reviewIDs[groupMemberIndex]}
            key={refreshKey}
        />
      </div>
      {showPrevious && (
        // <div className="prevButtonContainer">
        //   <button className="previousButton" onClick={previousButtonClickHandler}>PREVIOUS</button>
        // </div>
        <button className="previousButton" onClick={previousButtonClickHandler}>PREVIOUS</button>
      )}
      
      <button 
      className={Object.keys(surveyResults).length === Object.keys(surveyData.topics).length ? 'nextFinishButtonGreen': 'nextFinishButtonRed' }
      onClick={nextButtonClickHandler}>
        {Object.keys(surveyResults).length === Object.keys(surveyData.topics).length ? buttonText: buttonText === 'FINISH' ? 'FINISH' : 'SKIP'}
        </button>
    </div>
  )
}

export default SurveyForm
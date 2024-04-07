import React, { useState, useEffect } from "react";
import SurveyFormRow from "../Components/SurveyFormRow";
import { json, useLocation } from "react-router-dom";
import "../styles/surveyForm.css";

const SurveyForm = () => {
  const location = useLocation();
  const [surveyData, setSurveyData] = useState(null);
  const [groupMembers, setGroupMembers] = useState(null);
  const [groupMemberIndex, setGroupMemberIndex] = useState(0);
  const [buttonText, setButtonText] = useState('NEXT');
  const [showPrevious, setShowPrevious] = useState(false)
  const [surveyResults, setSurveyResults] = useState("");
  const survey_id = location.state.survey_id + "";
  
  const nextButtonClickHandler = () => {
    setSurveyResults([]);
    if (buttonText === 'FINISH') {
      return; // Return early if the button text is already 'FINISH'
    }  
    setGroupMemberIndex(groupMemberIndex + 1);
    setShowPrevious(true);
    
    if (groupMemberIndex >= groupMembers.length - 2) {
      setButtonText('FINISH');
    }
  }

  const previousButtonClickHandler = () => {
    setButtonText('NEXT');
    if (groupMemberIndex === 1) {
      setShowPrevious(false);
      setGroupMemberIndex(0);
    } else {
      setGroupMemberIndex(groupMemberIndex - 1);
    }
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
            student={groupMembers[groupMemberIndex]}
        />
      </div>
      {showPrevious && (
        // <div className="prevButtonContainer">
        //   <button className="previousButton" onClick={previousButtonClickHandler}>PREVIOUS</button>
        // </div>
        <button className="previousButton" onClick={previousButtonClickHandler}>PREVIOUS</button>
      )}
      
      <button className='nextFinishButton'onClick={nextButtonClickHandler}>{buttonText}</button>
    </div>
  )
}

export default SurveyForm
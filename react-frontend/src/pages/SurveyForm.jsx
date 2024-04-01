import React, { useState, useEffect } from "react";
import SurveyFormRow from "../Components/SurveyFormRow";
import { json, useLocation } from "react-router-dom";

const SurveyForm = () => {
  const location = useLocation();
  const [surveyData, setSurveyData] = useState(null);
  const [groupMembers, setGroupMembers] = useState(null);
  const [groupMemberIndex, setGroupMemberIndex] = useState(0);
  const [buttonText, setButtonText] = useState('NEXT');
  const survey_id = location.state.survey_id + "";
  
  const buttonClickHandler = () => {
    if (buttonText === 'FINISH') {
      return; // Return early if the button text is already 'FINISH'
    }  
    setGroupMemberIndex(groupMemberIndex + 1);
    if (groupMemberIndex >= groupMembers.length - 2) {
      setButtonText('FINISH');
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
      {console.log(groupMembers)}
      <div className="Header">
        <h1 className="Survey-Name">{location.state.course} {location.state.survey_name}</h1>
        <h2 className="Evaluation-Name">{groupMembers[groupMemberIndex]}</h2>
      </div>
      <div>
        <SurveyFormRow
            x={surveyData}
        />
      </div>
      <button onClick={buttonClickHandler}>{buttonText}</button>
    </div>
  )
}

export default SurveyForm
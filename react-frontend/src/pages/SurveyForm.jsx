import React, { useState, useEffect } from "react";
import SurveyFormRow from "../Components/SurveyFormRow";
import { useLocation } from "react-router-dom";

const SurveyForm = () => {
  const location = useLocation();
  const [surveyData, setSurveyData] = useState(null);
  console.log(location.state.survey_id);
  const survey_id = location.state.survey_id + "";
  console.log(survey_id);
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
      
            // setRubricData(jsonData);
        } catch (error) {
            console.error('Error:', error);
        }
    };

    fetchData();
}, []);

  return (
    <h1>Coming Soon Sprint 3</h1>
  )
}

export default SurveyForm
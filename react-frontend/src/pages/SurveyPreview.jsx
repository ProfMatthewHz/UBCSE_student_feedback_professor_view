import React, { useState, useEffect } from "react";
import SurveyFormRow from "../Components/SurveyFormRow";
import "../styles/survey.css";
import { useLocation } from "react-router-dom";

const SurveyPreview = () => {
    const location = useLocation();
    const [rubricData, setRubricData] = useState(null);
    console.log(location.state.survey_name);
    
    useEffect(() => {
      const postData = async () => {
          try {
              const response = await fetch(process.env.REACT_APP_API_URL + "lib/getRubricTable.php", {
                  method: 'POST',
                  headers: {
                      'Content-Type': 'application/x-www-form-urlencoded',
                      // Add any additional headers if needed
                      
                  },
                  body: new URLSearchParams({
                      rubric: location.state.rubric_id, // Example rubric ID, replace with actual data
                      // Add any other POST data required by the PHP file
                  }),
              });

              if (!response.ok) {
                  throw new Error('Network response was not ok');
              }

              const jsonData = await response.json();
              console.log(jsonData); // Handle the response data here
        
              setRubricData(jsonData);
          } catch (error) {
              console.error('Error:', error);
          }
      };

      postData();
  }, []);


  // Render null if rubricData is not set, otherwise render the page content
  if (rubricData === null) {
      return null;
  }

    return (
        <div>
            <div className="Header">
                <h1 className="Survey-Name">{location.state.course} {location.state.survey_name}</h1>
                <h2 className="Evaluation-Name">Evaluating: Matthew Hertz</h2>
            </div>
            <div>
                <SurveyFormRow
                    x={rubricData}
                />
            </div>
        </div>
    )
}

export default SurveyPreview;
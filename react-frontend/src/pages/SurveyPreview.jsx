import React, { useState, useEffect } from "react";
import SurveyFormRow from "../Components/SurveyFormRow";
import "../styles/survey.css";
import { useLocation, useNavigate } from "react-router-dom";

const SurveyPreview = () => {
    const location = useLocation();
    const [rubricData, setRubricData] = useState(null);
    const Navigate = useNavigate();

    const returnButtonClickHandler = async () => {
        Navigate("../");
        return; // Simulate the student survey view
    }

    useEffect(() => {
      const postData = async () => {
          try {
              const response = await fetch(process.env.REACT_APP_API_URL + "lib/getRubricTable.php", {
                  method: 'POST',
                  credentials: "include",
                  headers: {
                      'Content-Type': 'application/x-www-form-urlencoded',
                  },
                  body: new URLSearchParams({
                      rubric: location.state.rubric_id, // Example rubric ID, replace with actual data
                  }),
              });

              if (!response.ok) {
                  throw new Error('Network response was not ok');
              }

              const jsonData = await response.json();
              setRubricData(jsonData);
          } catch (error) {
              console.error('Error:', error);
          }
      };
      postData();
    }, [location.state.rubric_id]);

    return (
        rubricData === null ? <div>No rubric to preview</div> :
        <div>
            <div className="Header">
                <h1 className="Survey-Name">{location.state.course} {location.state.survey_name}</h1>
                <h2 className="Evaluation-Name">Evaluating: Matthew Hertz</h2>
            </div>
            <div>
                <SurveyFormRow
                    rubricData={rubricData}
                    surveyResults={null}
                    setSurveyResults={null}
                    survey_id={null}
                />
            </div>
            <button className='directional next green' onClick={returnButtonClickHandler}>
              FINISH
            </button>
        </div>
    )
}

export default SurveyPreview;
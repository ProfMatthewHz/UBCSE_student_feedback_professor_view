import React from "react";
import SurveyFormRow from "../Components/SurveyFormRow";
import "../styles/survey.css";
import { useLocation } from "react-router-dom";

const SurveyPreview = () => {
    const location = useLocation();
    console.log(location.state.survey_name);
    return (
        <div>
            <div className="Header">
                <h1 className="Survey-Name">{location.state.course} {location.state.survey_name}</h1>
                <h2 className="Evaluation-Name">Evaluating: Matthew Hertz</h2>
            </div>
            <div>
                <SurveyFormRow
                    x="Teamwork"
                />
                <SurveyFormRow
                    x="Leadership"
                />
            </div>
        </div>
    )
}

export default SurveyPreview;
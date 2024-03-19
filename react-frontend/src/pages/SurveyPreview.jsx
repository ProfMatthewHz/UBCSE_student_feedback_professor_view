import React from "react";
import SurveyFormRow from "../Components/SurveyFormRow";
import "../styles/survey.css";

const SurveyPreview = () => {
    return (
        <div>
            <div className="Header">
                <h1 className="Survey-Name">CSE 302 Sprint 3 Evaluation</h1>
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
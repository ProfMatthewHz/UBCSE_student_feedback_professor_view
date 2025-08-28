import { useState, useEffect, useCallback } from "react";
import SurveyFormRows from "./SurveyFormRows";
import "../styles/modal.css";
import "../styles/surveyForm.css";

const SurveyPreviewModal = ({modalClose, surveyData}) => {
    const [surveyResults, setSurveyResults] = useState([]);
    const [rubricData, setRubricData] = useState(null);
    const [advanceButtonState, setAdvanceButtonState] = useState('red');

    useEffect(() => {
        if (rubricData && (Object.keys(surveyResults).length === Object.keys(rubricData.topics).length)) {
         setAdvanceButtonState('green');
        } else {
         setAdvanceButtonState('red');
        }
    }, [rubricData, surveyResults]);

    const getRubricData = useCallback((rubric_id) => {
      fetch(process.env.REACT_APP_API_URL + "getRubricTable.php", {
          method: 'POST',
          credentials: "include",
          headers: {
              'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: new URLSearchParams({
              rubric: rubric_id,
          }),
      })
      .then((res) => res.json())
      .then((response) => setRubricData(response))
      .catch((err) => {
        console.log(err);
      });
    }, []);

    useEffect(() => {
      getRubricData(surveyData.rubric_id);
    }, [getRubricData, surveyData.rubric_id]);

    return (
        rubricData === null ? <div>No rubric to preview</div> :
        (<div style={{ minHeight: "100vh" }} className="modal">
            <div style={{ minHeight: "98vh", padding: "0px" }} className="modal-content modal-phone">
                <div style={{ maxWidth: "100vw", maxHeight: "100vh" }} >
                    <div className="Header">
                        <h1 className="Survey-Name">{surveyData.course} {surveyData.survey_name}</h1>
                        <h2 className="Evaluation-Name">Evaluating: Matthew Hertz</h2>
                    </div>
                    <div>
                        <SurveyFormRows
                            topicData={rubricData.topics}
                            surveyResults={surveyResults}
                            setSurveyResults={setSurveyResults}
                            survey_id={null}
                        />
                    </div>
                    <button className={'directional next ' + advanceButtonState} onClick={modalClose}>
                        FINISH
                    </button>
                </div>
            </div>
        </div>)
    )
}

export default SurveyPreviewModal;
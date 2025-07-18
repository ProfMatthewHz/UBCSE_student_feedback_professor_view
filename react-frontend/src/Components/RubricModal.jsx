import React, {useCallback, useEffect, useState} from 'react'
import "../styles/rubricModal.css";

const RubricModal = ({open,onClose,modalData}) => {
   
    // eslint-disable-next-line no-unused-vars
    const { student_id, survey_id, survey_name } = modalData; //obtains the student_id, survey_id,and survey_name from modalData 
    //Fetches data for the feedback form/ viewing results
   
    const [feedback, setFeedback] = useState([]);
    const [overall, setOverall] = useState(null);


    //GET request to backend to retrieve the feedback results using survey_id
    const fetchFeedback = useCallback(() => {
        fetch(process.env.REACT_APP_API_URL_STUDENT + "resultsEndpoint.php", {
            method: "POST",
            credentials: "include",
            body: new URLSearchParams({
                survey: survey_id,
                }),            
        })
            .then((res) => res.json())
            .then((result) => {
                setFeedback(result); 
            })
            .catch((err) => {
                console.log(err);
            });
    }, [survey_id]);

    //POST request to backend to retrieve the feedback results using survey_id
    const fetchOverall = useCallback(() => {
            fetch(process.env.REACT_APP_API_URL_STUDENT + "normalizedResult.php", {
                method: "POST",
                credentials: "include",
                body: new URLSearchParams({
                    survey: survey_id,
                }),
            })
                .then((res) => res.json())
                .then((result) => {
                    if ("data" in result) {
                        if (isNaN(result["data"])) {
                            setOverall(1.0);
                        } else {
                            setOverall(parseFloat(result["data"]).toFixed(4));
                        }
                    }
                })
                .catch((err) => {
                    console.log(err);
                });
        }, [survey_id]);
  
    useEffect(() => {
        fetchFeedback();
        fetchOverall();
    }, [fetchFeedback, fetchOverall]);
  
    /**  feedback object is in the following format
    {
        "criterion1": {"average":x, "median":x},
        "criterion2": {"average":x, "median":x},
        "criterion3": {"average":x, "median":x},
    }
    */

    // setFeedback({TEAMWORK : {average: 2.67, median: "Accepts all assigned team roles, always completes assigned work"}, LEADERSHIP: {average: 1.67, median: "Shows an ability to lead when necessary, willing to collaborate, willing to assist teammates"}, PARTICIPATION: {average: 1.33, median: "Occasionally misses/doesn't participate in meeting…ared for meetings, offers unclear/unhelpful ideas"}, PROFESSIONALISM: {average: 1, median: "Often discourteous and/or openly critical of teammates, doesn't listen to alternative perspectives"}, QUALITY: {average: 1.67, median: "Occasionally commits to shared documents, others s…etimes needed to revise, debug, or fix their work"}})
    
  if (!open) return null
  return (
        <div className = "modal">
            <div className='styled-input'>
                <div className = "feedback">
                    <button className = "CancelButton" onClick={onClose}>x</button>
                    <div className="courseHeader">
                            <h2>{survey_name}</h2>
                    </div>
                    <table className="surveyTable">
                            <thead>
                                <tr>
                                    <th>Criterion</th>
                                    <th>Median Evaluation</th>
                                    <th>Mean</th>
                                    
                                </tr>
                            </thead>
                            <tbody>
                            {feedback.length !== 0 ? (
                                Object.keys(feedback).map((criterion, index) => (
                                        <tr key={index}>
                                            <td className="criterion">{criterion}</td>
                                            <td>{feedback[criterion].median}</td>
                                            <td>{feedback[criterion].average} out of {feedback[criterion].maximum}</td>
                                        </tr>
                                    ))
                                    ) : (
                                        <tr><td colSpan="3">Feedback Results Not Available</td></tr>
                                    )}
                            </tbody>
                    </table>
                    {overall != null && (<div className="summary">
                        <div className="normalizedAnnounce">Normalized Result:</div> <div className="normalResult">{overall}</div>
                    </div>)}
                 </div>
            </div>
         </div>
 
  )
}

export default RubricModal;
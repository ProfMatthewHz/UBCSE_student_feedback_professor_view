import React, {useCallback, useEffect, useState} from 'react'
import "../styles/rubricModal.css";

const RubricModal = ({open,onClose,modalData}) => {
   
    // eslint-disable-next-line no-unused-vars
    const { student_id, survey_id, survey_name } = modalData; //obtrains the student_id, survey_id,and survey_name from modalData 
    //Fetches data for the feedback form/ viewing results
   
    const [feedback, setFeedback] = useState([]);


    //GET request to backend to retrieve the feedback results using survey_id
    const fetchFeedback = useCallback(() => {
        fetch(process.env.REACT_APP_API_URL_STUDENT + "resultsEndpoint.php?survey=" + survey_id, {
            method: "GET",
            credentials: "include",
        })
            .then((res) => res.json())
            .then((result) => {
                setFeedback(result); 
            })
            .catch((err) => {
                console.log(err);
            });
    }, [survey_id]);
  
    useEffect(() => {
        fetchFeedback()
    }, [fetchFeedback]);
  
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
                                    <th>Average Score</th>
                                    <th>Median</th>
                                    
                                </tr>
                            </thead>
                            <tbody>
                            {feedback.length !== 0 ? (
                                Object.keys(feedback).map((criterion, index) => (
                                        <tr key={index}>
                                            <td>{criterion}</td>
                                            <td>{feedback[criterion].average} out of {feedback[criterion].maximum}</td>
                                            <td>{feedback[criterion].median}</td>
                                        </tr>
                                    ))
                                    ) : (
                                        <tr><td colSpan="3">Feedback Results Not Available</td></tr>
                                    )}
                            </tbody>
                    </table>
                 </div>
            </div>
         </div>
 
  )
}

export default RubricModal;
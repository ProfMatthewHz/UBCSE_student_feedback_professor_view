import React, {useEffect,useState} from 'react'
import pic from "../assets/UBLogo.png";
import "../styles/rubricModal.css";

const RubricModal = ({open,onClose,modalData}) => {
   
    console.log("Modal Data");
    console.log(modalData);

  

    const { student_id, survey_id, survey_name } = modalData; //obtrains the student_id, survey_id,and survey_name from modalData 
    //Fetches data for the feedback form/ viewing results
    const [feedback, setFeedback] = useState([]);


    //GET request to backend to retrieve the feedback results using survey_id
    const fetchFeedback = () => { 
        const url = `${process.env.REACT_APP_API_URL_STUDENT}resultsEndpoint.php?survey=${survey_id}`;
  
        fetch(url, {
            method: "GET",
           
        })
            .then((res) => res.json())
            .then((result) => {
              
                setFeedback(result); 
            })
            .catch((err) => {
                console.log(err);
            });
    };
  
    useEffect(() => {
        fetchFeedback()
    }, []);
  
    /**  feedback object is in the following format
    {
        "criterion1": {"average":x, "median":x},
        "criterion2": {"average":x, "median":x},
        "criterion3": {"average":x, "median":x},
    }
    */
    console.log("FEEDBACK");
    console.log(feedback);



    if (!open) return null
  return (
   
        <div className = "modal">
            <div className='styled-input'>
                <div className = "feedback">
                    <button className = "CancelButton" onClick={onClose}>X</button>
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
                            {feedback.length > 0 ? (
                                Object.keys(feedback).map((criterion, index) => (
                                        <tr key={index}>
                                            <td>{criterion}</td>
                                            <td>{feedback[criterion].average}</td>
                                            <td>{feedback[criterion].median}</td>
                                        </tr>
                                    ))
                                    ) : (
                                        <th colSpan="3">Feedback Results Not Available</th>
                                    )}
                            </tbody>
                    </table>

                 </div>
            </div>
         </div>
 
  )
}

export default RubricModal
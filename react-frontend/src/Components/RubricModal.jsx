import React, {useState} from 'react'
import pic from "../assets/UBLogo.png";
import "../styles/rubricModal.css";

const RubricModal = ({open,onClose}) => {
    const tempData =[ {"criterion":"TEAMWORK","average":"3","median":"Accepts all assigned team roles, always completes assigned work"},
                    {"criterion":"LEADERSHIP","average":"3","median":"Takes leadership role, is a good collaborator, always willing to assist teammates"},
                    {"criterion":"PARTICIPATION","average":"3","median":"Attends and participates in all meetings, comes prepared, and clearly expresses well-developed ideas"},
                    {"criterion":"PROFESSIONALISM","average":"3","median":"Always courteous to teammates, values teammates' perspectives, knowledge, and experience, and always willing to consider them"},
                    {"criterion":"QUALITY","average":"3","median":"Frequently commits to shared documents, others rarely need to revise, debug, or fix their work"}
                    ]
    const tempFetch = [{"surveyName": "Peer Evaluation Feedback", "rubricData":tempData}]



    if (!open) return null
  return (
   
        <div className = "modal">
            <div className='styled-input'>
                <div className = "feedback">
                    <button className = "CancelButton" onClick={onClose}>X</button>
                    <div className="courseHeader">
                
                            <h2>{tempFetch[0].surveyName}</h2>
                    
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
                            {tempFetch[0].rubricData.map((item, index) => (
                                    <tr key={index} className="survey-row">

                                        <td>{item.criterion}</td>
                                        <td>{item.average}</td>
                                        <td>{item.median}</td>
                                    
                                    </tr>
                                    ))}

                                </tbody>
                </table>

        </div>
            </div>
         </div>
 
  )
}

export default RubricModal
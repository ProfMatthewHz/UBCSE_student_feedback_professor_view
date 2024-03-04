import React, {useState} from 'react'
import pic from "../assets/UBLogo.png";
import "../styles/rubricModal.css";

const RubricModal = ({open,onClose}) => {
    if (!open) return null
  return (
   
        <div className = "modal">
            <div className='modal-content'>
            <button className = "CancelButton" onClick={onClose}>X</button>
            <div className="courseHeader">
          
                    <h2>
                        Something Evaluation Feedback
                    </h2>
              
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
                            <tr className="survey-row" >
                                <td>TEAMWORK</td>
                                <td>3 (out of 3)</td>
                                <td>Accepts all assigned team roles, always completes assigned work</td>
                            </tr>
                            <tr className="survey-row" >
                                <td>LEADERSHIP</td>
                                <td>3 (out of 3)</td>
                                <td>Takes leadership role, is a good collaborator, always willing to assist teammates</td>
                            </tr>
                            <tr className="survey-row" >
                                <td>PARTICIPATION</td>
                                <td>3 (out of 3)</td>
                                <td>Attends and participates in all meetings, comes prepared, and clearly expresses well-developed ideas</td>
                            </tr>
                            <tr className="survey-row" >
                                <td>PROFESSIONALISM</td>
                                <td>3 (out of 3)</td>
                                <td>Always courteous to teammates, values teammates' perspectives, knowledge, and experience, and always willing to consider them</td>
                            </tr>
                            <tr className="survey-row" >
                                <td>QUALITY</td>
                                <td>3 (out of 3)</td>
                                <td>Frequently commits to shared documents, others rarely need to revise, debug, or fix their work</td>
                            </tr>
                           

                          
                        </tbody>



                </table>
           
          
            
            

          
         
        </div>
        </div>
  
   
  )
}

export default RubricModal
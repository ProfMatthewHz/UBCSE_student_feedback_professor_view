import React, { useState } from "react";
import "../styles/modal.css";
import "../styles/confirmsurvey.css";

const SurveyConfirmModal = ({ modalClose, survey_data }) => {
    const [survey_name,] = useState(survey_data.survey_name);
    const [start_date,] = useState(survey_data.start_date);
    const [end_date,] = useState(survey_data.end_date);
    const [course_code,] = useState(survey_data.course_code);
    const [rubric_name,] = useState(survey_data.rubric_name);
    const [roster_array,] = useState(survey_data.roster_array);
    const [nonroster_array,] = useState(survey_data.nonroster_array);

    async function confirmSurveyPost(data) {
        let fetchHTTP = process.env.REACT_APP_API_URL + "confirmationForSurvey.php";
        const result = await fetch(fetchHTTP, {
            method: "POST",
            credentials: "include",
            body: data,
        })
            .then((res) => res.json());
        return result; // Return the result directly
    }

    function quitModal() {
        modalClose(false);
    }

    function verifyConfirm() {
        let formData2 = new FormData();
        formData2.append("save-survey", "1");
        confirmSurveyPost(formData2);
        modalClose(true);
    }

    return (
        <div className="confirm-modal modal">
            <div style={{ width: "1200px", maxWidth: "90%" }} className="modal-content modal-phone">
                <div className="CancelContainer">
                    <button className="CancelButton" onClick={quitModal}>
                        ×
                    </button>
                </div>
                <div className="modal--contents-container">
                    <h2 className="modal--main-title">
                        Confirm Survey Information
                    </h2>
                </div>
                <div className="confirm--top-container">
                    <h3 className="form__item--info">
                        Survey Name: {survey_name}
                    </h3>
                    <h3 className="form__item--info">For Course: {course_code}</h3>
                    <h3 className="form__item--info">
                        Rubric Used: {rubric_name}
                    </h3>
                    <h3 className="form__item--info">
                        Survey Active: {start_date} to {end_date}
                    </h3>
                </div>
                <h3 className="confirm--bottom-label form__item--info">Survey Participants</h3>
                <div className="confirm--bottom-container">
                    {roster_array.length > 0 ? (
                        <table>
                            <caption>Course Roster</caption>
                            <thead>
                                <tr>
                                    <th>Email</th>
                                    <th>Name</th>
                                    <th>Reviewing Others</th>
                                    <th>Being Reviewed</th>
                                </tr>
                            </thead>
                            <tbody>
                                {roster_array.map((entry, index) => (
                                    <tr key={index}>
                                        <td>{entry.student_email}</td>
                                        <td>{entry.student_name}</td>
                                        {entry.reviewing ? <td>&#9989;</td> : <td>&#10060;</td>}
                                        {entry.reviewed ? <td>&#9989;</td> : <td>&#10060;</td>}
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    ) : (
                        <div className="confirm--empty-text">No students on roster</div>
                    )}

                    {nonroster_array.length > 0 ? (
                        <table>
                            <caption>Non-Course Students</caption>
                            <thead>
                                <tr>
                                    <th>Email</th>
                                    <th>Name</th>
                                    <th>Reviewing Others</th>
                                    <th>Being Reviewed</th>
                                </tr>
                            </thead>
                            <tbody>
                                {nonroster_array.map((entry, index) => (
                                    <tr key={index}>
                                        <td>{entry.student_email}</td>
                                        <td>{entry.student_name}</td>
                                        {entry.reviewing ? <td>&#9989;</td> : <td>&#10060;</td>}
                                        {entry.reviewed ? <td>&#9989;</td> : <td>&#10060;</td>}
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    ) : (
                        <div className="confirm--empty-text">Only includes roster students</div>
                    )}
                </div>
                    <div className="confirm--btn-container form__item--confirm-btn-container">
                    <button
                        className="form__item--cancel-btn"
                        onClick={quitModal}
                    >
                        Cancel
                    </button>
                        <button
                            className="form__item--confirm-btn"
                            onClick={verifyConfirm}

                        >
                            Confirm Survey
                        </button>
                    </div>
                </div>
            </div>
    );
}
export default SurveyConfirmModal;

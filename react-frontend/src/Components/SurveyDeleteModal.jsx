import React, { useEffect, useState } from "react";
import "../styles/modal.css";
import "../styles/deletesurvey.css";

/* Todo: Update the onchange method to enable the delete survey button if (and only if) it is a perfect match */
const SurveyDeleteModal = ({ modalClose, survey_data }) => {
    const [survey_id,] = useState(survey_data.id);
    const [survey_name,] = useState(survey_data.name);
    const [emptyOrWrongDeleteNameError, setEmptyOrWrongDeleteNameError] = useState(false);
    const [deleteName, setDeleteName] = useState("");

    async function postSurveyDelete(formdata) {
        let fetchHTTP =
            process.env.REACT_APP_API_URL + "deleteSurvey.php";
        // Just a quick test
        const response = await fetch(fetchHTTP, {
            method: "POST",
            body: formdata,
            credentials: "include",
        })
        const result = await response.json();
        return result; // Return the result directly
    }

    async function verifyAndSubmit() {
        if (!emptyOrWrongDeleteNameError) {
            let form = new FormData();
            form.append("survey_id", survey_id);
            form.append("agreement", 1);
            let post = await postSurveyDelete(form);
            if (post.errors) {
                modalClose([post.errors]);
            } else {
                modalClose([]);
            }
        }
    }

    const updateAndCheckSurveyName = (e) => {
        setDeleteName(e.target.value);
    }

    useEffect(() => {
        if (deleteName !== survey_name) {
            setEmptyOrWrongDeleteNameError(true);
        } else {
            setEmptyOrWrongDeleteNameError(false);
        }
    }, [deleteName, survey_name]);

    return (
        <div className="modal">
            <div style={{ width: "650px", maxWidth: "90%" }}className="delete-modal modal-content modal-phone">
                <div className="CancelContainer">
                    <button className="CancelButton" onClick={modalClose}>
                        ×
                    </button>
                </div>
                <div className="modal--contents-container">
                    <h2 className="modal--main-title">
                        Delete Survey: {survey_name}
                    </h2>
                        <label className="form__item--label" htmlFor="delete-name">
                            Enter Survey Name
                            <input
                                className={emptyOrWrongDeleteNameError ? "form__item--input-error" : undefined}
                                id="delete-name" 
                                type="text" 
                                onChange={updateAndCheckSurveyName} />
                        {emptyOrWrongDeleteNameError ? (
                            <label className="form__item--error-label">
                                <div className="form__item--red-warning-sign" />
                                Must be identical to the survey name
                            </label>
                        ) : null}
                        </label>
                    <div className="form__item--confirm-btn-container">
                        <button
                            className="form__item--confirm-btn"
                            onClick={verifyAndSubmit}
                            disabled={emptyOrWrongDeleteNameError}

                        >
                            Delete Survey
                        </button>
                    </div>
                </div>
            </div>
        </div>
    );
}
export default SurveyDeleteModal;
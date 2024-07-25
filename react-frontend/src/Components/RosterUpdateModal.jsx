import React, { useState } from "react";
import { RadioButton } from "primereact/radiobutton";
import "../styles/modal.css";
import "../styles/addsurvey.css";

const RosterUpdateModal = ({ modalClose, course }) => {
    const [rosterFile, setRosterFile] = useState(null);
    const [updateRosterOption, setUpdateRosterOption] = useState("replace");
    const [noRosterFileError, setNoRosterFileError] = useState(false);

    const verifyAndPost = () => {
        // Check that we have the data needed to update the roster
        if (rosterFile === null) {
            setNoRosterFileError(true);
            return;
        }
        const formData = new FormData();
        formData.append("roster-file", rosterFile);
        formData.append("course-id", course.id);
        formData.append("update-type", updateRosterOption);

        fetch(process.env.REACT_APP_API_URL + "rosterUpdate.php", {
            method: "POST",
            credentials: "include",
            body: formData,
        })
            .then((res) => res.json())
            .then((result) => {
                modalClose(result);
            });
    };

    const quitModal = () => {
        modalClose(false);
    }


    return (
        <div className="modal">
            <div style={{ width: "700px", maxWidth: "90%" }} className="modal-content modal-phone">
                <div className="CancelContainer">
                    <button className="CancelButton" onClick={quitModal}>
                        ×
                    </button>
                </div>

                <div className="modal--contents-container">
                    <h2 className="modal--main-title">
                        Update Roster for {course.code}
                    </h2>
                    {/* File input */}
                        <label className="form__item--file-label" htmlFor="updateroster-file-input">
                            Roster (CSV File) - Each Row Must Be Formatted: email, first name, last name
                            <input
                                type="file"
                                id="updateroster-file-input"
                                className="form__item--file-input"
                                onChange={(e) => setRosterFile(e.target.files[0])}
                            />
                        {noRosterFileError ? (
                            <label className="form__item--error-label">
                                <div className="form__item--red-warning-sign" />
                                Select a file</label>
                        ) : null}
                                                </label>

                    {/* Radio Buttons */}
                        <div className="form__item--radio-button-options">
                            <div className="form__item--radio-button">
                                <RadioButton
                                    inputId="replace"
                                    name="replace"
                                    value="replace"
                                    onChange={(e) => setUpdateRosterOption(e.value)}
                                    checked={updateRosterOption === "replace"}
                                />
                                <label htmlFor="replace" className="form__item--radio-button-label">
                                    Replace
                                </label>
                            </div>

                            <div className="form__item--radio-button">
                                <RadioButton
                                    inputId="expand"
                                    name="expand"
                                    value="expand"
                                    onChange={(e) => setUpdateRosterOption(e.value)}
                                    checked={updateRosterOption === "expand"}
                                />
                                <label htmlFor="expand" className="form__item--radio-button-label">
                                    Expand
                                </label>
                            </div>
                        </div>
                    </div>
                    <div className="form__item--confirm-btn-container">
                        <button className="form__item--confirm-btn" onClick={verifyAndPost}>
                            Update
                        </button>
                    </div>
                </div>
            </div>
     );
}

export default RosterUpdateModal;
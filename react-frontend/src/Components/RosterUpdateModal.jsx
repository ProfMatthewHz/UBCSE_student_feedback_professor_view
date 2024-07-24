import React, {useState} from "react";
import {RadioButton} from "primereact/radiobutton";
import "../styles/modal.css";
import "../styles/course.css";

const RosterUpdateModal = ({modalClose, course}) => {
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
    <div style={{width: "650px", maxWidth: "90%"}} className="update-modal-content modal-content modal-phone">
    <div className="CancelContainer">
        <button className="CancelButton" onClick={quitModal}>
            ×
        </button>
    </div>
              
    <div className="add-survey--contents-container">
        <h2 className="add-survey--main-title">
          Update Roster for {course.code}
        </h2>
        {/* File input */}
        <div className="form__item file-input-wrapper">
            <label className="form__item--label form__item--file" htmlFor="updateroster-file-input">
                Roster (CSV File)<br/>Emails in Column 1, First Names 
                in Columns 2, Last Names in Columns 3
                <input
                    type="file"
                    id="updateroster-file-input"
                    className="updateroster-file-input"
                    onChange={(e) => setRosterFile(e.target.files[0])}
                />
            </label>
            {noRosterFileError ? (
                            <label className="add-survey--error-label">
                                <div className="add-survey--red-warning-sign"/>
                                Select a file</label>
                        ) : null}
        </div>
        {/* Radio Buttons */}
        <div className="update-form__item">
            <div className="update-radio-options">
                <div className="update-radio-button--item">
                    <RadioButton
                        inputId="replace"
                        name="replace"
                        value="replace"
                        onChange={(e) => setUpdateRosterOption(e.value)}
                        checked={updateRosterOption === "replace"}
                    />
                    <label htmlFor="replace" className="update-radio--label">
                        Replace
                    </label>
                </div>

                <div className="update-radio-button--item">
                    <RadioButton
                        inputId="expand"
                        name="expand"
                        value="expand"
                        onChange={(e) => setUpdateRosterOption(e.value)}
                        checked={updateRosterOption === "expand"}
                    />
                    <label htmlFor="expand" className="update-radio--label">
                        Expand
                    </label>
                </div>
            </div>
        </div>
        <div className="add-survey--confirm-btn-container">
            <button className="add-survey--confirm-btn" onClick={verifyAndPost}>
                Update
            </button>
        </div>
     </div>
  </div>
  </div>
);
}

export default RosterUpdateModal;
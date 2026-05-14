import React, { useState } from "react";
import "../styles/modal.css";

const RequestNameChangeModal = ({ modalClose }) => {
    const [updatedName, setUpdatedName] = useState("");
    const [emptyName, setEmptyName] = useState(true);

    async function postRequestNameChange(formdata) {
        let fetchHTTP =
            process.env.REACT_APP_API_URL_STUDENT + "requestNameChange.php";
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
        if (updatedName.trim().length > 0) {
            let form = new FormData();
            form.append("new_name", updatedName);
            let post = await postRequestNameChange(form);
            if (post.errors) {
                modalClose();
            } else {
                modalClose();
            }
        }
    }

    const updateName = (e) => {
        setUpdatedName(e.target.value);
        setEmptyName(e.target.value.trim().length === 0);
    }

    return (
        <div className="modal">
            <div style={{ width: "650px", maxWidth: "90%" }}className="name-change-modal modal-content modal-phone">
                <div className="CancelContainer">
                    <button className="CancelButton" onClick={modalClose}>
                        ×
                    </button>
                </div>
                <div className="modal--contents-container">
                    <h2 className="modal--main-title">
                        Request Name Change
                    </h2>
                    <label className="form__item--label" htmlFor="updated-name">
                        Your Current Name (include first and last name):
                        <input
                            id="updated-name" 
                            type="text" 
                            onChange={updateName} />
                    </label>
                    <div className="form__item--confirm-btn-container">
                        <button
                            className={emptyName ? "form__item--confirm-btn-disabled": "form__item--confirm-btn"}
                            onClick={verifyAndSubmit}
                            disabled={emptyName}
                        >
                            Submit Name Change Request
                        </button>
                    </div>
                </div>
            </div>
        </div>
    );
}
export default RequestNameChangeModal;
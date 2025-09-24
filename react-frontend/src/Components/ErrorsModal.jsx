import React, {useEffect, useState} from "react";
import "../styles/modal.css";
import "../styles/surveyerrors.css";

const ErrorsModal = ({modalClose, error_type, errors}) => {
  const [errorsList,] = useState(errors);
  const [title,] = useState(error_type + " Errors");

  useEffect(() => {
    console.log("ErrorsModal mounted with errors:", errorsList);
  }, [errorsList]);

  return ( 
    <div className="modal">
      <div style={{ width: "650px", maxWidth: "90%" }} className="delete-modal modal-content modal-phone">
        <div className="CancelContainer">
              <button className="CancelButton" onClick={modalClose}>
                  ×
              </button>
          </div>
          <div className="modal--contents-container">
            <h2 className="modal--main-title">{title}</h2>
            <div className="error-list-container">
              <ul>
                {errorsList.map((string, index) => (
                    <li key={index} className="string-list-item">
                        {string}
                    </li>
                ))}
              </ul>
            </div>
            <div className="form__item--confirm-btn-container">
                <button
                    className="form__item--confirm-btn"
                    onClick={modalClose}
                >
                    Close
                </button>
            </div>
        </div>
      </div>
    </div>
  );
}
export default ErrorsModal;
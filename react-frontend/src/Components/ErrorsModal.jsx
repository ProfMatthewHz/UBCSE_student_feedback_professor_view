import React, {useState} from "react";
import "../styles/modal.css";
import "../styles/surveyerrors.css";

const ErrorsModal = ({modalClose, error_type, errors}) => {
  const [errorsList,] = useState(errors);
  const [title,] = useState(error_type + " Errors");
  return ( 
    <div className="modal">
      <div className="modal-content modal-phone">
        <div className="CancelContainer">
              <button className="CancelButton" onClick={modalClose}>
                  ×
              </button>
          </div>
          <div className="error-container">
              <div className="error-header">
                  <h2 className="error-header-text">{title}</h2>
              </div>
              <div className="error-list-container">
                  {errorsList.map((string, index) => (
                      <div key={index} className="string-list-item">
                          {string}
                      </div>
                  ))}
              </div>
          </div>
          <button className="Cancel error-cancel-button" onClick={modalClose}>
              Close
          </button>
      </div>
    </div>
  );
}
export default ErrorsModal;
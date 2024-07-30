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
          <div className="modal--contents-container">
            <h2 className="modal--main-title">{title}</h2>
          </div>
              <div className="error-list-container">
                  {errorsList.map((string, index) => (
                      <div key={index} className="string-list-item">
                          {string}
                      </div>
                  ))}
              </div>
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
  );
}
export default ErrorsModal;
import React, {useState} from "react";
import "../styles/modal.css";
import "../styles/surveyerrors.css";

const SurveyErrorsModal = ({modalClose, errors}) => {
  const [errorsList,] = useState(errors);

  return ( 
    <div className="modal">
      <div className="errors-modal modal-phone">
        <div className="CancelContainer">
              <button className="CancelButton" onClick={modalClose}>
                  ×
              </button>
          </div>
          <div className="error-container">
              <div className="error-header">
                  <h2 className="error-header-text">Survey Errors</h2>
              </div>
              <div class="error-list-container">
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
export default SurveyErrorsModal;
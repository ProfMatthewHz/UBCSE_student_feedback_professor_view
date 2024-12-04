import React, {useState, useCallback, useEffect} from "react";
import {DataTable} from "primereact/datatable";
import {Column} from "primereact/column";
import "primereact/resources/themes/lara-light-blue/theme.css";
import "primereact/resources/primereact.min.css";
import "../styles/modal.css";
import "../styles/extendsurvey.css";

const SurveyTeamAssignmentReviewModal = ({modalClose, course, survey_data}) => {
  const [survey_id,] = useState(survey_data.id);
  const [survey_name,] = useState(survey_data.name);
  const [surveyAssigns, setSurveyAssigns] = useState([]);
  const [surveyHeaders, setSurveyHeaders] = useState([]);

  const getTeamAssignments = useCallback((sid) => {
    let formData = new FormData();

    formData.append('survey-id', sid);
    fetch(
      process.env.REACT_APP_API_URL + "getTeamAssignments.php",
      {
          method: "POST",
          credentials: "include",
          body: formData,
      }
    )
      .then((res) => res.json())
      .then((result) => {
          if (result.errors) {
            // Handle errors
          } else {
            if (result.headers) {
              setSurveyHeaders(result.headers);
            }
            setSurveyAssigns(result.assigns);
          }
      })
      .catch((err) => {
          console.log(err);
      });
  }, [])

  
  async function updateAssignments(formdata) {
    let fetchHTTP =
        process.env.REACT_APP_API_URL + "updateAssignments.php";
    
    try {
        const response = await fetch(fetchHTTP, {
            method: "POST",
            credentials: "include",
            body: formdata,
        });
        const result = await response.json();
        return result; // Return the result directly
    } catch (err) {
        throw err; // Re-throw to be handled by the caller
    }
  }

  async function verifyAndSubmit() {
    let surveyId = survey_id;
    let formData = new FormData();

    formData.append('survey-id', surveyId);
    formData.append('assignments', JSON.stringify(surveyAssigns));
    let post = await updateAssignments();
    if (post.errors) {
        modalClose([post.errors]);
    } else {
      modalClose([]);
    }
}

useEffect(() => {
  getTeamAssignments(survey_id);
}, [survey_id, getTeamAssignments]);

return (
    <div className="modal">
      <div style={{ width: "650px", maxWidth: "90vw" }}className="modal-content modal-phone">
        <div className="CancelContainer">
            <button className="CancelButton" onClick={modalClose}>
                ×
            </button>
        </div>
        <div className="modal--contents-container">
            <h2 className="modal--main-title">
                Review Assignments {course.code}: {survey_name} 
            </h2>
            <div className="modal--boxes-container">
                <div className="modal--top-box-container">
                <DataTable
                            value={surveyAssigns}
                            paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                            paginator
                            rows={10}
                            rowsPerPageOptions={[5, 10, 25, 50]}
                            className="rawresults--table"
                            currentPageReportTemplate="{first} to {last} of {totalRecords}" 
                            emptyMessage="No results found"
                        >
                          <Column
                            field="team"
                            header="Team"
                            sortable />
                          {surveyHeaders.map((header) => {
                              return <Column field={header} header={header} sortable />
                            })
                          }
                  </DataTable>
                </div>
            </div>
            <div className="form__item--confirm-btn-container">
              <button className="form__item--confirm-btn" onClick={verifyAndSubmit}>
              Update Assignments
              </button>
          </div>
        </div>
      </div>
    </div>
  );
}

export default SurveyTeamAssignmentReviewModal;
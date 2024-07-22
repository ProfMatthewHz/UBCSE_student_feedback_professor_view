import React, {useState} from "react";
import "../styles/modal.css";
import "../styles/confirmsurvey.css";

const SurveyConfirmModal = ({modalClose, survey_data}) => {
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
    <div className="modal-content modal-phone">
{/*
  open={modalIsOpenSurveyConfirm}
  onRequestClose={closeModalSurveyConfirm}
  width={"1200px"}
  maxWidth={"90%"}
>*/}
        <div className="CancelContainer">
            <button className="CancelButton" onClick={quitModal}>
                ×
            </button>
        </div>
  <div
      style={{
          display: "flex",
          flexDirection: "column",
          flexWrap: "wrap",
          borderBottom: "thin solid #225cb5",
      }}
  >
      <div
          style={{color: "#225cb5", fontSize: "36px", fontWeight: "bolder"}}
      >
          Survey Confirmation
      </div>
      <div
          style={{
              color: "#225cb5",
              fontSize: "24px",
              fontWeight: "bolder",
              marginBottom: "5px",
              marginTop: "20px",
          }}
      >
          Survey Name: {survey_name}
      </div>
      <div
          style={{color: "#225cb5", fontSize: "24px", fontWeight: "bolder"}}
      >
          Survey Active: {start_date} to {end_date}
      </div>
      <div
          style={{
              color: "#225cb5",
              fontSize: "24px",
              fontWeight: "bolder",
              marginBottom: "5px",
              marginTop: "20px",
          }}
      >
          Rubric Used: {rubric_name}
      </div>
      <div
          style={{color: "#225cb5", fontSize: "24px", fontWeight: "bolder"}}
      >
          For Course: {course_code}
      </div>
  </div>

  <div className="table-containerConfirm">
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
                      {entry.reviewing ? <td>Yes</td> : <td>No</td>}
                      {entry.reviewed ? <td>Yes</td> : <td>No</td>}
                  </tr>
              ))}
              </tbody>
          </table>
      ) : (
          <div className="empty-view">Only includes non-roster students</div>
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
                      {entry.reviewing ? <td>Yes</td> : <td>No</td>}
                      {entry.reviewed ? <td>Yes</td> : <td>No</td>}
                  </tr>
              ))}
              </tbody>
          </table>
      ) : (
          <div className="empty-view">Only includes roster students</div>
      )}
  </div>
  <div
      style={{
          display: "flex",
          justifyContent: "center",
          marginTop: "20px",
          gap: "50px",
          marginBottom: "30px",
      }}
  >
      <button
          className="Cancel"
          style={{
              borderRadius: "5px",
              fontSize: "18px",
              fontWeight: "700",
              padding: "5px 12px",
          }}
          onClick={quitModal}
      >
          Cancel
      </button>
      <button
          className="CompleteSurvey"
          style={{
              borderRadius: "5px",
              fontSize: "18px",
              fontWeight: "700",
              padding: "5px 12px",
          }}
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

import React, { useEffect, useState } from "react";
import { CSVLink } from "react-csv";
import "../styles/course.css";
import "../styles/modal.css";
import Modal from "./Modal";
import Toast from "./Toast";
import BarChart from "./Barchart";

const Course = ({ course, page }) => {
  const [surveys, setSurveys] = useState([]);

  // MODAL CODE
  const [modalIsOpen, setModalIsOpen] = useState(false);

  const [showUpdateModal, setShowUpdateModal] = useState(false);

  const [showViewResultsModal, setViewResultsModal] = useState(false);
  const [viewingCurrentSurvey, setViewingCurrentSurvey] = useState(null)
  const [showRawSurveyResults, setShowRawSurveyResults] = useState(null)
  const [showNormalizedSurveyResults, setShowNormalizedSurveyResults] = useState(null)
  const [currentCSVData, setCurrentCSVData] = useState(null)

  const [rosterFile, setRosterFile] = useState(null);

  const [updateRosterOption, setUpdateRosterOption] = useState("replace");
  const [updateRosterError, setUpdateRosterError] = useState("");

  const [showErrorModal, setShowErrorModal] = useState(false);
  const [showToast, setShowToast] = useState(false)
  const [rubricNames, setNames] = useState([]);

  //const [rubricIDandDescriptions, setIDandDescriptions] = useState([]);
  const [pairingModesFull, setPairingModesFull] = useState([]);
  const [pairingModesNames, setPairingModesNames] = useState([]);

  const formData = new FormData()

  const fetchRubrics = () => {
    fetch("http://localhost/StudentSurvey/backend/instructor/rubricsGet.php", {
      method: "GET",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
    })
      .then((res) => res.json())
      .then((result) => {
        //this is an array of objects of example elements {rubricId: 1, rubricDesc: 'exampleDescription'}
        let rubricIDandDescriptions = result.rubrics.map((element) => element);
        //An array of just the rubricDesc
        let rubricNames = result.rubrics.map((element) => element.rubricDesc);
        setNames(rubricNames);
        //setIDandDescriptions(rubricIDandDescriptions)
      })
      .catch((err) => {
        console.log(err);
      });
  };
  const fetchPairingModes = () => {
    fetch(
      "http://localhost/StudentSurvey/backend/instructor/surveyTypesGet.php",
      {
        method: "GET",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
      }
    )
      .then((res) => res.json())
      .then((result) => {
        let allPairingModeArray = result.survey_types.mult.concat(
          result.survey_types.no_mult
        );

        let pairingModeNames = allPairingModeArray.map(
          (element) => element.description
        );
        let pairingModeFull1 = result.survey_types;
        setPairingModesFull(pairingModeFull1);
        setPairingModesNames(pairingModeNames);
      })
      .catch((err) => {
        console.log(err);
      });
  };

  const openModal = () => {
    setModalIsOpen(true);
    fetchRubrics();
    fetchPairingModes();
  };

  const closeModal = () => {
    setModalIsOpen(false);
  };

  const handleErrorModalClose = () => {
    setRosterFile(null) // sets the file to null
    setShowErrorModal(false) // close the error modal
    setShowUpdateModal(true) // open the update modal again
  }

  const getInitialStateRubric = () => {
    const value = "Select Rubric";
    return value;
  };
  const getInitialStatePairing = () => {
    const value = "Each Team Member Reviewed By Entire Team";
    return value;
  };

  const [valueRubric, setValueRubric] = useState(getInitialStateRubric);

  const [valuePairing, setValuePairing] = useState(getInitialStatePairing);

  const [multiplierNumber, setMultiplierNumber] = useState("one");

  const [validPairingModeForMultiplier, setMultiplier] = useState(false);

  const handleChangeRubric = (e) => {
    setValueRubric(e.target.value);
  };
  const handleChangeMultiplierNumber = (e) => {
    setMultiplierNumber(e.target.value);
  };
  const handleChangePairing = (e) => {
    var boolean = false;

    let multiplierCheckArray = pairingModesFull.mult.map(
      (element) => element.description
    );
    if (multiplierCheckArray.includes(e.target.value)) {
      boolean = true;
    }

    setValuePairing(e.target.value);
    setMultiplier(boolean);
  };

  const handleUpdateRosterSubmit = (e) => {
    e.preventDefault();

    formData.append('roster-file', rosterFile)
    formData.append('course-id', course.id)
    formData.append('update-type', updateRosterOption)

    fetch("http://localhost/StudentSurvey/backend/instructor/rosterUpdate.php", {
      method: "POST",
      body: formData,
    })
      .then((res) => res.text())
      .then((result) => {
        if (typeof result === "string" && result !== "") {
          try {
            const parsedResult = JSON.parse(result);
            console.log("Parsed as JSON object: ", parsedResult);
            if (parsedResult.hasOwnProperty("error") && parsedResult["error"] !== "") {
              if (
                parsedResult["error"].includes(
                  "does not contain an email, first name, and last name"
                )
              ) {
                parsedResult["error"] =
                  "Make sure each row contains an email in the first column, first name in the second column, and last name in the third column";
              }
              setUpdateRosterError(parsedResult["error"])
              setShowUpdateModal(false) // close the update modal
              setShowErrorModal(true) // show the error modal
            }
          } catch (e) {
            console.log("Failed to parse JSON: ", e);
          }
        }else{
          // no error
          // Roster is valid to update, so we can close the pop-up modal
          setShowUpdateModal(false);
          // show toast on success
          setShowToast(true)
        }
      })
      .catch((err) => {
        console.log(err);
      });
  };

  //MODAL CODE

  useEffect(() => {
    fetch(
      "http://localhost/StudentSurvey/backend/instructor/courseSurveysQueries.php",
      {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: new URLSearchParams({
          "course-id": course.id,
        }),
      }
    )
      .then((res) => res.json())
      .then((result) => {
        const activeSurveys = result.active.map(survey_info => ({...survey_info, expired: false}));
        const expiredSurveys = result.expired.map(survey_info => ({...survey_info, expired: true}));

        setSurveys([...activeSurveys, ...expiredSurveys]);
      })
      .catch((err) => {
        console.log(err);
      });
  }, []);

  const handleUpdateModalChange = () => {
    setShowUpdateModal((prev) => !prev);
  };

  const handleViewResultsModalChange = (survey) => {
    setViewResultsModal((prev) => !prev);
    setViewingCurrentSurvey(survey);
    setShowRawSurveyResults(null);
    setShowNormalizedSurveyResults(null);
  };

  const handleSelectedSurveyResultsModalChange = (surveyid, surveytype) => {
    fetch(
      "http://localhost/StudentSurvey/backend/instructor/resultsView.php",
      {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: new URLSearchParams({
          survey: surveyid,
          type: surveytype
        }),
      }
    )
      .then((res) => res.json())
      .then((result) => {
        if (surveytype == "raw-full") {
          setShowRawSurveyResults(result)
          setShowNormalizedSurveyResults(null)
          setCurrentCSVData(result)
        } else { // else if surveytype == "average" (For Normalized Results)
          setShowNormalizedSurveyResults(result)
          setShowRawSurveyResults(null)

          const results_without_headers = result.slice(1);
          const maxValue = Math.max(...results_without_headers.map(result => result[1]));

          let labels = {};
          let startLabel = 0.0;
          let endLabel = 0.2;
          labels[`${startLabel.toFixed(1)}-${endLabel.toFixed(1)}`] = 0

          while (endLabel < maxValue) {
            startLabel += 0.21;
            endLabel += 0.2;
            labels[`${startLabel.toFixed(1)}-${endLabel.toFixed(1)}`] = 0;
          }

          for (let individual_data of results_without_headers) {
            for (let key of Object.keys(labels)) {
              const label_split = key.split("-");
              const current_min = parseFloat(label_split[0]);
              const current_max = parseFloat(label_split[1]);

              if (individual_data[1] >= current_min && individual_data[1] <= current_max) {
                labels[key] += 1;
              }
            }
          }

          labels = Object.entries(labels)
          labels.unshift(["Normalized Averages", "Number of Students"])

          setCurrentCSVData(labels)
        }
      })
      .catch((err) => {
        console.log(err);
      });
  };


  return (
    <div id={course.code} className="courseContainer">
      <div className="courseContent">
        <div className="courseHeader">
          <h2>
            {course.code}: {course.name}
          </h2>
          {page === "home" ? (
            <div className="courseHeader-btns">
              <button className="btn add-btn" onClick={openModal}>
                + Add Survey
              </button>
              <button
                className="btn update-btn"
                type="button"
                onClick={handleUpdateModalChange}
              >
                Update Roster
              </button>
            </div>
          ) : null}
        </div>
        <Modal
          open={modalIsOpen}
          onRequestClose={closeModal}
          style={{
            content: {
              top: "50%",
              left: "50%",
              right: "auto",
              bottom: "auto",
              transform: "translate(-50%, -50%)",
              backgroundColor: "white",
              borderRadius: "10px",
              padding: "20px",
              width: "80%",
              maxWidth: "600px",
            },
            overlay: {
              backgroundColor: "rgba(0, 0, 0, 0.5)",
            },
          }}
        >
          <div
            style={{
              display: "flex",
              flexDirection: "column",
              gap: "5px",
              borderBottom: "thin solid #225cb5",
            }}
          >
            <div
              style={{
                display: "flex",
                marginTop: "20px",
                paddingBottom: "10px",
                justifyContent: "center",
                gap: "10px",
                borderBottom: "thin solid #225cb5",
              }}
            >
              <h2 style={{ color: "#225cb5" }}>
                Add A New Survey To The Following Course: {course.code}
              </h2>
            </div>

            <div style={{ marginLeft: "60px" }} class="input-wrapper">
              <label style={{ color: "#225cb5" }} for="survey-title">
                Survey Course
              </label>
              <div id="survey-title" class="styled-input" type="text">
                {course.code} {course.name} - Fall 2023
              </div>
            </div>
            <div style={{ marginLeft: "60px" }} class="input-wrapper">
              <label style={{ color: "#225cb5" }} for="subject-line">
                Survey Name
              </label>
              <input
                id="subject-line"
                class="styled-input"
                type="text"
                placeholder="Survey Name"
              ></input>
            </div>
            <div style={{ marginLeft: "60px" }} class="input-wrapper">
              <label style={{ color: "#225cb5" }} for="subject-line">
                Start Date
              </label>
              <input
                id="subject-line"
                class="styled-input"
                type="date"
                placeholder="Enter Start Date"
              ></input>
            </div>
            <div style={{ marginLeft: "60px" }} class="input-wrapper">
              <label style={{ color: "#225cb5" }} for="subject-line">
                End Date
              </label>
              <input
                id="subject-line"
                class="styled-input"
                type="date"
                placeholder="Enter End Date"
              ></input>
            </div>
            <div style={{ marginLeft: "60px" }} class="input-wrapper">
              <label style={{ color: "#225cb5" }} for="subject-line">
                Start Time
              </label>
              <input
                id="subject-line"
                class="styled-input"
                type="time"
                placeholder="Enter Start Time"
              ></input>
            </div>
            <div style={{ marginLeft: "60px" }} class="input-wrapper">
              <label style={{ color: "#225cb5" }} for="subject-line">
                End Time
              </label>
              <input
                id="subject-line"
                class="styled-input"
                type="time"
                placeholder="Enter End Time"
              ></input>
            </div>
            <div style={{ marginLeft: "60px" }} class="input-wrapper">
              <label style={{ color: "#225cb5" }} for="subject-line">
                Rubrics
              </label>
              <select
                value={valueRubric}
                onChange={handleChangeRubric}
                id="rubric-type"
                class="styled-input"
                placeholder="Select a rubric"
              >
                {rubricNames.map((rubric) => (
                  <option value={rubric}>{rubric}</option>
                ))}
              </select>
            </div>
            <div style={{ marginLeft: "60px" }} class="input-wrapper">
              <label style={{ color: "#225cb5" }} for="subject-line">
                Pairing Modes
              </label>
              <select
                value={valuePairing}
                onChange={handleChangePairing}
                id="rubric-type"
                class="styled-input"
              >
                {pairingModesNames.map((pairing) => (
                  <option value={pairing}>{pairing}</option>
                ))}
              </select>
            </div>
            {validPairingModeForMultiplier ? (
              <div style={{ marginLeft: "60px" }} class="input-wrapper">
                <label style={{ color: "#225cb5" }} for="subject-line">
                  Multiplier
                </label>
                <select
                  value={multiplierNumber}
                  onChange={handleChangeMultiplierNumber}
                  id="rubric-type"
                  class="styled-input"
                >
                  <option value="one">1</option>
                  <option value="two">2</option>
                  <option value="three">3</option>
                  <option value="four">4</option>
                </select>
              </div>
            ) : (
              ""
            )}

            <div style={{ marginLeft: "60px" }} class="input-wrapper">
              <label style={{ color: "#225cb5" }} for="subject-line">
                CSV File Upload
              </label>
              <input
                id="subject-line"
                class="styled-input"
                type="file"
                placeholder="Upload The File"
              ></input>
            </div>
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
              onClick={closeModal}
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
            >
              Verify Survey
            </button>
          </div>
        </Modal>
        {surveys.length > 0 ? (
          <table className="surveyTable">
            <thead>
              <tr>
                <th>Survey Name</th>
                <th>Dates Available</th>
                <th>Completion Rate</th>
              </tr>
            </thead>
            <tbody>
              {surveys.map((survey) => (
                <tr key={survey.id}>
                  <td>{survey.name}</td>
                  <td>
                    Begins: {survey.start_date}
                    <br />
                    Ends: {survey.end_date}
                  </td>
                  <td>{survey.completion}</td>
                  {survey.expired ? <td><button onClick={() => handleViewResultsModalChange(survey)}>View Results</button></td>
                  : <td><button>Actions</button></td>}
                </tr>
              ))}
            </tbody>
          </table>
        ) : (
          <div className="no-surveys">
            {page === "home" ? `No Surveys Yet` : `No Surveys Created`}
          </div>
        )}
      </div>
      {/* View Results Modal*/}
      {showViewResultsModal && (
        <div className="viewresults-modal">
          <div className="viewresults-modal-content">
            <h2 className="viewresults-modal--heading">
              Results for {course.code} Survey: {viewingCurrentSurvey.name}
            </h2>
            <div className="viewresults-modal--main-button-container">
              <button className={showRawSurveyResults? "survey-result--option-active" : "survey-result--option"} onClick={() => handleSelectedSurveyResultsModalChange(viewingCurrentSurvey.id, "raw-full")}>Raw Results</button>
              <button className={showNormalizedSurveyResults? "survey-result--option-active" : "survey-result--option"} onClick={() => handleSelectedSurveyResultsModalChange(viewingCurrentSurvey.id, "average")}>Normalized Results</button>
            </div>
            {
              showRawSurveyResults && (
                <div>
                  <div className="viewresults-modal--other-button-container">
                    <CSVLink className="downloadbtn" filename="surveyresults.csv" data={currentCSVData}>
                      Download Results
                    </CSVLink>
                  </div>
                  <table className="rawresults--table">
                    <thead>
                      <tr>
                        {showRawSurveyResults[0].map((header, index) => (
                          <th key={index}>{header}</th>
                        ))}
                      </tr>
                    </thead>
                    <tbody>
                      {showRawSurveyResults.slice(1).map((rowData, rowIndex) => (
                        <tr key={rowIndex}>
                          {rowData.map((cellData, cellIndex) => (
                            cellData ? <td key={cellIndex}>{cellData}</td> 
                            : <td key={cellIndex}>--</td>

                          ))}
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>
              )
            }
            {
              showNormalizedSurveyResults && (
                <div>
                  <div className="viewresults-modal--other-button-container">
                    <CSVLink className="downloadbtn" filename="surveyresults.csv" data={currentCSVData}>
                      Download Results
                    </CSVLink>
                  </div>
                  <div className="viewresults-modal--barchart-container">
                    <BarChart survey_data={currentCSVData}/>
                  </div>
                </div>

             )
            }
            <div className="viewresults-modal--cancel-button-container">
              <button className="cancel-btn" onClick={() => handleViewResultsModalChange(null)}>Cancel</button>
            </div>
          </div>
        </div>
      )}
      {/* Error Modal for updating roster */}
      {showUpdateModal && (
        <div className="update-modal">
          <div className="update-modal-content">
            <h2 className="update-modal--heading">
              Update Roster for {course.code} {course.name}
            </h2>
            <form onSubmit={handleUpdateRosterSubmit}>
              {/* File input */}
              <div className="update-form__item update-file-input-wrapper">
                <label className="form__item--label form__item--file">
                  Roster (CSV File) - Requires Emails in Columns 1, First Names
                  in Columns 2 and Last Names in Columns 3
                </label>
                <div>
                  <input
                    type="file"
                    id="file-input"
                    className="file-input"
                    onChange={(e) => setRosterFile(e.target.files[0])}
                    required
                  />
                  <label className="custom-file-label" htmlFor="file-input">
                    Choose File
                  </label>
                  <span className="selected-filename">
                    {rosterFile ? rosterFile.name : "No file chosen"}
                  </span>
                </div>
              </div>
              {/* Radio Buttons */}
              <div className="update-form__item">
                <div className="update-radio-options">
                  <label htmlFor="replace" className="update-radio--label">
                    <input
                      type="radio"
                      value="replace"
                      id="replace"
                      checked={updateRosterOption === "replace"}
                      onChange={(e) => setUpdateRosterOption(e.target.value)}
                    />
                    Replace Roster
                    <span></span>
                  </label>

                  <label htmlFor="expand" className="update-radio--label">
                    <input
                      type="radio"
                      value="expand"
                      id="expand"
                      checked={updateRosterOption === "expand"}
                      onChange={(e) => setUpdateRosterOption(e.target.value)}
                    />
                    Expand Roster
                    <span></span>
                  </label>
                </div>
              </div>
              <div className="form__submit--container">
                <button
                  onClick={handleUpdateModalChange}
                  type="button"
                  className="update-cancel-btn"
                >
                  Cancel
                </button>
                <button type="submit" className="update-form__submit">
                  Update
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
      {/* Error Modal */}
      {showErrorModal && (
        <div className="modal">
          <div className="modal-content">
            <h2>Error(s)</h2>
            <p>{updateRosterError}</p>
            <button onClick={handleErrorModalClose}>OK</button>
          </div>
        </div>
      )}

      <Toast
        message={`Roster for ${course.code} ${course.name} successfully updated!`}
        isVisible={showToast}
        onClose={() => setShowToast(false)}
      />
    </div>
  );
};

export default Course;

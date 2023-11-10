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
  const [modalIsOpenError, setModalIsOpenError] = useState(false);
  const [errorsList, setErrorsList] = useState([]);
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
  const [showToast, setShowToast] = useState(false);
  const [rubricNames, setNames] = useState([]);
  const [rubricIDandDescriptions, setIDandDescriptions] = useState([]);
  const [pairingModesFull, setPairingModesFull] = useState([]);
  const [pairingModesNames, setPairingModesNames] = useState([]);

  const updateRosterformData = new FormData();

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

  const closeModalError = () => {
    setModalIsOpenError(false);
  };

  const handleErrorModalClose = () => {
    setRosterFile(null); // sets the file to null
    setShowErrorModal(false); // close the error modal
    setShowUpdateModal(true); // open the update modal again
  };

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

  async function getAddSurveyResponse(formData) {
    console.log("this is before the addsurveyResponse function fetch call");

    let fetchHTTP =
      "http://localhost/StudentSurvey/backend/instructor/addSurveyToCourse.php?course=" +
      course.id;
    //let response = await fetch(fetchHTTP,{method: "POST", body: formData});
    try {
      const response = await fetch(fetchHTTP, {
        method: "POST",
        body: formData,
      });
      const result = await response.json();

      return result; // Return the result directly
    } catch (err) {
      console.error(err);
      throw err; // Re-throw to be handled by the caller
    }
  }

  async function verifySurvey() {
    var surveyName = document.getElementById("survey-name").value;
    var startTime = document.getElementById("start-time").value;
    var endTime = document.getElementById("end-time").value;
    var startDate = document.getElementById("start-date").value;
    var endDate = document.getElementById("end-date").value;
    var csvFile = document.getElementById("csv-file").value;
    var rubric = document.getElementById("rubric-type").value;

    var dictNameToInputValue = {
      "Survey name": surveyName,
      "Start time": startTime,
      "End time": endTime,
      "Start date": startDate,
      "End date": endDate,
      "Csv file": csvFile,
    };

    for (let k in dictNameToInputValue) {
      if (dictNameToInputValue[k] === "") {
        alert(k + " cannot be empty. Please fill in.");
        return;
      }
    }

    //date and time keyboard typing bound checks.

    let minDateObject = new Date("2023-08-31T00:00:00"); //first day of class
    let maxDateObject = new Date("2023-12-09T00:00:00"); //last day of class
    let startDateObject = new Date(startDate + "T00:00:00"); //inputted start date.
    let endDateObject = new Date(endDate + "T00:00:00"); //inputted end date.
    if (startDateObject < minDateObject) {
      alert("Start Date is too early. Must start atleast at August 31");
      return;
    }
    if (startDateObject > maxDateObject) {
      alert("Start Date is too late. Must be at or before December 9");
      return;
    }
    if (endDateObject < minDateObject) {
      alert("End Date is too early. Must start atleast at August 31");
      return;
    }
    if (endDateObject > maxDateObject) {
      alert("End Date is too late. Must be at or before December 9");
      return;
    }
    //END:date and time keyboard typing bound checks.

    //special startdate case. Startdate cannot be before the current day.
    let timestamp = new Date(Date.now());

    timestamp.setHours(0, 0, 0, 0); //set hours/minutes/seconds/etc to be 0. Just want to deal with the calendar date
    if (startDateObject < timestamp) {
      alert("Survey start date cannot be before the current day.");
      return;
    }
    //END:special startdate case. Startdate cannot be before the current day.

    //Start date cannot be greater than End date.
    if (startDateObject > endDateObject) {
      alert("Start date cannot be greater than the end date");
      return;
    }
    //END:Start date cannot be greater than End date.

    //If on the same day, start time must be before end time
    if (startDate === endDate) {
      if (startTime === endTime) {
        alert(
          "If start and end days are the same, Start and End times must differ"
        );
        return;
      }
      let startHour = parseInt(startTime.split(":")[0]);
      let endHour = parseInt(endTime.split(":")[0]);
      if (startHour === endHour) {
        alert(
          "If start and end days are the same, Start and End time hours must differ"
        );
        return;
      }
      if (startHour > endHour) {
        alert(
          "If start and end days are the same, Start time cannot be after End time"
        );
        return;
      }
    }
    //Start time must be after current time if start date is the current day.

    console.log("if conditional line 243");
    if (
      startDateObject.getDay(startDateObject) === timestamp.getDay(timestamp)
    ) {
      let timestampWithHour = new Date(Date.now());
      let currentHour = timestampWithHour.getHours(timestampWithHour);
      let currentMinutes = timestampWithHour.getMinutes(timestampWithHour);
      let startHourNew = parseInt(startTime.split(":")[0]);
      let startMinutes = parseInt(startTime.split(":")[1]);

      if (startHourNew < currentHour) {
        alert("Start time hour cannot be before the current hour");
        return;
      }
      if (startHourNew === currentHour) {
        if (startMinutes < currentMinutes) {
          alert("Start time minutes cannot be before current minutes");
          return;
        }
      }
      //End:Start time must be after current time
    }

    //Now it's time to send data to the backend

    let formData = new FormData();
    let rubricId;
    let pairingId;
    let multiplier;

    for (const element of rubricIDandDescriptions) {
      if (element.rubricDesc === rubric) {
        rubricId = element.rubricId;
      }
    }

    for (const element in pairingModesFull.no_mult) {
      if (
        pairingModesFull.no_mult[element].description ===
        document.getElementById("pairing-mode").value
      ) {
        pairingId = pairingModesFull.no_mult[element].id;
        multiplier = 1;
      }
    }
    for (const element in pairingModesFull.mult) {
      if (
        pairingModesFull.mult[element].description ===
        document.getElementById("pairing-mode").value
      ) {
        pairingId = pairingModesFull.mult[element].id;
        multiplier = document.getElementById("multiplier-type").value;
      }
    }

    let file = document.getElementById("csv-file").files[0];

    formData.append("survey-name", surveyName);
    formData.append("course-id", course.id);
    formData.append("rubric-id", rubricId);
    formData.append("pairing-mode", pairingId);
    formData.append("start-date", startDate);
    formData.append("start-time", startTime);
    formData.append("end-date", endDate);
    formData.append("end-time", endTime);
    formData.append("pm-mult", multiplier);
    formData.append("pairing-file", file);

    //form data is set. Call the post request
    let awaitedResponse = await getAddSurveyResponse(formData);
    console.log(awaitedResponse);

    //let errorsObject = errorOrSuccessResponse.errors;
    let errorsObject = awaitedResponse.errors;
    let dataObject = awaitedResponse.data;

    if (errorsObject.length === 0) {
      //succesful survey. Alert user
      alert("Survey has no errors");
      return;
    }

    if (dataObject.length === 0) {
      let errorString = errorsObject["pairing-file"];
      setErrorsList(errorString.split("<br>"));
      closeModal();
      setModalIsOpenError(true);

      return;
    }

    return;
  }

  const handleUpdateRosterSubmit = (e) => {
    e.preventDefault();

    updateRosterformData.append("roster-file", rosterFile);
    updateRosterformData.append("course-id", course.id);
    updateRosterformData.append("update-type", updateRosterOption);

    fetch(
      "http://localhost/StudentSurvey/backend/instructor/rosterUpdate.php",
      {
        method: "POST",
        body: updateRosterformData,
      }
    )
      .then((res) => res.text())
      .then((result) => {
        if (typeof result === "string" && result !== "") {
          try {
            const parsedResult = JSON.parse(result);
            console.log("Parsed as JSON object: ", parsedResult);
            if (
              parsedResult.hasOwnProperty("error") &&
              parsedResult["error"] !== ""
            ) {
              if (
                parsedResult["error"].includes(
                  "does not contain an email, first name, and last name"
                )
              ) {
                parsedResult["error"] =
                  "Make sure each row contains an email in the first column, first name in the second column, and last name in the third column";
              }
              setUpdateRosterError(parsedResult["error"]);
              setShowUpdateModal(false); // close the update modal
              setShowErrorModal(true); // show the error modal
            }
          } catch (e) {
            console.log("Failed to parse JSON: ", e);
          }
        } else {
          // no error
          // Roster is valid to update, so we can close the pop-up modal
          setShowUpdateModal(false);
          // show toast on success
          setShowToast(true);
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

  // States/variables for Pagination for Raw Results
  const [rawResultsCurrentPage, setRawResultsCurrentPage] = useState(1)
  const [rawResultsNumOfPages, setRawResultsNumOfPages] = useState(1)
  const [rawResultNumbers, setRawResultNumbers] = useState([...Array(rawResultsNumOfPages + 1).keys()].slice(1))
  const [rawResultsRecords, setRawResultsRecords] = useState([])
  const rawResultsPerPage = 5
  const rawResultsLastIndex = rawResultsCurrentPage * rawResultsPerPage
  const rawResultsFirstIndex = (rawResultsLastIndex - rawResultsPerPage)

  const changeRawResultsPage = (number) => {
    setRawResultsCurrentPage(number)
  }

  const rawResultsPrevPage = () => {
    if(rawResultsFirstIndex >= rawResultsCurrentPage) {
      setRawResultsCurrentPage((prevPage) => prevPage - 1);
    }
  }

  const rawResultsNextPage = () => {
    if(rawResultsCurrentPage < rawResultNumbers.length) {
      setRawResultsCurrentPage((prevPage) => prevPage + 1);
    }
  }

  const displayPageNumbers = () => {
    const totalPages = rawResultNumbers.length;
    const maxDisplayedPages = 4;

    if (totalPages <= maxDisplayedPages) {
      return rawResultNumbers;
    }

    const middleIndex = Math.floor(maxDisplayedPages / 2);
    const startIndex = Math.max(0, rawResultsCurrentPage - middleIndex);
    const endIndex = Math.min(totalPages, startIndex + maxDisplayedPages);

    const displayedNumbers = [
      1,
      ...(startIndex > 1 ? ['...'] : []),
      ...rawResultNumbers.slice(startIndex, endIndex),
      ...(endIndex < totalPages ? ['...'] : []),
      totalPages
    ];
    
    return Array.from(new Set(displayedNumbers));
  };

  useEffect(() => {
    setRawResultNumbers([...Array(rawResultsNumOfPages + 1).keys()].slice(1))
    if(showRawSurveyResults !== null){
      const showRawSurveyResultsWithoutFirstElement = showRawSurveyResults.slice(1); // Exclude the first element
      const rawResultsRecordsAtCurrentPage = showRawSurveyResultsWithoutFirstElement.slice(rawResultsFirstIndex, rawResultsLastIndex)
      setRawResultsRecords(rawResultsRecordsAtCurrentPage)
    }
  }, [showRawSurveyResults, rawResultsCurrentPage])

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
          setShowNormalizedSurveyResults(null)
          setShowRawSurveyResults(result)
          setRawResultsNumOfPages(Math.ceil((result.length - 1) / rawResultsPerPage))
          if (result.length > 1) {
            setCurrentCSVData(result)
          } else {
            setCurrentCSVData(null)
          }
        } else { // else if surveytype == "average" (For Normalized Results)
          setShowRawSurveyResults(null)

          console.log("Normalized Results", result)
          if (result.length > 1) {
            const results_without_headers = result.slice(1);
            const maxValue = Math.max(...results_without_headers.map(result => result[1]));


            let labels = {};
            let startLabel = 0.0;
            let endLabel = 0.2;
            labels[`${startLabel.toFixed(1)}-${endLabel.toFixed(1)}`] = 0

            startLabel = 0.01
            while (endLabel < maxValue) {
              startLabel += 0.2;
              endLabel += 0.2;
              labels[`${startLabel.toFixed(2)}-${endLabel.toFixed(1)}`] = 0;
            }

            for (let individual_data of results_without_headers) {
              for (let key of Object.keys(labels)) {
                const label_split = key.split("-");
                const current_min = parseFloat(label_split[0]);
                const current_max = parseFloat(label_split[1]);
                const current_normalized_average = individual_data[1].toFixed(1)

                if (current_normalized_average >= current_min && current_normalized_average <= current_max) {
                  labels[key] += 1;
                }
              }
            }

            labels = Object.entries(labels)
            labels.unshift(["Normalized Averages", "Number of Students"])

            console.log(labels)
            setCurrentCSVData(result)
            setShowNormalizedSurveyResults(labels)
          } else {
            setCurrentCSVData(null)
            setShowNormalizedSurveyResults(true)
          }
        }
      })
      .catch((err) => {
        console.log(err);
      });
  };


  return (
    <div id={course.code} className="courseContainer">
      <Modal open={modalIsOpenError} onRequestClose={closeModalError}>
        <div
          style={{
            display: "flex",
            flexDirection: "row",
            flexWrap: "wrap",
            borderBottom: "thin solid #225cb5",
          }}
        >
          <div
            style={{
              display: "flex",
              width: "1250px",
              marginTop: "2px",
              paddingBottom: "2px",
              justifyContent: "center",
              gap: "4px",
              borderBottom: "thin solid #225cb5",
            }}
          >
            <h2 style={{ color: "#225cb5" }}>Survey Errors</h2>
          </div>
          {errorsList.map((string, index) => (
            <div key={index} className="string-list-item">
              {string}
            </div>
          ))}
        </div>

        <button
          className="Cancel"
          style={{
            borderRadius: "5px",
            fontSize: "18px",
            fontWeight: "700",
            padding: "5px 12px",
          }}
          onClick={closeModalError}
        >
          Close
        </button>
      </Modal>
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
            flexDirection: "row",
            flexWrap: "wrap",
            borderBottom: "thin solid #225cb5",
          }}
        >
          <div
            style={{
              display: "flex",
              width: "1250px",
              marginTop: "2px",
              paddingBottom: "2px",
              justifyContent: "center",
              gap: "4px",
              borderBottom: "thin solid #225cb5",
            }}
          >
            <h2 style={{ color: "#225cb5" }}>
              Add A New Survey To The Following Course: {course.code}
            </h2>
          </div>

          <div marginLeft="10px" class="input-wrapper">
            <label style={{ color: "#225cb5" }} for="subject-line">
              Survey Name
            </label>
            <input
              id="survey-name"
              class="styled-input"
              type="text"
              placeholder="Survey Name"
            ></input>
          </div>

          <div class="input-wrapper">
            <label style={{ color: "#225cb5" }} for="subject-line">
              Rubrics
            </label>
            <select
              style={{ color: "black" }}
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

          <div class="input-wrapper1">
            <label style={{ color: "#225cb5" }} for="subject-line">
              Start Time
            </label>
            <input
              id="start-time"
              class="styled-input1"
              type="time"
              placeholder="Enter Start Time"
            ></input>
          </div>

          <div class="input-wrapper1">
            <label style={{ color: "#225cb5" }} for="subject-line">
              End Time
            </label>
            <input
              id="end-time"
              class="styled-input1"
              type="time"
              placeholder="Enter End Time"
            ></input>
          </div>

          <div class="input-wrapper1">
            <label style={{ color: "#225cb5" }} for="subject-line">
              Start Date
            </label>
            <input
              id="start-date"
              class="styled-input1"
              type="date"
              min="2023-08-31"
              max="2023-12-09"
              placeholder="Enter Start Date"
            ></input>
          </div>

          <div class="input-wrapper1">
            <label style={{ color: "#225cb5" }} for="subject-line">
              End Date
            </label>
            <input
              id="end-date"
              class="styled-input1"
              type="date"
              min="2023-08-31"
              max="2023-12-09"
              placeholder="Enter End Date"
            ></input>
          </div>

          <div class="input-wrapper">
            <label style={{ color: "#225cb5" }} for="subject-line">
              Pairing Modes
            </label>
            <select
              style={{ color: "black" }}
              value={valuePairing}
              onChange={handleChangePairing}
              id="pairing-mode"
              class="styled-input"
            >
              {pairingModesNames.map((pairing) => (
                <option value={pairing}>{pairing}</option>
              ))}
            </select>
          </div>

          <div class="input-wrapper">
            <label style={{ color: "#225cb5" }} for="subject-line">
              CSV File Upload
            </label>
            <input
              id="csv-file"
              class="styled-input"
              type="file"
              placeholder="Upload The File"
            ></input>
          </div>

          {validPairingModeForMultiplier ? (
            <div class="input-wrapper">
              <label style={{ color: "#225cb5" }} for="subject-line">
                Multiplier
              </label>
              <select
                style={{ color: "black" }}
                value={multiplierNumber}
                onChange={handleChangeMultiplierNumber}
                id="multiplier-type"
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
            onClick={verifySurvey}
          >
            Verify Survey
          </button>
        </div>
      </Modal>
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
            {!showRawSurveyResults && !showNormalizedSurveyResults ? 
              <div className="viewresults-modal--no-options-selected-text">Select Option to View Results</div>
            : null}
            {
              showRawSurveyResults && currentCSVData ? (
                <div>
                  <div className="viewresults-modal--other-button-container">
                    <CSVLink className="downloadbtn" filename={"survey-" + viewingCurrentSurvey.id + "-raw-results.csv"} data={currentCSVData}>
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
                      {rawResultsRecords && rawResultsRecords.map((rowData, rowIndex) => (
                        <tr key={rowIndex}>
                          {rowData.map((cellData, cellIndex) => (
                            cellData ? <td key={cellIndex}>{cellData}</td> 
                            : <td key={cellIndex}>--</td>

                          ))}
                        </tr>
                      ))}
                    </tbody>
                  </table>

                  {/* Pagination */}
                  <div className="rawresults--pagination-container">
                    <ul className="pagination">
                      <li className="page-item">
                        <div className="page-link page-link-prev" onClick={rawResultsPrevPage}>Prev</div>
                      </li>
                      {displayPageNumbers().map((pageNumber, index) => (
                        <li className={`page-item ${rawResultsCurrentPage === pageNumber ? 'page-active' : ''}`} key={index}>
                          {pageNumber === '...' ? (
                            <div className="page-link">...</div>
                          ) : (
                            <div className="page-link" onClick={() => changeRawResultsPage(pageNumber)}>{pageNumber}</div>
                          )}
                        </li>
                      ))}
                      <li className="page-item">
                        <div className="page-link page-link-next" onClick={rawResultsNextPage}>Next</div>
                      </li>
                    </ul>
                  </div>
                </div>
              )
              : (showRawSurveyResults && !currentCSVData) ? (
                <div className="viewresults-modal--no-options-selected-text">No Results Found</div>
              )
              : null}
            {
              showNormalizedSurveyResults && currentCSVData ? (
                <div>
                  <div className="viewresults-modal--other-button-container">
                    <CSVLink className="downloadbtn" filename={"survey-" + viewingCurrentSurvey.id + "-normalized-averages.csv"} data={currentCSVData}>
                      Download Results
                    </CSVLink>
                  </div>
                  <div className="viewresults-modal--barchart-container">
                    <BarChart survey_data={showNormalizedSurveyResults}/>
                  </div>
                </div>
             )
            : (showNormalizedSurveyResults && !currentCSVData) ? (
              <div className="viewresults-modal--no-options-selected-text">No Results Found</div>
            ) 
            : null}
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

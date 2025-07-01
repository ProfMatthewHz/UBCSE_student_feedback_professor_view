import React, { useEffect, useState } from "react";
import Team from "../assets/pairingmodes/TEAM.png"
import TeamSelf from "../assets/pairingmodes/TEAM+SELF.png"
import TeamSelfManager from "../assets/pairingmodes/TEAM+SELF+MANAGER.png"
import PM from "../assets/pairingmodes/PM.png"
import SinglePairs from "../assets/pairingmodes/SinglePairs.png"
import Collective from "../assets/pairingmodes/COLLECTIVE.png"
import "../styles/modal.css";
import "../styles/addsurvey.css";

const SurveyNewModal = ({ modalClose, button_text, survey_data, pairing_modes, rubrics_list }) => {
  const [surveyName, setSurveyName] = useState(survey_data.survey_name);
  const [startTime, setStartTime] = useState(survey_data.start_time ? survey_data.start_time : "");
  const [endTime, setEndTime] = useState(survey_data.end_time ? survey_data.end_time : "");
  const [startDate, setStartDate] = useState(survey_data.start_date ? survey_data.start_date : "");
  const [endDate, setEndDate] = useState(survey_data.end_date ? survey_data.end_date : "");
  const [modalReason,] = useState(survey_data.reason ? survey_data.reason : "Add");
  const [csvFile, setCsvFile] = useState(survey_data.csv_file ? survey_data.csv_file : "");
  const [teamFile, setTeamFile] = useState(survey_data.team_file ? survey_data.team_file : "");
  const [pairingModes, setPairingModes] = useState([]);
  const [rubric, setRubric] = useState(survey_data.rubric_id);
  const [rubricName, setRubricName] = useState("");
  const [pairingModeValue, setPairingModeValue] = useState(survey_data.pairing_mode ? survey_data.pairing_mode : 2);
  const [multiplier, setMultiplier] = useState(survey_data.pm_mult ? survey_data.pm_mult : 1);
  const [useMultipler, setUseMultiplier] = useState(false);
  const [pairingImage, setPairingImage] = useState(Team);
  const [CSVFileDescription, setCSVFileDescription] = useState("");
  const [surveyNameError, setSurveyNameError] = useState(false);
  const [emptyCSVFileError, setEmptyCSVFileError] = useState(false);
  const [emptyTeamFileError, setEmptyTeamFileError] = useState(false);
  const [emptySurveyNameError, setEmptySurveyNameError] = useState(false);
  const [longSurveyNameError, setLongSurveyNameError] = useState(false);
  const [emptyStartTimeError, setEmptyStartTimeError] = useState(false);
  const [emptyEndTimeError, setEmptyEndTimeError] = useState(false);
  const [emptyStartDateError, setEmptyStartDateError] = useState(false);
  const [emptyEndDateError, setEmptyEndDateError] = useState(false);
  const [startAfterCurrentError, setStartAfterCurrentError] = useState(false);
  const [startAfterEndError, setStartAfterEndError] = useState(false);

  const updateImage = (pairing) => {
    switch (pairing) {
      case 'TEAM':
        setPairingImage(Team);
        break;
      case 'TEAM + SELF':
        setPairingImage(TeamSelf);
        break;
      case 'TEAM + SELF + MANAGER':
        setPairingImage(TeamSelfManager);
        break;
      case 'Single Pairs':
        setPairingImage(SinglePairs);
        break;
      case 'MANAGER':
        setPairingImage(PM);
        break;
      case 'COLLECTIVE':
        setPairingImage(Collective);
        break;
      default:
        console.log('Unexpected pairing mode: ' + pairing);
        break;
    }
  }

  const findPairingData = (pairing, pairing_modes) => {
    for (let mode of pairing_modes) {
      if (mode.id === pairing) {
        return mode;
      }
    }
    return null;
  }

  const handleChangePairing = (e) => {
    let pairing = parseInt(e.target.value);
    setPairingModeValue(pairing);
  }

  useEffect(() => {
    let pairingMode = findPairingData(pairingModeValue, pairingModes);
    if (pairingMode != null) {
      setCSVFileDescription(pairingMode.file_organization);
      updateImage(pairingMode.text);
      setUseMultiplier(pairingMode.usesMultiplier);
    }
  }, [pairingModeValue, pairingModes]);

  useEffect(() => {
    if (survey_data.reason === "Add") {
      setPairingModes(pairing_modes);
    } else {
      let pairingMode = findPairingData(survey_data.pairing_mode, pairing_modes);
      let review_class = pairingMode ? pairingMode.review_class : "peer";
      setPairingModes(pairing_modes.filter((mode) => mode.review_class === review_class));
    }
  }, [survey_data.reason, survey_data.pairing_mode, pairing_modes])

  useEffect(() => {
    for(let rub of rubrics_list) {
      if (rub.id === rubric) {
        setRubricName(rub.description);
      }
    }
  }, [rubric, rubrics_list]);

  function duplicateSurveyBackend(formData) {
    let fetchHTTP = process.env.REACT_APP_API_URL + "getSurveyRosterFromSurvey.php";
    const result = fetch(fetchHTTP, {
      method: "POST",
      credentials: "include",
      body: formData,
    })
    .then((res) => res.json());
    return result; // Return the result directly
  }

  function addSurveyBackend(formData) {
    let fetchHTTP =
      process.env.REACT_APP_API_URL + "getSurveyRosterFromFile.php";
    const result = fetch(fetchHTTP, {
      method: "POST",
      credentials: "include",
      body: formData,
    })
    .then((res) => res.json());
    return result; // Return the result directly
  }

  const clearErrors = () => {
    setStartAfterCurrentError(false);
    setStartAfterEndError(false);
    setStartAfterCurrentError(false);
  };

  const checkForInvalidData = () => {
    let invalidData = false;

    if (surveyName == null || surveyName === "") {
      setSurveyNameError(true);
      setEmptySurveyNameError(true);
      invalidData = true;
    } else if (surveyName.length > 89) {
      setSurveyNameError(true);
      setLongSurveyNameError(true);
      invalidData = true;
    } else {
      setSurveyNameError(false);
      setEmptySurveyNameError(false);
    }

    if (startTime === "") {
      setEmptyStartTimeError(true);
      invalidData = true;
    } else {
      setEmptyStartTimeError(false);
    }

    if (endTime === "") {
      setEmptyEndTimeError(true);
      invalidData = true;
    } else {
      setEmptyEndTimeError(false);
    }

    if (startDate === "") {
      setEmptyStartDateError(true);
      invalidData = true;
    } else {
      setEmptyStartDateError(false);
    }

    if (endDate === "") {
      setEmptyEndDateError(true);
      invalidData = true;
    } else {
      setEmptyEndDateError(false);
    }
    if ((modalReason !== "Duplicate") && (csvFile === "")) {
      setEmptyCSVFileError(true);
      invalidData = true;
    } else {
      setEmptyCSVFileError(false);
    }

    if ((modalReason !== "Duplicate") && (pairingModeValue === 6) && (teamFile === "")) {
      setEmptyTeamFileError(true);
      invalidData = true;
    } else {
      setEmptyTeamFileError(false);
    }
    return invalidData;
  }

  const checkStartAndEndDateTimes = () => {
    // Check that the starting date is legal
    let startDateObject = new Date(startDate + "T" + startTime + ":00");
    // Get the current time, but then set the hours/minutes/seconds/etc to be 0. Just want to deal with the calendar date
    let timestamp = new Date();
    let startOfDay = new Date(timestamp.getFullYear(), timestamp.getMonth(), timestamp.getDate());
    if (startDateObject < startOfDay) {
      setStartAfterCurrentError(true);
      return true;
    }
    let endDateObject = new Date(endDate + "T" + endTime + ":00");
    //Start date cannot be greater than End date.
    if (startDateObject > endDateObject) {
      setStartAfterEndError(true);
      return true;
    }
    // If we start and end on the same day, make certain the times are ordered properly
    if (startDateObject.getTime() === endDateObject.getTime()) {
      let startHour = parseInt(startTime.split(":")[0]);
      let startMin = parseInt(startTime.split(":")[1]);
      let endHour = parseInt(endTime.split(":")[0]);
      let endMin = parseInt(endTime.split(":")[1]);
      if ((startHour > endHour) || ((startHour === endHour) && (startMin >= endMin))) {
        setStartAfterEndError(true);
        return true;
      }
    }
    return false;
  }

  async function verifyAndPostSurvey() {
    clearErrors();

    // Report errors due to missing data
    if (checkForInvalidData()) {
      return;
    }

    if (checkStartAndEndDateTimes()) {
      return;
    }

    //Now it's time to send data to the backend
    let formData = new FormData();
    let multInt;

    if (useMultipler) {
      multInt = parseInt(multiplier);
    } else {
      multInt = 1;
    }

    // Create the survey data object that will be passed along to the next screen
    let surveyData = {
      survey_name: surveyName,
      course_id: survey_data.course_id,
      course_code: survey_data.course_code,
      course_name: survey_data.course_name,
      team_file: teamFile,
      csv_file: csvFile,
      rubric_id: rubric,
      rubric_name: rubricName,
      start_date: startDate,
      start_time: startTime,
      end_date: endDate,
      end_time: endTime,
      pairing_mode: pairingModeValue,
      pm_mult: multInt,
      reason: modalReason
    };

    formData.append("course-id", survey_data.course_id);
    formData.append("pairing-mode", pairingModeValue);
    if (modalReason === "Add") {
      // Post the CSV file so that we get student and team information
      formData.append("pairing-file", csvFile);
      formData.append("team-file", teamFile);
      let response = await addSurveyBackend(formData);
      // Pass along all of the survey data the user entered so it can be used later
      modalClose(response, surveyData);
    } else if (modalReason === "Duplicate") {
      // Record the survey ID of the original survey
      formData.append("survey-id", survey_data.original_id);
      // Form data is set. post the new survey and get the responses
      let response = await duplicateSurveyBackend(formData);
      // Pass along all of the survey data the user entered so it can be used later
      modalClose(response, surveyData);
    }
  }

  const quitModal = () => {
    modalClose(false, null);
  }

  return (
    <div className="modal">
      <div style={{ width: "800px", maxWidth: "90%" }} className="add-modal modal-content modal-phone">
        <div className="CancelContainer">
          <button className="CancelButton" onClick={quitModal}>
            ×
          </button>
        </div>
        <div className="modal--contents-container">
          <h2 className="modal--main-title">
            {modalReason} Survey for {survey_data.course_code + ": " + survey_data.course_name}
          </h2>
          <label className="form__item--label" htmlFor="survey-name">
            Survey Name
            <input
              className={surveyNameError ? "form__item--input-error" : undefined}
              id="survey-name"
              type="text"
              placeholder="Survey Name"
              value={surveyName}
              onChange={(e) => setSurveyName(e.target.value)}
            />
            {emptySurveyNameError && (
              <label className="form__item--error-label">
                <div className="form__item--red-warning-sign" />
                Survey name cannot be empty
              </label>
            )}
            {longSurveyNameError && (
              <label className="form__item--error-label">
                <div className="form__item--red-warning-sign" />
                Survey name is too long
              </label>
            )}
          </label>
          <div className="add-survey--row-with-errors-container">
            <div className="add-survey--row-container">
              <div className="add-survey--col-with-error-container">
                <div className="add-survey--col-container">
                  <label className="form__item--label" htmlFor="start-date">
                    Start Date
                    <input
                      className={(startAfterEndError || startAfterCurrentError || emptyStartDateError) ? "form__item--input-error" : undefined}
                      id="start-date"
                      type="date"
                      defaultValue={startDate}
                      placeholder="Enter Start Date"
                      onChange={(e) => setStartDate(e.target.value)}
                    />
                  </label>

                  <label className="form__item--label" htmlFor="start-time">
                    Start Time
                    <input
                      className={(startAfterCurrentError || startAfterEndError || emptyStartTimeError) ? "form__item--input-error" : undefined}
                      id="start-time"
                      type="time"
                      defaultValue={startTime}
                      placeholder="Enter Start Time"
                      onChange={(e) => setStartTime(e.target.value)}
                    />
                  </label>
                </div>
                {startAfterEndError && (
                  <label className="form__item--error-label">
                    <div className="form__item--red-warning-sign" />
                    Start must be earlier than end
                  </label>)}
                {startAfterCurrentError && (
                  <label className="form__item--error-label">
                    <div className="form__item--red-warning-sign" />
                    Start must be in the future
                  </label>)}
                {emptyStartDateError && (
                  <label className="form__item--error-label">
                    <div className="form__item--red-warning-sign" />
                    Start date cannot be empty
                  </label>)}
                {emptyStartTimeError && (
                  <label className="form__item--error-label">
                    <div className="form__item--red-warning-sign" />
                    Start time cannot be empty
                  </label>)}
              </div>

              <div className="add-survey--col-with-error-container">
                <div className="add-survey--col-container">
                  <label className="form__item--label" htmlFor="end-date">
                    End Date
                    <input
                      className={(emptyEndDateError || startAfterEndError) ? "form__item--input-error" : undefined}
                      id="end-date"
                      type="date"
                      placeholder="Enter End Date"
                      defaultValue={endDate}
                      onChange={(e) => setEndDate(e.target.value)}
                    />
                  </label>

                  <label className="form__item--label" htmlFor="end-time">
                    End Time
                    <input
                      className={(emptyEndTimeError || startAfterEndError) ? "form__item--input-error" : undefined}
                      id="end-time"
                      type="time"
                      placeholder="Enter End Time"
                      defaultValue={endTime}
                      onChange={(e) => setEndTime(e.target.value)}
                    />
                  </label>
                </div>
                {startAfterEndError && (
                  <label className="form__item--error-label">
                    <div className="form__item--red-warning-sign" />
                    End must be later than start
                  </label>)}
                {emptyEndDateError && (
                  <label className="form__item--error-label">
                    <div className="form__item--red-warning-sign" />
                    End date cannot be empty
                  </label>)}
                {emptyEndTimeError && (
                  <label className="form__item--error-label">
                    <div className="form__item--red-warning-sign" />
                    End time cannot be empty
                  </label>)}
              </div>
            </div>
          </div>
          <label className="form__item--label" htmlFor="rubric-type">
            Choose Rubric
            <select
              value={rubric}
              onChange={(e) => setRubric(e.target.value)}
              id="rubric-type"
            >
              {rubrics_list.map((rubric_info) => (
                (rubric === rubric_info.id) ?
                <option key={rubric_info.id} value={rubric_info.id}>{rubric_info.description}</option> :
                <option key={rubric_info.id} value={rubric_info.id}>{rubric_info.description}</option>
              ))}
            </select>
          </label>
          <label className="form__item--label add-survey--label-pairing" htmlFor="pairing">
            <div className="drop-down-wrapper">
              Pairing Modes
              <select className="pairing"
                value={pairingModeValue}
                onChange={handleChangePairing}
                id="pairing-mode"
              >
                {pairingModes.map((pairing) => (
                  <option className="pairing-option" key={pairing.id} value={pairing.id}>{pairing.text}</option>
                ))}
              </select>
            </div>
            <div className="pairing-mode-img-wrapper">
              <img className="pairing-mode-img" src={pairingImage} alt="team pairing mode" />
            </div>
        </label>
          {useMultipler && (
            <label className="form__item--label" htmlFor="multiplier">
              Multiplier
              <select className="multiplier"
                id="multiplier-type"
                value={multiplier}
                onChange={(e) => setMultiplier(e.target.value)}>
                <option value="1">1</option>
                <option value="2">2</option>
                <option value="3">3</option>
                <option value="4">4</option>
              </select>
            </label>
          )}
          {(modalReason !== "Duplicate") && (pairingModeValue === 6) && <label className="form__item--file-label" htmlFor="team-file">
            Team Roster Upload
            <span className="form__item--file-label--optional">One row per team. The first column of each row is the team's name. Each of the following columns should the email addresses of its members separatived by commas. Blank columns are ignored</span>
            <input
              className={emptyCSVFileError ? "form__item-input-error" : undefined}
              id="team-file"
              type="file"
              accept="text/plain,text/csv"
              placeholder={csvFile ? csvFile.name : "Upload The File"}
              onChange={(e) => setTeamFile(e.target.files[0])}
            />
            {emptyTeamFileError && (
              <label className="form__item--error-label">
                <div className="form__item--red-warning-sign" />
                Select a file
              </label>)}
          </label>}
          {(modalReason !== "Duplicate") && <label className="form__item--file-label" htmlFor="csv-file">
            Review Assignment File Upload
            <span className="form__item--file-label--optional">{CSVFileDescription}</span>
            <input
              className={emptyCSVFileError ? "form__item-input-error" : undefined}
              id="csv-file"
              type="file"
              accept="text/plain,text/csv"
              placeholder={csvFile ? csvFile.name : "Upload The File"}
              onChange={(e) => setCsvFile(e.target.files[0])}
            />
            {emptyCSVFileError && (
              <label className="form__item--error-label">
                <div className="form__item--red-warning-sign" />
                Select a file
              </label>)}
          </label>}
          <div className="form__item--confirm-btn-container">
            <button className="form__item--confirm-btn" onClick={verifyAndPostSurvey}>
              {button_text}
            </button>
          </div>
        </div>
      </div>
    </div>
  );
}
export default SurveyNewModal;
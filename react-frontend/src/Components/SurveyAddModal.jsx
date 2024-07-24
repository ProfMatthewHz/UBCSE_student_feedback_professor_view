import React, {useState} from "react";
import Team from "../assets/pairingmodes/TEAM.png"
import TeamSelf from "../assets/pairingmodes/TEAM+SELF.png"
import TeamSelfManager from "../assets/pairingmodes/TEAM+SELF+MANAGER.png"
import PM from "../assets/pairingmodes/PM.png"
import SinglePairs from "../assets/pairingmodes/SinglePairs.png"

const SurveyAddModal = ({modalClose, survey_data, pairing_modes, rubrics_list}) => {
  const [surveyName, setSurveyName] = useState(survey_data.survey_name);
  const [startTime, setStartTime] = useState("");
  const [endTime, setEndTime] = useState("");
  const [startDate, setStartDate] = useState("");
  const [endDate, setEndDate] = useState("");
  const [csvFile, setCsvFile] = useState(null);
  const [rubric, setRubric] = useState(rubrics_list[0].id);
  const [valuePairing, setValuePairing] = useState("2");
  const [multiplier, setMultiplier] = useState("1");
  const [useMultipler, setUseMultiplier] = useState(false);
  const [pairingImage, setPairingImage] = useState(Team);
  const [emptyCSVFileError, setEmptyCSVFileError] = useState(false);
  const [emptySurveyNameError, setEmptyNameError] = useState(false);
  const [emptyStartTimeError, setEmptyStartTimeError] = useState(false);
  const [emptyEndTimeError, setEmptyEndTimeError] = useState(false);
  const [emptyStartDateError, setEmptyStartDateError] = useState(false);
  const [emptyEndDateError, setEmptyEndDateError] = useState(false);
  const [startAfterCurrentError, setStartAfterCurrentError] = useState(false);
  const [startAfterEndError, setStartAfterEndError] = useState(false);

const handleUpdateImage = (pairing) => {
    switch(pairing) {
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
    let pairingMode = findPairingData(pairing, pairing_modes);
    console.log(pairingMode);
    handleUpdateImage(pairingMode.description);
    setValuePairing(pairing);
    setUseMultiplier(pairingMode.usesMultiplier);
  };

  const clearErrors = () => {
    setStartAfterCurrentError(false);
    setStartAfterEndError(false);
    setStartAfterCurrentError(false);
};

async function getAddSurveyResponse(formData) {
  let fetchHTTP =
      process.env.REACT_APP_API_URL + "addSurveyToCourse.php";
  const result = await fetch(fetchHTTP, {
      method: "POST",
      credentials: "include",
      body: formData,
  })
  .then((res) => res.json());

  return result; // Return the result directly
}

const checkForMissingData = () => {
  let missingData = false;

  if (surveyName === "") {
    setEmptyNameError(true);
    missingData = true;
  } else {
    setEmptyNameError(false);
  }

  if (startTime === "") {
    setEmptyStartTimeError(true);
    missingData = true;
  } else {
    setEmptyStartTimeError(false);
  }

  if (endTime === "") {
    setEmptyEndTimeError(true);
    missingData = true;
  } else {
    setEmptyEndTimeError(false);
  }

  if (startDate === "") {
    setEmptyStartDateError(true);
    missingData = true;
  } else {
    setEmptyStartDateError(false);
  }

  if (endDate === "") {
    setEmptyEndDateError(true);
    missingData = true;
  } else {
    setEmptyEndDateError(false);
  }

  if (csvFile == null) {
    setEmptyCSVFileError(true);
    missingData = true;
  } else {
    setEmptyCSVFileError(false);
  }
  return missingData;
} 

const checkStartAndEndDateTimes = () => {
  // Check that the starting date is legal
  let startDateObject = new Date(startDate + "T" + startTime + ":00");
  // Get the current time, but then set the hours/minutes/seconds/etc to be 0. Just want to deal with the calendar date
  let timestamp = new Date(Date.now());
  if (startDateObject < timestamp) {
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
    if ((startHour > endHour) || ((startHour === endHour) && (startMin >= endMin)))  {
      setStartAfterEndError(true);
      return true;
    }
  }
  return false;
}

async function verifyAndPostSurvey() {
  clearErrors();

  // Report errors due to missing data
  if (checkForMissingData()) {
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

  formData.append("survey-name", surveyName);
  formData.append("course-id", survey_data.course_id);
  formData.append("rubric-id", rubric);
  formData.append("pairing-mode", valuePairing);
  formData.append("start-date", startDate);
  formData.append("start-time", startTime);
  formData.append("end-date", endDate);
  formData.append("end-time", endTime);
  formData.append("pm-mult", multInt);
  formData.append("pairing-file", csvFile);
  console.log(formData);

  // Form data is set. post the new survey and get the responses
  let awaitedResponse = await getAddSurveyResponse(formData);
  modalClose(awaitedResponse);
}

  return (
    <div className="modal">
      <div style={{width: "800px", maxWidth: "90%"}} className="add-modal modal-content modal-phone">
                <div className="CancelContainer">
                    <button className="CancelButton" onClick={modalClose}>
                        ×
                    </button>
                </div>
                <div className="add-survey--contents-container">
                    <h2 className="add-survey--main-title">
                        Add Survey for {survey_data.course_name}
                    </h2>

                    <label className="add-survey--label" htmlFor="survey-name">
                        Survey Name
                        <input
                            className={emptySurveyNameError ? "add-survey-input-error" : undefined}
                            id="survey-name"
                            type="text"
                            placeholder="Survey Name"
                            onChange={(e) => setSurveyName(e.target.value)}
                        />
                        {emptySurveyNameError ? (
                            <label className="add-survey--error-label">
                                <div className="add-survey--red-warning-sign"/>
                                Survey name cannot be empty
                            </label>
                        ) : null}
                    </label>
                    <div className="add-survey--date-times-errors-container">
                        <div className="add-survey--all-dates-and-times-container">
                            <div className="add-survey--date-times-error-container">
                                <div className="add-survey--date-and-times-container">
                                    <label className="add-survey--label" htmlFor="start-date">
                                        Start Date
                                        <input
                                            className={(startAfterEndError || startAfterCurrentError || emptyStartDateError ) ? "add-survey-input-error" : null}
                                            id="start-date"
                                            type="date"
                                            placeholder="Enter Start Date"
                                            onChange={(e) => setStartDate(e.target.value)}
                                        />
                                    </label>

                                    <label className="add-survey--label" htmlFor="start-time">
                                        Start Time
                                        <input
                                            className={(startAfterCurrentError || startAfterEndError || emptyStartTimeError ) ? "add-survey-input-error" : null}
                                            id="start-time"
                                            type="time"
                                            placeholder="Enter Start Time"
                                            onChange={(e) => setStartTime(e.target.value)}
                                        />
                                    </label>
                                </div>
                                {startAfterEndError ? <label className="add-survey--error-label">
                                    <div className="add-survey--red-warning-sign"/>
                                    Start must be earlier than end</label> : null}
                                {startAfterCurrentError ? <label className="add-survey--error-label">
                                    <div className="add-survey--red-warning-sign"/>
                                    Start must be in the future</label> : null}
                                {emptyStartDateError ? <label className="add-survey--error-label">
                                    <div className="add-survey--red-warning-sign"/>
                                    Start date cannot be empty</label> : null}
                                {emptyStartTimeError ? <label className="add-survey--error-label">
                                    <div className="add-survey--red-warning-sign"/>
                                    Start time cannot be empty</label> : null}
                            </div>

                            <div className="add-survey--date-times-error-container">
                                <div className="add-survey--date-and-times-container">
                                    <label className="add-survey--label" htmlFor="end-date">
                                        End Date
                                        <input
                                            className={(emptyEndDateError || startAfterEndError) ? "add-survey-input-error" : null}
                                            id="end-date"
                                            type="date"
                                            placeholder="Enter End Date"
                                            onChange={(e) => setEndDate(e.target.value)}
                                        />
                                    </label>

                                    <label className="add-survey--label" htmlFor="end-time">
                                        End Time
                                        <input
                                            className={(emptyEndTimeError || startAfterEndError) ? "add-survey-input-error" : null}
                                            id="end-time"
                                            type="time"
                                            placeholder="Enter End Time"
                                            onChange={(e) => setEndTime(e.target.value)}

                                        />
                                    </label>
                                </div>
                                {startAfterEndError ? <label className="add-survey--error-label">
                                    <div className="add-survey--red-warning-sign"/>
                                    End must be later than start</label> : null}
                                {emptyEndDateError ? <label className="add-survey--error-label">
                                    <div className="add-survey--red-warning-sign"/>
                                    End date cannot be empty</label> : null}
                                {emptyEndTimeError ? <label className="add-survey--error-label">
                                    <div className="add-survey--red-warning-sign"/>
                                    End time cannot be empty</label> : null}
                            </div>
                        </div>
                    </div>
                    <label className="add-survey--label" htmlFor="rubric-type">
                        Choose Rubric
                        <select
                            value={rubric}
                            onChange={(e) => setRubric(e.target.value)}
                            id="rubric-type"
                        >
                            {rubrics_list.map((rubric) => (
                                <option value={rubric.id}>{rubric.description}</option>
                            ))}
                        </select>
                    </label>
                    <label className="add-survey--label-pairing" htmlFor="pairing">
                        <div className="drop-down-wrapper">
                            Pairing Modes
                            <select className="pairing"
                                value={valuePairing}
                                onChange={handleChangePairing}
                                id="pairing-mode"
                            >
                                {pairing_modes.map((pairing) => (
                                    <option className= "pairing-option" value={pairing.id}>{pairing.description}</option>
                                ))}
                            </select>
                        </div>
                        <div className="pairing-mode-img-wrapper">
                            <img className="pairing-mode-img" src={pairingImage} alt="team pairing mode" />
                        </div>
                    </label>
                    {useMultipler && (
                        <label className="add-survey--label" htmlFor="multiplier">
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
                    <label className="add-survey--file-label" htmlFor="csv-file">
                        CSV File Upload
                        <input
                            className={emptyCSVFileError && "add-survey-input-error"}
                            id="csv-file"
                            type="file"
                            placeholder="Upload The File"
                            onChange={(e) => setCsvFile(e.target.files[0])}
                        />
                        {emptyCSVFileError ? (
                            <label className="add-survey--error-label">
                                <div className="add-survey--red-warning-sign"/>
                                Select a file</label>
                        ) : null}
                    </label>
                    <div className="add-survey--confirm-btn-container">
                        <button className="add-survey--confirm-btn" onClick={verifyAndPostSurvey}>
                            Verify Survey
                        </button>
                    </div>
                </div>
                </div>
                </div>
  );
}
export default SurveyAddModal;
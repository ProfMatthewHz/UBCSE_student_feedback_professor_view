import React, {useEffect, useState} from "react";
import "../styles/course.css";
import "../styles/modal.css";
import "../styles/duplicatesurvey.css";
import "../styles/addsurvey.css";
import Modal from "./Modal";
import Toast from "./Toast";
import ViewResults from "./ViewResults";
import {RadioButton} from "primereact/radiobutton";
import { useNavigate } from "react-router-dom";
import SurveyExtendModal from "./SurveyExtendModal";
import SurveyDeleteModal from "./SurveyDeleteModal";
import SurveyErrorsModal from "./SurveyErrorsModal";
import SurveyConfirmModal from "./SurveyConfirmModal";
import SurveyAddModal from "./SurveyAddModal";

/**
 * @component
 * @param {Object} course 
 * @param {String} page // What page the component is being used on. Either Home or History
 * @returns 
 */
const Course = ({course, page}) => {
    const [surveys, setSurveys] = useState([]);

    /**
     * Perform a POST call to courseSurveysQueries 
     */
    function updateAllSurveys() {
        fetch(process.env.REACT_APP_API_URL + "courseSurveysQueries.php", {
            method: "POST",
            credentials: "include",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded",
            },
            body: new URLSearchParams({
                "course-id": course.id,
            }),
        })
        .then((res) => res.json())
        .then((result) => {
            const activeSurveys = result.active.map((survey_info) => ({
                ...survey_info,
                expired: false,
            }));
            const expiredSurveys = result.expired.map((survey_info) => ({
                ...survey_info,
                expired: true,
            }));
            const upcomingSurveys = result.upcoming.map((survey_info) => ({
                ...survey_info,
                expired: false,
            }));
            setSurveys([...activeSurveys, ...expiredSurveys, ...upcomingSurveys]);
        })
        .catch((err) => {
            console.log(err);
            throw err;
        });
    }

    // MODAL CODE
    const [actionsButtonValue, setActionsButtonValue] = useState("");
    const [extendModal, setExtendModal] = useState(false);
    const [duplicateModal, setDuplicateModel] = useState(false);

    const [deleteModal, setDeleteModal] = useState(false);
    const [addSurveyModalIsOpen, setAddSurveyModalIsOpen] = useState(false);
    const [errorModalIsOpen, setModalIsOpenError] = useState(false);
    const [errorsList, setErrorsList] = useState([]);
    const [modalIsOpenSurveyConfirm, setModalIsOpenSurveyConfirm] = useState(false);
    const [showUpdateModal, setShowUpdateModal] = useState(false);
    const [currentSurvey, setCurrentSurvey] = useState("");

    const [showViewResultsModal, setViewResultsModal] = useState(false);
    const [viewingCurrentSurvey, setViewingCurrentSurvey] = useState(null);

    const [rosterFile, setRosterFile] = useState(null);

    const [updateRosterOption, setUpdateRosterOption] = useState("replace");
    const [updateRosterError, setUpdateRosterError] = useState([]);

    const [showErrorModal, setShowErrorModal] = useState(false);
    const [showToast, setShowToast] = useState(false);
    const [rubricNames, setNames] = useState([]);
    const [rubricIDandDescriptions, setIDandDescriptions] = useState([]);
    const [pairingModesFull, setPairingModesFull] = useState([]);
    const [survey_confirm_data, setSurveyConfirmData] = useState(null);

    //START:Error codes for modal frontend
    const [emptySurveyNameError, setEmptyNameError] = useState(false);
    const [emptyStartTimeError, setEmptyStartTimeError] = useState(false);
    const [emptyEndTimeError, setEmptyEndTimeError] = useState(false);
    const [emptyStartDateError, setEmptyStartDateError] = useState(false);
    const [emptyEndDateError, setEmptyEndDateError] = useState(false);
    const [startDateBoundError, setStartDateBoundError] = useState(false);
    const [startDateBound1Error, setStartDateBound1Error] = useState(false);
    const [endDateBoundError, setEndDateBoundError] = useState(false);
    const [endDateBound1Error, setEndDateBound1Error] = useState(false);
    const [StartAfterCurrentError, setStartAfterCurrentError] = useState(false);
    const [StartDateGreaterError, setStartDateGreaterError] = useState(false);
    const [StartTimeSameDayError, setStartTimeSameDayError] = useState(false);
    const [StartHourSameDayError, setStartHourSameDayError] = useState(false);
    const [StartHourAfterEndHourError, setStartHourAfterEndHourError] =
        useState(false);
    const [StartTimeHoursBeforeCurrent, setStartTimeHoursBeforeCurrent] =
        useState(false);
    const [StartTimeMinutesBeforeCurrent, setStartTimeMinutesBeforeCurrent] =
        useState(false);
    //END:Error codes for modal frontend

    const updateRosterformData = new FormData();

    /**
     * Perform a GET call to rubricsGet.php to fetch names and ID of the rubrics. 
     */
    const fetchRubrics = () => {
        fetch(process.env.REACT_APP_API_URL + "getInstructorRubrics.php", {
            method: "GET",
            credentials: "include",
        })
        .then((res) => res.json())
        .then((result) => {
            //this is an array of objects of example elements {id: 1, description: 'exampleDescription'}
            let rubricIDandDescriptions = result.rubrics.map((element) => element);
            //An array of just the descriptions of the rubrics
            let rubricNames = result.rubrics.map((element) => element.description);
            setNames(rubricNames);
            setIDandDescriptions(rubricIDandDescriptions);
        })
        .catch((err) => {
            console.log(err);
            throw err;
        });
    }; 
    /**
     * Perform a GET call to getSurveyTypes.php to fetch all possible survey pairing modes
     */
    const fetchPairingModes = () => {
        fetch(process.env.REACT_APP_API_URL + "getSurveyTypes.php", {
            method: "GET",
            credentials: "include"
        })
        .then((res) => res.json())
        .then((result) => {
            setPairingModesFull(result.survey_types);
        })
        .catch((err) => {
            console.log(err);
            throw err;
        });
    };

    const openAddSurveyModal = () => {
        setAddSurveyModalIsOpen(true);
        fetchRubrics();
        fetchPairingModes();
    };

    const closeAddSurveyModal = () => {
        setAddSurveyModalIsOpen(false);
    };

    const closeModalError = () => {
        setModalIsOpenError(false);
    };

    const closeModalSurveyConfirm = (success) => {
        setModalIsOpenSurveyConfirm(false);
        setSurveyConfirmData(null);
        if (success) {
            updateAllSurveys();
        }
    };

    const handleErrorModalClose = () => {
        setRosterFile(null); // sets the file to null
        setShowErrorModal(false); // close the error modal
        setShowUpdateModal(true); // open the update modal again
    };


    async function fetchRosterNonRoster() {
        let fetchHTTP = process.env.REACT_APP_API_URL + "confirmationForSurvey.php";
        const result = fetch(fetchHTTP, {
            method: "GET",
            credentials: "include",
        })
        .then((res) => res.json());

        return result; // Return the result directly
    }



 function duplicateSurveyBackend(formdata) {
        let fetchHTTP =
            process.env.REACT_APP_API_URL +
            "duplicateExistingSurvey.php?survey=" +
            currentSurvey.id;
        const result = fetch(fetchHTTP, {
            method: "POST",
            credentials: "include",
            body: formdata,
        }).then((res) => res.text());
        return result; // Return the result directly
    }

    const verifyDuplicateSurvey = () => {
        setEmptyNameError(false);
        setEmptyStartTimeError(false);
        setEmptyEndTimeError(false);
        setEmptyStartDateError(false);
        setEmptyEndDateError(false);
        //MHz setEmptyCSVFileError(false);
        setStartDateBoundError(false);
        setStartDateBound1Error(false);
        setEndDateBoundError(false);
        setEndDateBound1Error(false);
        setStartAfterCurrentError(false);
        setStartDateGreaterError(false);
        setStartTimeSameDayError(false);
        setStartHourSameDayError(false);
        setStartHourAfterEndHourError(false);
        setStartTimeHoursBeforeCurrent(false);
        setStartTimeMinutesBeforeCurrent(false);

        let surveyName = document.getElementById("survey-name").value;
        let startTime = document.getElementById("start-time").value;
        let endTime = document.getElementById("end-time").value;
        let startDate = document.getElementById("start-date").value;
        let endDate = document.getElementById("end-date").value;
        let rubric = document.getElementById("rubric-type").value;
        
        if (surveyName === "") {
            setEmptyNameError(true);
            return;
        }
        if (startTime === "") {
            setEmptyStartTimeError(true);
            return;
        }
        if (endTime === "") {
            setEmptyEndTimeError(true);
            return;
        }
        if (startDate === "") {
            setEmptyStartDateError(true);
            return;
        }
        if (endDate === "") {
            setEmptyEndDateError(true);
            return;
        }

        //date and time keyboard typing bound checks.
        let startDateObject = new Date(startDate + "T00:00:00"); //inputted start date.
        let endDateObject = new Date(endDate + "T00:00:00"); //inputted end date.

        //special startdate case. Startdate cannot be before the current day.
        let timestamp = new Date(Date.now());

        timestamp.setHours(0, 0, 0, 0); //set hours/minutes/seconds/etc to be 0. Just want to deal with the calendar date
        if (startDateObject < timestamp) {
            setStartAfterCurrentError(true);
            return;
        }
        //END:special startdate case. Startdate cannot be before the current day.

        //Start date cannot be greater than End date.
        if (startDateObject > endDateObject) {
            setStartDateGreaterError(true);
            return;
        }
        //END:Start date cannot be greater than End date.

        //If on the same day, start time must be before end time
        if (startDate === endDate) {
            if (startTime === endTime) {
                setStartTimeSameDayError(true);
                return;
            }
            let startHour = parseInt(startTime.split(":")[0]);
            let endHour = parseInt(endTime.split(":")[0]);
            if (startHour === endHour) {
                setStartHourSameDayError(true);
                return;
            }
            if (startHour > endHour) {
                setStartHourAfterEndHourError(true);
                return;
            }
        }
        //Start time must be after current time if start date is the current day.
        if (startDateObject.getDate(startDateObject) === timestamp.getDate(timestamp)) {
            let timestampWithHour = new Date(Date.now());
            let currentHour = timestampWithHour.getHours(timestampWithHour);
            let currentMinutes = timestampWithHour.getMinutes(timestampWithHour);
            let startHourNew = parseInt(startTime.split(":")[0]);
            let startMinutes = parseInt(startTime.split(":")[1]);

            if (startHourNew < currentHour) {
                setStartTimeHoursBeforeCurrent(true);
                return;
            }
            if (startHourNew === currentHour) {
                if (startMinutes < currentMinutes) {
                    setStartTimeMinutesBeforeCurrent(true);
                    return;
                }
            }
            //End:Start time must be after current time
        }

        //Now it's time to send data to the backend

        let formData3 = new FormData();
        let rubricId;

        for (const element of rubricIDandDescriptions) {
            if (element.description === rubric) {
                rubricId = element.id;
            }
        }

        formData3.append("survey-id", currentSurvey.id);
        formData3.append("survey-name", surveyName);
        formData3.append("rubric-id", rubricId);
        formData3.append("start-date", startDate);
        formData3.append("start-time", startTime);
        formData3.append("end-date", endDate);
        formData3.append("end-time", endTime);

        //form data is set. Call the post request
        duplicateSurveyBackend(formData3);
        updateAllSurveys();
        closeModalDuplicate();
    }

    let Navigate = useNavigate();
    const handleActionButtonChange = (e, survey) => {
        setActionsButtonValue(e.target.value);

        if (e.target.value === "Duplicate") {
            fetchRubrics();
            setCurrentSurvey(survey);
            setDuplicateModel(true);
        }
        if (e.target.value === "Delete") {
            setCurrentSurvey(survey);
            setDeleteModal(true);
        }
        if (e.target.value === "Extend") {
            setCurrentSurvey(survey);
            setExtendModal(true);
        }
        if (e.target.value === "View Results") {
            handleViewResultsModalChange(survey);
        }
        if (e.target.value === "Preview Survey") {
            Navigate("/SurveyPreview", {state:{survey_name: survey.name, rubric_id: survey.rubric_id, course: course.code}});
        }
        setActionsButtonValue("");
    };

    function formatRosterError(input) {
        // Split the string into an array on the "Line" pattern, then filter out empty strings
        const lines = input
            .split(/(Line \d+)/)
            .filter((line) => line.trim() !== "");
        // Combine adjacent elements so that each "Line #" and its message are in the same element
        const combinedLines = [];
        for (let i = 0; i < lines.length; i += 2) {
            combinedLines.push(lines[i] + (lines[i + 1] || ""));
        }
        return combinedLines
    }

    const handleUpdateRosterSubmit = (e) => {
        e.preventDefault();

        updateRosterformData.append("roster-file", rosterFile);
        updateRosterformData.append("course-id", course.id);
        updateRosterformData.append("update-type", updateRosterOption);

        fetch(process.env.REACT_APP_API_URL + "rosterUpdate.php", {
            method: "POST",
            credentials: "include",
            body: updateRosterformData,
        })
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
                            const updatedError = formatRosterError(
                                parsedResult["error"]
                            );
                            setUpdateRosterError(updatedError);
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
        fetch(process.env.REACT_APP_API_URL + "courseSurveysQueries.php", {
            method: "POST",
            credentials: "include",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded",
            },
            body: new URLSearchParams({
                "course-id": course.id,
            }),
        })
            .then((res) => res.json())
            .then((result) => {
                const activeSurveys = result.active.map((survey_info) => ({
                    ...survey_info,
                    expired: false,
                }));
                const expiredSurveys = result.expired.map((survey_info) => ({
                    ...survey_info,
                    expired: true,
                }));
                const upcomingSurveys = result.upcoming.map((survey_info) => ({
                    ...survey_info,
                    expired: false,
                }));

                setSurveys([...activeSurveys, ...expiredSurveys, ...upcomingSurveys]);
            })
            .catch((err) => {
                console.log(err);
            });
    }, [course.id]);

    function closeModalDuplicate() {
        setDuplicateModel(false);
        setEmptyNameError(false);
        setEmptyStartTimeError(false);
        setEmptyEndTimeError(false);
        setEmptyStartDateError(false);
        setEmptyEndDateError(false);
        //MHz setEmptyCSVFileError(false);
        setStartDateBoundError(false);
        setStartDateBound1Error(false);
        setEndDateBoundError(false);
        setEndDateBound1Error(false);
        setStartAfterCurrentError(false);
        setStartDateGreaterError(false);
        setStartTimeSameDayError(false);
        setStartHourSameDayError(false);
        setStartHourAfterEndHourError(false);
        setStartTimeHoursBeforeCurrent(false);
        setStartTimeMinutesBeforeCurrent(false);
    }

    const extendModalClose = (errorList) => {
        setExtendModal(false);
        if (errorList && errorList.length > 0) {
          setErrorsList(errorList);
          setModalIsOpenError(true);
        } else {
            updateAllSurveys();
        }
    }

    const deleteModalClose = (errorList) =>{
        setDeleteModal(false);
        if (errorList && errorList.length > 0) {
            setErrorsList(errorList);
            setModalIsOpenError(true);
        } else {
            updateAllSurveys();
        }
    }

    function handleUpdateModalChange() {
        setShowUpdateModal((prev) => !prev);
    };

    function handleViewResultsModalChange(survey) {
        setViewResultsModal((prev) => !prev);
        setViewingCurrentSurvey(survey);
    };

    return (
        <div id={course.code} className="courseContainer">
            {/* Survey extendsion modal*/}
            {extendModal &&
            (<SurveyExtendModal
                modalClose={extendModalClose}
                survey_data={currentSurvey} />
            )}
            {/* Survey deletion modal*/}
            {deleteModal &&
            (<SurveyDeleteModal
                modalClose={deleteModalClose}
                survey_data={currentSurvey} />
            )}
            {/* Survey creation errors modal*/}
            {errorModalIsOpen && (
            <SurveyErrorsModal
                modalClose={closeModalError}
                error_type={"Survey"}
                errors={errorsList} />
            )}
            {/* Survey creation confirmation modal*/}
            {modalIsOpenSurveyConfirm && (
            <SurveyConfirmModal
                modalClose={closeModalSurveyConfirm}
                survey_data={survey_confirm_data}/>
            )}
            {/* View Results Modal*/}
            {showViewResultsModal && (
            <ViewResults
                closeViewResultsModal={handleViewResultsModalChange}
                surveyToView={viewingCurrentSurvey}
                course={course}
            />
            )}
            {/* Roster error display */}
            {showErrorModal && (
            <SurveyErrorsModal
                    modalClose={handleErrorModalClose}
                    error_type={"Roster Update"}
                    errors={updateRosterError} />
            )}
            {/* Add Survey to a course modal*/}
            {addSurveyModalIsOpen && (
            <SurveyAddModal
                modalClose={closeAddSurveyModal}
                survey_data={ {"course_name" : course.code, "course_id" : course.id, "survey_name" : "", } }
                pairing_modes = {pairingModesFull}
                rubrics_list={rubricIDandDescriptions}/>
            )}
            {/*<Modal
                open={duplicateModal}
                onRequestClose={closeModalDuplicate}
                maxWidth={"90%"}
            >
                <div className="CancelContainer">
                    <button className="CancelButton" onClick={closeModalDuplicate}>
                        ×
                    </button>
                </div>
                <div className="duplicate-survey--contents-container">
                    <h2 className="duplicate-survey--main-title">
                        Duplicate Survey: {currentSurvey.name}
                    </h2>
                    <div
                        className={
                            emptySurveyNameError
                                ? "duplicate-survey--input-error"
                                : "duplicate-survey--input"
                        }
                    >
                        <label for="subject-line">New Survey Name</label>
                        <input id="survey-name" placeholder="New Name" type="text"/>
                        {emptySurveyNameError ? (
                            <label className="duplicate-survey--error-label">
                                <div className="duplicate-survey--red-warning-sign"/>
                                Survey name cannot be empty
                            </label>
                        ) : null}
                    </div>
                    <div className="duplicate-survey--input">
                        <label for="subject-line">Choose Rubric</label>
                        <select
                            value={valueRubric}
                            onChange={handleChangeRubric}
                            id="rubric-type"
                            placeholder="Select a rubric"
                        >
                            {rubricNames.map((rubric) => (
                                <option value={rubric}>{rubric}</option>
                            ))}
                        </select>
                    </div>
                    <div className="duplicate-survey--timeline-data-error-container">
                        <div className="duplicate-survey--timeline-data-container">
                            <div className="duplicate-survey--labels-dates-container">
                                <div className="duplicate-survey--dates-times-error-container">
                                    <label for="subject-line">
                                        Start Date
                                        <input
                                            className={(StartDateGreaterError || StartAfterCurrentError || emptyStartDateError || startDateBoundError || startDateBound1Error) ? "duplicate-survey--error-input" : null}
                                            id="start-date"
                                            type="date"
                                            placeholder="Enter New Start Date"
                                        />
                                    </label>
                                    <label for="subject-line">
                                        Start Time
                                        <input
                                            className={(StartHourAfterEndHourError || StartHourSameDayError || StartTimeSameDayError || emptyStartTimeError || StartTimeHoursBeforeCurrent || StartTimeMinutesBeforeCurrent ? "duplicate-survey--error-input" : null)}
                                            id="start-time"
                                            type="time"
                                            placeholder="Enter New Start Time"
                                        />
                                    </label>
                                </div>
                                {StartDateGreaterError ? <label className="duplicate-survey--error-label">
                                    <div className="duplicate-survey--red-warning-sign"/>
                                    Start date cannot be before the end date</label> : null}
                                {StartAfterCurrentError ? <label className="duplicate-survey--error-label">
                                    <div className="duplicate-survey--red-warning-sign"/>
                                    Start date cannot be before the current date</label> : null}
                                {emptyStartDateError ? <label className="duplicate-survey--error-label">
                                    <div className="duplicate-survey--red-warning-sign"/>
                                    Start date cannot be empty</label> : null}
                                {startDateBoundError ? <label className="duplicate-survey--error-label">
                                    <div className="duplicate-survey--red-warning-sign"/>
                                    Start date must be at August 31st or later</label> : null}
                                {startDateBound1Error ? <label className="duplicate-survey--error-label">
                                    <div className="duplicate-survey--red-warning-sign"/>
                                    Start date must be at December 31st or earlier</label> : null}
                                {StartHourAfterEndHourError ? <label className="duplicate-survey--error-label">
                                    <div className="duplicate-survey--red-warning-sign"/>
                                    If start and end dates are the same, start time cannot be after end
                                    time</label> : null}
                                {StartHourSameDayError ? <label className="duplicate-survey--error-label">
                                    <div className="duplicate-survey--red-warning-sign"/>
                                    If start and end dates are the same, end hour can not be in the same hour as
                                    start</label> : null}
                                {StartTimeSameDayError ? <label className="duplicate-survey--error-label">
                                    <div className="duplicate-survey--red-warning-sign"/>
                                    If start and end dates are the same, start and end times must differ</label> : null}
                                {emptyStartTimeError ? <label className="duplicate-survey--error-label">
                                    <div className="duplicate-survey--red-warning-sign"/>
                                    Start time cannot be empty</label> : null}
                                {StartTimeHoursBeforeCurrent ? <label className="duplicate-survey--error-label">
                                    <div className="duplicate-survey--red-warning-sign"/>
                                    Start time hour cannot be before the current hour</label> : null}
                                {StartTimeMinutesBeforeCurrent ? <label className="duplicate-survey--error-label">
                                    <div className="duplicate-survey--red-warning-sign"/>
                                    Start time minutes cannot be before current minutes</label> : null}
                            </div>
                            <div className="duplicate-survey--labels-dates-container">
                                <div className="duplicate-survey--dates-times-error-container">
                                    <label for="subject-line">
                                        End Date
                                        <input
                                            className={(emptyEndDateError || endDateBoundError || endDateBound1Error) ? "duplicate-survey--error-input" : null}
                                            id="end-date"
                                            type="date"
                                            placeholder="Enter New End Date"
                                        />
                                    </label>

                                    <label for="subject-line">
                                        End Time
                                        <input
                                            className={emptyEndTimeError ? "duplicate-survey--error-input" : null}
                                            id="end-time"
                                            type="time"
                                            placeholder="Enter New End Time"
                                        />
                                    </label>
                                </div>
                                {emptyEndDateError ? <label className="duplicate-survey--error-label">
                                    <div className="duplicate-survey--red-warning-sign"/>
                                    End date cannot be empty</label> : null}
                                {endDateBoundError ? <label className="duplicate-survey--error-label">
                                    <div className="duplicate-survey--red-warning-sign"/>
                                    End date must be at August 31st or later</label> : null}
                                {endDateBound1Error ? <label className="duplicate-survey--error-label">
                                    <div className="duplicate-survey--red-warning-sign"/>
                                    End date must be at December 31st or earlier</label> : null}
                                {emptyEndTimeError ? <label className="duplicate-survey--error-label">
                                    <div className="duplicate-survey--red-warning-sign"/>
                                    End time cannot be empty</label> : null}
                            </div>
                        </div>
                    </div>
                    <div className="duplicate-survey--confirm-btn-container">
                        <button
                            className="duplicate-survey--confirm-btn"
                            onClick={verifyDuplicateSurvey}
                        >
                            Duplicate Survey
                        </button>
                    </div>
                </div>
            </Modal>*/}

            {/*<Modal
                open={addSurveyModalIsOpen}
                onRequestClose={closeAddSurveyModal}
                width={"800px"}
                maxWidth={"90%"}
            >
                <div className="CancelContainer">
                    <button className="CancelButton" onClick={closeAddSurveyModal}>
                        ×
                    </button>
                </div>
                <div className="add-survey--contents-container">
                    <h2 className="add-survey--main-title">
                        Add Survey for {course.code}
                    </h2>

                    <label className="add-survey--label" for="subject-line">
                        Survey Name
                        <input
                            className={emptySurveyNameError && "add-survey-input-error"}
                            id="survey-name"
                            type="text"
                            placeholder="Survey Name"
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
                                    <label className="add-survey--label" for="subject-line">
                                        Start Date
                                        <input
                                            className={(StartDateGreaterError || StartAfterCurrentError || emptyStartDateError || startDateBoundError || startDateBound1Error) ? "add-survey-input-error" : null}
                                            id="start-date"
                                            type="date"
                                            placeholder="Enter Start Date"
                                        />
                                    </label>

                                    <label className="add-survey--label" for="subject-line">
                                        Start Time
                                        <input
                                            className={(StartHourAfterEndHourError || StartHourSameDayError || StartTimeSameDayError || emptyStartTimeError || StartTimeHoursBeforeCurrent || StartTimeMinutesBeforeCurrent) ? "add-survey-input-error" : null}
                                            id="start-time"
                                            type="time"
                                            placeholder="Enter Start Time"
                                        />
                                    </label>
                                </div>
                                {StartDateGreaterError ? <label className="add-survey--error-label">
                                    <div className="add-survey--red-warning-sign"/>
                                    Start date cannot be before the end date</label> : null}
                                {StartAfterCurrentError ? <label className="add-survey--error-label">
                                    <div className="add-survey--red-warning-sign"/>
                                    Start date cannot be before the current date</label> : null}
                                {emptyStartDateError ? <label className="add-survey--error-label">
                                    <div className="add-survey--red-warning-sign"/>
                                    Start date cannot be empty</label> : null}
                                {startDateBoundError ? <label className="add-survey--error-label">
                                    <div className="add-survey--red-warning-sign"/>
                                    Start date must be at August 31st or later</label> : null}
                                {startDateBound1Error ? <label className="add-survey--error-label">
                                    <div className="add-survey--red-warning-sign"/>
                                    Start date must be at December 31st or earlier</label> : null}
                                {StartHourAfterEndHourError ? <label className="add-survey--error-label">
                                    <div className="add-survey--red-warning-sign"/>
                                    If start and end dates are the same, start time cannot be after end
                                    time</label> : null}
                                {StartHourSameDayError ? <label className="add-survey--error-label">
                                    <div className="add-survey--red-warning-sign"/>
                                    If start and end dates are the same, end hour cannot be in the same hour as the
                                    start</label> : null}
                                {StartTimeSameDayError ? <label className="add-survey--error-label">
                                    <div className="add-survey--red-warning-sign"/>
                                    If start and end dates are the same, start and end times must differ</label> : null}
                                {emptyStartTimeError ? <label className="add-survey--error-label">
                                    <div className="add-survey--red-warning-sign"/>
                                    Start time cannot be empty</label> : null}
                                {StartTimeHoursBeforeCurrent ? <label className="add-survey--error-label">
                                    <div className="add-survey--red-warning-sign"/>
                                    Start time hour cannot be before the current hour</label> : null}
                                {StartTimeMinutesBeforeCurrent ? <label className="add-survey--error-label">
                                    <div className="add-survey--red-warning-sign"/>
                                    Start time minutes cannot be before current minutes</label> : null}
                            </div>


                            <div className="add-survey--date-times-error-container">
                                <div className="add-survey--date-and-times-container">
                                    <label className="add-survey--label" for="subject-line">
                                        End Date
                                        <input
                                            className={(emptyEndDateError || endDateBoundError || endDateBound1Error) ? "add-survey-input-error" : null}
                                            id="end-date"
                                            type="date"
                                            placeholder="Enter End Date"
                                        />
                                    </label>

                                    <label className="add-survey--label" for="subject-line">
                                        End Time
                                        <input
                                            className={(emptyEndTimeError) ? "add-survey-input-error" : null}
                                            id="end-time"
                                            type="time"
                                            placeholder="Enter End Time"
                                        />
                                    </label>
                                </div>
                                {emptyEndDateError ? <label className="add-survey--error-label">
                                    <div className="add-survey--red-warning-sign"/>
                                    End date cannot be empty</label> : null}
                                {endDateBoundError ? <label className="add-survey--error-label">
                                    <div className="add-survey--red-warning-sign"/>
                                    End date must be at August 31st or later</label> : null}
                                {endDateBound1Error ? <label className="add-survey--error-label">
                                    <div className="add-survey--red-warning-sign"/>
                                    End date must be at December 31st or earlier</label> : null}
                                {emptyEndTimeError ? <label className="add-survey--error-label">
                                    <div className="add-survey--red-warning-sign"/>
                                    End time cannot be empty</label> : null}
                            </div>
                        </div>
                    </div>
                    <label className="add-survey--label" for="subject-line">
                        Choose Rubric
                        <select
                            value={valueRubric}
                            onChange={handleChangeRubric}
                            id="rubric-type"
                            placeholder="Select a rubric"
                        >
                            {rubricNames.map((rubric) => (
                                <option value={rubric}>{rubric}</option>
                            ))}
                        </select>
                    </label>
                    <label className="add-survey--label-pairing" for="subject-line">
                        <div className="drop-down-wrapper">
                            Pairing Modes
                            <select className="pairing"
                                value={valuePairing}
                                onChange={handleChangePairing}
                                id="pairing-mode"
                            >
                                {pairingModesNames.map((pairing) => (
                                    <option className= "pairing-option" value={pairing}>{pairing}</option>
                                ))}
                            </select>
                        </div>
                        <div className="pairing-mode-img-wrapper">
                            <img className="pairing-mode-img" src={pairingImage} alt="team pairing mode" />
                        </div>
                    </label>
                    {validPairingModeForMultiplier && (
                        <label className="add-survey--label" for="subject-line">
                            Multiplier
                            <select className="multiplier"
                                    id="multiplier-type"
                                    value={multiplierNumber}
                                    onChange={handleChangeMultiplierNumber}>
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                                <option value="4">4</option>
                            </select>
                        </label>
                    )}
                    <label className="add-survey--file-label" for="subject-line">
                        CSV File Upload
                        <input
                            className={emptyCSVFileError && "add-survey-input-error"}
                            id="csv-file"
                            type="file"
                            placeholder="Upload The File"
                        />
                        {emptyCSVFileError ? (
                            <label className="add-survey--error-label">
                                <div className="add-survey--red-warning-sign"/>
                                Select a file</label>
                        ) : null}
                    </label>
                    <div className="add-survey--confirm-btn-container">
                        <button className="add-survey--confirm-btn" onClick={verifySurvey}>
                            Verify Survey
                        </button>
                    </div>
                </div>
            </Modal>*/}

            <div className="courseContent">
                <div className="courseHeader">
                    <h2>
                        {course.code}: {course.name}
                    </h2>
                    {page === "home" ? (
                        <div className="courseHeader-btns">
                            <button className="btn add-btn" onClick={openAddSurveyModal}>
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
                            <th>Survey Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        {surveys.map((survey) => (
                            <tr className="survey-row" key={survey.id}>
                                <td>{survey.name}</td>
                                <td>
                                    Begins: {survey.start_date}
                                    <br/>
                                    Ends: {survey.end_date}
                                </td>
                                <td>{survey.completion}</td>
                                <td>
                                    {page === "home" ? (
                                        <select
                                            className="surveyactions--select"
                                            style={{
                                                backgroundColor: "#EF6C22",
                                                color: "white",
                                                fontSize: "18px",
                                                fontWeight: "bold",
                                                textAlign: "center",
                                            }}
                                            onChange={(e) => handleActionButtonChange(e, survey)}
                                            value={actionsButtonValue}
                                            defaultValue=""
                                        >
                                            <option
                                                className="surveyactions--option"
                                                value=""
                                                disabled
                                            >
                                                Actions
                                            </option>
                                            <option
                                                className="surveyactions--option"
                                                value="Preview Survey"
                                            >
                                                Preview Survey
                                            </option>
                                            <option
                                                className="surveyactions--option"
                                                value="View Results"
                                            >
                                                View Results
                                            </option>
                                            <option
                                                className="surveyactions--option"
                                                value="Duplicate"
                                            >
                                                Duplicate
                                            </option>
                                            <option
                                                className="surveyactions--option"
                                                value="Extend"
                                            >
                                                Extend
                                            </option>
                                            <option
                                                className="surveyactions--option"
                                                value="Delete"
                                            >
                                                Delete
                                            </option>
                                        </select>
                                    ) : page === "history" ? (
                                        <button
                                            className="viewresult-button"
                                            onClick={() => handleViewResultsModalChange(survey)}
                                        >
                                            View Results
                                        </button>
                                    ) : null}
                                    {/* Add more options as needed */}
                                </td>
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
            {/* Error Modal for updating roster */}
            {showUpdateModal && (
                <div className="update-modal">
                    <div className="update-modal-content">
                        <div className="CancelContainer">
                            <button
                                className="CancelButton"
                                style={{top: "0px"}}
                                onClick={handleUpdateModalChange}
                            >
                                ×
                            </button>
                        </div>
                        <h2 className="update-modal--heading">
                            Update Roster for {course.code} {course.name}
                        </h2>
                        <form onSubmit={handleUpdateRosterSubmit}>
                            {/* File input */}
                            <div className="form__item file-input-wrapper">
                                <label className="form__item--label form__item--file">
                                    Roster (CSV File) - Requires Emails in Columns 1, First Names
                                    in Columns 2 and Last Names in Columns 3
                                    <input
                                        type="file"
                                        id="updateroster-file-input"
                                        className={`updateroster-file-input`}
                                        onChange={(e) => setRosterFile(e.target.files[0])}
                                        required
                                    />
                                </label>
                            </div>
                            {/* Radio Buttons */}
                            <div className="update-form__item">
                                <div className="update-radio-options">
                                    <div className="update-radio-button--item">
                                        <RadioButton
                                            inputId="replace"
                                            name="replace"
                                            value="replace"
                                            onChange={(e) => setUpdateRosterOption(e.value)}
                                            checked={updateRosterOption === "replace"}
                                        />
                                        <label htmlFor="replace" className="update-radio--label">
                                            Replace
                                        </label>
                                    </div>

                                    <div className="update-radio-button--item">
                                        <RadioButton
                                            inputId="expand"
                                            name="expand"
                                            value="expand"
                                            onChange={(e) => setUpdateRosterOption(e.value)}
                                            checked={updateRosterOption === "expand"}
                                        />
                                        <label htmlFor="expand" className="update-radio--label">
                                            Expand
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div className="form__submit--container">
                                <button type="submit" className="update-form__submit">
                                    Update
                                </button>
                            </div>
                        </form>
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
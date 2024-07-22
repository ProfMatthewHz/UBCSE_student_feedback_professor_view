import React, {useEffect, useState} from "react";
import "../styles/course.css";
import "../styles/modal.css";
import "../styles/duplicatesurvey.css";
import "../styles/addsurvey.css";
import Modal from "./Modal";
import Toast from "./Toast";
import ViewResults from "./ViewResults";
import {RadioButton} from "primereact/radiobutton";
import Team from "../assets/pairingmodes/TEAM.png"
import TeamSelf from "../assets/pairingmodes/TEAM+SELF.png"
import TeamSelfManager from "../assets/pairingmodes/TEAM+SELF+MANAGER.png"
import PM from "../assets/pairingmodes/PM.png"
import SinglePairs from "../assets/pairingmodes/SinglePairs.png"
import { useNavigate } from "react-router-dom";
import SurveyExtendModal from "./SurveyExtendModal";
import SurveyDeleteModal from "./SurveyDeleteModal";


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
    const [modalIsOpenSurveyConfirm, setModalIsOpenSurveyConfirm] =
        useState(false);
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
    const [pairingModesNames, setPairingModesNames] = useState([]);
    const [RosterArray, setRosterArray] = useState([]);
    const [NonRosterArray, setNonRosterArray] = useState([]);

    //START:Error codes for modal frontend
    const [emptySurveyNameError, setEmptyNameError] = useState(false);
    const [emptyStartTimeError, setEmptyStartTimeError] = useState(false);
    const [emptyEndTimeError, setEmptyEndTimeError] = useState(false);
    const [emptyStartDateError, setEmptyStartDateError] = useState(false);
    const [emptyEndDateError, setEmptyEndDateError] = useState(false);
    const [emptyCSVFileError, setEmptyCSVFileError] = useState(false);
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
    const [surveyNameConfirm, setSurveyNameConfirm] = useState();
    const [rubricNameConfirm, setRubricNameConfirm] = useState();
    const [startDateConfirm, setStartDateConfirm] = useState();
    const [endDateConfirm, setEndDateConfirm] = useState();

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
     * Perform a GET call to getSurveyTypes.php to fetch pairing modes for each surver, stored in pairingModesFull and pairingModesNames 
     */
    const fetchPairingModes = () => {
        fetch(process.env.REACT_APP_API_URL + "getSurveyTypes.php", {
            method: "GET",
            credentials: "include"
        })
        .then((res) => res.json())
        .then((result) => {
            let allPairingModeArray = result.survey_types.mult.concat(
                result.survey_types.no_mult
            );

            let pairingModeNames = allPairingModeArray.map(
                (element) => element.description
            );
            let pairingModeFull_ = result.survey_types;
            console.log(pairingModeFull_);
            setPairingModesFull(pairingModeFull_);
            setPairingModesNames(pairingModeNames);
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
        setEmptyNameError(false);
        setEmptyStartTimeError(false);
        setEmptyEndTimeError(false);
        setEmptyStartDateError(false);
        setEmptyEndDateError(false);
        setEmptyCSVFileError(false);
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
    };

    const closeModalError = () => {
        setModalIsOpenError(false);
    };
    const closeModalSurveyConfirm = () => {
        setModalIsOpenSurveyConfirm(false);
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
        const value = "TEAM";
        return value;
    };
    const getInitialMultiplier = () => {
        const value = "1";
        return value;
    };

    const [valueRubric, setValueRubric] = useState(getInitialStateRubric);
    const [valuePairing, setValuePairing] = useState(getInitialStatePairing);
    const [multiplierNumber, setMultiplierNumber] = useState(getInitialMultiplier);
    const [validPairingModeForMultiplier, setMultiplier] = useState(true);
    const [pairingImage, setPairingImage] = useState(Team);

    const handleChangeRubric = (e) => {
        setValueRubric(e.target.value);
    };
    const handleChangeMultiplierNumber = (e) => {
        setMultiplierNumber(e.target.value);
    };

    const handleUpdateImage = (e) => {
        switch(e.target.value) {
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
                console.log('Unexpected pairing mode: ' + e.target.value);
                break;
        }
    }
    const handleChangePairing = (e) => {
        let showMult = false;

        let multiplierCheckArray = pairingModesFull.mult.map(
            (element) => element.description
        );
        if (multiplierCheckArray.includes(e.target.value)) {
            showMult = true;
        }
        
        handleUpdateImage(e);
        setValuePairing(e.target.value);
        setMultiplier(showMult);
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

    async function fetchAddSurveyToDatabaseComplete(data) {
        console.log(data);
        let fetchHTTP = process.env.REACT_APP_API_URL + "confirmationForSurvey.php";
        const result = await fetch(fetchHTTP, {
            method: "POST",
            credentials: "include",
            body: data,
        })
        .then((res) => res.json());
        console.log(result);
        return result; // Return the result directly
    }

    function confirmSurveyComplete() {
        let formData2 = new FormData();
        formData2.append("save-survey", "1");
        fetchAddSurveyToDatabaseComplete(formData2);
        updateAllSurveys();
        closeModalSurveyConfirm();
    }

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

    function verifyDuplicateSurvey() {
        setEmptyNameError(false);
        setEmptyStartTimeError(false);
        setEmptyEndTimeError(false);
        setEmptyStartDateError(false);
        setEmptyEndDateError(false);
        setEmptyCSVFileError(false);
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

    async function verifySurvey() {
        setEmptyNameError(false);
        setEmptyStartTimeError(false);
        setEmptyEndTimeError(false);
        setEmptyStartDateError(false);
        setEmptyEndDateError(false);
        setEmptyCSVFileError(false);
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

        var surveyName = document.getElementById("survey-name").value;
        var startTime = document.getElementById("start-time").value;
        var endTime = document.getElementById("end-time").value;
        var startDate = document.getElementById("start-date").value;
        var endDate = document.getElementById("end-date").value;
        var csvFile = document.getElementById("csv-file").value;
        var rubric = document.getElementById("rubric-type").value;

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
        if (csvFile === "") {
            setEmptyCSVFileError(true);
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
        let formData = new FormData();
        let rubricId;
        let pairingId;
        let multiplier;

        for (const element of rubricIDandDescriptions) {
            if (element.description === rubric) {
                rubricId = element.id;
            }
        }

        for (const element in pairingModesFull.no_mult) {
            if (pairingModesFull.no_mult[element].description === document.getElementById("pairing-mode").value) {
                pairingId = pairingModesFull.no_mult[element].id;
                multiplier = 1;
            }
        }

        for (const element in pairingModesFull.mult) {
            if (pairingModesFull.mult[element].description === document.getElementById("pairing-mode").value) {
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

        let errorsObject = awaitedResponse.errors;
        let dataObject = awaitedResponse.data;
        console.log(errorsObject.length);
        if (errorsObject.length === 0) {
            //succesful survey.
            let rosterDataAll = await fetchRosterNonRoster();
            let rosterData = rosterDataAll.data;
            if (rosterData) {
                let rostersArrayHere = rosterData["roster-students"];
                let nonRosterArrayHere = rosterData["non-roster-students"];
                setRosterArray(rostersArrayHere);
                setNonRosterArray(nonRosterArrayHere);
                setSurveyNameConfirm(surveyName);
                setRubricNameConfirm(rubric);
                setStartDateConfirm(startDate + " at " + startTime);
                setEndDateConfirm(endDate + " at " + endTime);
                closeAddSurveyModal();
                setModalIsOpenSurveyConfirm(true);
                return;
            }
            return;
        }
        if (dataObject.length === 0) {
            let errorKeys = Object.keys(errorsObject);
            let pairingFileStrings = [];
            let anyOtherStrings = [];
            let i = 0;
            while (i < errorKeys.length) {
                if (errorKeys[i] === "pairing-file") {
                    pairingFileStrings = errorsObject["pairing-file"].split("<br>");
                } else {
                    let error = errorKeys[i];
                    anyOtherStrings.push(errorsObject[error]);
                }
                i++;
            }
            const allErrorStrings = pairingFileStrings.concat(anyOtherStrings);
            setErrorsList(allErrorStrings);
            closeAddSurveyModal();
            setModalIsOpenError(true);
            return;
        }
        return;
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
            console.log(survey.name);
            console.log(survey.rubric_id);
            console.log(course.code);
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
        setEmptyCSVFileError(false);
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

    function extendModalClose(errorList) {
        setExtendModal(false);
        if (errorList && errorList.length > 0) {
          setErrorsList(errorList);
          setModalIsOpenError(true);
        } else {
            updateAllSurveys();
        }
    }

    function deleteModalClose() {
        updateAllSurveys();
        setDeleteModal(false);
    }

    const handleUpdateModalChange = () => {
        setShowUpdateModal((prev) => !prev);
    };

    const handleViewResultsModalChange = (survey) => {
        setViewResultsModal((prev) => !prev);
        setViewingCurrentSurvey(survey);
    };

    return (
        <div id={course.code} className="courseContainer">
            {extendModal &&
            (<SurveyExtendModal
                modalClose={extendModalClose}
                survey_data={currentSurvey} />
            )}
            {deleteModal &&
            (<SurveyDeleteModal
                modalClose={deleteModalClose}
                survey_data={currentSurvey} />
            )}
            <Modal
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
            </Modal>

            <Modal
                open={modalIsOpenSurveyConfirm}
                onRequestClose={closeModalSurveyConfirm}
                width={"1200px"}
                maxWidth={"90%"}
            >
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
                        Confirmation
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
                        Survey Name: {surveyNameConfirm}
                    </div>
                    <div
                        style={{color: "#225cb5", fontSize: "24px", fontWeight: "bolder"}}
                    >
                        Survey Active: {startDateConfirm} to {endDateConfirm}
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
                        Rubric Used: {rubricNameConfirm}
                    </div>
                    <div
                        style={{color: "#225cb5", fontSize: "24px", fontWeight: "bolder"}}
                    >
                        For Course: {course.code}
                    </div>
                </div>

                <div class="table-containerConfirm">
                    {RosterArray.length > 0 ? (
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
                            {RosterArray.map((entry, index) => (
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
                        <div className="empty-view">No students on course roster</div>
                    )}

                    {NonRosterArray.length > 0 ? (
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
                            {NonRosterArray.map((entry, index) => (
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
                        <div className="empty-view">No data to show</div>
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
                        onClick={closeModalSurveyConfirm}
                    >
                        Cancel
                    </button>
                    <button
                        className="CompleteSurvey1"
                        style={{
                            borderRadius: "5px",
                            fontSize: "18px",
                            fontWeight: "700",
                            padding: "5px 12px",
                        }}
                        onClick={confirmSurveyComplete}
                    >
                        Confirm Survey
                    </button>
                </div>
            </Modal>
            <Modal
                open={errorModalIsOpen}
                onRequestClose={closeModalError}
                width={"800px"}
            >
                <div
                    style={{
                        display: "flex",
                        flexDirection: "column",
                        borderBottom: "thin solid #225cb5",
                    }}
                >
                    <div
                        style={{
                            display: "flex",
                            width: "100%",
                            marginTop: "2px",
                            paddingBottom: "2px",
                            justifyContent: "center",
                            borderBottom: "thin solid #225cb5",
                        }}
                    >
                        <h2 style={{color: "#225cb5"}}>Survey Errors</h2>
                    </div>
                    <div
                        style={{
                            display: "flex",
                            flexDirection: "column",
                            maxHeight: "400px", // Set a maximum height for the container
                            overflowY: "auto", // Enable vertical scrolling
                            overflowX: "hidden", // Disable horizontal scrolling
                        }}
                    >
                        {errorsList.map((string, index) => (
                            <div key={index} className="string-list-item">
                                {string}
                            </div>
                        ))}
                    </div>
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
            </Modal>
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
            {/* View Results Modal*/}
            {showViewResultsModal && (
                <ViewResults
                    closeViewResultsModal={handleViewResultsModalChange}
                    surveyToView={viewingCurrentSurvey}
                    course={course}
                />
            )}
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
            {/* Error Modal */}
            {showErrorModal && (
                <div className="modal">
                    <div className="modal-content">
                        <h2>Error(s)</h2>
                        {
                            updateRosterError.length > 0 && updateRosterError.map((err) => (
                                <p>{err}</p>
                            ))
                        }
                        <button className="roster-file--error-btn" onClick={handleErrorModalClose}>OK</button>
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
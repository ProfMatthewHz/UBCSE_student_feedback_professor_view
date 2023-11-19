import React, { useEffect, useState } from "react";
import "../styles/course.css";
import "../styles/modal.css";
import Modal from "./Modal";
import Toast from "./Toast";
import ViewResults from "./ViewResults";


const Course = ({ course, page }) => {
  const [surveys, setSurveys] = useState([]);

  function updateAllSurveys() {
    fetch(
      process.env.REACT_APP_API_URL + "courseSurveysQueries.php",
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
        console.log(result);
        const activeSurveys = result.active.map(survey_info => ({...survey_info, expired: false}));
        console.log(result);
        const expiredSurveys = result.expired.map(survey_info => ({...survey_info, expired: true}));
        const upcomingSurveys = result.upcoming.map(survey_info => ({...survey_info, expired: false}));
        console.log(result);

        setSurveys([...activeSurveys, ...expiredSurveys, ...upcomingSurveys]);
      })
      .catch((err) => {
        console.log(err);
      });
  };

  // MODAL CODE
  
  const [actionsButtonValue, setActionsButtonValue] = useState("")
  const[currentSurveyEndDate, setCurrentSurveyEndDate] = useState("")

  const [extendModal, setExtendModal] = useState(false);
  const [duplicateModal,setDuplicateModel] = useState(false);
  const [emptyOrWrongDeleteNameError,setemptyOrWrongDeleteNameError]= useState(false);
  const [deleteModal,setDeleteModal] = useState(false);
  const [modalIsOpen, setModalIsOpen] = useState(false);
  const [modalIsOpenError, setModalIsOpenError] = useState(false);
  const [errorsList, setErrorsList] = useState([]);
  const [modalIsOpenSurveyConfirm, setModalIsOpenSurveyConfirm] = useState(false);
  const [showUpdateModal, setShowUpdateModal] = useState(false);
  const [currentSurvey, setCurrentSurvey] = useState('');

  const [showViewResultsModal, setViewResultsModal] = useState(false);
  const [viewingCurrentSurvey, setViewingCurrentSurvey] = useState(null)

  const [rosterFile, setRosterFile] = useState(null);

  const [updateRosterOption, setUpdateRosterOption] = useState("replace");
  const [updateRosterError, setUpdateRosterError] = useState("");

  const [showErrorModal, setShowErrorModal] = useState(false);
  const [showToast, setShowToast] = useState(false);
  const [rubricNames, setNames] = useState([]);
  const [rubricIDandDescriptions, setIDandDescriptions] = useState([]);
  const [pairingModesFull, setPairingModesFull] = useState([]);
  const [pairingModesNames, setPairingModesNames] = useState([]);
  const [RosterArray, setRosterArray] = useState([]);
  const [NonRosterArray, setNonRosterArray] = useState([]);

  //START:Error codes for modal frontend

  const [emptySurveyNameError,setEmptyNameError] = useState(false);
  const [emptyStartTimeError,setEmptyStartTimeError] = useState(false);
  const [emptyEndTimeError,setEmptyEndTimeError] = useState(false);
  const [emptyStartDateError,setEmptyStartDateError] = useState(false);
  const [emptyEndDateError,setEmptyEndDateError] = useState(false);
  const [emptyCSVFileError,setEmptyCSVFileError] = useState(false);
  const [startDateBoundError,setStartDateBoundError] = useState(false);
  const [startDateBound1Error,setStartDateBound1Error] = useState(false);
  const [endDateBoundError,setEndDateBoundError] = useState(false);
  const [endDateBound1Error,setEndDateBound1Error] = useState(false);
  const [StartAfterCurrentError,setStartAfterCurrentError] = useState(false);
  const [StartDateGreaterError,setStartDateGreaterError] = useState(false);
  const [StartTimeSameDayError,setStartTimeSameDayError] = useState(false);
  const [StartHourSameDayError,setStartHourSameDayError] = useState(false);
  const [StartHourAfterEndHourError,setStartHourAfterEndHourError] = useState(false);
  const [StartTimeHoursBeforeCurrent,setStartTimeHoursBeforeCurrent] = useState(false);
  const [StartTimeMinutesBeforeCurrent,setStartTimeMinutesBeforeCurrent] = useState(false);
  //END:Error codes for modal frontend
  const [surveyNameConfirm,setSurveyNameConfirm] = useState();
  const [rubricNameConfirm,setRubricNameConfirm] = useState();
  const [startDateConfirm,setStartDateConfirm] = useState();
  const [endDateConfirm,setEndDateConfirm] = useState();

  const updateRosterformData = new FormData();

  

  const fetchRubrics = () => {
    fetch(process.env.REACT_APP_API_URL + "rubricsGet.php", {
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
        setIDandDescriptions(rubricIDandDescriptions)
      })
      .catch((err) => {
        console.log(err);
      });
  };
  const fetchPairingModes = () => {
    fetch(
      process.env.REACT_APP_API_URL + "surveyTypesGet.php",
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

  async function fetchRosterNonRoster() {
    let fetchHTTP = process.env.REACT_APP_API_URL + "confirmationForSurvey.php";
    console.log(fetchHTTP);
    //let response = await fetch(fetchHTTP,{method: "POST", body: formData});
    try {
      const response = await fetch(fetchHTTP, {
        method: "GET"
      });
      const result = await response.json();
      

      return result; // Return the result directly
    } catch (err) {
      console.log('goes to error');
      console.error(err);
      throw err; // Re-throw to be handled by the caller
    }
    
  };

  
  async function fetchAddSurveyToDatabaseComplete(data){
    console.log(data);
    let fetchHTTP =
    process.env.REACT_APP_API_URL + "confirmationForSurvey.php"
  //let response = await fetch(fetchHTTP,{method: "POST", body: formData});
  try {
    const response = await fetch(fetchHTTP, {
      method: "POST",
      body: data
    });
    const result = await response.json();
    console.log(result);
    return result; // Return the result directly
  } catch (err) {

    throw err; // Re-throw to be handled by the caller
  }

  }

  async function confirmSurveyComplete() {
    let formData2 = new FormData();
    formData2.append("save-survey", '1');
    let any = await fetchAddSurveyToDatabaseComplete(formData2);
    updateAllSurveys();
    closeModalSurveyConfirm();
    return;
  }

  async function getAddSurveyResponse(formData) {
    console.log("this is before the addsurveyResponse function fetch call");

    let fetchHTTP =
      process.env.REACT_APP_API_URL + "addSurveyToCourse.php?course=" +
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
  async function duplicateSurveyBackend(formdata){
    let fetchHTTP =
      process.env.REACT_APP_API_URL + "duplicateExistingSurvey.php?survey=" +
      currentSurvey.id;
    //let response = await fetch(fetchHTTP,{method: "POST", body: formData});
    try {
      const response = await fetch(fetchHTTP, {
        method: "POST",
        body: formdata,
      });
      const result = await response.text();
      console.log(currentSurvey)
      console.log(result);

      return result; // Return the result directly
    } catch (err) {
      console.error(err);
      throw err; // Re-throw to be handled by the caller
    }
  }

  async function verifyDuplicateSurvey(){
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
    var rubric = document.getElementById("rubric-type").value;

    var dictNameToInputValue = {
      "Survey name": surveyName,
      "Start time": startTime,
      "End time": endTime,
      "Start date": startDate,
      "End date": endDate,
    };
    
    for (let k in dictNameToInputValue) {
      if (dictNameToInputValue[k] === "") {
        if(k === "Survey name"){
          setEmptyNameError(true);
          return;
        }
        if(k === "Start time"){
          setEmptyStartTimeError(true);
          return;
        }
        if(k === "End time"){
          setEmptyEndTimeError(true);
          return;
        }
        if(k === "Start date"){
          setEmptyStartDateError(true);
          return;
        }
        if(k === "End date"){
          setEmptyEndDateError(true);
          return;
        }
      }
    }

    //date and time keyboard typing bound checks.

    let minDateObject = new Date("2023-08-31T00:00:00"); //first day of class
    let maxDateObject = new Date("2023-12-09T00:00:00"); //last day of class
    let startDateObject = new Date(startDate + "T00:00:00"); //inputted start date.
    let endDateObject = new Date(endDate + "T00:00:00"); //inputted end date.
    if (startDateObject < minDateObject) {
      setStartDateBoundError(true);
      return;
    }
    if (startDateObject > maxDateObject) {
      setStartDateBound1Error(true);
      return;
    }
    if (endDateObject < minDateObject) {
      setEndDateBoundError(true);
      return;
    }
    if (endDateObject > maxDateObject) {
      setStartDateBound1Error(true);
      return;
    }
    //END:date and time keyboard typing bound checks.

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
    let pairingId;
    let multiplier;

    for (const element of rubricIDandDescriptions) {
      if (element.rubricDesc === rubric) {
        rubricId = element.rubricId;
      }
    }

   

    formData3.append("survey-id",currentSurvey.id);
    formData3.append("survey-name", surveyName);
    formData3.append("rubric-id", rubricId);
    formData3.append("start-date", startDate);
    formData3.append("start-time", startTime);
    formData3.append("end-date", endDate);
    formData3.append("end-time", endTime);

    //form data is set. Call the post request
    let awaitedResponse = await duplicateSurveyBackend(formData3);
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
        if(k === "Survey name"){
          setEmptyNameError(true);
          return;
        }
        if(k === "Start time"){
          setEmptyStartTimeError(true);
          return;
        }
        if(k === "End time"){
          setEmptyEndTimeError(true);
          return;
        }
        if(k === "Start date"){
          setEmptyStartDateError(true);
          return;
        }
        if(k === "End date"){
          setEmptyEndDateError(true);
          return;
        }
        if(k === "Csv file"){
          setEmptyCSVFileError(true);
          return;
        }
      }
    }

    //date and time keyboard typing bound checks.

    let minDateObject = new Date("2023-08-31T00:00:00"); //first day of class
    let maxDateObject = new Date("2023-12-09T00:00:00"); //last day of class
    let startDateObject = new Date(startDate + "T00:00:00"); //inputted start date.
    let endDateObject = new Date(endDate + "T00:00:00"); //inputted end date.
    if (startDateObject < minDateObject) {
      setStartDateBoundError(true)
      return;
    }
    if (startDateObject > maxDateObject) {
      setStartDateBound1Error(true); 
      return;
    }
    if (endDateObject < minDateObject) {
      setEndDateBoundError(true)
      return;
    }
    if (endDateObject > maxDateObject) {
      setStartDateBound1Error(true)
      return;
    }
    //END:date and time keyboard typing bound checks.

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
        console.log(pairingId);
        multiplier = 1;
      }
    }
    for (const element in pairingModesFull.mult) {
      if (
        pairingModesFull.mult[element].description ===
        document.getElementById("pairing-mode").value
      ) {
        pairingId = pairingModesFull.mult[element].id;
        console.log(pairingId);
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

    //let errorsObject = errorOrSuccessResponse.errors;
    let errorsObject = awaitedResponse.errors;
    let dataObject = awaitedResponse.data;
  
    if (errorsObject.length === 0) {
      //succesful survey. 
      let rosterDataAll = await fetchRosterNonRoster();
      let rosterData= rosterDataAll.data;
      if(rosterData){
        let rostersArrayHere=rosterData['roster-students'];
        let nonRosterArrayHere = rosterData['non-roster-students'];
        setRosterArray(rostersArrayHere);
        setNonRosterArray(nonRosterArrayHere);
        setSurveyNameConfirm(surveyName);
        setRubricNameConfirm(rubric);
        setStartDateConfirm(startDate + " at " + startTime);
        setEndDateConfirm(endDate + " at " + endTime);
        closeModal();
        setModalIsOpenSurveyConfirm(true);
        return;
      }
      return;
    }
    if (dataObject.length === 0) {
      let errorKeys =   Object.keys(errorsObject)
      let pairingFileStrings = [];
      let anyOtherStrings =[];
      let i = 0;
      while(i<errorKeys.length){
        if(errorKeys[i]==="pairing-file"){
          pairingFileStrings = errorsObject["pairing-file"].split("<br>");
        }
        else{
          let error = errorKeys[i];
          anyOtherStrings.push(errorsObject[error]);
        }
        i++;
      }
      const allErrorStrings= pairingFileStrings.concat(anyOtherStrings);

      setErrorsList(allErrorStrings);
      closeModal();
      setModalIsOpenError(true);

      return;
    }

    return;
  }


  
  const handleActionButtonChange = (e,survey) => {
    setActionsButtonValue(e.target.value)

    if(e.target.value === 'Duplicate'){
      fetchRubrics();
      setCurrentSurvey(survey);
      setDuplicateModel(true);
    };
    if(e.target.value === 'Delete'){
      setCurrentSurvey(survey);
      setDeleteModal(true);
    };
    if(e.target.value === 'Extend'){
      setCurrentSurvey(survey);
      setExtendModal(true);
    }
    if(e.target.value == 'View Results'){
      handleViewResultsModalChange(survey);
    }
    setActionsButtonValue("")
  }

  const handleUpdateRosterSubmit = (e) => {
    e.preventDefault();

    updateRosterformData.append("roster-file", rosterFile);
    updateRosterformData.append("course-id", course.id);
    updateRosterformData.append("update-type", updateRosterOption);

    fetch(
      process.env.REACT_APP_API_URL + "rosterUpdate.php",
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
      process.env.REACT_APP_API_URL + "courseSurveysQueries.php",
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
        const upcomingSurveys = result.upcoming.map(survey_info => ({...survey_info, expired: false}));

        setSurveys([...activeSurveys, ...expiredSurveys, ...upcomingSurveys]);
      })
      .catch((err) => {
        console.log(err);
      });
  }, []);

function closeModalDuplicate(){
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

async function verifyDeleteBackendGet(id){
  let fetchHTTP =
    process.env.REACT_APP_API_URL + "deleteSurvey.php?survey="+id
  //let response = await fetch(fetchHTTP,{method: "POST", body: formData});
  try {
    const response = await fetch(fetchHTTP, {
      method: "GET",
    });
    const result = await response.json();
    console.log(result);
    return result; // Return the result directly
  } catch (err) {

    throw err; // Re-throw to be handled by the caller
  }

}


async function verifyDeleteBackend(formdata,id){
  let fetchHTTP =
    process.env.REACT_APP_API_URL + "deleteSurvey.php?survey="+id
  //let response = await fetch(fetchHTTP,{method: "POST", body: formData});
  try {
    const response = await fetch(fetchHTTP, {
      method: "POST",
      body: formdata
    });
    const result = await response.json();
    console.log(result);
    return result; // Return the result directly
  } catch (err) {

    throw err; // Re-throw to be handled by the caller
  }

}


async function extendSurveyBackendGet(id){
  let fetchHTTP =
  process.env.REACT_APP_API_URL + "extendSurvey.php?survey="+id
//let response = await fetch(fetchHTTP,{method: "POST", body: formData});
try {
  const response = await fetch(fetchHTTP, {
    method: "GET",
  });
  const result = await response.text();
  console.log(result);
  return result; // Return the result directly
} catch (err) {

  throw err; // Re-throw to be handled by the caller
}

}
async function extendSurveyBackendPost(id, formdata){
  let fetchHTTP =
  process.env.REACT_APP_API_URL + "extendSurvey.php?survey="+id
//let response = await fetch(fetchHTTP,{method: "POST", body: formData});
try {
  const response = await fetch(fetchHTTP, {
    method: "POST",
    body: formdata
  });
  const result = await response.json();
  console.log(result);
  return result; // Return the result directly
} catch (err) {

  throw err; // Re-throw to be handled by the caller
}

}

async function verifyExtendModal(){
  let newEndDate = document.getElementById("new-endDate").value;
  let newEndTime = document.getElementById("new-endTime").value;
  let surveyId = currentSurvey.id;
  let formData5 = new FormData();
  formData5.append('survey-id',surveyId);
  formData5.append('end-date',newEndDate);
  formData5.append('end-time', newEndTime);
  formData5.append('rubric-id', currentSurvey.rubric_id)
  formData5.append('start-date', currentSurvey.sort_start_date.split(' ')[0]);
  let currentTime = currentSurvey.sort_start_date.split(' ')[1]
  console.log(currentSurvey);
  currentTime = currentTime.split(':');
  currentTime = currentTime[0] + ':' + currentTime[1];

  formData5.append('start-time', currentTime)
  let pre = await extendSurveyBackendGet(surveyId);
  let post = await extendSurveyBackendPost(surveyId,formData5);
  if(post.errors['end-date'] || post.errors['end-time'] || post.errors['start-date'] || post.errors['start-time']){
    //there are errors 
    let errorList = [];
    if(post.errors['end-date']){
      errorList.push(post.errors['end-date']);
    }
    if(post.errors['start-date']){
      errorList.push(post.errors['start-date']);
    }
    if(post.errors['end-time']){
      errorList.push(post.errors['end-time']);
    }
    if(post.errors['start-time']){
      errorList.push(post.errors['start-time']);
    }
    extendModalClose();
    setErrorsList(errorList)
    setModalIsOpenError(true);
    return;
  }

  updateAllSurveys();
  extendModalClose();

}

async function verifyDelete(){
  setemptyOrWrongDeleteNameError(false);
  let inputtedText = document.getElementById('delete-name').value;
  if(inputtedText!==currentSurvey.name){
    setemptyOrWrongDeleteNameError(true);
  }
  else{
    let form = new FormData();
    form.append("agreement",1);
    let surveyId = currentSurvey.id;
    let pre = await verifyDeleteBackendGet(surveyId);
    let final = await verifyDeleteBackend(form,surveyId);
    updateAllSurveys();
    deleteModalClose();

  }
}
function extendModalClose(){
  setExtendModal(false);
}
function deleteModalClose() {
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
      <Modal open={extendModal} onRequestClose={extendModalClose}
      width = {'500px'}>
        <div className="CancelContainer">
          <button className="CancelButton" onClick={extendModalClose}>×</button>
        </div>
        <div style={{
            display: "flex",
            flexDirection: "row",
            flexWrap: "wrap",
            borderBottom: "thin solid #225cb5",
          }}>
         <div
            style={{
              display: "flex",
              flexDirection: "column",
              width: "500px",
              marginTop: "2px",
              marginBottom: '12px',
              paddingBottom: '2px',
              justifyContent: "center",
              gap: "12px",
              borderBottom: "thin solid #225cb5",
            }}
          >
            <h2 className="modal-title" style={{ color: "#225cb5" }}>
              Extend Chosen Survey: {currentSurvey.name}
            </h2>
            <h2 className="modal-title" style={{ color: "#225cb5" }}>
              From Old Date: {currentSurvey.end_date}
            </h2>
            </div>
            <div class="input-wrapper1" style = {{width: '100%', marginBottom:'0%', marginTop: '3px'}}>
            <label style={{ color: "#225cb5"}} for="subject-line">
              New End Date
            </label>
            <input
              id="new-endDate"
              class="styled-input2"
              type="date"
              min="2023-08-31"
              max="2023-12-09"
              width = '50px'
              placeholder="New End Date"
            ></input>
          </div>
          <div class="input-wrapper1" style = {{width: '100%', marginBottom:'0%', marginTop: '3px'}}>
            <label style={{ color: "#225cb5"}} for="subject-line">
              New End Time
            </label>
            <input
              id="new-endTime"
              class="styled-input2"
              type="time"
              width = '50px'
              placeholder="New End Time"
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
                className="CompleteSurveyExtend"
                style={{
                  borderRadius: "5px",
                  fontSize: "18px",
                  fontWeight: "700",
                  padding: "5px 12px",
                }}
                onClick={verifyExtendModal}
              >
                Extend Survey
              </button>
        </div>


      </Modal>
      <Modal open={deleteModal} onRequestClose={deleteModalClose}
      width = {'750px'}>
        <div className="CancelContainer">
          <button className="CancelButton" onClick={deleteModalClose}>×</button>
        </div>
        <div style={{
            display: "flex",
            flexDirection: "row",
            flexWrap: "wrap",
            borderBottom: "thin solid #225cb5",
          }}>
         <div
            style={{
              display: "flex",
              width: "750px",
              marginTop: "2px",
              marginBottom: '12px',
              paddingBottom: '2px',
              justifyContent: "center",
              gap: "4px",
              borderBottom: "thin solid #225cb5",
            }}
          >
            <h2 className="modal-title" style={{ color: "#225cb5" }}>
              Delete Chosen Survey: {currentSurvey.name}
            </h2>
            </div>
            <div class="input-wrapper1" style = {{width: '100%', marginBottom:'0%', marginTop: '3px'}}>
            {emptyOrWrongDeleteNameError? <label style= {{color:'red'}}>Must match the text</label> : null}
            <label style={{ color: "#225cb5"}} for="subject-line">
              Retype Survey Name Before Clicking Delete
            </label>
            <input
              id="delete-name"
              class="styled-input1"
              style = {{width: '100%'}}
              type="text"
              placeholder="New Name"
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
                className="CompleteSurveyDelete"
                style={{
                  borderRadius: "5px",
                  fontSize: "18px",
                  fontWeight: "700",
                  padding: "5px 12px",
                }}
                onClick={verifyDelete}
              >
                Delete Survey
              </button>
        </div>
            </Modal>
      <Modal open={duplicateModal} onRequestClose={closeModalDuplicate}
      width = {'1250px'}>
        <div className="CancelContainer">
          <button className="CancelButton" onClick={closeModalDuplicate}>×</button>
        </div>
         <div
            style={{
              display: "flex",
              marginTop: "2px",
              paddingBottom: "2px",
              justifyContent: "center",
              gap: "4px",
              borderBottom: "thin solid #225cb5",

              '@media (max-width: 550px)': {
                maxWidth: "90%",
              }
            }}
          >
            <h2 className="modal-title" style={{ color: "#225cb5" }}>
              Duplicate Chosen Survey: {currentSurvey.name}
            </h2>
            </div>
          <div style={{
            display: "flex",
            flexDirection: "row",
            flexWrap: "wrap",
            borderBottom: "thin solid #225cb5",
          }}>

          <div class="input-wrapper1">
            {emptySurveyNameError? <label style= {{color:'red'}}>Survey Name cannot be empty</label> : null}
              <label style={{ color: "#225cb5" }} for="subject-line">
                New Survey Name
              </label>
              <input
                id="survey-name"
                class="styled-input1"
                type="text"
                placeholder="New Name"
              ></input>
          </div>  
          <div class="input-wrapper">
            <label style={{ color: "#225cb5" }} for="subject-line">
              Choose Rubric
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
          {StartDateGreaterError? <label style= {{color:'red'}}>Start date cannot be greater than the end date</label> : null}
          {StartAfterCurrentError? <label style= {{color:'red'}}>Survey start date cannot be before the current day</label> : null}
          {emptyStartDateError? <label style= {{color:'red'}}>Start Date cannot be empty</label> : null}
          {startDateBoundError?  <label style= {{color:'red'}}>Start Date is too early. Must start atleast at August 31 </label> : null}
          {startDateBound1Error? <label style= {{color:'red'}}>Start Date is too late. Must be at or before December 9</label> : null}
          {StartHourAfterEndHourError? <label style= {{color:'red'}}>If start and end days are the same, Start time cannot be after End time</label> : null}
          {StartHourSameDayError? <label style= {{color:'red'}}>If start and end days are the same, Start and End time hours must differ</label> : null}
          {StartTimeSameDayError? <label style= {{color:'red'}}>If start and end days are the same, Start and End times must differ</label> : null}
          {emptyStartTimeError? <label style= {{color:'red'}}>Start Time cannot be empty</label> : null}
          {StartTimeHoursBeforeCurrent? <label style= {{color:'red'}}>Start time hour cannot be before the current hour</label> : null}
          {StartTimeMinutesBeforeCurrent? <label style= {{color:'red'}}>Start time minutes cannot be before current minutes</label> : null}
            <label style={{ color: "#225cb5" }} for="subject-line">
              New Start Date & Time
            </label>
            <input
              id="start-date"
              class="styled-input1"
              type="date"
              min="2023-08-31"
              max="2023-12-09"
              placeholder="Enter New Start Date"
            ></input>
             <input
              id="start-time"
              class="styled-input1"
              type="time"
              placeholder="Enter New Start Time"
            ></input>
          </div>
          <div class="input-wrapper1">
          {emptyEndDateError? <label style= {{color:'red'}}>End Date cannot be empty</label> : null}
          {endDateBoundError? <label style= {{color:'red'}}>End Date is too early. Must start atleast at August 31</label> : null}
          {endDateBound1Error? <label style= {{color:'red'}}>End Date is too late. Must be at or before December 9</label> : null}
          {StartHourSameDayError? <label style= {{color:'red'}}>If start and end days are the same, Start and End time hours must differ</label> : null}
          {StartTimeSameDayError? <label style= {{color:'red'}}>If start and end days are the same, Start and End times must differ</label> : null}
          {emptyEndTimeError? <label style= {{color:'red'}}>End Time cannot be empty</label> : null}
            <label style={{ color: "#225cb5" }} for="subject-line">
              New End Date & Time
            </label>
            <input
              id="end-date"
              class="styled-input1"
              type="date"
              min="2023-08-31"
              max="2023-12-09"
              placeholder="Enter New End Date"
            ></input>
            <input
              id="end-time"
              class="styled-input1"
              type="time"
              placeholder="Enter New End Time"
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
                className="CompleteSurveyDuplicate"
                style={{
                  borderRadius: "5px",
                  fontSize: "18px",
                  fontWeight: "700",
                  padding: "5px 12px",
                }}
                onClick={verifyDuplicateSurvey}
              >
                Duplicate Survey
              </button>
        </div>
        
      </Modal>

      <Modal open={modalIsOpenSurveyConfirm} onRequestClose={closeModalSurveyConfirm}
      width = {'1200px'}>
      <div style={{
            display: "flex",
            flexDirection: "column",
            flexWrap: "wrap",
            borderBottom: "thin solid #225cb5",
          }}>
      <div style= {{color: "#225cb5",fontSize:'36px', fontWeight:'bolder'}}>Confirmation</div>
        <div style= {{color: "#225cb5", fontSize:'24px',fontWeight:'bolder', marginBottom: '5px',marginTop: '20px'}}>Survey Name: {surveyNameConfirm}</div>
        <div style = {{color: "#225cb5", fontSize:'24px',fontWeight:'bolder'}}>Survey Active: {startDateConfirm} to {endDateConfirm}</div>
        <div style= {{color: "#225cb5", fontSize:'24px',fontWeight:'bolder', marginBottom: '5px',marginTop: '20px'}}>Rubric Used: {rubricNameConfirm}</div>
        <div style= {{color: "#225cb5", fontSize:'24px',fontWeight:'bolder'}}>For Course: {course.code}</div>
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
                {entry.reviewing? <td>Yes</td>: <td>No</td>}
                {entry.reviewed? <td>Yes</td>: <td>No</td>}
              </tr>
            ))}
          </tbody>
        </table>
      ) : (
        <div className="empty-view">Course Roster has no Students</div>
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
                {entry.reviewing? <td>Yes</td>: <td>No</td>}
                {entry.reviewed? <td>Yes</td>: <td>No</td>}
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
      <Modal open={modalIsOpenError} onRequestClose={closeModalError}
      width = {'800px'}>
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
    <h2 style={{ color: "#225cb5" }}>Survey Errors</h2>
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
        open={modalIsOpen}
        onRequestClose={closeModal}
        width = {'1300px'}
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
        <div className="CancelContainer">
          <button className="CancelButton" onClick={closeModal}>×</button>
        </div>
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
          {emptySurveyNameError? <label style= {{color:'red'}}>Survey Name cannot be empty</label> : null}
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
          {StartDateGreaterError? <label style= {{color:'red'}}>Start date cannot be greater than the end date</label> : null}
          {StartAfterCurrentError? <label style= {{color:'red'}}>Survey start date cannot be before the current day</label> : null}
          {emptyStartDateError? <label style= {{color:'red'}}>Start Date cannot be empty</label> : null}
          {startDateBoundError?  <label style= {{color:'red'}}>Start Date is too early. Must start atleast at August 31 </label> : null}
          {startDateBound1Error? <label style= {{color:'red'}}>Start Date is too late. Must be at or before December 9</label> : null} 
          {StartHourAfterEndHourError? <label style= {{color:'red'}}>If start and end days are the same, Start time cannot be after End time</label> : null}
          {StartHourSameDayError? <label style= {{color:'red'}}>If start and end days are the same, Start and End time hours must differ</label> : null}
          {StartTimeSameDayError? <label style= {{color:'red'}}>If start and end days are the same, Start and End times must differ</label> : null}
          {emptyStartTimeError? <label style= {{color:'red'}}>Start Time cannot be empty</label> : null}
          {StartTimeHoursBeforeCurrent? <label style= {{color:'red'}}>Start time hour cannot be before the current hour</label> : null}
          {StartTimeMinutesBeforeCurrent? <label style= {{color:'red'}}>Start time minutes cannot be before current minutes</label> : null}
            <label style={{ color: "#225cb5" }} for="subject-line">
              Start Date & Time
            </label>
            <input
              id="start-date"
              class="styled-input1"
              type="date"
              min="2023-08-31"
              max="2023-12-09"
              placeholder="Enter Start Date"
            ></input>
            <input
              id="start-time"
              class="styled-input1"
              type="time"
              placeholder="Enter Start Time"
            ></input>
          </div>
          <div class="input-wrapper1">
          {emptyEndDateError? <label style= {{color:'red'}}>End Date cannot be empty</label> : null}
          {endDateBoundError? <label style= {{color:'red'}}>End Date is too early. Must start atleast at August 31</label> : null}
          {endDateBound1Error? <label style= {{color:'red'}}>End Date is too late. Must be at or before December 9</label> : null}
          {StartHourSameDayError? <label style= {{color:'red'}}>If start and end days are the same, Start and End time hours must differ</label> : null}
          {StartTimeSameDayError? <label style= {{color:'red'}}>If start and end days are the same, Start and End times must differ</label> : null}
          {emptyEndTimeError? <label style= {{color:'red'}}>End Time cannot be empty</label> : null}
            <label style={{ color: "#225cb5" }} for="subject-line">
              End Date & Time
            </label>
            <input
              id="end-date"
              class="styled-input1"
              type="date"
              min="2023-08-31"
              max="2023-12-09"
              placeholder="Enter End Date"
            ></input>
            <input
              id="end-time"
              class="styled-input1"
              type="time"
              placeholder="Enter End Time"
            ></input>
          </div>    
          <div class="input-wrapper">
            <div style={{ display: "flex", flexDirection: "row"}}>
            <label  style={{ color: "#225cb5"}}  for="subject-line">
              Pairing Modes
            </label>
            <div class = "pairingLabel" style={{ color: "#225cb5", width:'25px', fontSize:'14px', marginLeft: '450px',fontWeight:'bolder'}}>
              (i)
            </div>
            </div>
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
          {emptyCSVFileError? <label style= {{color:'red'}}>Select a file</label> : null}
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
              <div style={{ display: "flex", flexDirection: "row"}}>
            <label  style={{ color: "#225cb5"}}  for="subject-line">
              Multiplier
            </label>
            <div class = "multiplierLabel" style={{ color: "#225cb5", width:'25px', fontSize:'14px', marginLeft: '485px',fontWeight:'bolder'}}>
              (i)
            </div>
            </div>
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
                <th>Survey Actions</th>
              </tr>
            </thead>
            <tbody>
              {surveys.map((survey) => (
                <tr className="survey-row" key={survey.id}>
                  <td>{survey.name}</td>
                  <td>
                    Begins: {survey.start_date}
                    <br />
                    Ends: {survey.end_date}
                  </td>
                  <td>{survey.completion}</td>
                  <td>
                  {page === "home" ? (
                    <select className="surveyactions--select" style={{ backgroundColor: '#EF6C22', color: 'white',fontSize:'18px', fontWeight:'bold',textAlign:'center' }} onChange={(e) => handleActionButtonChange(e, survey)} value={actionsButtonValue} defaultValue="">
                      <option className="surveyactions--option" value="" disabled>Actions</option>
                      <option className="surveyactions--option" value="View Results">View Results</option>
                      <option className="surveyactions--option" value="Duplicate">Duplicate</option>
                      <option className="surveyactions--option" value="Extend">Extend</option>
                      <option className="surveyactions--option" value="Delete">Delete</option>
                    </select>
                  ) : page === "history" ? (
                    <button className="viewresult-button" onClick={() => handleViewResultsModalChange(survey)}>View Results</button>
                  )
                  : null}
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
          handleViewResultsModalChange={handleViewResultsModalChange}
          viewingCurrentSurvey={viewingCurrentSurvey}
          course={course}
        />
      )}
      {/* Error Modal for updating roster */}
      {showUpdateModal && (
        
        <div className="update-modal">
          <div className="update-modal-content">
            <div className="CancelContainer">
              <button className="CancelButton" style={{top: "0px"}} onClick={handleUpdateModalChange}>×</button>
            </div>
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

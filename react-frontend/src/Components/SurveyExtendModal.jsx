import React, {useState} from "react";
import "../styles/modal.css";
import "../styles/extendsurvey.css";

const SurveyExtendModal = ({modalClose, course, survey_data}) => {
  const [survey_id,] = useState(survey_data.id);
  const [survey_name,] = useState(survey_data.name);
  const [originalEndDate,] = useState(survey_data.end_date);
  const [endDate, setEndDate] = useState("");
  const [endTime, setEndTime] = useState("");
  const [emptyFieldsError, setEmptyFieldsError] = useState(false);
  const [startDateGreater, setStartDateGreater] = useState(false);
  const [startHourIsGreater, setStartHourIsGreater] = useState(false);
  const [mustBeAfterCurrentTime, setMustBeAfterCurrentTime] = useState(false);
  const [newEndMustComeAfterOldEndDay, setNewEndMustComeAfterOldEndDay] = useState(false);
  const [newEndMustComeAfterOldEndHour, setNewEndMustComeAfterOldEndHour] = useState(false);

  async function extendSurveyPost(formdata) {
    let fetchHTTP =
        process.env.REACT_APP_API_URL + "extendSurvey.php";
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
    setEmptyFieldsError(false);
    setStartDateGreater(false);
    setStartHourIsGreater(false);
    setMustBeAfterCurrentTime(false);
    setNewEndMustComeAfterOldEndDay(false);
    setNewEndMustComeAfterOldEndHour(false);
    let newEndDate = endDate;
    let newEndTime = endTime;

    //empty fields check
    if (newEndDate === "") {
        setEmptyFieldsError(true);
        return;
    }
    if (newEndTime === "") {
        setEmptyFieldsError(true);
        return;
    }

    let startDate = survey_data.start_date.split(' ')[0]
    let currentTime = survey_data.start_date.split(' ')[1]
    currentTime = currentTime.split(':');
    currentTime = currentTime[0] + ':' + currentTime[1];

    let startDateTimeObject = new Date(startDate + "T00:00:00"); //inputted start date.
    let endDateTimeObject = new Date(newEndDate + "T00:00:00"); //inputted end date.

    let timestamp = new Date(Date.now());
    timestamp.setHours(0, 0, 0, 0); //set hours/minutes/seconds/etc to be 0. Just want to deal with the calendar date
    //if the selected end date occurs in the past error
    if (endDateTimeObject < timestamp) {
        setMustBeAfterCurrentTime(true);
        return;
    }

    //new end date must come after old end date
    let oldEndDate = originalEndDate.split(' ')[0]
    let oldEndTimeHours = originalEndDate.split(' ')[1]
    oldEndTimeHours = oldEndTimeHours.split(':');
    oldEndTimeHours = oldEndTimeHours[0] + ':' + oldEndTimeHours[1];
    let oldEndDateTimeObject = new Date(oldEndDate + "T00:00:00")
    //conditional to check if old end calendar date isnt ahead of the new one
    if (oldEndDateTimeObject > endDateTimeObject) {
        setNewEndMustComeAfterOldEndDay(true);
        return;
    }
    //conditional to check if new end time hours is greater than old
    if (oldEndDateTimeObject.getDate(oldEndDateTimeObject) === endDateTimeObject.getDate(endDateTimeObject)) { //same date new hours should be ahead of old
        if (oldEndDateTimeObject.getMonth(oldEndDateTimeObject) === endDateTimeObject.getMonth(endDateTimeObject)) {
            if (parseInt(oldEndTimeHours.split(':')[0]) >= parseInt(newEndTime.split(':')[0])) {
                setNewEndMustComeAfterOldEndHour(true);
                return;
            }
        }
    }

    //selected end date is in the current day. Hours and minutes must be after current h/m
    if (endDateTimeObject.getDate(endDateTimeObject) === timestamp.getDate(timestamp)) {
        if (endDateTimeObject.getMonth(endDateTimeObject) === timestamp.getMonth(timestamp)) {
            let timestampWithHour = new Date(Date.now());
            let currentHour = timestampWithHour.getHours(timestampWithHour);
            let currentMinutes = timestampWithHour.getMinutes(timestampWithHour);
            let endHours = parseInt(newEndTime.split(":")[0]);
            let endMinutes = parseInt(newEndTime.split(":")[1]);

            if (endHours < currentHour) {
                setMustBeAfterCurrentTime(true);
                return;
            }
            if (endHours === currentHour) {
                if (endMinutes <= currentMinutes) {
                    setMustBeAfterCurrentTime(true);
                    return;
                }
            }
        }
    }

    //start date comes after end date error
    if (startDateTimeObject > endDateTimeObject) {
        setStartDateGreater(true);
        return
    }
    // same date. End date hours must be ahead
    if (startDateTimeObject === endDateTimeObject) {
        //hour check. Start date hour must be less than end date hour
        if (parseInt(currentTime.split(':')[0]) >= parseInt(newEndTime.split(':')[0])) {
            setStartHourIsGreater(true);
            return;
        }

    }
    let surveyId = survey_id;
    let formData5 = new FormData();

    formData5.append('survey-id', surveyId);
    formData5.append('end-date', newEndDate);
    formData5.append('end-time', newEndTime);
    let post = await extendSurveyPost(formData5);
    if (
        post.errors["end-date"] ||
        post.errors["end-time"] ||
        post.errors["start-date"] ||
        post.errors["start-time"]
    ) {
        //there are errors
        let errorList = [];
        if (post.errors["end-date"]) {
            errorList.push(post.errors["end-date"]);
        }
        if (post.errors["start-date"]) {
            errorList.push(post.errors["start-date"]);
        }
        if (post.errors["end-time"]) {
            errorList.push(post.errors["end-time"]);
        }
        if (post.errors["start-time"]) {
            errorList.push(post.errors["start-time"]);
        }
        modalClose(errorList);
        return;
    }

    modalClose([]);
}

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
                Extend Deadline {course.code}: {survey_name} 
            </h2>
            <div className="extend-survey--boxes-container">
                <div className="extend-survey--top-box-container">
                    <h3 className="form__item--info">Current Deadline: {originalEndDate}</h3>
                </div>
                <div className="extend-survey--bottom-box-container">
                     <h3 className="extend-survey--bottom-label form__item--info">Extended Deadline</h3>
                    <div className="extend-survey--inputs-container">
                        <label className="form__item--label" htmlFor="new-endDate">
                            New Date
                        <input
                            id="new-endDate"
                            className={(emptyFieldsError || startDateGreater || mustBeAfterCurrentTime || newEndMustComeAfterOldEndDay) ? "form__item--input-error" : null}
                            type="date"
                            placeholder="New End Date"
                            onChange={(e) => setEndDate(e.target.value)}
                        /> </label>
                        <label className="form__item--label" htmlFor="new-endTime">
                            New Time
                        <input
                            id="new-endTime"
                            className={(emptyFieldsError || startHourIsGreater || newEndMustComeAfterOldEndHour) ? "form__item--input-error" : null}
                            type="time"
                            placeholder="New End Time"
                            onChange={(e) => setEndTime(e.target.value)}
                        />
                        </label>
                    </div>
                    {emptyFieldsError && (
                        <label className="form__item--error-label">
                            <div className="form__item--red-warning-sign"/>
                            New date and time must be entered
                        </label>)}
                    {startDateGreater && (
                        <label className="form__item--error-label">
                            <div className="form__item--red-warning-sign"/>
                            End date must be later than start date
                        </label>)}
                    {startHourIsGreater && (
                        <label className="form__item--error-label">
                            <div className="form__item--red-warning-sign"/>
                            New end time must be later than starting time
                        </label>)}
                    {mustBeAfterCurrentTime && (
                        <label className="form__item--error-label">
                            <div className="form__item--red-warning-sign"/>
                            End date cannot be in the past
                        </label>)}
                    {newEndMustComeAfterOldEndDay && (
                        <label className="form__item--error-label">
                            <div className="form__item--red-warning-sign"/>
                            New end date must be later than current end date
                            </label>)}
                    {newEndMustComeAfterOldEndHour && (
                        <label className="form__item--error-label">
                            <div className="form__item--red-warning-sign"/>
                            New end time must be later than current end time
                        </label>)}
                </div>
            </div>
            <div className="form__item--confirm-btn-container">
            <button className="form__item--confirm-btn" onClick={verifyAndSubmit}>
             Extend Survey
            </button>
          </div>
        </div>
      </div>
    </div>
  );
}

export default SurveyExtendModal;
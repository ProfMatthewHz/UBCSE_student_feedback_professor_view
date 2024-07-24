import React, {useEffect, useState} from "react";
import "../styles/course.css";
import "../styles/modal.css";
import "../styles/duplicatesurvey.css";
import "../styles/addsurvey.css";
import Toast from "./Toast";
import ViewResults from "./ViewResults";
import {RadioButton} from "primereact/radiobutton";
import { useNavigate } from "react-router-dom";
import SurveyExtendModal from "./SurveyExtendModal";
import SurveyDeleteModal from "./SurveyDeleteModal";
import SurveyErrorsModal from "./SurveyErrorsModal";
import SurveyConfirmModal from "./SurveyConfirmModal";
import SurveyNewModal from "./SurveyNewModal";

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
        const response = fetch(process.env.REACT_APP_API_URL + "courseSurveysQueries.php", {
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
    const [rubrics, setRubrics] = useState([]);
    const [pairingModesFull, setPairingModesFull] = useState([]);
    const [survey_confirm_data, setSurveyConfirmData] = useState(null);
    const updateRosterformData = new FormData();

    /**
     * Create the effect which loads all of the potential rubrics from the system 
     */
    useEffect(() => {
        fetch(process.env.REACT_APP_API_URL + "getInstructorRubrics.php", {
            method: "GET",
            credentials: "include",
        })
        .then((res) => res.json())
        .then((result) => {
            //this is an array of objects of example elements {id: 1, description: 'exampleDescription'}
            let rubricIDandDescriptions = result.rubrics.map((element) => element);
            // An array of just the descriptions of the rubrics
            setRubrics(rubricIDandDescriptions);
        })
        .catch((err) => {
            console.log(err);
            throw err;
        });
    }, []);

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

    const openAddSurveyModal = async () => {
        fetchPairingModes();
        setAddSurveyModalIsOpen(true);
    };

    const closeNewSurveyModalAdd = async (result) => {
        setAddSurveyModalIsOpen(false);
        // Response is either the onclick event or the add survey response object
        if (!("type"  in result)) {
            // Form data is set. post the new survey and get the responses
            let response = await addSurveyBackend(result);
            let errorsObject = response.errors;
            let dataObject = response.data;
            if (errorsObject.length === 0) {
                // valid survey subitted
                let rosterDataAll = await fetchRosterNonRoster();
                let rosterData = rosterDataAll.data;
                if (rosterData) {
                    console.log(dataObject)
                    let startDateObject = new Date(dataObject["survey_data"]["start"].date);
                    let endDateObject = new Date(dataObject["survey_data"]["end"].date);
                    let surveyName = dataObject["survey_data"]["name"];
                    let rubric_name = dataObject["survey_data"]["rubric_name"];
                    let rostersArrayHere = rosterData["roster-students"];
                    let nonRosterArrayHere = rosterData["non-roster-students"];
                    let start = startDateObject.toLocaleString('default', {month: 'short', day: '2-digit'}) + " at " +  startDateObject.toLocaleString('default', {timeStyle: 'short'});
                    let end = endDateObject.toLocaleString('default', {month: 'short', day: '2-digit'}) + " at " +  endDateObject.toLocaleString('default', {timeStyle: 'short'});
                    let survey_data = {course_code: course.code, survey_name: surveyName, rubric_name: rubric_name, start_date: start, end_date: end, roster_array : rostersArrayHere, nonroster_array: nonRosterArrayHere};
                    setSurveyConfirmData(survey_data);
                    setModalIsOpenSurveyConfirm(true);
                }
            } else {
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
                setModalIsOpenError(true);
            }
        }
    };


  const closeNewSurveyModalDuplicate = async (result) => {
    // Response is either the onclick event or the new survey response object
    if (!("type"  in result)) {
      // Call the post request and wait for it to complete
      await duplicateSurveyBackend(result);
      updateAllSurveys();
    }
    setDuplicateModel(false);
  }

    const closeModalError = () => {
        setModalIsOpenError(false);
    };

    const closeModalSurveyConfirm = (success) => {
        setSurveyConfirmData(null);
        if (success) {
          updateAllSurveys();
        }
        setModalIsOpenSurveyConfirm(false);
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

 function duplicateSurveyBackend(formData) {
    formData.append("survey-id", currentSurvey.id);
    let fetchHTTP = process.env.REACT_APP_API_URL + "duplicateExistingSurvey.php";
    const result = fetch(fetchHTTP, {
        method: "POST",
        credentials: "include",
        body: formData,
    }).then((res) => res.text());
    return result; // Return the result directly
  }

  function addSurveyBackend(formData) {
    let fetchHTTP =
        process.env.REACT_APP_API_URL + "addSurveyToCourse.php";
    const result = fetch(fetchHTTP, {
        method: "POST",
        credentials: "include",
        body: formData,
    })
    .then((res) => res.json());
    return result; // Return the result directly
  }

  let Navigate = useNavigate();
  async function handleActionButtonChange(e, survey) {
        setActionsButtonValue(e.target.value);

        if (e.target.value === "Duplicate") {
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
    }

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

    const extendModalClose = (errorList) => {
        if (errorList && errorList.length > 0) {
          setErrorsList(errorList);
          setModalIsOpenError(true);
        } else {
          updateAllSurveys();
        }
        setExtendModal(false);
    }

    const deleteModalClose = (errorList) =>{
        if (errorList && errorList.length > 0) {
            setErrorsList(errorList);
            setModalIsOpenError(true);
        } else {
            updateAllSurveys();
        }
        setDeleteModal(false);
    }

    function handleUpdateModalChange() {
        setShowUpdateModal((prev) => !prev);
    }

    function handleViewResultsModalChange(survey) {
        setViewResultsModal((prev) => !prev);
        setViewingCurrentSurvey(survey);
    }

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
            {/* Add Survey modal display */}
            {addSurveyModalIsOpen && (
            <SurveyNewModal
                modalClose={closeNewSurveyModalAdd}
                modalReason="Add"
                button_text="Verify Survey"
                survey_data={ {"course_name" : course.code, "course_id" : course.id, "survey_name" : "", } }
                pairing_modes ={pairingModesFull}
                rubrics_list={rubrics}/>
            )}
            {/* Add Survey to a course modal*/}
            {duplicateModal && (
            <SurveyNewModal
                modalClose={closeNewSurveyModalDuplicate}
                modalReason="Duplicate"
                button_text="Duplicate Survey"
                survey_data={ {"course_name" : course.code, "course_id" : course.id, "survey_name" : currentSurvey.name + " copy" } }
                pairing_modes={null}
                rubrics_list={rubrics}/>
            )}
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
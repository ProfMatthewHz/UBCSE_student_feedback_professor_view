import React, { useEffect, useState } from "react";
import "../styles/course.css";
import "../styles/modal.css";
import "../styles/duplicatesurvey.css";
import "../styles/addsurvey.css";
import Toast from "./Toast";
import ViewResults from "./ViewResults";
import { useNavigate } from "react-router-dom";
import SurveyExtendModal from "./SurveyExtendModal";
import SurveyDeleteModal from "./SurveyDeleteModal";
import ErrorsModal from "./ErrorsModal";
import SurveyConfirmModal from "./SurveyConfirmModal";
import SurveyNewModal from "./SurveyNewModal";
import RosterUpdateModal from "./RosterUpdateModal";
import SurveyTeamAssignmentReviewModal from "./SurveyTeamAssignmentReviewModal";

const Course = ({ course, page }) => {
    const [surveys, setSurveys] = useState([]);
    const [extendModal, setExtendModal] = useState(false);
    const [duplicateModal, setDuplicateModal] = useState(false);

    const [deleteModal, setDeleteModal] = useState(false);
    const [addSurveyModalIsOpen, setAddSurveyModalIsOpen] = useState(false);
    const [errorModalIsOpen, setModalIsOpenError] = useState(false);
    const [errorsList, setErrorsList] = useState([]);
    const [modalIsOpenSurveyConfirm, setModalIsOpenSurveyConfirm] = useState(false);
    const [showUpdateModal, setShowUpdateModal] = useState(false);
    const [teamReviewModal, setTeamReviewModal] = useState(false);
    const [currentSurvey, setCurrentSurvey] = useState("");

    const [showViewResultsModal, setViewResultsModal] = useState(false);
    const [viewingCurrentSurvey, setViewingCurrentSurvey] = useState(null);

    const [updateRosterError, setUpdateRosterError] = useState([]);

    const [showErrorModal, setShowErrorModal] = useState(false);
    const [showToast, setShowToast] = useState(false);
    const [rubrics, setRubrics] = useState([]);
    const [pairingModesFull, setPairingModesFull] = useState([]);
    const [survey_confirm_data, setSurveyConfirmData] = useState(null);

    const processSurveys = (result) => {
        const activeSurveys = result.active.map((survey_info) => ({
            ...survey_info,
            expired: false,
            active: true,
        }));
        const expiredSurveys = result.expired.map((survey_info) => ({
            ...survey_info,
            expired: true,
            active: false,
        }));
        const upcomingSurveys = result.upcoming.map((survey_info) => ({
            ...survey_info,
            expired: false,
            active: false,
        }));
        setSurveys([...activeSurveys, ...expiredSurveys, ...upcomingSurveys]);
    };

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
            .then(processSurveys)
            .catch((err) => {
                console.log(err);
                throw err;
            });
    }

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

    const openAddSurveyModal = () => {
        fetchPairingModes();
        setAddSurveyModalIsOpen(true);
    };

    const closeNewSurveyModalAdd = async (result) => {
        setAddSurveyModalIsOpen(false);
        // Response is either the onclick event or the add survey response object
        if (result) {
            let errorsObject = result.errors;
            let dataObject = result.data;
            if (errorsObject.length === 0) {
                // valid survey subitted
                console.log(dataObject)
                let startDateObject = new Date(dataObject["survey_data"]["start"].date);
                let endDateObject = new Date(dataObject["survey_data"]["end"].date);
                let surveyName = dataObject["survey_data"]["name"];
                let rubric_name = dataObject["survey_data"]["rubric_name"];
                let start = startDateObject.toLocaleString('default', { month: 'short', day: '2-digit' }) + " at " + startDateObject.toLocaleString('default', { timeStyle: 'short' });
                let end = endDateObject.toLocaleString('default', { month: 'short', day: '2-digit' }) + " at " + endDateObject.toLocaleString('default', { timeStyle: 'short' });
                let survey_data = { course_code: course.code, survey_name: surveyName, rubric_name: rubric_name, start_date: start, end_date: end };
                setSurveyConfirmData(survey_data);
                setModalIsOpenSurveyConfirm(true);
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


    const closeNewSurveyModalDuplicate = (result) => {
        // Response is either the onclick event or the new survey response object
        if (result) {
            let errorsObject = result.errors;
            if (errorsObject.length === 0) {
                updateAllSurveys();
            } else {
                // TODO: Display the errors in a modal or something.
            }
        }
        setDuplicateModal(false);
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
        setShowErrorModal(false); // close the error modal
        setShowUpdateModal(true); // open the update modal again
    };

    let Navigate = useNavigate();
    async function handleActionButtonChange(e, survey) {
        if (e.target.value === "Duplicate") {
            setCurrentSurvey(survey);
            setDuplicateModal(true);
        }
        else if (e.target.value === "Delete") {
            setCurrentSurvey(survey);
            setDeleteModal(true);
        }
        else if (e.target.value === "Extend") {
            setCurrentSurvey(survey);
            setExtendModal(true);
        }
        else if (e.target.value === "View Results") {
            handleViewResultsModalChange(survey);
        }
        else if (e.target.value === "Preview Survey") {
            Navigate("/SurveyPreview", { state: { survey_name: survey.name, rubric_id: survey.rubric_id, course: course.code } });
        } else if (e.target.value === "Team Review") {
            setCurrentSurvey(survey);
            setTeamReviewModal(true);
        }
    }

    const handleUpdateRosterSubmit = (result) => {
        setShowUpdateModal(false);
        if (result) {
            if (result.error !== "") {
                setUpdateRosterError(result.error);
                setShowErrorModal(true); // show the error modal
            } else {
                setShowToast(true);
            }
        }
    }

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
            .then(processSurveys)
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

    const deleteModalClose = (errorList) => {
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
                    course={course}
                    survey_data={currentSurvey} />
                )}
            {/* Survey deletion modal*/}
            {deleteModal &&
                (<SurveyDeleteModal
                    modalClose={deleteModalClose}
                    course={course}
                    survey_data={currentSurvey} />
                )}
            {/* Survey creation errors modal*/}
            {errorModalIsOpen && (
                <ErrorsModal
                    modalClose={closeModalError}
                    error_type={"Survey"}
                    errors={errorsList} />
            )}
            {/* Survey creation confirmation modal*/}
            {modalIsOpenSurveyConfirm && (
                <SurveyConfirmModal
                    modalClose={closeModalSurveyConfirm}
                    survey_data={survey_confirm_data} />
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
                <ErrorsModal
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
                    survey_data={{ course_name: course.code, course_id: course.id, survey_name: "", pairing_mode: ""}}
                    pairing_modes={pairingModesFull}
                    rubric_id={rubrics[0].id}
                    rubrics_list={rubrics} />
            )}
            {/* Add Survey to a course modal*/}
            {duplicateModal && (
                <SurveyNewModal
                    modalClose={closeNewSurveyModalDuplicate}
                    modalReason="Duplicate"
                    button_text="Duplicate Survey"
                    survey_data={{ course_name: course.code, course_id: course.id, survey_name: currentSurvey.name + " copy", original_id: currentSurvey.id, pairing_mode: currentSurvey.survey_type }}
                    pairing_modes={pairingModesFull}
                    rubric_id={currentSurvey.rubric_id}
                    rubrics_list={rubrics} />
            )}
            {/* Review survey's team pairings modal display */}
            {teamReviewModal && (
                <SurveyTeamAssignmentReviewModal
                    modalClose={closeNewSurveyModalAdd}
                    modalReason="Add"
                    button_text="Verify Survey"
                    survey_data={{ course_name: course.code, course_id: course.id, survey_name: "", }}
                    pairing_modes={pairingModesFull}
                    rubric_id={rubrics[0].id}
                    rubrics_list={rubrics} />
            )}
            {/* Show modal to update the roster */}
            {showUpdateModal && (
                <RosterUpdateModal
                    modalClose={handleUpdateRosterSubmit}
                    course={course} />
            )}
            <div className="courseContent">
                <div className="courseHeader">
                    <h2>
                        {course.code}: {course.name}
                    </h2>
                    {page === "home" && (
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
                    )}
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
                                            <select
                                                className="surveyactions--select"
                                                onChange={(e) => handleActionButtonChange(e, survey)}
                                                value=''
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
                                                {/* Future expansion to allow updating evaluation assignments */}
                                                {!survey.active && false && (
                                                    <option
                                                        className="surveyactions--option"
                                                        value="Team Review"
                                                    >
                                                        Update Evaluation Assignments
                                                    </option>
                                                )}
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
                                        ) : page === "history" && (
                                            <button
                                                className="viewresult-button"
                                                onClick={() => handleViewResultsModalChange(survey)}
                                            >
                                                View Results
                                            </button>
                                        )}
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
            <Toast
                message={`Roster for ${course.code} ${course.name} successfully updated!`}
                isVisible={showToast}
                onClose={() => setShowToast(false)}
            />
        </div>
    );
};

export default Course;
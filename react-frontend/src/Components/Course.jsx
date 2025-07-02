import { useEffect, useState, useCallback } from "react";
import "../styles/course.css";
import "../styles/modal.css";
import "../styles/addsurvey.css";
import Toast from "./Toast";
import SurveyResultsView from "./SurveyResultsView";
import SurveyExtendModal from "./SurveyExtendModal";
import SurveyDeleteModal from "./SurveyDeleteModal";
import ErrorsModal from "./ErrorsModal";
import SurveyConfirmModal from "./SurveyConfirmModal";
import SurveyNewModal from "./SurveyNewModal";
import RosterUpdateModal from "./RosterUpdateModal";
import SurveyPreviewModal from "./SurveyPreviewModal";

const Course = ({ course, page, rubricList, pairingModes }) => {
    const [surveys, setSurveys] = useState([]);
    const [extendModal, setExtendModal] = useState(false);
    const [duplicateModal, setDuplicateModal] = useState(false);

    const [deleteModal, setDeleteModal] = useState(false);
    const [addSurveyModal, setAddSurveyModal] = useState(false);
    const [surveyErrorModal, setSurveyModalError] = useState(false);
    const [surveyErrorsList, setSurveyErrorsList] = useState([]);
    const [surveyConfirmModal, setSurveyConfirmModal] = useState(false);
    const [updateRosterModal, setUpdateRosterModal] = useState(false);
    const [errorRosterModal, setErrorRosterModal] = useState(false);
    const [updateRosterErrorsList, setUpdateRosterErrorsList] = useState([]);
    const [previewSurveyModal, setPreviewSurveyModal] = useState(false);

    const [currentSurvey, setCurrentSurvey] = useState("");
    const [viewResultsModal, setViewResultsModal] = useState(false);

    const [showToast, setShowToast] = useState(false);
    const [rubrics, ] = useState(rubricList);
    const [pairingModesFull, ] = useState(pairingModes);
    const [survey_confirm_data, setSurveyConfirmData] = useState(null);
    const [survey_confirm_roster, setSurveyConfirmRoster] = useState(null);
    const [survey_new_data, setSurveyNewData] = useState(null);

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
    const updateAllSurveys = useCallback(() => {
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

    //MODAL CODE
    useEffect(() => {
        updateAllSurveys();
    }, [updateAllSurveys]);

    useEffect(() => {
        if (survey_confirm_roster != null) {
            setSurveyConfirmModal(true);
        }
    }, [survey_confirm_roster]);


    function getSurveyAssignmentData(formData) {
        let fetchHTTP = process.env.REACT_APP_API_URL + "getSurveyRosterFromSurvey.php";
        const result = fetch(fetchHTTP, {
            method: "POST",
            credentials: "include",
            body: formData,
        })
            .then((res) => res.json());
        return result; // Return the result directly
    }

    async function setRosterForConfirmation(courseID, pairingMode, surveyID) {
        // Create a FormData object to send the course ID, pairing mode, and survey ID
        let formData = new FormData();
        formData.append("course-id", courseID);
        formData.append("pairing-mode", pairingMode);
        formData.append("survey-id", surveyID);
        // Call the function to get the survey assignment data
        let result = await getSurveyAssignmentData(formData);
        setSurveyConfirmRoster(result["data"]);
    }

    const openAddSurveyModal = () => {
        setSurveyNewData({course_code: course.code, course_name: course.name, course_id: course.id, rubric_id: rubrics[0].id, reason: "Add"});
        setAddSurveyModal(true);
    };
    
    const openUpdateRosterModal = () => {
        setUpdateRosterModal(true);
    }

    const closeNewSurveyReview = (result, surveyData) => {
        // Close the modal (only one will have been used, but this is safe and simple)
        setAddSurveyModal(false);
        setDuplicateModal(false);
        // Response is either the onclick event or the add survey response object
        if (result) {
            let errorsObject = result.errors;
            let dataObject = result.data;
            if (errorsObject.length === 0) {
                setSurveyNewData(null);
                setSurveyConfirmData(surveyData);
                setSurveyConfirmRoster(dataObject);
            } else {
                let allErrorStrings = [];
                for (let key in errorsObject) {
                    if ((key === "pairing-file") || (key === "team-file")) {
                        allErrorStrings = [...allErrorStrings, ...errorsObject[key]];
                    } else {
                        // If the key is not "pairing-file", we just add the error string directly
                        allErrorStrings.push(errorsObject[key]);
                    }
                }
                setSurveyErrorsList(allErrorStrings);
                setSurveyModalError(true);
            }
        }
    }

    const closeSurveyModalError = () => {
        setSurveyModalError(false);
    }

    const closeSurveyConfirm = (goBack, success) => {
        if (goBack) {
            setSurveyConfirmModal(false);
            setSurveyNewData(survey_confirm_data);
            setSurveyConfirmData(null);
            setAddSurveyModal(true);
        } else {
            setSurveyConfirmData(null);
            if (success) {
                updateAllSurveys();
            }
            setSurveyConfirmModal(false);
        }
    };

    const closeErrorLists = () => {
        setErrorRosterModal(false); // close the error modal
        setUpdateRosterModal(true); // open the update modal again
    };

    function handleActionButtonChange(e, survey) {
        if (e.target.value === "Duplicate") {
            setCurrentSurvey(survey);
            setSurveyNewData({ course_code: course.code, course_name: course.name, course_id: course.id, survey_name: survey.name + " copy", original_id: survey.id, pairing_mode: survey.survey_type, rubric_id: survey.rubric_id, pm_mult: survey.pm_weight, reason: "Duplicate" });
            setDuplicateModal(true);
        } else if (e.target.value === "Team Review") {
            setCurrentSurvey(survey);
            setSurveyConfirmData({ course_code: course.code, course_name: course.name, course_id: course.id, survey_name: survey.name, survey_id: survey.id, pairing_mode: survey.survey_type, reason: "Review" });
            setRosterForConfirmation(course.id, survey.survey_type, survey.id);
        } else if (e.target.value === "Team Update") {
            setCurrentSurvey(survey);
            setSurveyConfirmData({ course_code: course.code, course_name: course.name, course_id: course.id, survey_name: survey.name, survey_id: survey.id, pairing_mode: survey.survey_type, reason: "Update" });
            setRosterForConfirmation(course.id, survey.survey_type, survey.id);
        } else if (e.target.value === "Delete") {
            setCurrentSurvey(survey);
            setDeleteModal(true);
        }
        else if (e.target.value === "Extend") {
            setCurrentSurvey(survey);
            setExtendModal(true);
        }
        else if (e.target.value === "View Results") {
            setCurrentSurvey(survey);
            setViewResultsModal(true);
        }
        else if (e.target.value === "Preview Survey") {
            setCurrentSurvey({ ...survey, survey_name: survey['name'], course: course.name });
            setPreviewSurveyModal(true);
        }
    }

    const closeUpdateRoster = (result) => {
        setUpdateRosterModal(false);
        if (result) {
            if (result.error !== "") {
                setUpdateRosterErrorsList(result.error);
                setErrorRosterModal(true); // show the error modal
            } else {
                setShowToast(true);
            }
        }
    }

    const closeExtendSurvey = (errorList) => {
        if (errorList && errorList.length > 0) {
            setSurveyErrorsList(errorList);
            setSurveyModalError(true);
        } else {
            updateAllSurveys();
        }
        setExtendModal(false);
    }

    const closeDeleteSurvey = (errorList) => {
        if (errorList && errorList.length > 0) {
            setSurveyErrorsList(errorList);
            setSurveyModalError(true);
        } else {
            updateAllSurveys();
        }
        setDeleteModal(false);
    }

    function closeViewResults(survey) {
        setViewResultsModal(false);
    }
    
    function closePreviewModal() {
        setPreviewSurveyModal(false);
    }

    return (
        <div id={course.code} className="courseContainer">
            {/* Survey extendsion modal*/}
            {extendModal &&
                (<SurveyExtendModal
                    modalClose={closeExtendSurvey}
                    course={course}
                    survey_data={currentSurvey} />
                )}
            {/* Survey deletion modal*/}
            {deleteModal &&
                (<SurveyDeleteModal
                    modalClose={closeDeleteSurvey}
                    course={course}
                    survey_data={currentSurvey} />
                )}
            {/* Survey creation errors modal*/}
            {surveyErrorModal && (
                <ErrorsModal
                    modalClose={closeSurveyModalError}
                    error_type={"Survey"}
                    errors={surveyErrorsList} />
            )}
            {/* Survey creation confirmation modal*/}
            {surveyConfirmModal && (
                <SurveyConfirmModal
                    modalClose={closeSurveyConfirm}
                    survey_data={survey_confirm_data} 
                    survey_roster={survey_confirm_roster}/>
            )}
            {/* View Results Modal*/}
            {viewResultsModal && (
                <SurveyResultsView
                    closeViewResultsModal={closeViewResults}
                    surveyToView={currentSurvey}
                    course={course}
                />
            )}
            {/* Roster error display */}
            {errorRosterModal && (
                <ErrorsModal
                    modalClose={closeErrorLists}
                    error_type={"Roster Update"}
                    errors={updateRosterErrorsList} />
            )}
            {/* Add Survey modal display */}
            {addSurveyModal && (
                <SurveyNewModal
                    modalClose={closeNewSurveyReview}
                    button_text="Verify Survey"
                    survey_data={ survey_new_data }
                    pairing_modes={pairingModesFull}
                    rubrics_list={rubrics} />
            )}
            {/* Add Survey to a course modal*/}
            {duplicateModal && (
                <SurveyNewModal
                    modalClose={closeNewSurveyReview}
                    button_text="Duplicate Survey"
                    survey_data={survey_new_data}
                    pairing_modes={pairingModesFull}
                    rubrics_list={rubrics} />
            )}
            {/* Show modal to update the roster */}
            {updateRosterModal && (
                <RosterUpdateModal
                    modalClose={closeUpdateRoster}
                    course={course} />
            )}
            {/* Show modal to preview a survey */}
            {previewSurveyModal && (
                <SurveyPreviewModal
                    modalClose={closePreviewModal}
                    surveyData={currentSurvey} />
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
                                onClick={openUpdateRosterModal}
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
                                                {survey.active && (
                                                    <option
                                                        className="surveyactions--option"
                                                        value="Team Review"
                                                    >
                                                        Review Assignments
                                                    </option>
                                                )}
                                                {!survey.active && !survey.expired && (
                                                    <option
                                                        className="surveyactions--option"
                                                        value="Team Update"
                                                    >
                                                        Update Assignments
                                                    </option>
                                                )}
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
                                                onClick={() => handleActionButtonChange({target: {value: "View Results"}}, survey)}
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
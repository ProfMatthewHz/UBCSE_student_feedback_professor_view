import React, { useState, useEffect } from "react";
import {DataTable} from "primereact/datatable";
import {Column} from "primereact/column";
import { ConfirmPopup, confirmPopup } from 'primereact/confirmpopup';
import "../styles/modal.css";
import "../styles/confirmsurvey.css";

const SurveyConfirmModal = ({ modalClose, survey_data, survey_roster }) => {
    const [survey_name,] = useState(survey_data.survey_name);
    const [start_date,] = useState(survey_data.start_date);
    const [start_time,] = useState(survey_data.start_time);
    const [start_display,setStartDisplay ] = useState(null);
    const [end_date,] = useState(survey_data.end_date);
    const [end_time,] = useState(survey_data.end_time);
    const [end_display,setEndDisplay ] = useState(null);
    const [course_code,] = useState(survey_data.course_code);
    const [course_name,] = useState(survey_data.course_name);
    const [rubric_name,] = useState(survey_data.rubric_name);
    const [pairing_mode] = useState(survey_data.pairing_mode);
    const [pm_mult,] = useState(survey_data.pm_mult);
    const [course_id,] = useState(survey_data.course_id);
    const [rubric_id,] = useState(survey_data.rubric_id);
    const [listingType, setListingType] = useState("individual");
    const [team_data, setTeamData] = useState(null);
    const [individual_data, setIndividualData] = useState(null);
    const [roster_array, setRosterArray] = useState([]);
    const [nonroster_array, setNonRosterArray] = useState([]);
    const [unassigned_students, setUnassignedStudents] = useState([]);

    async function confirmSurveyPost() {
        let formData = new FormData();
        formData.append("survey-name", survey_name);
        formData.append("course-id", course_id);
        formData.append("pairing-mode", pairing_mode);
        formData.append("start-date", start_date);
        formData.append("start-time", start_time);
        formData.append("end-date", end_date);
        formData.append("end-time", end_time);
        formData.append("team-data", JSON.stringify(team_data));
        formData.append("pm-mult", pm_mult);
        formData.append("rubric-id", rubric_id);
        let fetchHTTP = process.env.REACT_APP_API_URL + "surveyAdd.php";
        const result = await fetch(fetchHTTP, {
            method: "POST",
            credentials: "include",
            body: formData,
        })
            .then((res) => res.json());
        return result; // Return the result directly
    }

    function calcReviewedRole(pairing_mode) {
        // Determine the role based on pairing mode
        if (pairing_mode === 1) {
            return "reviewed";
        } else if (pairing_mode === 2 || pairing_mode === 3 || pairing_mode === 5) {
            return "member";
        } else if (pairing_mode === 4) {
            return "manager";
        }
    }

    const checkForRole = (email, role, teamData) => {
        // Pessimistically assume that this will not happen
        let ret_val = false;
        // Check each team to see if the student is being reviewed
        for (let team of Object.values(teamData)) {
            if (team.some((element) => element.email === email && element.role === role)) {
                ret_val = true;
            }
        }
        return ret_val;
    }

    const calculateReviewing = (email, mode, teamData) => {
        // Calculate if the student is reviewing anyone based on the pairing mode
        let ret_val = false;
        if ((mode === 2) || (mode === 3) || (mode === 5)) {
            // In these modes, they only need to be on a team to be reviewing
        for (let team of Object.values(teamData)) {
                if (team.some((element) => element.email === email)) {
                    ret_val = true;
                }
            }
        } else if (mode === 4) {
            // In this mode, they need to be a member to be reviewing
            ret_val = checkForRole(email, "member", teamData);
        } else if (mode === 1) {
            // In this mode, they need to be a reviewer to be reviewing
            ret_val = checkForRole(email, "reviewer", teamData);
        }
        return ret_val;
    };

    const updateStatus = (email, mode, teamData) => {
         // Now recalculate if this student is a reviewer and/or being reviewed
        let role = calcReviewedRole(mode);
        let reviewed = checkForRole(email, role, teamData);
        let reviewing = calculateReviewing(email, mode, teamData);
        individual_data[email].reviewed_unicode = reviewed ? "✅" : "❌";
        individual_data[email].reviewing_unicode = reviewing ? "✅" : "❌";
        return individual_data[email];
    };

    useEffect(() => {
        function calculateReviewing(email, mode, teamData) {
            // Calculate if the student is reviewing anyone based on the pairing mode
            let ret_val = false;
            if ((mode === 2) || (mode === 3) || (mode === 5)) {
                // In these modes, they only need to be on a team to be reviewing
                for (let team of Object.values(teamData)) {
                    if (team.some((element) => element.email === email)) {
                        ret_val = true;
                    }
                }
            } else if (mode === 4) {
                // In this mode, they need to be a member to be reviewing
                ret_val = checkForRole(email, "member", teamData);
            } else if (mode === 1) {
                // In this mode, they need to be a reviewer to be reviewing
                ret_val = checkForRole(email, "reviewer", teamData);
            }
            return ret_val;
        }
        let roster_students = [];
        let non_roster_students = [];
        let unassigned_students = [];
        let assigned_students = [];

        let mode = survey_data.pairing_mode;
        let role = calcReviewedRole(mode);

        // Create the lists of individual students
        for (let email in survey_roster.individuals) {
            let student = survey_roster.individuals[email];
            // Add the student to the proper list for display
            if (student.rostered) {
                roster_students.push(student);
            } else {
                non_roster_students.push(student);
            }
            let reviewed = checkForRole(email, role, survey_roster.teams);
            let reviewing = calculateReviewing(email, mode, survey_roster.teams);
            student.reviewed_unicode = reviewed ? "✅" : "❌" ;
            student.reviewing_unicode = reviewing ? "✅" : "❌" ;
            if (!reviewed && !reviewing) {
                // If the student is not reviewing anyone and not being reviewed, they are unassigned
                unassigned_students.push({email: email, name: student.name});
            }
        }
        setRosterArray(roster_students);
        setNonRosterArray(non_roster_students);
        unassigned_students.sort((a, b) => a.name.localeCompare(b.name));
        setUnassignedStudents(unassigned_students);
        assigned_students.sort((a, b) => a.name.localeCompare(b.name));
        setIndividualData(survey_roster.individuals);
        setTeamData({...survey_roster.teams});
    }, [survey_roster, survey_data]);

    useEffect(() => {
        let start = new Date(start_date+"T"+start_time);
        let end = new Date(end_date+"T"+end_time);

        setStartDisplay(start.toLocaleString('default', {month: 'short', day: 'numeric'}) + " at " + start.toLocaleString('default', {hour: 'numeric', minute: 'numeric'}));
        setEndDisplay(end.toLocaleString('default', {month: 'short', day: 'numeric'}) + " at " + end.toLocaleString('default', {hour: 'numeric', minute: 'numeric'}));
    }, [start_date, start_time, end_date, end_time]);

    function hideStudentSelector(event) {
        // Hide the select widget when a student is selected
        let widget = event.target;
        widget.previousElementSibling.classList.remove("invisible");
        widget.previousElementSibling.classList.add("visible");
        widget.value = ""; // Reset the select widget
        widget.blur();
    }

    function addMember(event, teamIndex) {
        // Get the select widget where we just selected a student
        let widget = event.target;
        let selectedEmail = widget.value;
        if (selectedEmail) {
            // Add the selected student to the team roster
            team_data[teamIndex] = [{email: selectedEmail, role : "member"}, ...team_data[teamIndex]];
            setTeamData({...team_data});
            // Remove the student from unassigned_students
            setUnassignedStudents(prevStudents => prevStudents.filter(student => student.email !== selectedEmail));
            // Hide the select widget
            hideStudentSelector(event);
            // Record that the student will be reviewing someone but may also be a reviewer
            updateStatus(selectedEmail, pairing_mode, team_data);
        }
    }

    // Function adding a student to an array that is sorted by name
    function addStudentToOrderedArray(email, array) {
        let name = individual_data[email].name;
        // Find the correct position to insert the student based on their name
        let index = array.findIndex(s => s.name.localeCompare(name) > 0);
        if (index === -1) {
            // If no larger name is found, append to the end
            array.push({email: email, name: name});
        } else {
            // Insert the student at the found index
            array.splice(index, 0, {email: email, name: name});
        }
        return array;
    }
    
    function removeMember(teamKey, member) {
        // Sanity check that this is a member who can be removed
        if ((member.role === 'member') && team_data[teamKey].length > 2) {
            team_data[teamKey] = team_data[teamKey].filter(m => m !== member);
            setTeamData({...team_data});

            let newArray = addStudentToOrderedArray(member.email, unassigned_students);
            setUnassignedStudents(newArray);
            updateStatus(member.email, pairing_mode, team_data);
        } else {
            console.log("Cannot remove member: " + member + " from team " + teamKey + ".");
        }
    }

    function removeTeam(deleteKey) {
        // Sanity check to ensure deleteIndex is set
        if (deleteKey != null) {
            let team = team_data[deleteKey];
            // Now remove the team from the team_data state
            delete team_data[deleteKey];
            setTeamData({...team_data});

            // Now we need to process each member of the team
            let unassigned = [...unassigned_students];
            for (let student of team ) {
                updateStatus(student.email, pairing_mode, team_data);
                if (!unassigned.some((element) => element.email === student.email)) {
                    unassigned = addStudentToOrderedArray(student.email, unassigned);
                }
            }
            setUnassignedStudents([...unassigned]);
        }
    }

    const verifyDeleteRow = (event, teamIndex) => {
        confirmPopup({
            target: event.currentTarget,
            message: 'Are you sure you want to delete this team?', 
            icon: 'pi pi-exclamation-triangle',
            dismissable: 'false',
            accept: () => {removeTeam(teamIndex)},
        });
    }

    function allowAddMember(event) {
        event.target.classList.remove("visible");
        event.target.classList.add("invisible");
    }

    function calculateLabelClass(member, teamLength) {
        let className = "string-list-item";
        if (member.role !== "member") {
            className += " notremovable fixedLabel";
        } else if (teamLength <= 2) {
            className += " notremovable";
        } else {
            className += " removable";
        }
        return className;
    }

    function quitModal() {
        modalClose(false, false);
    }

    function backModal() {
        modalClose(true, false);
    }

    async function verifyConfirm() {
        // Send data to the server
        let result = await confirmSurveyPost();
        if (Object.keys(result["errors"]).length > 0) {
            console.log("Errors occurred while confirming survey: ", result["errors"]);
        } else {
           modalClose(false, true);
        }
    }

    return (
        <div className="confirm-modal modal">
            <div style={{ width: "1200px", maxWidth: "90%" }} className="modal-content modal-phone">
                <ConfirmPopup />
                <div className="CancelContainer">
                    <button className="CancelButton" onClick={quitModal}>
                        ×
                    </button>
                </div>
                <div className="modal--contents-container">
                    <h2 className="modal--main-title">
                        Confirm Survey Information
                    </h2>
                </div>
                <div className="confirm--top-container">
                    <h3 className="form__item--info">
                        Survey Name: {survey_name}
                    </h3>
                    <h3 className="form__item--info">For Course: {course_code + ": " + course_name}</h3>
                    <h3 className="form__item--info">
                        Rubric Used: {rubric_name}
                    </h3>
                    <h3 className="form__item--info">
                        Survey Active: {start_display} to {end_display}
                    </h3>
                </div>
                <div className="viewresults-modal--main-button-container">
                <button
                    className={
                        listingType === "individual"
                            ? "survey-result--option-active"
                            : "survey-result--option"
                    }
                    onClick={() => {setListingType("individual");}}
                >
                    Survey Participants 
                </button>
                
                <button
                    className={
                        listingType === "team"
                            ? "survey-result--option-active"
                            : "survey-result--option"
                        }
                        onClick={() => {setListingType("team");}}
                >
                    Teams
                </button>
            </div>
            <h3 className="confirm--bottom-label form__item--info">{listingType==="individual"? "Survey Participants" : "Survey Teams"}</h3>
            {listingType === "individual" ? (
                <div className="confirm--bottom-container">
                    {roster_array && roster_array.length > 0 ? (
                        <DataTable
                            value={roster_array}
                            name="roster"
                            header="Course Students"
                            showGridlines>
                            <Column
                                field="email"
                                header="Email"
                                filter
                                filterPlaceholder="Search by email"
                                filterMatchMode="contains">
                            </Column>
                            <Column
                                field="name"
                                header="Name"
                                filter
                                filterPlaceholder="Search by name"
                                filterMatchMode="contains">
                            </Column>
                            <Column
                                field="reviewing_unicode"
                                header="Reviewing Others"
                                sortable>
                            </Column>
                            <Column
                                field="reviewed_unicode"
                                header="Being Reviewed"
                                sortable>
                            </Column>
                        </DataTable>
                    ) : (
                        <div className="confirm--empty-text">No students on roster</div>
                    )}

                    {nonroster_array && nonroster_array.length > 0 ? (
                        <DataTable
                            name="nonroster"
                            header="Non-course Students"
                            value={nonroster_array}
                            showGridlines>
                            <Column
                                field="email"
                                header="Email"
                                filter
                                filterPlaceholder="Search by email"
                                filterMatchMode="contains">
                            </Column>
                            <Column
                                field="name"
                                header="Name"
                                filter
                                filterPlaceholder="Search by name"
                                filterMatchMode="contains">
                            </Column>
                            <Column
                                field="reviewing_unicode"
                                header="Reviewing Others"
                                sortable>
                            </Column>
                            <Column
                                field="reviewed_unicode"
                                header="Being Reviewed"
                                sortable>
                            </Column>
                        </DataTable>
                    ) : (
                        <div className="confirm--empty-text">Only includes roster students</div>
                    )}
                </div>) : (
                <div className="confirm--bottom-container">
                    {team_data && Object.keys(team_data).length > 0 ? (
                        <table>
                            <tbody>
                                {Object.keys(team_data).map((teamKey, index) => (
                                    <tr key={teamKey}>
                                        <td>
                                            <div className="teammembers">
                                            {team_data[teamKey].map((member, memberIndex) => (
                                                <label className={calculateLabelClass(member, team_data[teamKey].length)} key={memberIndex}>
                                                    <div className="name">
                                                    {individual_data[member.email]["name"]}</div> <div className="close" onClick={() => removeMember(teamKey, member)}>x</div>
                                                </label>
                                            ))}
                                            {pairing_mode !== 1 && 
                                            (<div className="add-member-container">
                                                <label className="string-list-item addition visible" onClick={allowAddMember}>
                                                    + Add members
                                                </label>
                                                <select className="addition add-member-select" onChange={(e) => {addMember(e, teamKey)}} onBlur={(e) => hideStudentSelector(e)} >
                                                    <option value="">Select a member</option>
                                                    {unassigned_students.map((student, studentIndex) => (
                                                        <option key={studentIndex} value={student.email}>
                                                            {student.name} ({student.email})
                                                        </option>
                                                    ))}
                                                </select>
                                            </div>)}
                                            <label className="delete-team" onClick={(e) => verifyDeleteRow(e, teamKey)}></label>
                                            </div>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    ) : (
                        <div className="confirm--empty-text">No teams available</div>
                    )}
                </div>
                )}
                    <div className="confirm--btn-container form__item--confirm-btn-container">
                    <button
                        className="form__item--cancel-btn"
                        onClick={backModal}
                    >
                        Back to New Survey
                    </button>
                    <button
                        className="form__item--confirm-btn"
                        onClick={verifyConfirm}
                    >
                        Confirm Survey
                    </button>
                    </div>
                </div>
            </div>
    );
}
export default SurveyConfirmModal;

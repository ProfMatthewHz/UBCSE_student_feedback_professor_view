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
    const [reasonShown,] = useState(survey_data.reason);
    const [survey_id,] = useState(survey_data.survey_id);
    const [listingType, setListingType] = useState("team_roster");
    const [team_data, setTeamData] = useState(null);
    const [individual_data, setIndividualData] = useState(null);
    const [roster_array, setRosterArray] = useState([]);
    const [nonroster_array, setNonRosterArray] = useState([]);
    const [unassigned_students, setUnassignedStudents] = useState([]);
    const [reviewing_teams, setReviewingTeams] = useState([]);
    const [reviewed_teams, setReviewedTeams] = useState([]);
    const [pairings, setPairings] = useState(survey_roster.pairings ? survey_roster.pairings : []);

    async function postSurvey() {
        let formData = new FormData();
        formData.append("survey-name", survey_name);
        formData.append("course-id", course_id);
        formData.append("pairing-mode", pairing_mode);
        formData.append("start-date", start_date);
        formData.append("start-time", start_time);
        formData.append("end-date", end_date);
        formData.append("end-time", end_time);
        formData.append("team-data", JSON.stringify(team_data));
        if (pairing_mode === 6) {
            // If pairing mode is 6, we need to send the pairings as well
            formData.append("collective-pairings", JSON.stringify(pairings));
        }
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

    async function postUpdate() {
        let formData = new FormData();
        formData.append("course-id", course_id);
        formData.append("survey-id", survey_id);
        formData.append("team-data", JSON.stringify(team_data));
        if (pairing_mode === 6) {
            // If pairing mode is 6, we need to send the pairings as well
            formData.append("collective-pairings", JSON.stringify(pairings));
        }
        let fetchHTTP = process.env.REACT_APP_API_URL + "assignmentUpdate.php";
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
        } else if (pairing_mode === 2 || pairing_mode === 3 || pairing_mode === 5 || pairing_mode === 6) {
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
            if (team["roster"].some((element) => element.email === email && element.role === role)) {
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
                if (team["roster"].some((element) => element.email === email)) {
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

    const updateStatus = (email, mode, reviewed_teams, reviewing_teams) => {
         // Now recalculate if this student is a reviewer and/or being reviewed
        let role = calcReviewedRole(mode);
        let reviewed = checkForRole(email, role, reviewed_teams);
        let reviewing = calculateReviewing(email, mode, reviewing_teams);
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
                    if (team["roster"].some((element) => element.email === email)) {
                        ret_val = true;
                    }
                }
            } else if ( (mode === 4) || (mode === 6) ) {
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
        let reviewed_teams = [];
        let reviewing_teams = [];
        let role = calcReviewedRole(mode);

        if (survey_data.pairing_mode !== 6) {
            reviewed_teams = survey_roster.teams;
            reviewing_teams = survey_roster.teams;
        } else {
            for (let pairing of survey_roster.pairings) {
                reviewing_teams.push(survey_roster.teams[pairing.reviewing]);
                reviewed_teams.push(survey_roster.teams[pairing.reviewed]);
            }
        }

        // Create the lists of individual students
        for (let email in survey_roster.individuals) {
            let student = survey_roster.individuals[email];
            // Add the student to the proper list for display
            if (student.rostered) {
                roster_students.push(student);
            } else {
                non_roster_students.push(student);
            }
            let reviewed = checkForRole(email, role, reviewed_teams);
            let reviewing = calculateReviewing(email, mode, reviewing_teams);
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
        setTeamData(survey_roster.teams);
        setReviewedTeams(reviewed_teams);
        setReviewingTeams(reviewing_teams);
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
            team_data[teamIndex]["roster"].splice(0, 0, {email: selectedEmail, role : "member"});
            // Remove the student from unassigned_students
            setUnassignedStudents(prevStudents => prevStudents.filter(student => student.email !== selectedEmail));
            // Hide the select widget
            hideStudentSelector(event);
            // Record that the student will be reviewing someone but may also be a reviewer
            updateStatus(selectedEmail, pairing_mode, reviewed_teams, reviewing_teams);
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
    
    function removeMember(teamKey, memberIndex) {
        // Get the member to be removed
        let member = team_data[teamKey]["roster"][memberIndex];
        team_data[teamKey]["roster"].splice(memberIndex, 1);
        setTeamData({...team_data});

        let newArray = addStudentToOrderedArray(member.email, unassigned_students);
        setUnassignedStudents(newArray);
        updateStatus(member.email, pairing_mode, reviewed_teams, reviewing_teams);
    }

    function removeTeam(deleteKey) {
        // Precondition: deleteKey is a valid key in team_data

        // If we are dealing with collective reviews, we also need to remove any pairings involving this team
        if (pairing_mode === 6) {
            for (let index in pairings) {
                let pairing = pairings[index];
                // If the team is either the reviewing or reviewed team, we need to remove this pairing
                if (pairing.reviewing === deleteKey || pairing.reviewed === deleteKey) {
                    // Remove this pairing from our data
                    removePairing(index);
                }
            }
        }

        let team = team_data[deleteKey];
        // Now remove the team from the team_data state
        delete team_data[deleteKey];

        // Now we need to process each member of the team
        let unassigned = [...unassigned_students];
        for (let student of team["roster"]) {
            updateStatus(student.email, pairing_mode, reviewed_teams, reviewing_teams);
            if (!unassigned.some((element) => element.email === student.email)) {
                unassigned = addStudentToOrderedArray(student.email, unassigned);
            }
        }
        setUnassignedStudents([...unassigned]);
    }

    function removePairing(deleteIdx) {
        // Precondition: deleteIdx is a valid index in pairings

        const new_pair = pairings.toSpliced(deleteIdx, 1);
        const new_reviewed = reviewed_teams.toSpliced(deleteIdx, 1);
        const new_reiewing = reviewing_teams.toSpliced(deleteIdx, 1);
        setPairings(new_pair);
        setReviewedTeams(new_reviewed);
        setReviewingTeams(new_reiewing);
    }

    const verifyDeletePairing = (event, teamIndex) => {
        confirmPopup({
            target: event.currentTarget,
            message: 'Are you sure you want to delete this evaluation?', 
            icon: 'pi pi-exclamation-triangle',
            dismissable: 'false',
            accept: () => {removePairing(teamIndex)},
        });
    }

    const verifyDeleteTeam = (event, teamIndex) => {
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
        } else if ( ((pairing_mode === 6) && (teamLength === 1)) ||
                    ((pairing_mode !== 6) && (teamLength <= 2)) ) {
            className += " notremovable";
        } else {
            className += " removable";
        }
        return className;
    }

    function confirmationForNewSurvey() {
        return ((reasonShown === "Add") || (reasonShown === "Duplicate"));
    }

    function quitModal() {
        modalClose(false, false);
    }

    function backModal() {
        modalClose(true, false);
    }

    async function verifyConfirm() {
        // Send data to the server
        let result = await postSurvey();
        if (Object.keys(result["errors"]).length > 0) {
            console.log("Errors occurred while confirming survey: ", result["errors"]);
        } else {
           modalClose(false, true);
        }
    }

    async function verifyUpdate() {
        // Send data to the server
        let result = await postUpdate();
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
                        {confirmationForNewSurvey() ? "Confirm Survey Information" : "Review Assignments"}
                    </h2>
                </div>
                <div className="modal--top-container">
                    <h3 className="form__item--info">
                        Survey Name: {survey_name}
                    </h3>
                    <h3 className="form__item--info">For Course: {course_code + ": " + course_name}</h3>
                    {confirmationForNewSurvey() && (
                        <h3 className="form__item--info">
                            Rubric Used: {rubric_name}
                        </h3>)}
                    {confirmationForNewSurvey() && (
                        <h3 className="form__item--info">
                            Survey Active: {start_display} to {end_display}
                        </h3>)
                    }
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
                    Individual Participants 
                </button>
                
                <button
                    className={
                        listingType === "team_roster"
                            ? "survey-result--option-active"
                            : "survey-result--option"
                        }
                        onClick={() => {setListingType("team_roster");}}
                >
                    Team Rosters
                </button>
                { pairing_mode === 6 && (
                <button
                    className={
                        listingType === "team_pairing"
                            ? "survey-result--option-active"
                            : "survey-result--option"
                        }
                        onClick={() => {setListingType("team_pairing");}}
                >
                    Team Evaluation Assignments
                </button>)}
            </div>
            <h3 className="confirm--bottom-label form__item--info">{listingType==="individual"? "Survey Participants" : listingType==="individual" ? "Survey Teams" : "Evaluation Assignments"}</h3>
            {listingType === "individual" && (
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
                </div>)}
                { listingType === "team_roster" && (
                <div className="confirm--bottom-container">
                    {team_data && Object.keys(team_data).length > 0 ? (
                        <table>
                            <tbody>
                                {Object.keys(team_data).map((teamKey, index) => (
                                    <tr key={teamKey}>
                                        {pairing_mode === 6 && (
                                            <td className="team-name">
                                                {teamKey}
                                            </td>
                                        )}
                                        <td>
                                            <div className="teammembers">
                                            {team_data[teamKey]["roster"].map((member, memberIndex) => (
                                                <label className={calculateLabelClass(member, team_data[teamKey]["roster"].length)} key={memberIndex}>
                                                    <div className="name">
                                                    {individual_data[member.email]["name"]}</div> <div className="close" onClick={() => removeMember(teamKey, memberIndex)}>x</div>
                                                </label>
                                            ))}
                                            {pairing_mode !== 1 && reasonShown !== "Review" &&
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
                                            <label className="delete-team" onClick={(e) => verifyDeleteTeam(e, teamKey)}></label>
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
                { listingType === "team_pairing" && (
                <div className="confirm--bottom-container">
                    {pairings && (pairings.length > 0) ? (
                        <table className="confirm--pairing-table">
                            <tbody>
                                {pairings.map((pairing, index) => (
                                    <tr key={index}>
                                        <td>
                                            <div className="whole-team">
                                                <span className="team-name">
                                                    {pairing.reviewing}
                                                </span>
                                                <div className="teamlisting reviewing-team">
                                                {team_data[pairing.reviewing]["roster"].map(member => (
                                                    <span key={member.email} className="string-list-item">
                                                        {individual_data[member.email]["name"]}
                                                    </span>))}
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div className="connection-arrow">
                                                &mdash; is reviewing &rarr;
                                            </div>
                                        </td>
                                        <td>
                                            <div className="whole-team">
                                                <span className="team-name">
                                                    {pairing.reviewed}
                                                </span>
                                                <div className="teamlisting reviewed-team">
                                                {team_data[pairing.reviewed]["roster"].map(member => (
                                                    <span key={member.email} className="string-list-item">
                                                        {individual_data[member.email]["name"]}
                                                    </span>))}
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <label className="delete-team" onClick={(e) => verifyDeletePairing(e, index)}></label>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    ) : (
                        <div className="confirm--empty-text">No evaluations provided</div>
                    )}
                </div>)}
                {confirmationForNewSurvey() ?
                    (<div className="confirm--btn-container form__item--confirm-btn-container">
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
                    </div>) :
                    (<div className="confirm--btn-container form__item--confirm-btn-container">
                        <button
                            className="form__item--cancel-btn"
                            onClick={quitModal}
                        >
                            Cancel Changes
                        </button>
                        <button
                            className="form__item--confirm-btn"
                            onClick={verifyUpdate}
                        >
                            Update Assigmments
                        </button>
                    </div>)}
                </div>
            </div>
    );
}
export default SurveyConfirmModal;

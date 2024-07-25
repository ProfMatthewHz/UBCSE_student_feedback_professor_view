import React, {useState, useEffect} from "react";
import "../styles/addcourse.css";
import "../styles/modal.css";
import "../styles/course.css";
import {Select} from "../Components/Select";
import {ErrorsModal} from "../Components/ErrorsModal";

/**
 * The AddCourse component displays a form for adding a new course to the system.
 * @param handleAddCourseModal
 * @param getCourses
 * @returns {Element}
 * @constructor
 */

const AddCourse = ({closeModal, updateCourseListing}) => {
    const [courseCode, setCourseCode] = useState(""); // State for storing the course code
    const [courseName, setCourseName] = useState(""); // State for storing the course name
    const [file, setFile] = useState(null); // State for storing the file
    const [semester, setSemester] = useState(""); // State for storing the semester
    const [year, setYear] = useState(0); // State for storing the year
    const [rosterFileError, setRosterFileError] = useState([]); // State for storing the roster file error
    const [duplicateError, setDuplicateError] = useState(""); // State for storing the duplicate error
    const [showFileErrorModal, setShowFileErrorModal] = useState(false); // State for showing the modal
    const [instructors, setInstructors] = useState([]); // array of instructor objects selected including their id and name
    const [allInstructors, setAllInstructors] = useState([]); // array of all instructors in the database
    const [selectedInstructors, setSelectedInstructors] = useState([]); // array of selected instructor ids to send to backend

    /**
     * Determines the current year based on the current date.
     * @returns {number}
     */
    const getCurrentYear = () => {
        const date = new Date();
        return date.getFullYear();
    };

    // Using 2023-2024 course schedule
    /**
     * Determines the current semester based on the current date.
     * @returns {number}
     */
    const getCurrentSemester = () => {
        const date = new Date();
        const month = date.getMonth(); // 0 for January, 1 for February, etc.
        const day = date.getDate();

        // Summer Sessions (May 30 to Aug 18)
        if (
            (month === 4 && day >= 30) ||
            (month > 4 && month < 7) ||
            (month === 7 && day <= 18)
        ) {
            return 3; // Summer
        }

        // Fall Semester (Aug 19 to Dec 28)
        else if (
            (month === 7 && day >= 19) ||
            (month > 7 && month < 11) ||
            (month === 11 && day <= 28)
        ) {
            return 4; // Fall
        }

        // Winter Session (Dec 29 to Jan 19)
        else if ((month === 11 && day >= 29) || (month === 0 && day <= 19)) {
            return 1; // Winter
        }

        // If none of the above conditions are met, it must be Spring (Jan 24 to May 19)
        return 2; // Spring
    };

    /**
     * Converts semester names to their corresponding integer codes.
     */
    const getFutureSemesters = () => {
        const currentYear = getCurrentYear();
        const currentSemester = getCurrentSemester();
        const futureSemesters = [];

        let startSem;
        if (currentSemester === 1) startSem = "winter";
        if (currentSemester === 2) startSem = "spring";
        if (currentSemester === 3) startSem = "summer";
        if (currentSemester === 4) startSem = "fall";
        let year = currentYear;

        // Include 4 semesters from the current one
        for (let i = 0; i < 4; i++) {
            futureSemesters.push({
                value: `${startSem}_${year}`,
                text: `${startSem.charAt(0).toUpperCase() + startSem.slice(1)} ${year}`,
            });
            startSem = getNextSemester(startSem);
            if (startSem === "winter") {
                year++;
            }
        }

        return futureSemesters;
    };

    /**
     * Returns the next semester given the current semester.
     * @param currentSemester
     * @returns {string}
     */
    const getNextSemester = (currentSemester) => {
        switch (currentSemester) {
            case "winter":
                return "spring";
            case "spring":
                return "summer";
            case "summer":
                return "fall";
            case "fall":
                return "winter";
            default:
                return "";
        }
    };

    // fetch the courses to display on the sidebar
    useEffect(() => {
        setYear(getCurrentYear());
        let currSem = getCurrentSemester();
        if (currSem === 1) setSemester("winter");
        if (currSem === 2) setSemester("spring");
        if (currSem === 3) setSemester("summer");
        if (currSem === 4) setSemester("fall");

        // Fetch all instructors
        fetch(process.env.REACT_APP_API_URL + "getInstructors.php", {
            method: "GET",
            credentials: "include",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded",
            },
        })
            .then((res) => res.json())
            .then((result) => {
                let fetchedInstructors = [];
                for (let instructorArray of result) {
                    let currentInstructor = {
                        label: instructorArray[1],
                        value: instructorArray[0],
                    };
                    fetchedInstructors.push(currentInstructor);
                };
                setAllInstructors(fetchedInstructors);
            })
            .catch((err) => {
                console.log(err);
            });
    }, []);

    // Everytime an instructor is selected/deselected the selectedInstructors state updates
    useEffect(() => {
        let instructorIds = [];
        for (let instructor of instructors) {
            instructorIds.push(parseInt(instructor.value));
        }
        setSelectedInstructors(instructorIds);
    }, [instructors]);

    /**
     * Handles the change of the semester.
     */
    const handleSemesterChange = (e) => {
        const selectedValue = e.target.value; // For example, "fall_2024"
        const [newSemester, newYear] = selectedValue.split("_"); // Splits to ["fall", "2024"]

        // Update the states
        setSemester(newSemester);
        setYear(parseInt(newYear));
    };

    /**
     * Handles the form submission.
     * @param e
     */
    const handleSubmit = (e) => {
        e.preventDefault();
        const formData = new FormData();
        formData.append("course-code", courseCode);
        formData.append("course-name", courseName);
        formData.append("course-year", year);
        formData.append("roster-file", file); // Assuming `file` is a File object
        formData.append("semester", semester);
        formData.append("additional-instructors", selectedInstructors);

        // Send the form data to the API
        fetch(process.env.REACT_APP_API_URL + "courseAdd.php", {
            method: "POST",
            credentials: "include",
            body: formData,
        })
            // Parse the response to JSON format
            .then((res) => res.text())
            .then((result) => {
              // If the result is a string and not empty, it means there is an error
                if (typeof result === "string" && result !== "") {
                    try {
                        const parsedResult = JSON.parse(result);
                        console.log(parsedResult);
                        if (parsedResult["roster-file"]) {
                            setShowFileErrorModal(true);
                            const updatedError = parsedResult["roster-file"];
                            setRosterFileError(updatedError);
                        } else if (parsedResult["duplicate"]) {
                            setDuplicateError(parsedResult["duplicate"]);
                        }
                    } catch (e) {
                        console.log("Failed to parse JSON: ", e);
                    }
                } else {
                    // Class is valid, so we can just navigate to the home page
                    closeModal();
                    updateCourseListing();
                    setRosterFileError([]);
                    setDuplicateError("");
                }
            })
            .catch((err) => {
                console.log(err);
            });
    };

    const fileErrorModalClose = () => {
        setShowFileErrorModal(false); // Close the modal
    };

    // The AddCourse component renders a form to add a new course.
    return (
        <>
            <div className="formContainer">
                <div className="formContent">
                    <h2 className="add-header">Add Course</h2>
                    {/*The form element where the onSubmit event is handled by the handleSubmit function.*/}
                    <form
                        className="add__form"
                        onSubmit={handleSubmit}
                        encType="multipart/form-data"
                    >
                        <div className="addcourse-form__item--container">
                            {/* Section for course code and name with potential duplicate error messages. */}
                            <div className="addcourse--namecode-error">
                                {/* Input fields for course code and name. */}
                                <div className="addcourse--name-code">
                                    <div className="name-code--item form__item">
                                        <label className="form__item--label" htmlFor="course-code">
                                            Course Code
                                            <input
                                                type="text"
                                                id="course-code"
                                                value={courseCode}
                                                onChange={(e) => setCourseCode(e.target.value)}
                                                placeholder="CSE 115"
                                                required
                                                className={
                                                    duplicateError && "addcourse-duplicate-error"
                                                }
                                            />
                                        </label>
                                    </div>

                                    <div className="name-code--item form__item">
                                        <label className=" form__item--label" htmlFor="course-name">
                                            Course Name
                                            <input
                                                type="text"
                                                id="course-name"
                                                value={courseName}
                                                onChange={(e) => setCourseName(e.target.value)}
                                                placeholder="Introduction to Computer Science"
                                                required
                                                className={
                                                    duplicateError && "addcourse-duplicate-error"
                                                }
                                            />
                                        </label>
                                    </div>
                                </div>
                                {/* Displays error message if the course being added is a duplicate. */}
                                {duplicateError && (
                                    <div className="add-course--error">
                                        This course already exists
                                    </div>
                                )}
                            </div>

                            {/* File input for course roster CSV file with specific requirements. */}
                            <div className="form__item file-input-wrapper">
                                <label className="form__item--label form__item--file" htmlFor="addcourse-file-input">
                                    Roster (CSV File) - Each Row Must Be Formatted: email, first name, last name
                                    <input
                                        type="file"
                                        id="addcourse-file-input"
                                        className={`addcourse-file-input`}
                                        onChange={(e) => setFile(e.target.files[0])}
                                        required
                                    />
                                </label>
                            </div>

                            <div className="sem-year--additional-instructor--container">
                                {/* Dropdown for selecting the course's semester and year. */}
                                <div className="form__item form__item--select">
                                    <label className="form__item--label" htmlFor="semester">
                                        Course Semester and Year
                                    </label>
                                    <select
                                        value={`${semester}_${year}`}
                                        className="add-course--select"
                                        onChange={handleSemesterChange}
                                        id="semester"
                                        name="semester"
                                        required
                                    >
                                        {getFutureSemesters().map((sem) => {
                                            return (
                                                <option
                                                    key={sem.value}
                                                    value={sem.value}
                                                    selected={sem.value === `${semester}_${year}`}
                                                >
                                                    {sem.text}
                                                </option>
                                            );
                                        })}
                                    </select>
                                </div>

                                {/* Select component for choosing additional instructors. */}
                                <div className="form__item additional-instructors--item">
                                    <label className="form__item--label" htmlFor="additional-instructors">
                                        Additional Instructor(s)
                                    </label>
                                    <Select
                                        id="additional-instructors"
                                        multiple
                                        options={allInstructors}
                                        value={instructors}
                                        onChange={(o) => setInstructors(o)}
                                    />
                                </div>
                            </div>
                        </div>

                        {/* Submission button for the form. */}
                        <div className="add-form__submit--container">
                            <button type="submit" className="form__submit">
                                + Add Course
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {/* Conditional rendering of a modal dialog for roster file errors. */}
            {showFileErrorModal && (
                <ErrorsModal
                    modalClose={fileErrorModalClose}
                    error_type={"Roster File"}
                    errors={rosterFileError}/>
            )}
        </>
    );
};

export default AddCourse;

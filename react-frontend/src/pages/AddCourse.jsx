import React, { useState, useEffect } from "react";
import "../styles/addcourse.css";
import "../styles/modal.css";
import "../styles/course.css";
import { Select } from "../Components/Select";

const AddCourse = ({ handleAddCourseModal, getCourses }) => {
  const [courseCode, setCourseCode] = useState("");
  const [courseName, setCourseName] = useState("");
  const [file, setFile] = useState(null);
  const [semester, setSemester] = useState("");
  const [year, setYear] = useState(null);
  const [formErrors, setFormErrors] = useState({});
  const [showModal, setShowModal] = useState(false);
  const [instructors, setInstructors] = useState([]); // array of instructor objects selected including their id and name
  const [allInstructors, setAllInstructors] = useState([]); // array of all instructors in the database
  const [selectedInstructors, setSelectedInstructors] = useState([]); // array of selected instructor ids to send to backend
  const formData = new FormData();

  const getCurrentYear = () => {
    const date = new Date();
    return date.getFullYear();
  };

  // Using 2023-2024 course schedule
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

    // Fall Semester (Aug 28 to Dec 20)
    if (
      (month === 7 && day >= 28) ||
      (month > 7 && month < 11) ||
      (month === 11 && day <= 20)
    ) {
      return 4; // Fall
    }

    // Winter Session (Dec 28 to Jan 19)
    if ((month === 11 && day >= 28) || (month === 0 && day <= 19)) {
      return 1; // Winter
    }

    // If none of the above conditions are met, it must be Spring (Jan 24 to May 19)
    return 2; // Spring
  };

  const getFutureSemesters = () => {
    const date = new Date();
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

  const futureSemesters = getFutureSemesters();
  const [semesters, setSemesters] = useState(futureSemesters);

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
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
    })
      .then((res) => res.json())
      .then((result) => {
        let fetchedInstructors = [];
        result.map((instructor) => {
          let currentInstructor = {
            label: instructor[1],
            value: instructor[0],
          };
          fetchedInstructors.push(currentInstructor);
        });
        setAllInstructors(fetchedInstructors);
      })
      .catch((err) => {
        console.log(err);
      });
  }, []);

  // Everytime an instructor is selected/deselected the selectedInstructors state updates
  useEffect(() => {
    let instructorIds = [];
    instructors.map((instructor) => {
      instructorIds.push(+instructor.value);
    });
    setSelectedInstructors(instructorIds);
  }, [instructors]);

  const handleSemesterChange = (e) => {
    const selectedValue = e.target.value; // For example, "fall_2024"
    const [newSemester, newYear] = selectedValue.split("_"); // Splits to ["fall", "2024"]

    // Update the states
    setSemester(newSemester);
    setYear(parseInt(newYear));
  };

  const handleSubmit = (e) => {
    e.preventDefault();

    formData.append("course-code", courseCode);
    formData.append("course-name", courseName);
    formData.append("course-year", year);
    formData.append("roster-file", file); // Assuming `file` is a File object
    formData.append("semester", semester);
    formData.append("additional-instructors", selectedInstructors);

    fetch(process.env.REACT_APP_API_URL + "courseAdd.php", {
      method: "POST",
      body: formData,
    })
      .then((res) => res.text())
      .then((result) => {
        if (typeof result === "string" && result !== "") {
          try {
            const parsedResult = JSON.parse(result);
            if (parsedResult.hasOwnProperty("roster-file")) {
              if (
                parsedResult["roster-file"].includes(
                  "does not contain an email, first name, and last name"
                )
              ) {
                parsedResult["roster-file"] =
                  "Make sure each row contains an email in the first column, first name in the second column, and last name in the third column";
              }
            }
            setFormErrors(parsedResult);
            setShowModal(true);
          } catch (e) {
            console.log("Failed to parse JSON: ", e);
          }
        } else {
          // Class is valid, so we can just navigate to the home page
          handleAddCourseModal();
          getCourses();
        }
      })
      .catch((err) => {
        console.log(err);
      });
  };

  const handleModalClose = () => {
    setShowModal(false); // Close the modal
  };

  return (
    <>
      <form onSubmit={handleSubmit} encType="multipart/form-data">
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
            <h2 style={{ color: "#225cb5" }}>Add Course</h2>
          </div>

          <div className="input-wrapper1">
            <label style={{ color: "#225cb5" }}>Course Code</label>
            <input
              type="text"
              id="course-code"
              value={courseCode}
              onChange={(e) => setCourseCode(e.target.value)}
              placeholder="CSE 115"
              className="styled-input"
              required
            />
          </div>

          <div className="input-wrapper1">
            <label style={{ color: "#225cb5" }}>Course Name</label>
            <input
              type="text"
              id="course-name"
              value={courseName}
              onChange={(e) => setCourseName(e.target.value)}
              placeholder="Intro. to Computer Science I"
              className="styled-input"
              required
            />
          </div>

          <div className="input-wrapper">
            <label style={{ color: "#225cb5" }}>Course Semester and Year</label>
            <select
              value={`${semester}_${year}`}
              className="styled-input"
              onChange={handleSemesterChange}
              name="semester"
              id="semester"
              style={{ color: "black" }}
              required
            >
              {semesters.map((sem) => {
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

          <div className="input-wrapper">
            <label style={{ color: "#225cb5" }}>Additional Instructor(s)</label>
            <Select
              multiple
              options={allInstructors}
              value={instructors}
              onChange={(o) => setInstructors(o)}
            />
          </div>

          <div className="input-wrapper1">
            <label style={{ color: "#225cb5 " }}>
              Roster (CSV File) - Requires Emails in Columns 1, First Names in
              Columns 2 and Last Names in Columns 3
            </label>
            <input
              type="file"
              id="file-input"
              className="styled-input"
              onChange={(e) => setFile(e.target.files[0])}
              required
            />
          </div>
        </div>
        {/* Buttons */}
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
            type="submit"
          >
            + Add Course
          </button>
        </div>
      </form>
      {/* Error Modal */}
      {showModal && (
        <div className="modal">
          <div className="modal-content">
            <h2>Error(s)</h2>
            {Object.keys(formErrors).map((key) => (
              <p>{formErrors[key]}</p>
            ))}
            <button onClick={handleModalClose}>OK</button>
          </div>
        </div>
      )}
    </>
  );
};

export default AddCourse;

import React, { useState, useEffect } from "react";
import { useNavigate } from "react-router-dom";
import SideBar from "../Components/Sidebar";
import "../styles/addcourse.css";

const AddCourse = () => {
  const [courses, setCourses] = useState([]);
  const [courseCode, setCourseCode] = useState("");
  const [courseName, setCourseName] = useState("");
  const [file, setFile] = useState(null);
  const [semester, setSemester] = useState("");
  const [year, setYear] = useState(null);
  const [formErrors, setFormErrors] = useState({});
  const formData = new FormData();
  const navigate = useNavigate();
  const semesters = [
    {
      value: "winter",
      text: "Winter",
    },
    {
      value: "spring",
      text: "Spring",
    },
    {
      value: "summer",
      text: "Summer",
    },
    {
      value: "fall",
      text: "Fall",
    },
  ];

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

  // fetch the courses to display on the sidebar
  useEffect(() => {
    fetch(
      "http://localhost/StudentSurvey/backend/instructor/instructorCoursesInTerm.php",
      {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: new URLSearchParams({
          semester: getCurrentSemester(),
          year: getCurrentYear(),
        }),
      }
    )
      .then((res) => res.json())
      .then((result) => {
        setCourses(result);
      })
      .catch((err) => {
        console.log(err);
      });
    setYear(getCurrentYear());
    let currSem = getCurrentSemester();
    if (currSem === 1) setSemester("winter");
    if (currSem === 2) setSemester("spring");
    if (currSem === 3) setSemester("summer");
    if (currSem === 4) setSemester("fall");
  }, []);

  const sidebar_content = {
    Courses: courses ? courses.map((course) => course.code) : [],
  };

  const handleSubmit = (e) => {
    e.preventDefault();

    formData.append("course-code", courseCode);
    formData.append("course-name", courseName);
    formData.append("course-year", year);
    formData.append("roster-file", file); // Assuming `file` is a File object
    formData.append("semester", semester);

    fetch("http://localhost/StudentSurvey/backend/instructor/courseAdd.php", {
      method: "POST",
      body: formData,
    })
      .then((res) => {
        if (
          res.headers.get("content-type") === "application/json; charset=utf-8"
        ) {
          return res.json();
        } else {
          return res.text();
        }
      })
      .then((result) => {
        if (typeof result === "string") {
          try {
            const parsedResult = JSON.parse(result);
            console.log("Parsed as JSON object: ", parsedResult);
            setFormErrors(parsedResult);
          } catch (e) {
            console.log("Failed to parse JSON: ", e);
          }
        } else {
          // Class is valid, so we can just navigate to the home page
          navigate("/");
        }
      })
      .catch((err) => {
        console.log(err);
      });
  };

  return (
    <>
      <SideBar route="/" content_dictionary={sidebar_content} />
      <div className="container">
        <div className="formContainer">
          <div className="formContent">
            <div className="formHeader">
              <h2 className="add-header">Add Course</h2>
            </div>
            <form
              className="add__form"
              onSubmit={handleSubmit}
              encType="multipart/form-data"
            >
              {formErrors["duplicate"] && (
                <div className="add_course--error">
                  {formErrors["duplicate"]}
                </div>
              )}
              <div className="form__item">
                <label className="form__item--label">Course Code</label>
                <input
                  type="text"
                  id="course-code"
                  value={courseCode}
                  onChange={(e) => setCourseCode(e.target.value)}
                  placeholder="CSE115"
                  required
                />
                {formErrors["course-code"] && (
                  <div className="add_course--error">
                    {formErrors["course-code"]}
                  </div>
                )}
              </div>

              <div className="form__item">
                <label className="form__item--label">Course Name</label>
                <input
                  type="text"
                  id="course-name"
                  value={courseName}
                  onChange={(e) => setCourseName(e.target.value)}
                  placeholder="Introduction to Computer Science"
                  required
                />
                {formErrors["course-name"] && (
                  <div className="add_course--error">
                    {formErrors["course-name"]}
                  </div>
                )}
              </div>

              <div className="form__item file-input-wrapper">
                <label className="form__item--label form__item--file">
                  Roster (CSV File) - Requires Emails in Columns 1, First Names
                  in Columns 2 and Last Names in Columns 3
                </label>
                <div>
                  <input
                    type="file"
                    id="file-input"
                    className="file-input"
                    onChange={(e) => setFile(e.target.files[0])}
                    required
                  />
                  <label className="custom-file-label" htmlFor="file-input">
                    Choose File
                  </label>
                  <span className="selected-filename">
                    {file ? file.name : "No file chosen"}
                  </span>
                </div>
                {formErrors["roster-file"] && (
                  <div className="add_course--error">
                    {formErrors["roster-file"]}
                  </div>
                )}
              </div>

              <div className="form__year-sem--container">
                <div className="form__item form__item--select">
                  <label className="form__item--label">Course Semester</label>
                  <select
                    value={semester}
                    className="add-course--select"
                    onChange={(e) => setSemester(e.target.value)}
                    id="semester"
                    name="semester"
                    required
                  >
                    {semesters.map((sem) => {
                      return (
                        <option
                          key={sem.value}
                          value={sem.value}
                          selected={sem.value === semester}
                        >
                          {sem.text}
                        </option>
                      );
                    })}
                  </select>
                  {formErrors["semester"] && (
                    <div className="add_course--error">
                      {formErrors["semester"]}
                    </div>
                  )}
                </div>

                <div className="form__item form__item--year">
                  <label className="form__item--label">Course Year</label>
                  <input
                    type="number"
                    name="course-year"
                    id="course-year"
                    placeholder={year}
                    value={year}
                    onChange={(e) => setYear(e.target.value)}
                    required
                  />
                  {formErrors["course-year"] && (
                    <div className="add_course--error">
                      {formErrors["course-year"]}
                    </div>
                  )}
                </div>
              </div>

              <div className="form__submit--container">
                <button type="submit" className="form__submit">
                  + Add Course
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </>
  );
};

export default AddCourse;
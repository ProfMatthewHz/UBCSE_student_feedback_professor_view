import React, { useState, useEffect } from "react";
import SideBar from "../Components/Sidebar";
import "../styles/addcourse.css";

const AddCourse = () => {
  const [courses, setCourses] = useState([]);
  const [courseCode, setCourseCode] = useState("");
  const [courseName, setCourseName] = useState("");
  const [file, setFile] = useState(null);
  const [semester, setSemester] = useState("");
  const [year, setYear] = useState(null);

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
              </div>

              <div className="form__item file-input-wrapper">
                <label className="form__item--label form__item--file">
                  Roster (CSV File) - Requires Names in Column 1 and Emails in
                  Column 2
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
                    <option value="fall">Fall</option>
                    <option value="spring">Spring</option>
                    <option value="winter">Winter</option>
                    <option value="summer">Summer</option>
                  </select>
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
                </div>
              </div>

              <input type="hidden" name="csrf-token" value="" />

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

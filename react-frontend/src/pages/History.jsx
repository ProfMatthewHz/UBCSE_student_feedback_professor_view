import React, {useState, useEffect} from "react";
import SideBar from "../Components/Sidebar";
import Course from "../Components/Course";
import "../styles/home.css";

/**
 * The History component displays a historical list of courses based on term selection.
 * It allows users to view courses they have been involved with in past semesters.
 */

const History = () => {
    const [terms, setTerms] = useState({}); // State for storing terms and associated courses
    const [currentTerm, setCurrentTerm] = useState(''); // State to track the currently selected term
    const [sidebar_content, setSidebarContent] = useState({}); // State to store the content for the Sidebar component

    /**
     * Updates the currently selected term.
     * @param {string} newValue The new term value to set.
     */
    const updateCurrentTerm = (newValue) => {
        setCurrentTerm(newValue)
    }

    /**
     * Converts semester names to their corresponding integer codes.
     * @param {string} semester The name of the semester.
     * @returns {number} The integer code of the semester.
     */
    const getSemesterAsInt = (semester) => {
        if (semester === 'Fall') {
            return 4;
        } else if (semester === 'Summer') {
            return 3;
        } else if (semester === 'Spring') {
            return 2;
        } else {
            return 1; // winter
        }
    }

    /**
     * This useEffect hook is triggered on component mount due to the empty dependency array ([]).
     * Its primary role is to fetch historical terms and the courses associated with each term from an API.
     */
    useEffect(() => {
        // First, a fetch request is made to retrieve the terms (e.g., Fall 2023, Spring 2024) for which the instructor has courses.
        fetch(
            process.env.REACT_APP_API_URL + "getInstructorHistoricalTerms.php",
            {
                method: "GET",
                credentials: "include"
            }
        )
            .then((res) => res.json()) // Parsing the response to JSON format.
            .then((result) => { // Handling the parsed JSON data.
                const all_courses = {} // An object to store courses grouped by their terms.
                const sidebar_data = {} // An object mapping terms to the names of courses in that term
                // Mapping through each term received from the first API call to fetch courses for those terms.
                const fetchCourses = result.map((term) => {
                    // Constructing a key for each term combining its name and year for easy identification and storage.
                    const term_key = term.semester + " " + term.year
                    all_courses[term_key] = []
                    sidebar_data[term_key] = []
                    const formData = new FormData();
                    formData.append("semester", getSemesterAsInt(term.semester));
                    formData.append("year", parseInt(term.year));
                    return fetch(
                        process.env.REACT_APP_API_URL + "getInstructorCoursesInTerm.php",
                        {
                            method: "POST",
                            credentials: "include",
                            body: formData
                        }
                    )
                        .then((res2) => res2.json())
                        .then((result2) => {
                            all_courses[term_key].push(...result2)
                            sidebar_data[term_key] = result2.map((course) => course.code)
                        })
                        .catch(err => {
                            console.log(err)
                        })

                });
                // Create a single promise that resolves only after each term is completed
                Promise.all(fetchCourses)
                    .then(() => {
                        setTerms(all_courses)
                        setSidebarContent(sidebar_data);
                    })
                    .catch(err => {
                        console.log(err);
                    });


            })
            .catch(err => {
              if (err.name === 'TypeError') {
                // If we get a TypeError, we have been logged out of shibboleth and need to redirect to the starting page
                window.location.href = `${process.env.REACT_APP_API_START}`;
              }
              console.log(err)
            })
    }, []);

  return (
    <>
      <SideBar route="/history" content_dictionary={sidebar_content} currentTerm={currentTerm} updateCurrentTerm={updateCurrentTerm}/>
      <div className="home--container">
        {currentTerm !== "" && Object.entries(terms).length > 0 ? (
          Object.entries(terms).map(([term, courses]) => (
            term === currentTerm ? (
              <div key={term} className="containerOfCourses">

                <div className="yes-course">
                      <h1>{currentTerm}</h1>
                    </div>


                {courses.length > 0 ? (
                  courses.map((course) => (
                    <Course key={course.id} course={course} page="history" />
                  ))
                )
                 : (
                  <div className="no-course">
                    <h1>No Courses Found</h1>
                  </div>
                )}
              </div>
            ) : 
            null
          ))
        ) : (
          <div className="termContainer">
            <div className="termContent">
              {currentTerm === "" && Object.entries(terms).length > 0 ? <h1>No Terms Selected</h1> : <h1>No Terms Found</h1>}
            </div>
          </div>
        )}
      </div>
    </>
  );
};

export default History;
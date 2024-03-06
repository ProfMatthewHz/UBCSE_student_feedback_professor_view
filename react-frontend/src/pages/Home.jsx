import React, {useEffect, useState} from "react";
import SideBar from "../Components/Sidebar";
import "../styles/home.css";
import "../styles/course.css";
import Modal from "../Components/RubricModal";
import Course from "../Components/Course";

/**
 * The Home component is the main component that users see when they visit the home page.
 * It displays a list of courses for the current semester and year, fetched from an external API.
 */
const Home = () => {
  // State to store the list of courses
  const [coursesCurrent, setCoursesCurrent] = useState([]);
  const [courses, setCourses] = useState([]);
  const [coursesPast, setCoursesPast] = useState([]);
  const [coursesFuture, setCoursesFuture] = useState([]);
  const [openModal,setOpenModal] = useState(false);

  const getCurrentYear = () => {
    const date = new Date();
    return date.getFullYear();
  };

  /**
   * Determines the current semester based on the current date.
   * Semesters are determined by specific date ranges within the year.
   * @returns {number} The current semester encoded as an integer (1 for Winter, 2 for Spring, 3 for Summer, 4 for Fall).
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

  /**
   * Fetches the list of courses for the current semester and year from an external API.
    */
  const fetchCourses = () => {
    fetch(
        process.env.REACT_APP_API_URL + "instructorCoursesInTerm.php",
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
  };
/** */
  // Fetch courses when the component mounts
  useEffect(() => {
    fetchCourses()
  }, []);

    const fetchCurrent = () => {
        // Adjust the URL to point to your surveys endpoint and include the survey type query parameter
        const url = `${process.env.REACT_APP_API_URL}endpoints.php?type=current`;

        fetch(url, {
            method: "GET",
            // Note: Removed the 'Content-Type' header and 'body' since it's a GET request
        })
            .then((res) => res.json())
            .then((result) => {
                // Assuming you have a way to set the surveys in your state or UI, similar to how courses were set
                setCoursesCurrent(result); // Consider renaming this function to reflect that it now sets surveys, not courses
            })
            .catch((err) => {
                console.log(err);
            });
    };

    /** */
    // Fetch courses when the component mounts
    useEffect(() => {
        fetchCurrent()
    }, []);

    console.log("CURRENT");
    console.log(coursesCurrent);





    //past evals
    const fetchPast = () => {
        // Adjust the URL to point to your surveys endpoint and include the survey type query parameter
        const url = `${process.env.REACT_APP_API_URL}endpoints.php?type=past`;

        fetch(url, {
            method: "GET",
            // Note: Removed the 'Content-Type' header and 'body' since it's a GET request
        })
            .then((res) => res.json())
            .then((result) => {
                // Assuming you have a way to set the surveys in your state or UI, similar to how courses were set
                setCoursesPast(result); // Consider renaming this function to reflect that it now sets surveys, not courses
            })
            .catch((err) => {
                console.log(err);
            });
    };

    /** */
    // Fetch courses when the component mounts
    useEffect(() => {
        fetchPast()
    }, []);

    console.log("PAST");
    console.log(coursesPast);


    const fetchFuture = () => {
        // Adjust the URL to point to your surveys endpoint and include the survey type query parameter
        const url = `${process.env.REACT_APP_API_URL}endpoints.php?type=upcoming`;

        fetch(url, {
            method: "GET",
            // Note: Removed the 'Content-Type' header and 'body' since it's a GET request
        })
            .then((res) => res.json())
            .then((result) => {
                // Assuming you have a way to set the surveys in your state or UI, similar to how courses were set
                setCoursesFuture(result); // Consider renaming this function to reflect that it now sets surveys, not courses
            })
            .catch((err) => {
                console.log(err);
            });
    };

    /** */
    // Fetch courses when the component mounts
    useEffect(() => {
        fetchFuture()
    }, []);

    console.log("FUTURE");
    console.log(coursesFuture);



  // Preparing content for the Sidebar component
  const sidebar_content = {
    Courses: courses ? courses.map((course) => course.code) : [],
  };

  const [surveys, setSurveys] = useState([]);

  /**
   * Perform a POST call to courseSurveysQueries, or where ever to find students info
   */
  function updateAllSurveys() {
      fetch(process.env.REACT_APP_API_URL + "courseSurveysQueries.php", { // TODO: need to update the api url
          method: "POST",
          headers: {
              "Content-Type": "application/x-www-form-urlencoded",
          }
      })
          .then((res) => res.json())
          .then((result) => {
              console.log(result);
              const activeSurveys = result.active.map((survey_info) => ({
                  ...survey_info,
                  expired: false,
              }));
              console.log(result);
              const expiredSurveys = result.expired.map((survey_info) => ({
                  ...survey_info,
                  expired: true,
              }));
              const upcomingSurveys = result.upcoming.map((survey_info) => ({
                  ...survey_info,
                  expired: false,
              }));
              console.log(result);

              setSurveys([...activeSurveys, ...expiredSurveys, ...upcomingSurveys]);
          })
          .catch((err) => {
              console.log(err);
          });
  }




  /**
   * The Home component renders a SideBar component and a list of Course components.
   */
  return (
    <>
    <Modal open={openModal} onClose={()=>setOpenModal(false)}/>
      <SideBar route="/" content_dictionary={sidebar_content} getCourses={fetchCourses} />
      <div className="home--container">
        <div className="containerOfCourses">
          <div id="Open Surveys" class="courseContainer">
          <div className="courseContent">
                <div className="courseHeader">
                    <h2>
                        Open Surveys
                    </h2>
                </div>
                <table className="surveyTable">
                    <thead>
                    <tr>
                        <th>Survey Closes</th>
                        <th>Course Name</th>
                        <th>Survey Name</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>
                            <tr className="survey-row" >
                               
                                  <td>
                                  <div className="warning">
                                    2/25/2024
                                    <br/>
                                    11:59PM
                                    </div>
                                  </td>
                                
                                <td>CSE 115 </td>
                                <td>ADEPT Evaluation</td>
                                <td>More Responses Needed</td>
                                <td><button><div className="openSurveyButton">Start</div></button></td>
                            </tr>

                            <tr className="survey-row" >
                                  <td>
                                    2/26/2024
                                    <br/>
                                    11:59PM
                                  </td>
                                <td>CSE 444 </td>
                                <td>Miss Claus Evaluation</td>
                                <td>100%</td>
                                <td><button><div className="openSurveyButton">Revise</div></button></td>
                            </tr>

                            <tr className="survey-row" >
                                  <td>
                                    2/29/2024
                                    <br/>
                                    11:59PM
                                  </td>
                                <td>CSE 404 </td>
                                <td>PM Evaluation</td>
                                <td>91%</td>
                                <td><button><div className="openSurveyButton">Continue</div></button></td>
                            </tr>
                       
                        </tbody>



                </table>
                
                
            </div>
          </div>

          <div id="Future Surveys" class="courseContainer">
          <div className="courseContent">
                <div className="courseHeader">
                    <h2>
                        Future Surveys
                    </h2>
                </div>
                <table className="surveyTable">
                    <thead>
                    <tr>
                        <th>Survey Closes</th>
                        <th>Course Name</th>
                        <th>Survey Name</th>
                  
                    </tr>
                    </thead>
                    <tr className="survey-row" >
                        <td>
                          3/26/202
                          <br/>
                          11:59PM
                        </td>
                      <td>CSE 444 </td>
                      <td>Miss Claus Evaluation</td>
                   </tr>

                   <tr className="survey-row" >
                        <td>
                          4/26/202
                          <br/>
                          11:59PM
                        </td>
                      <td>CSE 777 </td>
                      <td>Miss Claus Evaluation</td>
                   </tr>

                </table>
                
                
            </div>
          </div>


          <div id="Closed Surveys" class="courseContainer">
          <div className="courseContent">
                <div className="courseHeader">
                    <h2>
                        Closed Surveys
                    </h2>
                </div>
                <table className="surveyTable">
                    <thead>
                    <tr>
                        <th>Survey Closed</th>
                        <th>Course Name</th>
                        <th>Survey Name</th>
                        <th>Submission</th>
                        <th>Feedback</th>
                    </tr>
                    </thead>

                    <tbody>

                    <tr className="survey-row" >
                          <td>
                            1/26/2024
                            <br/>
                            11:59PM
                          </td>
                        <td>CSE 444 </td>
                        <td>Something Evaluation</td>
                        <td><button>View Submission</button></td>
          
                        <td><button onClick={()=>setOpenModal(true)}>View Feedback</button> </td>
                        
                    </tr>
                        </tbody>

                        <tr className="survey-row" >
                          <td>
                            1/23/2024
                            <br/>
                            11:59PM
                          </td>
                        <td>CSE 111 </td>
                        <td>Another Evaluation</td>
                        <td><button>View Submission</button></td>
                        <td><button>View Feedback</button></td>
                    </tr>
                        

                    <tr className="survey-row" >
                          <td>
                            1/22/2024
                            <br/>
                            11:59PM
                          </td>
                        <td>CSE 111 </td>
                        <td>Santa Evaluation</td>
                        <td><button>View Submission</button></td>
                        <td><button>View Feedback</button></td>
                    </tr>
                        

                </table>
                
            </div>
            
          </div>



        </div>
      </div>
    </>
  );
};

export default Home;

import React, {useEffect, useState} from "react";
import SideBar from "../Components/studentSidebar";
import "../styles/home.css";
import "../styles/rubricCourse.css";
import Modal from "../Components/RubricModal";


/**
 * The Home component is the main component that users see when they visit the home page.
 * It displays a list of courses for the current semester and year, fetched from an external API.
 */
const StudentHome = () => {
  // State to store the list of courses
  const [rubricCurrent, setRubricCurrent] = useState([]);
  const [rubricPast, setRubricPast] = useState([]);
  const [rubricFuture, setRubricFuture] = useState([]);
  const [openModal,setOpenModal] = useState(false);
  


  const reformatTime = (data) => {
    const dataList = data.split(' ');
    const timeString = dataList[1];
    const date = new Date(`1970-01-01T${timeString}Z`);
    const formattedTime = date.toLocaleTimeString('en-US', {
      hour12: true,
      hour: 'numeric',
      minute: '2-digit',
      second: '2-digit',
    });

    return formattedTime;
  };

  const reformatDate = (data) => {
    const dataList = data.split(' ');
    const originalDate= dataList[0];
    const dateParts = originalDate.split('-');
    const year = dateParts[0];
    const month = dateParts[1];
    const day = dateParts[2];
    // Create a new Date object with the parts
    const reformattedDate = new Date(`${month}/${day}/${year}`);

    // Use the toLocaleDateString method to format the date as MM/DD/YYYY
    const formattedDateString = reformattedDate.toLocaleDateString('en-US');

    return formattedDateString;
  }

  const sortByDate= (dataArray) => {
    return [...dataArray].sort((a, b) => {
      const dateA = new Date(a.openingDate.date);
      const dateB = new Date(b.openingDate.date);
      return dateA - dateB;
    });
  };

  const openChooseAction = (amtCompleted) => {
    if (amtCompleted === 0) {
      return "Start";
    } else if (amtCompleted === 100) {
      return "Revise";
    } else {
      return "Continue";
    }

  }

/**  Checks how far away the due date is from the current date.
* Return 0 if due date > 3 days away
* Return 1 if due date < 3 days away
*/
  const dateWarning = (date) => {
    
      const currentDate = new Date();
      const inputDate = new Date(date);
      const threeDaysAhead = new Date();
      threeDaysAhead.setDate(currentDate.getDate() + 3);
    
      const result = inputDate <= threeDaysAhead ? 1 : 0;
    
      return result;
    
  }
    const fetchCurrent = () => {
        // Adjust the URL to point to your surveys endpoint and include the survey type query parameter
        const url = `${process.env.REACT_APP_API_URL_STUDENT}endpoints.php?type=current`;

        fetch(url, {
            method: "GET",
            // Note: Removed the 'Content-Type' header and 'body' since it's a GET request
        })
            .then((res) => res.json())
            .then((result) => {
                // Assuming you have a way to set the surveys in your state or UI, similar to how courses were set
                setRubricCurrent(result); // Consider renaming this function to reflect that it now sets surveys, not courses
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

    // console.log("CURRENT");
    // console.log(rubricCurrent);
  
    //past evals
    const fetchPast = () => {
        // Adjust the URL to point to your surveys endpoint and include the survey type query parameter
        const url = `${process.env.REACT_APP_API_URL_STUDENT}endpoints.php?type=past`;

        fetch(url, {
            method: "GET",
            // Note: Removed the 'Content-Type' header and 'body' since it's a GET request
        })
            .then((res) => res.json())
            .then((result) => {
                // Assuming you have a way to set the surveys in your state or UI, similar to how courses were set
                setRubricPast(result); // Consider renaming this function to reflect that it now sets surveys, not courses
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

    // console.log("PAST");
    // console.log(rubricPast);


    const fetchFuture = () => {
        // Adjust the URL to point to your surveys endpoint and include the survey type query parameter
        const url = `${process.env.REACT_APP_API_URL_STUDENT}endpoints.php?type=upcoming`;

        fetch(url, {
            method: "GET",
            // Note: Removed the 'Content-Type' header and 'body' since it's a GET request
        })
            .then((res) => res.json())
            .then((result) => {
                // Assuming you have a way to set the surveys in your state or UI, similar to how courses were set
                setRubricFuture(result); // Consider renaming this function to reflect that it now sets surveys, not courses
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

    // console.log("FUTURE");
  
    // console.log(rubricFuture);


    const handleButtonClick = () => {
    // Assuming sendPostRequest is a function to handle the POST request
      console.log("View Feedback Clicked");
     }

    const combinedClickHandler = () => {
      handleButtonClick();
      setOpenModal(true);
    };

    const tempCurrentData = [
      {"courseName": "Computer Security","openingDate": {"date": "2024-3-10 01:58:06.000000", "timezone_type": 3, "timezone": "Europe/Berlin"},"surveyID": 23,"surveyName": "Dummy Name 5","completeRate":0},
      { "courseName": "Algorithms and Complexity","openingDate": {"date": "2024-3-21 04:25:09.000000", "timezone_type": 3, "timezone": "Europe/Berlin"}, "surveyID": 27, "surveyName": "Dummy Name 1","completeRate":100},{
        "courseName": "Software Project Managment", "openingDate": {"date": "2024-3-22 00:43:04.000000", "timezone_type": 3, "timezone": "Europe/Berlin"}, "surveyID": 19,"surveyName": "CSE 404 #2","completeRate":13
      }
    ];
   
    const tempPastData = [
      {"courseName": "Computer Security","openingDate": {"date": "2024-1-23 01:58:06.000000", "timezone_type": 3, "timezone": "Europe/Berlin"},"surveyID": 23,"surveyName": "Dummy Name 5"},
      { "courseName": "Algorithms and Complexity","openingDate": {"date": "2024-2-27 04:25:09.000000", "timezone_type": 3, "timezone": "Europe/Berlin"}, "surveyID": 27, "surveyName": "Dummy Name 1"},{
        "courseName": "Software Project Managment", "openingDate": {"date": "2024-2-28 00:43:04.000000", "timezone_type": 3, "timezone": "Europe/Berlin"}, "surveyID": 19,"surveyName": "CSE 404 #2"
      }
    ];
 


  



  /**
   * The Home component renders a SideBar component and a list of Course components.
   */
  return (
    <>
    <Modal open={openModal} onClose={()=>setOpenModal(false)}/>
      <SideBar route="/" content_dictionary={rubricCurrent} getCourses={fetchCurrent} />
      <div className="home--container">
        <div className="containerOfCourses">
          <div id="Open Surveys" class="courseContainer">
          <div className="courseContent">
                <div className="courseHeader">
                    <h2>
                        Open Surveys
                    </h2>
                </div>
                {tempCurrentData.length > 1 ? (
                        <table className="surveyTable">
                          <thead>
                            <tr>
                              <th>Survey Closes</th>
                              <th>Course Name</th>
                              <th>Survey Name</th>
                              <th>Completion Rate</th>
                              <th>Action</th>


                            </tr>
                          </thead>

                          <tbody>
                            {sortByDate(tempCurrentData).map((item, index) => (
                              <tr key={index} className="survey-row">

                                {/* if the date is < 3 days away, make the text red */}
                                {dateWarning(item.openingDate.date) > 0?(
                                    <td><div className="warning">
                                    {reformatDate(item.openingDate.date)}
                                    <br />
                                    {reformatTime(item.openingDate.date)}
                                    </div> </td>


                                  ):(
                                    <td>
                                    {reformatDate(item.openingDate.date)}
                                    <br />
                                    {reformatTime(item.openingDate.date)}
                                  </td>
                                ) }
                                
                                <td>{item.courseName}</td>
                                <td>{item.surveyName}</td>
                                <td>{item.completeRate}% Completed</td>
                                <td><button>{openChooseAction(item.completeRate)}</button></td>
                              </tr>
                            ))}
                          </tbody>
                        </table>
                      ) : (
                        <table className="surveyTable">
                        <thead>
                          <tr>
                            <th>No Open Surveys</th>
                            
                          </tr>
                        </thead>
                        </table>
                      )}
                
                
            </div>
          </div>

          <div id="Future Surveys" class="courseContainer">
          <div className="courseContent">
                <div className="courseHeader">
                    <h2>
                        Future Surveys
                    </h2>
                </div>
                 {rubricFuture.length > 1 ? (
                        <table className="surveyTable">
                          <thead>
                            <tr>
                              <th>Survey Closes</th>
                              <th>Course Name</th>
                              <th>Survey Name</th>
                            </tr>
                          </thead>

                          <tbody>
                            {sortByDate(rubricFuture).map((item, index) => (
                              <tr key={index} className="survey-row">
                                <td>
                                  {reformatDate(item.openingDate.date)}
                                  <br />
                                  {reformatTime(item.openingDate.date)}
                                </td>
                                <td>{item.courseName}</td>
                                <td>{item.surveyName}</td>
                              
                              </tr>
                            ))}
                          </tbody>
                        </table>
                      ) : (
                        <table className="surveyTable">
                        <thead>
                          <tr>
                            <th>No Future Surveys</th>
                            
                          </tr>
                        </thead>
                        </table>
                      )}
            </div>
          </div>


          <div id="Closed Surveys" class="courseContainer">
          <div className="courseContent">
                <div className="courseHeader">
                    <h2>
                        Closed Surveys
                    </h2>
                </div>
               
                

                {tempPastData.length > 1 ? (
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
                            {sortByDate(tempPastData).map((item, index) => (
                              <tr key={index} className="survey-row">
                                <td>
                                  {reformatDate(item.openingDate.date)}
                                  <br />
                                  {reformatTime(item.openingDate.date)}
                                </td>
                                <td>{item.courseName}</td>
                                <td>{item.surveyName}</td>
                                <td><button>View Submission</button></td>
                               <td><button onClick={combinedClickHandler}>View Feedback</button></td>
                              </tr>
                            ))}
                          </tbody>
                        </table>
                      ) : (
                        <table className="surveyTable">
                        <thead>
                          <tr>
                            <th>No Closed Surveys</th>
                            
                          </tr>
                        </thead>
                        </table>
                      )}



            </div>
            
          </div>



        </div>
      </div>
    </>
  );
};

export default StudentHome;

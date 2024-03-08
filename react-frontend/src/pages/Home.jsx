import React, {useEffect, useState} from "react";
import SideBar from "../Components/Sidebar";
import "../styles/home.css";
import "../styles/course.css";
import Modal from "../Components/RubricModal";


/**
 * The Home component is the main component that users see when they visit the home page.
 * It displays a list of courses for the current semester and year, fetched from an external API.
 */
const Home = () => {
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

    console.log("CURRENT");
    console.log(rubricCurrent);
    




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

    console.log("PAST");
    console.log(rubricPast);


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

    console.log("FUTURE");
  
    console.log(rubricFuture);


   
 


  



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
                {rubricCurrent.length > 1 ? (
                        <table className="surveyTable">
                          <thead>
                            <tr>
                              <th>Survey Closes</th>
                              <th>Course Name</th>
                              <th>Survey Name</th>
                            </tr>
                          </thead>

                          <tbody>
                            {sortByDate(rubricCurrent).map((item, index) => (
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
               
                

                {rubricPast.length > 1 ? (
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
                            {sortByDate(rubricPast).map((item, index) => (
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

export default Home;

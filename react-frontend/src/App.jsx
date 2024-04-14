import { BrowserRouter as Router, Route, Routes } from "react-router-dom";
import React, {useState, useEffect} from "react";
import Home from "./pages/Home";
import About from "./pages/About";
import History from "./pages/History";
import Library from "./pages/Library";
import StudentHome from "./pages/studentHome";
import ProfStudentHome from "./pages/profStudentHome";
import SurveyForm from "./pages/SurveyForm"
import SurveyPreview from "./pages/SurveyPreview"

function App() {


    const [userFlag, setUserFlag] = useState(1);  // userFlag = 1 -> prof    userFlag = 2 -> student
    const [csrfToken, setCsrfToken] = useState('');
    
   //Find who is logging in and load page accordingly
    const fetchFlag = () => {
      const url = `${process.env.REACT_APP_API_URL_STUDENT}redirectEndpoint.php?`;
      //console.log("Current Url: ", url);
    
      fetch(url, {
          method: "GET",
      })
          .then((res) => res.json())
          .then((result) => {
              setUserFlag(result["redirect"]); 
              
             // console.log("Result Redirect: ", result["redirect"])
              //console.log("User Flag: ", userFlag)
          })
          .catch((err) => {
            console.error('There was a problem with your fetch operation:', err);
          });
  };
  useEffect(() => {
    fetchFlag()
}, []);

//Fetch the randomly generated CSRF token from backend to embed into frontend
const fetchCsrfToken = () => {
    
    const url = `${process.env.REACT_APP_API_URL_STUDENT}unified_fake_Shibboleth.php?`;
        //console.log("Current Url: ", url);
    fetch(url, {
      method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
      if (data) {
        setCsrfToken(data);
        console.log('CSRF Token fetched:', data);
      }
    })
    .catch(error => {
      console.error('Error fetching CSRF token:', error);
    });
  };


useEffect(() => {
  fetchCsrfToken()
}, []);


  

  return (
    <Router basename={process.env.REACT_APP_BASE_URL}>
      <div className="app">
        <div className="background-design"></div>
        <input type="hidden" name="csrfToken" value={csrfToken} />
            <Routes>
            {/* Professor Paths */}
            {userFlag === 1 && (
                <>
                <Route path="/" element={<Home />} />
                <Route path="/library" element={<Library />} />
                <Route path="/history" element={<History />} />
                <Route path="/student" element={<ProfStudentHome />} /> 
                <Route path="/about" element={<About />} />
                <Route path ="/SurveyPreview" element={<SurveyPreview />} />
                </>
            )}

            {/* Student Paths */}
            {userFlag === 2 && (
                <>
                  <Route path="/" element={<StudentHome />} /> 
                  <Route path="/SurveyForm" element={<SurveyForm />} />
                </>
            )}          

            </Routes>    
      </div>
    </Router>

  );
}

export default App;
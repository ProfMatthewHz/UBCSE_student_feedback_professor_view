import { BrowserRouter as Router, Route, Routes } from "react-router-dom";
import React, {useState, useEffect} from "react";
import Home from "./pages/Home";
import About from "./pages/About";
import History from "./pages/History";
import Library from "./pages/Library";
import StudentHome from "./pages/studentHome";
import ProfStudentHome from "./pages/profStudentHome";


function App() {

    const [userFlag, setUserFlag] = useState(1);  // userFlag = 1 -> prof    userFlag = 2 -> student
    
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

  return (
    <Router basename={process.env.REACT_APP_BASE_URL}>
      <div className="app">
        <div className="background-design"></div>
            <Routes>
            {/* Professor Paths */}
            {userFlag === 1 && (
                <>
                <Route path="/" element={<Home />} />
                <Route path="/library" element={<Library />} />
                <Route path="/history" element={<History />} />
                <Route path="/student" element={<ProfStudentHome />} /> 
                <Route path="/about" element={<About />} />
                
                </>
            )}

            {/* Student Paths */}
            {userFlag === 2 && (
                <>
                  <Route path="/" element={<StudentHome />} /> 
                </>
            )}
            </Routes>    
      </div>
    </Router>

  );
}

export default App;
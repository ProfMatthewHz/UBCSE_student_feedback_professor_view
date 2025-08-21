import { BrowserRouter, Route, Routes } from "react-router-dom";
import React, {useState, useEffect, useCallback} from "react";
import Home from "./pages/Home";
import About from "./pages/About";
import History from "./pages/History";
import Library from "./pages/Library";
import StudentHome from "./pages/StudentHome";
import ProfStudentHome from "./pages/ProfStudentHome";

function App() {
    const [userFlag, setUserFlag] = useState(1);  // userFlag = 1 -> prof    userFlag = 2 -> student

    //Find who is logging in and load page accordingly
    const fetchFlag = useCallback(() => {
      const url = `${process.env.REACT_APP_API_URL_STUDENT}redirectEndpoint.php`;
    
      fetch(url, {
          method: "GET",
          credentials: "include",
      })
          .then((response) => {
            if (!response.ok) {
              throw new Error('Network response was not ok');
            }
            return response.json();
          })
          .then((result) => {
            if ("error" in result) {
              setUserFlag(-1);
            } else {
              setUserFlag(result["redirect"]);
            }
          })
          .catch((err) => {
            console.error('There was a problem with your fetch operation:', err);
            // If we fail to get the user flag, redirect to the starting page
            window.location.href = `${process.env.REACT_APP_API_START}`;
          });
  }, []);

  useEffect(() => {
    fetchFlag()
}, [fetchFlag]);

    return (
      <BrowserRouter basename={process.env.REACT_APP_BASE_URL}>
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
      </BrowserRouter>
    );
}

export default App;
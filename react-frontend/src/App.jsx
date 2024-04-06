import { BrowserRouter as Router, Route, Routes } from "react-router-dom";
import React, {useState} from "react";
import Home from "./pages/Home";
import About from "./pages/About";
import History from "./pages/History";
import Library from "./pages/Library";
import StudentHome from "./pages/studentHome";
import ProfStudentHome from "./pages/profStudentHome";


function App() {

    const [userFlag, setUserFlag] = useState(2);  // userFlag = 2 -> prof    userFlag = 1 -> student
    
    // TODO CALL API

  return (
    <Router basename={process.env.REACT_APP_BASE_URL}>
      <div className="app">
        <div className="background-design"></div>
            <Routes>
            {/* Professor Paths */}
            {userFlag === 2 && (
                <>
                <Route path="/" element={<Home />} />
                <Route path="/library" element={<Library />} />
                <Route path="/history" element={<History />} />
                <Route path="/student" element={<ProfStudentHome />} /> 
                <Route path="/about" element={<About />} />
                
                </>
            )}

            {/* Student Paths */}
            {userFlag === 1 && (
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
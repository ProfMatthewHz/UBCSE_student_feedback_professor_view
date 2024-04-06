import { BrowserRouter as Router, Route, Routes } from "react-router-dom";
import React, {useState} from "react";
import Home from "./pages/Home";
import About from "./pages/About";
import History from "./pages/History";
import Library from "./pages/Library";
import StudentHome from "./pages/studentHome";


function App() {

    const [userFlag, setUserFlag] = useState(1);  // userFlag = 2 -> prof    userFlag = 1 -> student
    
  return (
    <Router basename={process.env.REACT_APP_BASE_URL}>
      <div className="app">
        <div className="background-design"></div>
            <Routes>
            {/* Professor Paths */}
            {userFlag === 2 && (
                <>
                <Route path="/" element={<Home />} />
                <Route path="/about" element={<About />} />
                <Route path="/history" element={<History />} />
                <Route path="/library" element={<Library />} />
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
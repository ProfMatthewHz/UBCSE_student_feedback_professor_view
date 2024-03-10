import { BrowserRouter as Router, Route, Routes } from "react-router-dom";
import Home from "./pages/Home";
import About from "./pages/About";
import History from "./pages/History";
import Library from "./pages/Library";
import StudentHome from "./pages/studentHome";
import SurveyForm from "./pages/SurveyForm"

function App() {

  return (
    <Router basename={process.env.REACT_APP_BASE_URL}>
      <div className="app">
        <div className="background-design"></div>
      
        <Routes>
          {/* Add Routes here with a component to render at that Route */}

          {/* Professor Side Paths */}
          <Route path="/" element={<Home />} />
          <Route path="/about" element={<About />} />
          <Route path="/history" element={<History />} />
          <Route path="/library" element={<Library />} />

          {/* Student Side Paths */}
          <Route path="/student" element={<StudentHome />} /> 
          {/* Coming Sprint 3 Page */}
          <Route path="/SurveyForm" element={<SurveyForm />} /> 

        </Routes>
      </div>
    </Router>


    
  );
}

export default App;
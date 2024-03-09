import { BrowserRouter as Router, Route, Routes } from "react-router-dom";
import Home from "./pages/Home";
import About from "./pages/About";
import History from "./pages/History";
import Library from "./pages/Library";
import StudentHome from "./pages/studentHome";

function App() {

  return (
    <Router basename={process.env.REACT_APP_BASE_URL}>
      {/* <Router > */}
      <div className="app">
        <div className="background-design"></div>
      
        <Routes>
          {/* Add Routes here with a component to render at that Route */}
          {/* <Route path="/" element={<Home />} />
          <Route path="/about" element={<About />} />
          <Route path="/history" element={<History />} />
          <Route path="/library" element={<Library />} /> */}
          <Route path="/" element={<StudentHome />} />
        </Routes>
      </div>
    </Router>
  );
}

export default App;
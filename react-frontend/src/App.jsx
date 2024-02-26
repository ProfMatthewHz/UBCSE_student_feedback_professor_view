import { BrowserRouter as Router, Route, Routes } from "react-router-dom";
import Navbar from "./Components/Navbar";
import Home from "./pages/Home";


function App() {

  return (
    <Router basename={process.env.REACT_APP_BASE_URL}>
      {/* <Router > */}
      <div className="app">
        <div className="background-design"></div>
        <Navbar />
        <Routes>
          {/* Add Routes here with a component to render at that Route */}
          <Route path="/" element={<Home />} />{ /*only one page with 3 options directing to diff locations on the page*/} 
        </Routes>
      </div>
    </Router>
  );
}

export default App;

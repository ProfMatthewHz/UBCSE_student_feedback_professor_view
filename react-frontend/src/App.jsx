import { Routes, Route } from "react-router";
import { HashRouter } from "react-router-dom";
import Navbar from "./Components/Navbar";
import Home from "./pages/Home";
import About from "./pages/About";
import History from "./pages/History";
import Library from "./pages/Library";

function App() {

  return (
    <HashRouter>
      {/* <Router > */}
      <div className="app">
        <div className="background-design"></div>
        <Navbar />
        <Routes>
          {/* Add Routes here with a component to render at that Route */}
          <Route path="/" element={<Home />} />
          <Route path="/about" element={<About />} />
          <Route path="/history" element={<History />} />
          <Route path="/library" element={<Library />} />
        </Routes>
      </div>
    </HashRouter>
  );
}

export default App;

import { BrowserRouter as Router, Route, Routes } from 'react-router-dom'
import Navbar from './Components/Navbar';
import Home from './pages/Home';
import History from './pages/History';
import Library from './pages/Library';


function App() {
  return (
    <Router>
      <div className="app">
        <Navbar />
        <Routes>
          {/* Add Routes here with a component to render at that Route */}
          <Route path="/" element={<Home />} />
          <Route path="/history" element={<History />} />
          <Route path="/library" element={<Library />} />
        </Routes>
      </div>
    </Router>
  );
}

export default App;
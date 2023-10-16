import { BrowserRouter as Router, Route, Routes } from 'react-router-dom'
import SideBar from './Components/Sidebar';
import Navbar from './Components/Navbar';
import Home from './pages/Home';
import History from './pages/History';
import Library from './pages/Library';
import HistorySpecific from './pages/HistorySpecific';
import HomeSpecific from './pages/HomeSpecific';



function App() {
  return (
    
      <Router>
      <div className="app">
        <Navbar />
        <Routes>
          {/* Add Routes here with a component to render at that Route */}
          <Route path="/" element={<Home />} />
          <Route path="/CSE115" element={<HomeSpecific />} />
          <Route path="/CSE199" element={<HomeSpecific />} />
          <Route path="/CSE999" element={<HomeSpecific />} />
          <Route path="/history" element={<History />} />
          <Route path="/history/CSE116" element={<HistorySpecific />} />
          <Route path="/history/CSE312" element={<HistorySpecific />} />
          <Route path="/history/Spring_2023" element={<HistorySpecific />} />
          <Route path="/history/Spring_2023/CSE116" element={<HistorySpecific />} />
          <Route path="/history/Spring_2023/CSE312" element={<HistorySpecific />} />
          <Route path="/history/Fall_2022" element={<HistorySpecific />} />
          <Route path="/history/Fall_2022/CSE116" element={<HistorySpecific />} />
          <Route path="/history/Fall_2022/CSE312" element={<HistorySpecific />} />
          <Route path="/history/Spring_2022" element={<HistorySpecific />} />
          <Route path="/history/Spring_2022/CSE116" element={<HistorySpecific />} />
          <Route path="/history/Spring_2022/CSE312" element={<HistorySpecific />} />
          <Route path="/library" element={<Library />} />
        </Routes>
      </div>
    </Router>
    
    
  );
}

export default App;
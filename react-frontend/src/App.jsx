import { BrowserRouter as Router, Route, Routes } from 'react-router-dom'
import SideBar from './Components/Sidebar';


function App() {
  return (
    <Router>
      <div>
        <SideBar />
        <Routes>
          {/* Add Routes here with a component to render at that Route */}
        </Routes>
      </div>
    </Router>
  );
}

export default App;
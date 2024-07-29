
import React, { useState } from "react";
import { Link } from "react-router-dom";
import "../styles/sidebar.css";


function StudentSideBar() {

  const scrollToTable1 = () => {
    const table1Element = document.getElementById('Open Surveys');
    if (table1Element) {
      table1Element.scrollIntoView({ behavior: 'smooth',block: 'start' });
    }
  };

  const scrollToTable2 = () => {
    const table2Element = document.getElementById('Future Surveys');
    if (table2Element) {
      table2Element.scrollIntoView({ behavior: 'smooth',block: 'start' });
    }
  };

  const scrollToTable3 = () => {
    const table3Element = document.getElementById('Closed Surveys');
    if (table3Element) {
      table3Element.scrollIntoView({ behavior: 'smooth',block: 'start' });
    }
  };



  // eslint-disable-next-line no-unused-vars
  const [clicked, setClicked] = useState(false)

  // eslint-disable-next-line no-unused-vars
  const handleClick = () => {
    setClicked((prev) => !prev)
  }

  const [activeButton, setActiveButton] = useState(false);
  
  
  return (
    <>

      <div className = "title">
        
          <div className ="headerTitle">
          <h1>TEAMWORK</h1>
            <h1>EVALUATION</h1>
            </div>
          <div className="sidebar">

            <nav> 

            <div className="sidebar-content" style={{ minHeight: "90%" }}>
                {/* list of courses */}
                <div className="sidebar-list2">
                  <Link to={{hash: "#Open Surveys"}}>
                    <div
                    
                      onClick={() =>{
                        setActiveButton("Open Surveys-Option");
                        scrollToTable1();
                      }}
                      id="Open Surveys-Option"
                      className={activeButton === "Open Surveys-Option" ? "active" : "Open Surveys-Option"}
                    >
                      Open Surveys
                    </div>
                  </Link>
                  <Link to={{hash: "#Future Surveys"}}>
                  <div
                    onClick={() => {setActiveButton("Future Surveys-Option");
                      scrollToTable2();}}
                    id="Future Surveys-Option"
                    className={activeButton === "Future Surveys-Option" ? "active" : "Future Surveys-Option"}
                  >
                    Future Surveys
                  </div>
                </Link>
                <Link to={{hash: "#Closed Surveys"}}>
                  <div
                    onClick={() => {setActiveButton("Closed Surveys-Option");
                  scrollToTable3();}}
                    id="Closed Surveys-Option"
                    className={activeButton === "Closed Surveys-Option" ? "active" : "Closed Surveys-Option"}
                  >
                    Closed Surveys
                  </div>
                </Link>
              
                </div>


              </div>
                    
            </nav>

          </div>
        </div> 
      
  
    </>
  );
}

export default StudentSideBar;

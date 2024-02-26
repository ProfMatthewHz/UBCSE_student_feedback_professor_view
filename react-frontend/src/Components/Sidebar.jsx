import "../styles/sidebar.css";
import React, { useEffect, useState } from "react";
import { Link, NavLink } from "react-router-dom";
import UBLogo from "../assets/UBLogo.png";
import Dropdown from "./Dropdown";
import Modal from "./Modal";
import AddCourse from "../pages/AddCourse";
import AddRubric from "./AddRubric";



/** Combining NavBar into Side Bar*/

/**
 * The Sidebar component is a reusable component that displays a sidebar.
 * @param props
 * @returns {Element}
 * @constructor
 */

function SideBar(props) {

  const [clicked, setClicked] = useState(false)

  const handleClick = () => {
    setClicked((prev) => !prev)
  }

  const [activeButton, setActiveButton] = useState(false);
  const [dropdown_value, setDropDownValue] = useState("");
  const sidebar_items = props.content_dictionary["Courses"] ? Object.values(props.content_dictionary["Courses"])
    : (props.content_dictionary["Rubrics"]) ? Object.values(props.content_dictionary["Rubrics"])
    : []
  const [termContents, setTermContents] = useState([]);
  // Add course stuff
  const [showAddCourseModal, setShowAddCourseModal] = useState(false);

    const handleAddCourseModal = () => {
        setShowAddCourseModal(prevState => !prevState);
    };

    // + Add Rubric for Library Page
    const [showAddRubricModal, setShowAddRubricModal] = useState(false);

    const handleAddRubricModal = () => {
        setShowAddRubricModal(prevState => !prevState);
    }

    useEffect(() => {
        const handleScroll = () => {
            const scrollPosition = window.scrollY;
            const sidebar_items_positions = sidebar_items.map((item) => {
                const connected_course = document.getElementById(item);
                if (connected_course) {
                    return document.getElementById(item).offsetTop - 366;
                }
            });

            for (let i = sidebar_items.length - 1; i >= 0; i--) {
                if (scrollPosition >= sidebar_items_positions[i]) {
                    setActiveButton(sidebar_items[i] + "-Option");
                    break;
                }
            }
        };
        // Add event listener to the window
        window.addEventListener("scroll", handleScroll);
        return () => {
            window.removeEventListener("scroll", handleScroll);
        };
    }, [sidebar_items]);

    useEffect(() => {
        if (props.route === "/history") {
            if (!dropdown_value) {
                props.updateCurrentTerm("");
            } else if (
                dropdown_value &&
                props.content_dictionary["Terms"][dropdown_value]
            ) {
                setTermContents(
                    Object.values(props.content_dictionary["Terms"][dropdown_value])
                );
                props.updateCurrentTerm(dropdown_value);
            } else {
                setTermContents([]);
            }
        }
    }, [dropdown_value, props.content_dictionary]);

  return (
    <>
      

      {/* Add Course Modal Below */}
      <Modal
        open={showAddCourseModal}
        onRequestClose={handleAddCourseModal}
        width={"750px"}
        maxWidth={"90%"}
      >
       
        <div className="CancelContainer">
          <button className="CancelButton" onClick={handleAddCourseModal}>
            ×
          </button>
        </div>
        <AddCourse
          handleAddCourseModal={handleAddCourseModal}
          getCourses={props.getCourses}
        />
      </Modal>

      {/* Add Rubric Modal Below */}
      <Modal
        open={showAddRubricModal}
        onRequestClose={handleAddRubricModal}
        width={"auto"}
        maxWidth={"90%"}
      >
        <div className="CancelContainer">
          <button className="CancelButton" onClick={handleAddRubricModal}>
            ×
          </button>
        </div>
        <AddRubric
        handleAddRubricModal ={handleAddRubricModal}
        getRubrics={props.getRubrics}
        />
      </Modal>
     

      <div className = "title">
        <div className ="headerTitle">
        <h1>TEAMWORK</h1>
          <h1>EVALUATION</h1>
          </div>
        <div className="sidebar">

          <nav> 

          <div className="sidebar-content" style={{ minHeight: "90%" }}>
                          {/* list of courses */}
                          <div className="sidebar-list">
                          <a href="#Open Surveys">
                              <div
                                onClick={() => setActiveButton("Open Surveys-Option")}
                                id="Open Surveys-Option"
                                className={activeButton === "Open Surveys-Option" ? "active" : "Open Surveys-Option"}
                              >
                                Open Surveys
                              </div>
                            </a>
                          <a href="#Future Surveys">
                            <div
                              onClick={() => setActiveButton("Future Surveys-Option")}
                              id="Future Surveys-Option"
                              className={activeButton === "Future Surveys-Option" ? "active" : "Future Surveys-Option"}
                            >
                             Future Surveys
                            </div>
                          </a>
                          <a href="#Closed Surveys">
                            <div
                              onClick={() => setActiveButton("Closed Surveys-Option")}
                              id="Closed Surveys-Option"
                              className={activeButton === "Closed Surveys-Option" ? "active" : "Closed Surveys-Option"}
                            >
                              Closed Surveys
                            </div>
                          </a>
                                          
                            
                        
                            
                          </div>


                        


             
                        
                      

                        </div>
                   



          </nav>

  


        </div>
      </div> {/* div for title */}
      





    </>
  );
}

export default SideBar;

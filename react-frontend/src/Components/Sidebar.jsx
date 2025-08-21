import "../styles/sidebar.css";
import React, { useEffect, useState } from "react";
import { NavLink } from "react-router-dom";
import Dropdown from "./Dropdown";
import CourseAddModal from "./CourseAddModal";
import RubricAdd from "./RubricAdd";
import Modal from "./Modal";
import SidebarList from "./SidebarList";

/** Combining NavBar into Side Bar*/

const SideBar = (props) => {
  const [clicked, ] = useState(false);
  const [dropdown_value, setDropDownValue] = useState("");
  const [sidebar_items, setSidebarItems] = useState([])
  const [showAddCourseModal, setShowAddCourseModal] = useState(false);
  const [showAddRubricModal, setShowAddRubricModal] = useState(false);
  const [courseState, setCourseState] = useState(false);
  const [rubricState, setRubricState] = useState(false);
  const [historyState, setHistoryState] = useState(false);

  const closeAddCourseModal = () => {
    setShowAddCourseModal(false);
  };

  const openAddCourseModal = () => {
    setShowAddCourseModal(true);
  };

  const handleAddRubricModal = () => {
    setShowAddRubricModal(prevState => !prevState);
  }

  // eslint-disable-next-line no-unused-vars
  /*const handleClick = () => {
    setClicked(prevState => !prevState)
  }*/

  useEffect(() => {
    if (props.content_dictionary["Courses"]) {
      setSidebarItems(Object.values(props.content_dictionary["Courses"]));
    } else if (props.content_dictionary["Rubrics"]) {
      setSidebarItems(Object.values(props.content_dictionary["Rubrics"]));
    } else {
      setSidebarItems([]);
    }
  }, [props.content_dictionary]);

  useEffect(() => {
    if (props.route === "/") {
      setCourseState(true);
    } else if (props.route === "/history") {
      setHistoryState(true);
    } else if (props.route === "/library") {
      setRubricState(true);
    }
  }, [props]);

  useEffect(() => {
    if (historyState) {
      if (!dropdown_value) {
        props.updateCurrentTerm("");
      } else if (dropdown_value && props.content_dictionary[dropdown_value]) {
        setSidebarItems(Object.values(props.content_dictionary[dropdown_value]));
        props.updateCurrentTerm(dropdown_value);
      } else {
        setSidebarItems([]);
      }
    }
  }, [dropdown_value, historyState, props, props.content_dictionary]);

  return (
    <>
      {/* Add Course Modal Below */}
      {courseState && showAddCourseModal && (
           <CourseAddModal
            closeModal={closeAddCourseModal}
            updateCourseListing={props.getCourses}
          />)}

      {(rubricState &&
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
          <RubricAdd
            handleCloseModal={handleAddRubricModal}
            getRubrics={props.getRubrics}
          />
        </Modal>)}

      <div className="title">
        <h1>TEAMWORK</h1>
        <h1>EVALUATION</h1>
        <div className="sidebar">
          <nav>
            <ul className={`${clicked ? "open" : ""}`}>
              <li>
                <NavLink to="/">Home</NavLink>

                {courseState &&
                  (
                    <div className="sidebar-content" style={{ minHeight: "90%" }}>
                      <SidebarList list={sidebar_items} emptyMessage={"No Courses"} />
                    </div>
                  )}
              </li>
              <li>
                <NavLink to="/history">History</NavLink>
                {/* dropdown of terms */}
                {historyState &&
                  <div className="sidebar-content">
                    <div className="sidebar-list">
                      {/* dropdown button only exists for history when there are term options */}
                      {Object.keys(props.content_dictionary).length > 0 ? (
                        <Dropdown
                          value={dropdown_value}
                          onChange={setDropDownValue}
                          options={[
                            { value: "", label: "Select Term" },
                            ...Object.keys(props.content_dictionary).map((term) => ({
                              value: term,
                              label: term,
                            })),
                          ]}
                        />
                      ) : (
                        <div className="no-content">No Terms</div>
                      )}
                    </div>
                  </div>
                }
                { /* listing of courses in the term */}
                {historyState &&
                  (
                    <div className="sidebar-content" style={{ maxHeight: "75%" }}>
                      {dropdown_value !== "" ? (
                        <SidebarList list={sidebar_items} emptyMessage={"No Courses"} />
                      ) : (<div className="no-content"></div>)
                      }
                    </div>
                  )
                }
              </li>
              <li>
                <NavLink to="/library">Library </NavLink>
                {rubricState && (
                  <div className="sidebar-content" style={{ minHeight: "90%" }}>
                    <SidebarList list={sidebar_items} emptyMessage={"No Courses"} />
                  </div>
                )}
              </li>
              <li>
                <NavLink to="/student">Student View</NavLink>
              </li>
              <li>
                <NavLink to="/about">About</NavLink>
              </li>
            </ul>
            {/* add course button */}
            {(courseState &&
              <div className="button-container">
                <button
                  className="add_course-btn"
                  onClick={openAddCourseModal}
                >
                  + Add Course
                </button>
              </div>)}
            {/* add rubric button */}
            {rubricState && (
              <div className="button-container">
                <button
                  className="add_course-btn"
                  onClick={handleAddRubricModal}
                >
                  + Add Rubric
                </button>
              </div>)}
            {/* Hamburger menu for phone, commented out bc of hertz request with only having Home
                May be changed in the future so just uncomment the code below and a hamburger menu will
                show on mobile
            */}
            { /*<div id="nav-mobile" onClick={handleClick}>
              <i
                id="nav-bar"
                className={`fas ${clicked ? "fa-times" : "fa-bars"}`}
              ></i>
            </div> */
            }
          </nav>
        </div>
      </div> {/* div for title */}
    </>
  );
};

export default SideBar;
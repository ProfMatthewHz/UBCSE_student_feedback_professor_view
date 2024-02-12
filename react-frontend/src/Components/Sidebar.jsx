import "../styles/sidebar.css";
import React, { useEffect, useState } from "react";
import { Link, NavLink } from "react-router-dom";
import UBLogo from "../assets/UBLogo.png";
import Dropdown from "./Dropdown";
import Modal from "./Modal";
import AddCourse from "../pages/AddCourse";
import AddRubric from "./AddRubric";

// --------original sidebar------------------
{/* <div className="sidebar">
  
{Object.entries(props.content_dictionary).map(([title, contents]) => {
  return props.route === "/history" ? (


    <div
      className="sidebar-content"
      style={title === "Courses" ? { maxHeight: "75%" } : null}
    >
      {(title === "Courses" && dropdown_value !== "") ||
      title === "Terms" ? (
        <h1>{title}</h1>
      ) : null}
      <div className="sidebar-list">
        {title === "Terms" ? (
          Object.keys(contents).length > 0 ? (
            <Dropdown
              value={dropdown_value}
              onChange={setDropDownValue}
              options={[
                { value: "", label: "Select Term" },
                ...Object.keys(contents).map((term) => ({
                  value: term,
                  label: term,
                })),
              ]}
            />
          ) : (
            <div className="no-content">No {title}</div>
          )
        ) : title === "Courses" && dropdown_value !== "" ? (
          termContents.length > 0 ? (
            termContents.map((item) => {
              return (
                <a href={"#" + item.code}>
                  <div
                    onClick={() =>
                      setActiveButton(item.code + "-Option")
                    }
                    id={item.code + "-Option"}
                    className={
                      activeButton === item.code + "-Option"
                        ? "active"
                        : item.code + "-Option"
                    }
                  >
                    {item.code}
                  </div>
                </a>
              );
            })
          ) : (
            <div className="no-content">No {title}</div>
          )
        ) : null}
      </div>
    </div>
  ) : (
    <div className="sidebar-content" style={{ minHeight: "90%" }}>
      <h1>{title}</h1>
      <div className="sidebar-list">
        {contents.length > 0 ? (
          contents.map((item) => {
            return (
              <a href={"#" + item}>
                <div
                  onClick={() => setActiveButton(item + "-Option")}
                  id={item + "-Option"}
                  className={
                    activeButton === item + "-Option"
                      ? "active"
                      : item + "-Option"
                  }
                >
                  {item}
                </div>
              </a>
            );
          })
        ) : (
          <div className="no-content">No {title}</div>
        )}
      </div>
      {props.route === "/" ? (
        <button
          className="add_course-btn"
          onClick={handleAddCourseModal}
        >
          + Add Course
        </button>
      ) : props.route === "/library" ? (
        <button 
          className="add_course-btn" 
          onClick={handleAddRubricModal}
        >
          + Add Rubric
        </button>
      ) : 
      null}
    </div>
  );
})}
</div> */}
//-----------original sidebar----------------





/** Combining NavBar into Side Bar*/

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
     

      <div className="title">
        <h1>TEAMWORK</h1>
        <h1>EVALUATION</h1>
      </div>


      <div className="sidebar">

        <nav> 
          <ul className={`${clicked ? "open" : ""}`}>
            <li>
              <NavLink to="/">Home</NavLink>
                  {/* -------------------------additional stuff--------------------------- */}
                  {props.route==="/" && Object.entries(props.content_dictionary).map(([title, contents]) => {
                        return props.route === "/history" ? (
                        
                      <div
                        className="sidebar-content"
                        style={title === "Courses" ? { maxHeight: "75%" } : null}
                      >
                        
                        {(title === "Courses" && dropdown_value !== "") 
                        // ||title === "Terms" 
                        ? (
                          <h1>{title}</h1>
                        ) : null}

                        
                        <div className="sidebar-list">
                          {
                          // title === "Terms" ? (
                          //   Object.keys(contents).length > 0 ? (
                          //     <Dropdown
                          //       value={dropdown_value}
                          //       onChange={setDropDownValue}
                          //       options={[
                          //         { value: "", label: "Select Term" },
                          //         ...Object.keys(contents).map((term) => ({
                          //           value: term,
                          //           label: term,
                          //         })),
                          //       ]}
                          //     />
                          //   ) : (
                          //     <div className="no-content">No {title}</div>
                          //   )
                          // ) : 
                          title === "Courses" && dropdown_value !== "" && props.route ==="/" ? (
                            termContents.length > 0 ? (
                              termContents.map((item) => {
                                return (
                                  <a href={"#" + item.code}>
                                    <div
                                      onClick={() =>
                                        setActiveButton(item.code + "-Option")
                                      }
                                      id={item.code + "-Option"}
                                      className={
                                        activeButton === item.code + "-Option"
                                          ? "active"
                                          : item.code + "-Option"
                                      }
                                    >
                                      {item.code}
                                    </div>
                                  </a>
                                );
                              })
                            ) : (
                              <div className="no-content">No {title}</div>
                            )
                          ) : null}
                        </div>
                      </div>

                    ) : (
                      <div className="sidebar-content" style={{ minHeight: "90%" }}>
                        {/* <h1>{title}</h1> */}
                        <div className="sidebar-list">
                          {contents.length > 0 ? (
                            contents.map((item) => {
                              return (
                                <a href={"#" + item}>
                                  <div
                                    onClick={() => setActiveButton(item + "-Option")}
                                    id={item + "-Option"}
                                    className={
                                      activeButton === item + "-Option"
                                        ? "active"
                                        : item + "-Option"
                                    }
                                  >
                                    {item}
                                  </div>
                                </a>
                              );
                            })
                          ) : (
                            <div className="no-content">No Courses</div>
                          )}
                        </div>



                        {props.route === "/" ? (
                          <div class="button-container">
                          <button
                            className="add_course-btn"
                            onClick={handleAddCourseModal}
                          >
                            + Add Course
                          </button>
                         </div>


                        ) 
                        // : props.route === "/library" ? (
                        //   <div class="button-container">
                        //   <button 
                        //     className="add_course-btn" 
                        //     onClick={handleAddRubricModal}
                        //   >
                        //     + Add Rubric
                        //   </button>
                        //   </div>
                        // ) 
                        : null}  {/* button */}
                       
                        


                      </div>
                    );
                  })} {/*end of object*/}
                  {/* ----------------------------additional stuff----------------------------------- */}
            </li>
            <li>
              <NavLink to="/history" className="mobile-disable">History</NavLink>
            </li>
            <li>
              <NavLink to="/library">Library</NavLink>
               {/* -------------------------additional stuff--------------------------- */}
               {props.route==="/library" && Object.entries(props.content_dictionary).map(([title, contents]) => {
                        return props.route === "/history" ? (
                        
                      <div
                        className="sidebar-content"
                        style={title === "Courses" ? { maxHeight: "75%" } : null}
                      >
                        
                        {(title === "Courses" && dropdown_value !== "") 
                        // ||title === "Terms" 
                        ? (
                          <h1>{title}</h1>
                        ) : null}

                        
                        <div className="sidebar-list">
                          {
                          // title === "Terms" ? (
                          //   Object.keys(contents).length > 0 ? (
                          //     <Dropdown
                          //       value={dropdown_value}
                          //       onChange={setDropDownValue}
                          //       options={[
                          //         { value: "", label: "Select Term" },
                          //         ...Object.keys(contents).map((term) => ({
                          //           value: term,
                          //           label: term,
                          //         })),
                          //       ]}
                          //     />
                          //   ) : (
                          //     <div className="no-content">No {title}</div>
                          //   )
                          // ) : 
                          title === "Courses" && dropdown_value !== "" && props.route ==="/" ? (
                            termContents.length > 0 ? (
                              termContents.map((item) => {
                                return (
                                  <a href={"#" + item.code}>
                                    <div
                                      onClick={() =>
                                        setActiveButton(item.code + "-Option")
                                      }
                                      id={item.code + "-Option"}
                                      className={
                                        activeButton === item.code + "-Option"
                                          ? "active"
                                          : item.code + "-Option"
                                      }
                                    >
                                      {item.code}
                                    </div>
                                  </a>
                                );
                              })
                            ) : (
                              <div className="no-content">No {title}</div>
                            )
                          ) : null}
                        </div>
                      </div>

                    ) : (
                      <div className="sidebar-content" style={{ minHeight: "90%" }}>
                        {/* <h1>{title}</h1> */}
                        <div className="sidebar-list">
                          {contents.length > 0 ? (
                            contents.map((item) => {
                              return (
                                <a href={"#" + item}>
                                  <div
                                    onClick={() => setActiveButton(item + "-Option")}
                                    id={item + "-Option"}
                                    className={
                                      activeButton === item + "-Option"
                                        ? "active"
                                        : item + "-Option"
                                    }
                                  >
                                    {item}
                                  </div>
                                </a>
                              );
                            })
                          ) : (
                            <div className="no-content">No Rubrics</div>
                          )}
                        </div>



                        { props.route === "/library" ? (
                          <div class="button-container">
                          <button 
                            className="add_course-btn" 
                            onClick={handleAddRubricModal}
                          >
                            + Add Rubric
                          </button>
                          </div>
                        ) 
                        : null}  {/* button */}
                       
                        


                      </div>
                    );
                  })} {/*end of object*/}
                  {/* ----------------------------additional stuff----------------------------------- */}
            </li>
            <li>
              <NavLink to="/about">About</NavLink>
            </li>
          </ul>


          {/* Hamburger menu for phone, commented out bc of hertz request with only having Home
              May be changed in the future so just uncomment the code below and a hamburger menu will
              show on mobile
          */}
          {/* <div id="nav-mobile" onClick={handleClick}>
            <i
              id="nav-bar"
              className={`fas ${clicked ? "fa-times" : "fa-bars"}`}
            ></i>
          </div> */}
        </nav>

 
</div>
      





    </>
  );
}

export default SideBar;

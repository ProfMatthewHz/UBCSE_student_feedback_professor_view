import "../styles/sidebar.css";
import React, { useEffect, useState } from "react";
import { Link } from "react-router-dom";
import Dropdown from "./Dropdown";
import Modal from "./Modal";
import AddCourse from "../pages/AddCourse";

function SideBar(props) {
  const [activeButton, setActiveButton] = useState(false);
  const [dropdown_value, setDropDownValue] = useState("");
  const sidebar_items = props.content_dictionary["Courses"]
    ? Object.values(props.content_dictionary["Courses"])
    : [];
  const [termContents, setTermContents] = useState([]);
  // Add course stuff
  const [showAddCourseModal, setShowAddCourseModal] = useState(false);

  const handleAddCourseModal = () => {
    if (showAddCourseModal === false) {
      setShowAddCourseModal(true);
    } else {
      setShowAddCourseModal(false);
    }
  };

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
      <Modal
        open={showAddCourseModal}
        onRequestClose={handleAddCourseModal}
        width={"1000px"}
        style={{
          content: {
            top: "50%",
            left: "50%",
            right: "auto",
            bottom: "auto",
            transform: "translate(-50%, -50%)",
            backgroundColor: "white",
            borderRadius: "10px",
            padding: "20px",
          },
          overlay: {
            backgroundColor: "rgba(0, 0, 0, 0.5)",
          },
        }}
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
      <div className="sidebar">
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
              ) : null}
            </div>
          );
        })}
      </div>
    </>
  );
}

export default SideBar;

import '../styles/sidebar.css';
import React from "react";
import { Link, NavLink } from "react-router-dom";


function SideBar(props){

  

  let add_course_button;
  let sidebar_minheight;
  if (props.route == "/") {
    add_course_button = <button>+ Add Course</button>
    sidebar_minheight = "90%"
  }

  return (
    <div className="sidebar">
      {Object.entries(props.content_dictionary).map(([title, contents]) => {
        return(
          <div key={title} className="sidebar-content" style={{minHeight: sidebar_minheight}}>
            <h1>{title}</h1>
            <div className='sidebar-list'>
              {contents.length > 0 ? (
                contents.map(item => {
                  return (
                    <NavLink  to={item}  style={{ textDecoration: 'none' }} > 
                    <div className="sidebar-option">{item}</div> </NavLink>
                  ) 
                })
              ) : (
                <div className="no-content">No {title}</div>
              )
              }
            </div>
            {add_course_button}
          </div>
        )
      })}
    </div>
  )

};

export default SideBar;

import '../styles/sidebar.css';
import React from "react";

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
        if (contents.length === 0) {
          return(
            <div key={title} className="sidebar-content" style={{minHeight: sidebar_minheight}}>
              <h1>{title}</h1>
              <div className='sidebar-list'>
                <div className="no-content">No {title}</div>
              </div>
              {add_course_button}
            </div>
          )
        }

        return(
          <div key={title} className="sidebar-content" style={{minHeight: sidebar_minheight}}>
            <h1>{title}</h1>
            <div className='sidebar-list'>
              {
              contents.map(item => {
                return (
                  <a href={item}><div className="sidebar-option">{item}</div></a>
                ) 
              })}
            </div>
            {add_course_button}
          </div>
        )
      })}
    </div>
  )

};

export default SideBar;

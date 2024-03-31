import React, { useState } from "react";
import { NavLink } from "react-router-dom";

//this is the sidebar only for the About Page
const AboutSidebar = () => {
  const [clicked, setClicked] = useState(false);

  const handleClick = () => {
    setClicked((prev) => !prev);
  };

  return (
    <>
    <div className="title">
    <h1>TEAMWORK</h1>
    <h1>EVALUATION</h1>
    

    <div className="sidebar">
      <nav>
        <ul className={`${clicked ? "open" : ""}`}>
          <li>
            <NavLink to="/">Home</NavLink>
          </li>
          <li>
            <NavLink to="/history">History
            </NavLink>
          </li>
          <li>
            <NavLink to="/library">Library</NavLink>
          </li>
          <li>
            <NavLink to="/about">About</NavLink>
          </li>
          <li>
                <NavLink to="/student">Student Side</NavLink>  
              </li>
        </ul>

        {/* Hamburger menu for phone */}
        {/* <div id="nav-mobile" onClick={handleClick}>
          <i
            id="nav-bar"
            className={`fas ${clicked ? "fa-times" : "fa-bars"}`}
          ></i>
        </div> */}
      </nav>
    </div>
    </div>
    </>
  );
};

export default AboutSidebar;

import React, { useState } from "react";
import { Link, NavLink } from "react-router-dom";
import UBLogo from "../assets/UBLogo.png";
import "../styles/navbar.css";

const Navbar = () => {
  const [clicked, setClicked] = useState(false)

  const handleClick = () => {
    setClicked((prev) => !prev)
  }

  return (
    <>
      <div className="topbar" />
      <nav>
        <div className="nav-left">
          <Link to="/">
            <img src={UBLogo} alt="UB logo" className="logo" />
            <div className="">
              <h1 className="title">Evaluation</h1>
              <h1 className="title">Tool</h1>
            </div>
          </Link>
        </div>
        <ul className={`${clicked ? "open" : ""}`}>
          <li>
            <NavLink to="/">Home</NavLink>
          </li>
          <li>
            {/* disable history in navbar */}
            <NavLink to="/history" className="mobile-disable">History</NavLink>
          </li>
          <li>
            <NavLink to="/library">Library</NavLink>
          </li>
        </ul>

        {/* Hamburger menu for phone */}
        <div id="nav-mobile" onClick={handleClick}>
          <i
            id="nav-bar"
            className={`fas ${clicked ? "fa-times" : "fa-bars"}`}
          ></i>
        </div>
      </nav>
    </>
  );
};

export default Navbar;

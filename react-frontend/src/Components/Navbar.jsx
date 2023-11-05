import React from "react";
import { Link, NavLink } from "react-router-dom";
import UBLogo from "../assets/UBLogo.png";
import "../styles/navbar.css";

const Navbar = () => {
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
        <ul>
          <li>
            <NavLink to="/">Home</NavLink>
          </li>
          <li>
            <NavLink to="/history">History</NavLink>
          </li>
          <li>
            <NavLink to="/library">Library</NavLink>
          </li>
        </ul>
      </nav>
    </>
  );
};

export default Navbar;

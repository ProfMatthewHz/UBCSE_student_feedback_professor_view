import React, { useState } from "react";
import "../styles/newdropdown.css";

const NewDropDown = ({pm}) => {
  const [open, setOpen] = useState(false);
  const handleOpen = () => {
    setOpen(!open);
  };
  return (
    <div className="dropdown">
      <button className="dropdown-button" onClick={handleOpen}>
        <div className="dropdown-text">
          <div>Title</div>
          {/* <div
            className="material-icons"
            style={{
              transform: `rotate(${open ? 180 : 0}deg)`,
              transition: "all 0.25s",
            }}></div> */}
        </div>
      </button>
      {open ? (<ul className="menu">
        {pm.map((pairing) => (
          <li className="menu-item" key={pairing} value={pairing}>
            <button className="pair">{pairing}</button>
            <button className="pairing-mode-visual">Test</button>
          </li>
        ))}
      </ul>) : null}
    </div>
  );
};

export default NewDropDown;
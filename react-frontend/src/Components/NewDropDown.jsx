import React, { useState } from "react";
import "../styles/newdropdown.css";

const NewDropDown = ({pm}) => {
  const [open, setOpen] = useState(false);
  const [showImage, setShowImage] = useState(false);
  const [image, setImage] = useState("");
  

  const handleOpen = () => {
    setOpen(!open);
  };

  const handleImageClick = (pairingMode,e) => {
    e.stopPropagation();
    if(open) {
      switch(pairingMode) {
        case 'Each Team Member Reviewed by Entire Team + Manager':
          setImage("../src/assets/pairingmodes/team+manager+self.jpg");
          setShowImage(!showImage);
          break;
        case 'Individual Reviewed by Individual':
          setImage("../src/assets/pairingmodes/single-pairs.jpg");
          setShowImage(!showImage);
          break;
        case 'Each Team Member Reviewed By Entire Team':
          setImage("../src/assets/pairingmodes/team.jpg");
          setShowImage(!showImage);
          break;
        case 'Single Individual Reviewed by Each Team Member':
          setImage("../src/assets/pairingmodes/team+self.jpg");
          setShowImage(!showImage);
          break;
        default:
          console.log('Unexpected pairing mode: ${pairingMode}');
          break;
      }
    }

  
  }
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
            <button className="pairing-mode-visual" onClick={(e) => handleImageClick(pairing, e)}>
              <img src="../src/assets/help.png" alt="" />
            </button>
          </li>
        ))}
      </ul>) : null}
      {showImage ? (
        <div className="pairing-mode-image">
          <img src={image} alt="Pairing Mode Visual"/>
        </div>
      ) : null}
    </div>
  );
};

export default NewDropDown;
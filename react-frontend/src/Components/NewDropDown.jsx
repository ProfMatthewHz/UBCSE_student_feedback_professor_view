import React, { useState } from "react";
import "../styles/newdropdown.css";

const NewDropDown = ({pm}) => {
  const [open, setOpen] = useState(false);
  const [showImage, setShowImage] = useState(false);
  const [image, setImage] = useState("");
  const [selectedPairing, setSelectedPairing] = useState("Each Team Member Reviewed by Entire Team + Manager")


  const handleOpen = () => {
    setOpen(!open);
  };

  const handleImageClick = (e, pairingMode) => {
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

  const handlePairingClick = (e, pairingMode) => {
    e.stopPropagation();
    setSelectedPairing(pairingMode);
    setOpen(false);
  }
  return (
    <div className="dropdown">
      <button className="dropdown-button" onClick={handleOpen}>
        <div className="dropdown-text">
          <div>{selectedPairing}</div>
            {<div className="dropdown-icon">
             <img src="../src/assets/dropdown-icon.png"/> 
            </div>}
        </div>
      </button>
      {open ? (<ul className="menu">
        {pm.map((pairing) => (
          <li className="menu-item" key={pairing} value={pairing}>
            <button className="pair" onClick={(e) => handlePairingClick(e, pairing)} >{pairing}</button>
            <button className="pairing-mode-visual" onClick={(e) => handleImageClick(e, pairing)}>
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
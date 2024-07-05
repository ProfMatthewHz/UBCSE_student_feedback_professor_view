import React from 'react';
import "../styles/modal.css";


/**
 * The Modal component is a reusable component that displays a modal window.
 */

export default function Modal({open,children,width,maxWidth}) {
    if (!open) return null

    else{
    // The Modal component renders a modal window with the specified content.
    return (
      <div className="modal">
        <div style= {{width: width, maxWidth: maxWidth}} className="modal-content modal-phone">
          {children}
        </div>
      </div>
    );
    }
}

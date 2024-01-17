import React, { useEffect, useState } from 'react';
import "../styles/modal.css";




export default function Modal({open,children,width,maxWidth}) {



    if (!open) return null

    else{

    return (
      <div className="modal">
        <div style= {{width: width, maxWidth: maxWidth}} className="modal-content modal-phone">
          {children}
        </div>
      </div>
    );
    }
}

import React, { useEffect, useState } from 'react';
import "../styles/modal.css";




export default function Modal({open,children,width}) {



    if (!open) return null

    else{

    return (
      <div className="modal">
        <div style= {{width: width}}className="modal-content modal-phone">
          {children}
        </div>
      </div>
    );
    }
}
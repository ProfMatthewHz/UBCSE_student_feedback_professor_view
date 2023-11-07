import React, { useEffect, useState } from 'react';
import "../styles/modal.css";




export default function Modal({open,children}) {



    if (!open) return null

    else{

    return (
      <div className="modal">
        <div style= {{width:'1300px '}}className="modal-content">
          {children}
        </div>
      </div>
    );
    }
}
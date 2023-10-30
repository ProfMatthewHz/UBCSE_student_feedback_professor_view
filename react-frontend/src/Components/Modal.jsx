import React, { useEffect, useState } from 'react';
import "../styles/modal.css";




export default function Modal({open,children}) {

    
    
    if (!open) return null

    else{

    return (
        <div>
          {children}
        </div>
    );
    }
}

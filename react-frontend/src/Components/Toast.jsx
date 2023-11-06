import React, { useState, useEffect } from 'react';
import '../styles/toast.css';

const Toast = ({ message, isVisible, duration = 3000, onClose }) => {
  // State for the width of the progress bar
  const [barWidth, setBarWidth] = useState(100);

  useEffect(() => {
    if (isVisible) {
      // Set the width of the bar to 100% when the toast becomes visible
      setBarWidth(100);

      // Update the progress bar width over time
      const intervalId = setInterval(() => {
        setBarWidth(prevWidth => {
          const newWidth = prevWidth - (100 * (intervalDuration / duration));
          if (newWidth <= 0) {
            clearInterval(intervalId);
            return 0;
          }
          return newWidth;
        });
      }, intervalDuration);

      // Set a timeout to close the toast after the specified duration
      const timer = setTimeout(() => {
        clearInterval(intervalId); // Clear interval when closing the toast
        onClose();
      }, duration);

      // Clean up interval and timer when the component is unmounted or the visibility changes
      return () => {
        clearInterval(intervalId);
        clearTimeout(timer);
      };
    }
  }, [isVisible, duration, onClose]);

  // The interval frequency should be high for a smooth animation, let's say every 10ms
  const intervalDuration = 10;

  // Calculate the width percentage for the bar
  const barStyle = {
    width: `${barWidth}%`,
    transition: 'width linear', // Apply the linear transition to width
    transitionDuration: `${intervalDuration}ms` // Match the transition duration to the interval frequency
  };

  if (!isVisible) return null;

  return (
    <div className='toast'>
      {message}
      <div className="toast-bar" style={barStyle} />
    </div>
  );
};

export default Toast;

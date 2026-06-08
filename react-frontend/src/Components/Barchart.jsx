import { React, useRef, useState, useEffect }  from 'react';
import { Bar, getElementAtEvent } from 'react-chartjs-2';
import { BarElement, LinearScale, CategoryScale, Chart } from 'chart.js';
// Register the necessary components for Chart.js. This is required for Chart.js to work properly in React.
Chart.register(BarElement, LinearScale, CategoryScale);

const BarChart = ({axes, labels, values, filterView}) => {
  const [activeBar, setActiveBar] = useState(-1);
  const [colors, setColors] = useState([]);
  const [activeBarColor,] = useState('rgba(34, 92, 181, 1)'); // Color for the active bar
  const [defaultBarColor,] = useState('rgba(34, 92, 181, 0.8)'); // Color for the inactive bars
  const [inactiveBarColor,] = useState('rgba(34, 92, 181, 0.4)'); // Color for the inactive bars
  const barRef = useRef(null);

  const handleClick = (event) => {
    const { current } = barRef;
    if (!current) return;
    const element = getElementAtEvent(current, event);
    // Update which bar is active based on the click event
    if (element.length === 0) { 
      setActiveBar(-1); // No bar is active
    } else {
      setActiveBar(element[0].index); // Set the active bar index
    }
  }

  useEffect(() => {
    filterView(activeBar);
    if (activeBar !== -1) {
      let newColors = values.map((_, index) => index === activeBar ? activeBarColor : inactiveBarColor);
      setColors(newColors);
    } else {
      let newColors = values.map(() => defaultBarColor);
      setColors(newColors);
    }
  }, [activeBar, filterView, activeBarColor, defaultBarColor, inactiveBarColor, values]);

  useEffect(() => {
    let newColors = values.map(() => 'rgba(34, 92, 181, 0.6)'); // Default color for all bars
    setColors(newColors);
  }, [labels, values]);
  

  // The data for the bar chart
  const data = {
    labels: labels,
    datasets: [
      {
        label: '# of Students',
        data: values,
        backgroundColor: colors, // Bar color
        borderColor: 'rgba(34, 92, 181, 0)', // Border color
        hoverBorderColor: 'rgba(34, 92, 181, 0.8)', // Border color on hover
        hoverBackgroundColor: 'rgba(34, 92, 181, 1)', // Border color on hover
        borderWidth: 1,
      },
    ],
  };

  // The options for the bar chart
  const options = {
    scales: {
      x: {
        beginAtZero: true, // This is for the X-axis
        title: {
          display: true,
          text: axes[0]
        },
      },
      y: {
        beginAtZero: true,
        title: {
          display: true,
          text: axes[1]
        },
      },
    },
  };

  // Return the bar chart
  return (
    <div>
     {/* <h2>Number of Students Per Normalized Averages</h2>*/}
      <Bar ref={barRef} data={data} options={options} className="normalized-barchart" onClick={handleClick} />
    </div>
  );
};

export default BarChart;

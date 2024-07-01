import React from 'react';
import { Bar } from 'react-chartjs-2';
import { BarElement, LinearScale, CategoryScale, Chart } from 'chart.js';

Chart.register(BarElement, LinearScale, CategoryScale);

const BarChart = (results) => {
  const results_without_headers = results["survey_data"].slice(1); // Remove the headers
  const result_labels = results_without_headers.map(item => item[0]); // Get the labels
  const result_data = results_without_headers.map(item => item[1]); // Get the data

  // The data for the bar chart
  const data = {
    labels: result_labels,
    datasets: [
      {
        label: '# of Students',
        data: result_data,
        backgroundColor: 'rgba(75, 192, 192, 0.6)', // Bar color
        borderColor: 'rgba(75, 192, 192, 1)', // Border color
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
          text: 'Normalized Averages'
        },
      },
      y: {
        beginAtZero: true,
        title: {
          display: true,
          text: "Number of Students"
        },
        ticks: {
          stepSize: 1,
        },
      },
    },
  };

  // Return the bar chart
  return (
    <div>
     {/* <h2>Number of Students Per Normalized Averages</h2>*/}
      <Bar data={data} options={options} className='normalized-barchart' />
    </div>
  );
};

export default BarChart;

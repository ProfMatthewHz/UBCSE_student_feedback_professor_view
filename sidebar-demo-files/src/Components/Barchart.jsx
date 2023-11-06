import React from 'react';
import { Bar } from 'react-chartjs-2';
import { BarElement, LinearScale, CategoryScale, Chart } from 'chart.js';

Chart.register(BarElement, LinearScale, CategoryScale);

const BarChart = () => {
  const data = {
    labels: ['Abigail Desantis', 'Adarsh Sivadas', 'Alan Hunt', 'Alejandro Izquierdo'],
    datasets: [
      {
        label: 'Scores',
        data: [4, 3, 4, 1],
        backgroundColor: 'rgba(75, 192, 192, 0.6)', // Bar color
        borderColor: 'rgba(75, 192, 192, 1)', // Border color
        borderWidth: 1,
      },
    ],
  };

  const options = {
    scales: {
      x: {
        beginAtZero: true, // This is for the X-axis
      },
      y: {
        beginAtZero: true, // This is for the Y-axis
      },
    },
  };

  return (
    <div>
      <h2>Teamwork Score for Reviewee: Jason Kuang</h2>
      <Bar data={data} options={options} />
    </div>
  );
};

export default BarChart;

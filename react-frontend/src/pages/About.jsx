import React from "react";
import "../styles/about.css";
import AriannaPFP from "../assets/fall2023team/ariannaescobarreyes.jpg"

const About = () => {

  return (
    <div className="about-page--container">
      <div className="about-page--individual-team-container">
        <h1>Development Team</h1>
        <div className="about-page--team-info">
          <h1>Fall 2023</h1>
          <div className="about-page--team-members">

            <div className="about-page--profile">
              <img src={AriannaPFP}/>
              <h1>Arianna Escobar-Reyes</h1>
              <h2>Design Lead</h2>
              <h2>Full Stack Developer</h2>
              <div className="about-page--contacts">
                <a href="https://www.linkedin.com/in/arianna-escobar-reyes/" />
                <a href="mailto:escobarreyesarianna@gmail.com" />
              </div>
            </div>

            <div className="about-page--profile">
              <img src="https://static-00.iconduck.com/assets.00/profile-circle-icon-2048x2048-cqe5466q.png" />
              <h1>Justin Variara</h1>
              <h2>Full Stack Developer</h2>
            </div>

            <div className="about-page--profile">
              <img src="https://static-00.iconduck.com/assets.00/profile-circle-icon-2048x2048-cqe5466q.png" />
              <h1>Korey Liu</h1>
              <h2>Back-End Developer</h2>
            </div>

            <div className="about-page--profile">
              <img src="https://static-00.iconduck.com/assets.00/profile-circle-icon-2048x2048-cqe5466q.png" />
              <h1>Ardian Muriqi</h1>
              <h2>Back-End Developer</h2>
            </div>

            <div className="about-page--profile">
              <img src="https://static-00.iconduck.com/assets.00/profile-circle-icon-2048x2048-cqe5466q.png" />
              <h1>Ahmed Alabadi</h1>
              <h2>Full Stack Developer</h2>
            </div>

          </div>

        </div>
      </div>
    </div>
  );
};

export default About;

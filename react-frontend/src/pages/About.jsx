import React from "react";
import "../styles/about.css";
import AriannaPFP from "../assets/fall2023team/ariannaescobarreyes.jpg";
import JustinPFP from "../assets/fall2023team/justinvariara.png";
import KoreyPFP from "../assets/fall2023team/koreyliu.jpg"
import ArdianPFP from "../assets/fall2023team/ardianmuriqi.jpg"
import AhmedPFP from "../assets/fall2023team/ahmed_alabadi.jpg"
import MarianPFP from "../assets/spring2024team/marianhuynh.png";
import { FaLinkedin } from "react-icons/fa";
import { MdEmail } from "react-icons/md";
import { FaLink } from "react-icons/fa";

const About = () => {
  return (
    <div className="about-page--container">
      <div className="about-page--individual-team-container">
        <h1>Development Team</h1>
        <div className="about-page--team-info">
          <h1>Fall 2023</h1>
          <div className="about-page--team-members">
            <div className="about-page--profile">
              <img src={AriannaPFP} alt="Picture of Arianna" />
              <h1>Arianna Escobar-Reyes</h1>
              <h2>Design Founder & Lead</h2>
              <h2>Full Stack Developer</h2>
              <div className="about-page--contacts">
                <a
                  href="https://www.linkedin.com/in/arianna-escobar-reyes/"
                  target="_blank"
                >
                  <FaLinkedin />
                </a>
                <a href="mailto:escobarreyesarianna@gmail.com" target="_blank">
                  <MdEmail />
                </a>
              </div>
            </div>

            <div className="about-page--profile">
              <img src={JustinPFP} alt="Picture of Justin" />
              <h1>Justin Variara</h1>
              <h2>Front-End Lead</h2>
              <h2>Full Stack Developer</h2>
              <div className="about-page--contacts">
                <a
                  href="https://www.linkedin.com/in/justinvariara/"
                  target="_blank"
                >
                  <FaLinkedin />
                </a>
                <a href="mailto:jvariara@gmail.com" target="_blank">
                  <MdEmail />
                </a>
                <a href="https://www.justinvariara.com/" target="_blank">
                  <FaLink />
                </a>
              </div>
            </div>

            <div className="about-page--profile">
              <img src={KoreyPFP} alt="Picture of Korey" />
              <h1>Korey Liu</h1>
              <h2>Back-End Developer</h2>
              <div className="about-page--contacts">
                <a
                  href="https://www.linkedin.com/in/koreyliu/"
                  target="_blank"
                >
                  <FaLinkedin />
                </a>
                <a href="mailto:koreyliu1221@gmail.com" target="_blank">
                  <MdEmail />
                </a>
              </div>
            </div>

            <div className="about-page--profile">
              <img src={ArdianPFP} alt="Picture of Ardian" />
              <h1>Ardian Muriqi</h1>
              <h2>Back-End Developer</h2>
              <div className="about-page--contacts">
                <a
                  href="https://www.linkedin.com/in/ardian-muriqi-00am995777/"
                  target="_blank"
                >
                  <FaLinkedin />
                </a>
                <a href="mailto:muriqiardian@gmail.com" target="_blank">
                  <MdEmail />
                </a>
              </div>
            </div>

            <div className="about-page--profile">
              <img src={AhmedPFP} alt="Picture of Ahmed" />
              <h1>Ahmed Alabadi</h1>
              <h2>Full Stack Developer</h2>
              <div className="about-page--contacts">
                <a href="mailto:alabadiahmed1129@gmail.com" target="_blank">
                  <MdEmail />
                </a>
              </div>
            </div>

            <div className="about-page--profile">
              <img src={MarianPFP} alt="Picture of Marian" />
              <h1>Marian Huynh</h1>
              <h2>Front-End Developer</h2>
              <div className="about-page--contacts">
                <a
                  href="https://www.linkedin.com/in/marian-huynh-b7a068211/"
                  target="_blank"
                >
                  <FaLinkedin />
                </a>
                <a href="mailto:marianvhuy@gmail.com" target="_blank">
                  <MdEmail />
                </a>
              </div>
            </div>

            



          </div>
        </div>
      </div>
    </div>
  );
};

export default About;

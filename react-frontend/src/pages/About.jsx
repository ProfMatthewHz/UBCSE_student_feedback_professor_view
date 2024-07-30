import React from "react";
import "../styles/about.css";
import SideBar from "../Components/Sidebar";
import AriannaPFP from "../assets/fall2023team/ariannaescobarreyes.jpg";
import JustinPFP from "../assets/fall2023team/justinvariara.png";
import KoreyPFP from "../assets/fall2023team/koreyliu.jpg"
import ArdianPFP from "../assets/fall2023team/ardianmuriqi.jpg"
import AhmedPFP from "../assets/fall2023team/ahmed_alabadi.jpg"
import MarianPFP from "../assets/spring2024team/marianhuynh.png";
import MattPFP from "../assets/spring2024team/matthewprier.jpg"
import TariqPFP from "../assets/spring2024team/tariqPFP.jpg"
import {FaLinkedin} from "react-icons/fa";
import {MdEmail} from "react-icons/md";
import {FaLink} from "react-icons/fa";

/**
 * The About component displays information about the development team.
 * @returns {Element}
 * @constructor
 */

const About = () => {
  
  return (
    <>
  <SideBar route="/About" content_dictionary={{}} />
    <div className="about-page--container">
      <div className="about-page--individual-team-container">
        <h1>Development Team</h1>
        <div className="about-page--team-info">
          <h1>Fall 2023</h1>
            <div className="about-page--team-members">
                <div className="about-page--profile">
                    <img src={AriannaPFP} alt="Arianna"/>
                    <h1>Arianna Escobar-Reyes</h1>
                    <h2>Design Founder & Lead</h2>
                    <h2>Full Stack Developer</h2>
                    <div className="about-page--contacts">
                        <a
                            href="https://www.linkedin.com/in/arianna-escobar-reyes/"
                            target="_blank" rel="noreferrer"
                        >
                            <FaLinkedin/>
                        </a>
                        <a href="mailto:escobarreyesarianna@gmail.com" target="_blank" rel="noreferrer">
                            <MdEmail/>
                        </a>
                    </div>
                </div>

                <div className="about-page--profile">
                    <img src={JustinPFP} alt="Justin"/>
                    <h1>Justin Variara</h1>
                    <h2>Front-End Lead</h2>
                    <h2>Full Stack Developer</h2>
                    <div className="about-page--contacts">
                        <a
                            href="https://www.linkedin.com/in/justinvariara/"
                            target="_blank" rel="noreferrer"
                        >
                            <FaLinkedin/>
                        </a>
                        <a href="mailto:jvariara@gmail.com" target="_blank" rel="noreferrer">
                            <MdEmail/>
                        </a>
                        <a href="https://www.justinvariara.com/" target="_blank" rel="noreferrer">
                            <FaLink/>
                        </a>
                    </div>
                </div>

                <div className="about-page--profile">
                    <img src={KoreyPFP} alt="Korey"/>
                    <h1>Korey Liu</h1>
                    <h2>Back-End Developer</h2>
                    <div className="about-page--contacts">
                        <a
                            href="https://www.linkedin.com/in/koreyliu/"
                            target="_blank" rel="noreferrer"
                        >
                            <FaLinkedin/>
                        </a>
                        <a href="mailto:koreyliu1221@gmail.com" target="_blank" rel="noreferrer">
                            <MdEmail/>
                        </a>
                    </div>
                </div>

                <div className="about-page--profile">
                    <img src={ArdianPFP} alt="Ardian"/>
                    <h1>Ardian Muriqi</h1>
                    <h2>Back-End Developer</h2>
                    <div className="about-page--contacts">
                        <a
                            href="https://www.linkedin.com/in/ardian-muriqi-00am995777/"
                            target="_blank" rel="noreferrer"
                        >
                            <FaLinkedin/>
                        </a>
                        <a href="mailto:muriqiardian@gmail.com" target="_blank" rel="noreferrer">
                            <MdEmail/>
                        </a>
                    </div>
                </div>

                <div className="about-page--profile">
                    <img src={AhmedPFP} alt="Ahmed"/>
                    <h1>Ahmed Alabadi</h1>
                    <h2>Full Stack Developer</h2>
                    <div className="about-page--contacts">
                        <a href="mailto:alabadiahmed1129@gmail.com" target="_blank" rel="noreferrer">
                            <MdEmail/>
                        </a>
                    </div>
                </div>

                <div className="about-page--profile">
                    <img src={MarianPFP} alt="Marian"/>
                    <h1>Marian Huynh</h1>
                    <h2>Front-End Developer</h2>
                    <div className="about-page--contacts">
                        <a
                            href="https://www.linkedin.com/in/marian-huynh-b7a068211/"
                            target="_blank" rel="noreferrer"
                        >
                            <FaLinkedin/>
                        </a>
                        <a href="mailto:marianvhuy@gmail.com" target="_blank" rel="noreferrer">
                            <MdEmail/>
                        </a>
                    </div>
                </div>

                <div className="about-page--profile">
                    <img src={MattPFP} alt="Matt"/>
                    <h1>Matthew Prier</h1>
                    <h2>Full Stack Developer</h2>
                    <div className="about-page--contacts">
                        <a
                            href="https://www.linkedin.com/in/matthewprier"
                            target="_blank" rel="noreferrer"
                        >
                            <FaLinkedin/>
                        </a>
                        <a href="mailto:mrprier@hotmail.com" target="_blank" rel="noreferrer">
                            <MdEmail/>
                        </a>
                    </div>
                </div>

                <div className="about-page--profile">
                    <img src={TariqPFP} alt="Tariq"/>
                    <h1>Tariq Nazeem</h1>
                    <h2>Back-End Developer</h2>
                    <div className="about-page--contacts">
                        <a
                            href="https://www.linkedin.com/in/tariq-nazeem-0559ba22a/"
                            target="_blank" rel="noreferrer"
                        >
                            <FaLinkedin/>
                        </a>
                        <a href="mailto:tariqnaz2346@gmail.com" target="_blank" rel="noreferrer">
                            <MdEmail/>
                        </a>
                    </div>
                </div>
            </div>
        </div>
      </div>
    </div>
    </>
  );
};

export default About;

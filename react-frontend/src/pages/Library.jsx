import React, { useEffect, useState } from "react";
import SideBar from "../Components/Sidebar";
import Rubric from "../Components/Rubric";
import "../styles/library.css";

const Library = () => {

  const [rubrics, setRubrics] = useState([])

  const fetchRubrics = () => {
    fetch(
      process.env.REACT_APP_API_URL + "getInstructorRubrics.php",
      {
        method: "GET",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
      }
    )
      .then((res) => res.json())
      .then((result) => {
        setRubrics(result)
      })
      .catch((err) => {
        console.log(err);
      });
  };

  useEffect(() => {
    fetchRubrics()
  }, []);

  const sidebar_content = {
    Rubrics: rubrics ? rubrics.map((rubric) => rubric.description) : [],
  };
  console.log("Sidebar content", sidebar_content)

  return (
    <>
      <SideBar route="/library" content_dictionary={sidebar_content} getRubrics={fetchRubrics} />
      <div>Library</div>
      <div className="container library--container">
        <div className="container-of-rubrics">
          {rubrics.length > 0 ? (
            <div>
               <div className="yes-course"><h1>Rubrics</h1></div>
            {rubrics.map((rubric) => (
              <Rubric rubric_id={rubric.id} getRubrics={fetchRubrics}/>
            ))}
            </div>
          ) : (
            <div className="no-course">
              <h1>No Rubrics Found</h1>
            </div>
          )}
        </div>
      </div>
    </>
  );
};

export default Library;

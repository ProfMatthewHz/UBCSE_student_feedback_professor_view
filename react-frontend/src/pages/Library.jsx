import React, {useCallback, useEffect, useState} from "react";
import SideBar from "../Components/Sidebar";
import Rubric from "../Components/Rubric";
import "../styles/library.css";


const Library = () => {
    // State to store the list of rubrics
    const [rubrics, setRubrics] = useState([])
    const [sidebar_content, setSidebarContent] = useState({});

    /**
     * Fetches the list of rubrics from the API.
     */
    const fetchRubrics = useCallback(() => {
        fetch(process.env.REACT_APP_API_URL + "getInstructorRubrics.php", {
                method: "GET",
                credentials: "include"
        })
        .then((res) => res.json())
        .then((result) => {
          setRubrics(result["rubrics"])
        })
        .catch((err) => {
          console.log(err);
        });
    }, []);

    // Fetch rubrics when the component mounts
    useEffect(() => {
        fetchRubrics()
    }, [fetchRubrics]);

    useEffect(() => {
      setSidebarContent({
        Rubrics: rubrics.length > 0 ? rubrics.map((rubric) => rubric.description) : [],
      })
    }, [rubrics]);

  return (
    <>
      <SideBar route="/library" content_dictionary={sidebar_content} getRubrics={fetchRubrics} />
      <div>Library</div>
      <div className="library--container">
        <div className="container-of-rubrics">
          {rubrics.length > 0 ? (
            <div>
               <div className="yes-course"><h1>Rubrics</h1></div>
            {rubrics.map((rubric) => (
              <Rubric key={rubric.id} rubric_id={rubric.id} getRubrics={fetchRubrics}/>
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

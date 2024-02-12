import React, {useEffect, useState} from "react";
import SideBar from "../Components/Sidebar";
import Rubric from "../Components/Rubric";
import "../styles/library.css";

const Library = () => {
    // State to store the list of rubrics
    const [rubrics, setRubrics] = useState([])

    /**
     * Fetches the list of rubrics from the API.
     */
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

    // Fetch rubrics when the component mounts
    useEffect(() => {
        fetchRubrics()
    }, []);

    // Prepare content for the Sidebar component
    const sidebar_content = {
        Rubrics: rubrics ? rubrics.map((rubric) => rubric.description) : [],
    };
    console.log("Sidebar content", sidebar_content)

    // The Library component renders a SideBar component and a list of Rubric components.
    return (
        <>
            <SideBar route="/library" content_dictionary={sidebar_content} getRubrics={fetchRubrics}/>
            <div>Library</div>
            <div className="container library--container">
                <div className="container-of-rubrics">
                    {rubrics.length > 0 ? (
                        rubrics.map((rubric) => (
                            <Rubric rubric_id={rubric.id} getRubrics={fetchRubrics}/>
                        ))
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

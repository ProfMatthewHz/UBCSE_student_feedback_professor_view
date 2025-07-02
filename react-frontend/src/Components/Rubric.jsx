import React, {useCallback, useEffect, useState} from "react";
import RubricAddModal from "./RubricAdd";
import "../styles/rubric.css";

const Rubric = ({rubric_id, updateRubrics}) => {
    // IMPORTANT: rubricData contains rubric name, levels, and criterions
    const [duplicatedRubricData, setDuplicatedRubricData] = useState({}); // Contains the duplicated rubric data
    const [criterions, setCriterions] = useState({}) // Contains the criterions
    const [levels, setLevels] = useState({}) // Contains the levels
    const [rubricName, setRubricName] = useState("") // Contains the rubric name
    const [showDuplicateRubricModal, setShowDuplicateRubricModal] = useState(false); // Show Duplicate Rubric Modal

    /**
     * Toggles the visibility of the duplicate rubric modal.
     */
    const closeDuplicateRubricModal = () => {
        setShowDuplicateRubricModal(false);
    }

    const openDuplicateRubricModal = () => {
        setShowDuplicateRubricModal(true);
    }

    /**
     * Fetches the rubric info from the API.
     * @param filename
     */
    const fetchRubricInfo = useCallback(() => {
        fetch(process.env.REACT_APP_API_URL + "getInstructorRubrics.php",
            {
                method: "POST",
                credentials: "include",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded",
                },
                body: new URLSearchParams({
                    "rubric-id": rubric_id,
                }),
            }
        )
            .then((res) => res.json())
            .then((result) => {
                setRubricName(result.data.name);
                setLevels(Object.values(result.data.levels));
                setCriterions(result.data.topics);
                result.data.name = result.data.name + " copy";
                setDuplicatedRubricData(result.data);
            })
            .catch((err) => {
                console.log(err);
            });
    }, [rubric_id]);

    useEffect(() => {
        fetchRubricInfo();
    }, [fetchRubricInfo]);

    /**
     * The Rubric component renders a rubric with the specified name, levels, and criterions.
     */
    return (
        <>
            {showDuplicateRubricModal && 
            <RubricAddModal
                handleAddRubricModal={closeDuplicateRubricModal}
                updateRubrics={updateRubrics}
                duplicatedRubricData={duplicatedRubricData}
            />}
            <div id={rubricName} className="rubric--container">
                <div className="rubric--content">
                    <div className="rubric--header">
                        <h2>
                            {rubricName}
                        </h2>
                        <div className="rubric--header-btns">
                            <button
                                className="btn duplicate-btn"
                                onClick={openDuplicateRubricModal}
                            >
                                + Duplicate Rubric
                            </button>
                        </div>
                    </div>
                    {Object.entries(levels).length > 0 ? (
                            <div className="table-overflow--div">
                                <table className="rubric--table">
                                    <thead>
                                    <tr>
                                        <th key="Criterion">Criterion</th>
                                        {Object.values(levels).map((level) => (
                                            <th key={level.score}>{level.name + " (" + level.score + " pts)"}</th>
                                        ))}
                                    </tr>
                                    </thead>
                                    <tbody>
                                    {Object.values(criterions).map((criterion) =>
                                        <tr key={criterion.question}>
                                            <td className="criterion--heading">{criterion.question}</td>
                                            {Object.values(criterion.responses).map((response) =>
                                                <td key={crypto.randomUUID()}>{response}</td>
                                            )}
                                        </tr>
                                    )}
                                    </tbody>
                                </table>
                            </div>
                        ) :
                        <div className="no-rubric-content">
                            No Rubric Data Found
                        </div>
                    }
                </div>
            </div>
        </>
    )

}

export default Rubric;

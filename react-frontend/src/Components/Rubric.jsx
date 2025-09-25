import React, {useCallback, useEffect, useState} from "react";
import Modal from "./Modal";
import "../styles/rubric.css";
import RubricAdd from "./RubricAdd";

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
    const handleDuplicateRubricModal = () => {
        setShowDuplicateRubricModal(prevState => !prevState);
    }

    /**
     * Fetches the rubric info from the API.
     * @param filename
     */
    const fetchRubricInfo = useCallback(() => {
        let formData = new FormData();
        formData.append("rubric-id", rubric_id);
        fetch(process.env.REACT_APP_API_URL + "getInstructorRubrics.php",
            {
                method: "POST",
                credentials: "include",
                body: formData
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
            <Modal
                open={showDuplicateRubricModal}
                onRequestClose={handleDuplicateRubricModal}
                width={"auto"}
                maxWidth={"90%"}
            >
                <div className="CancelContainer">
                    <button className="CancelButton" onClick={handleDuplicateRubricModal}>
                        ×
                    </button>
                </div>
                <RubricAdd
                    handleAddRubricModal={handleDuplicateRubricModal}
                    updateRubrics={updateRubrics}
                    duplicatedRubricData={duplicatedRubricData}
                />
            </Modal>
            <div id={rubricName} className="rubric--container">
                <div className="rubric--content">
                    <div className="rubric--header">
                        <h2>
                            {rubricName}
                        </h2>
                        <div className="rubric--header-btns">
                            <button
                                className="btn duplicate-btn"
                                onClick={handleDuplicateRubricModal}
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

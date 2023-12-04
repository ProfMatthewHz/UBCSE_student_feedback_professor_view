import React, { useState, useEffect } from "react";
import "../styles/addrubric.css";
import Rubric from "./Rubric";

const AddRubric = ({ getRubrics }) => {

  // IMPORTANT: rubricData contains all the data collected from each modal
  const [rubricData, setRubricData] = useState({});

  // (Modal 1) Create Rubric Levels
  const [deleteColumnHovered, setDeleteColumnsHovered] = useState([]);
  const [showCreateLevelsModal, setShowCreateLevelsModal] = useState(true);

  // (Modal 2) Create Rubric Criteria
  const [deleteRowHovered, setDeleteRowsHovered] = useState([]);
  const [showCreateCriteriaModal, setShowCreateCriteriaModal] = useState(false);

  // (Modal 3) Preview Rubric
  const [showPreviewModal, setShowPreviewModal] = useState(false);

  // (Modal 1) Rubric Levels

  const handleAddColumn = () => {
    const updatedData = {
      ...rubricData,
      levels: [...rubricData["levels"], { name: "", score: "" }]
    };
    setRubricData(updatedData);
    setDeleteColumnsHovered([...deleteColumnHovered, false]);
  }

  const handleDeleteColumn = (indexToRemove) => {
    const updatedData = {
      ...rubricData,
      levels: rubricData["levels"].filter((_, index) => index !== indexToRemove)
    };
    const updatedColumnDisplay = deleteColumnHovered.filter((_, index) => index !== indexToRemove);

    setRubricData(updatedData);
    setDeleteColumnsHovered(updatedColumnDisplay);
  };

  const handleDeleteColumnsHovered = (action, index) => {

    if (action == "hovered") {
      const updatedData = [...deleteColumnHovered];
      updatedData[index] = true;
      setDeleteColumnsHovered(updatedData);
    } else {
      const updatedData = [...deleteColumnHovered];
      updatedData[index] = false;
      setDeleteColumnsHovered(updatedData);
    }
  };

  const handleLevelNameChange = (index, value) => {
    const updatedData = { ...rubricData };
    updatedData["levels"][index]["name"] = value;
    setRubricData(updatedData);
  };

  const handleLevelPointsChange = (index, value) => {
    const updatedData = { ...rubricData };
    updatedData["levels"][index]["score"] = parseInt(value);
    setRubricData(updatedData);
  };

  const handleRubricNameChange = (value) => {
    const updatedData = {...rubricData}
    updatedData["name"] = value;
    setRubricData(updatedData)
  };

  // (Modal 2) Rubric Criteria

  const handleAddRow = () => {
    const updatedData = {
      ...rubricData,
      topics: [...rubricData["topics"], {question: "", responses: [], type: "multiple_choice" }]
    };
    setRubricData(updatedData);
    setDeleteRowsHovered([...deleteRowHovered, false]);
  };

  const handleCriterionNameChange = (index, value) => {
    const updatedData = { ...rubricData };
    updatedData["topics"][index]["question"] = value;
    setRubricData(updatedData);
  };

  const handleCriterionResponseChange = (criterionIndex, levelIndex, value) => {
    const updatedData = { ...rubricData };
    updatedData["topics"][criterionIndex]["responses"][levelIndex] = value;
    setRubricData(updatedData);
  };

  const handleDeleteRow = (indexToRemove) => {
    const updatedData = {
      ...rubricData,
      topics: rubricData["topics"].filter((_, index) => index !== indexToRemove)
    };
    const updatedRowDisplay = deleteRowHovered.filter((_, index) => index !== indexToRemove);

    setRubricData(updatedData);
    setDeleteRowsHovered(updatedRowDisplay);
  };

  const handleDeleteRowsHovered = (action, index) => {

    if (action == "hovered") {
      const updatedData = [...deleteRowHovered];
      updatedData[index] = true;
      setDeleteRowsHovered(updatedData);
    } else {
      const updatedData = [...deleteRowHovered];
      updatedData[index] = false;
      setDeleteRowsHovered(updatedData);
    }
  };

  const handleBackButton = () => {
    if (showCreateCriteriaModal) {
      setShowCreateLevelsModal(true);
      setShowCreateCriteriaModal(false);
      setShowPreviewModal(false);
    } else if (showPreviewModal) {
      setShowCreateLevelsModal(false);
      setShowCreateCriteriaModal(true);
      setShowPreviewModal(false);
    }
  }

  const handleNextButton = () => {
    if (showCreateLevelsModal) {
      setShowCreateLevelsModal(false);
      setShowCreateCriteriaModal(true);
      setShowPreviewModal(false);
    } else if (showCreateCriteriaModal) {
      setShowCreateLevelsModal(false);
      setShowCreateCriteriaModal(false);
      setShowPreviewModal(true);
    }
  };


  useEffect(() => {

    // Set Delete Button States for Columns and Rows
    const defaultDeleteColumnsHovered = []
    const defaultDeleteRowsHovered = []

    // Set Levels
    const defaultLevelAmount = 4;
    const defaultLevels = [];

    for (let i = 0; i < defaultLevelAmount; i++) {
      defaultLevels.push({ name: "", score: "" });
      defaultDeleteColumnsHovered.push(false);
    }
    setDeleteColumnsHovered(defaultDeleteColumnsHovered);

    // Set Criterions
    const defaultCriterionAmount = 4;
    const defaultCriterions = [];

    for (let i = 0; i < defaultCriterionAmount; i++) {
      defaultCriterions.push({ question: "", responses: [], type: "multiple_choice" });
      defaultDeleteRowsHovered.push(false);
    }
    setDeleteRowsHovered(defaultDeleteRowsHovered)

    // Set Rubric Data
    setRubricData({ name: "", levels: defaultLevels, topics: defaultCriterions })
 
  }, [])


  return (
    <div className="addrubric--container">
      {showCreateLevelsModal ? (
        <div className="addrubric--contents-container">
          <h2>Create Rubric Levels</h2>
          <label className="addrubric--rubric-name">
            Rubric Name
            <input 
              placeholder="Enter rubric name"
              onChange={(e) => handleRubricNameChange(e.target.value)}
              required
              value={rubricData["name"]}
            />
          </label>
          {rubricData["levels"] && (rubricData["levels"].length === 2 || rubricData["levels"].length === 5) && (
            <div className="addrubric--min-max-notification">
            {rubricData["levels"].length === 2 ? 
              "A minimum of 2 levels is required"
             : 
              "A maximum of 5 levels is allowed"
            }
            </div>
          )}
          <div className="addrubric-table-overflow-container">
            <table className="addrubric--table">
            {rubricData["levels"] && rubricData["levels"].length > 2 && (
              <thead>
                <tr>
                  <td></td>
                  {rubricData["levels"].map((_, index) => (
                      <td className="addrubric--label" key={index}>
                          <div
                            className={
                              deleteColumnHovered[index]
                                ? "addrubric--delete-column-btn-container-hovered"
                                : "addrubric--delete-column-btn-container"
                            }
                          >
                            <button
                              onClick={() => handleDeleteColumn(index)}
                              onMouseEnter={() => { handleDeleteColumnsHovered("hovered", index) }}
                              onMouseLeave={() => { handleDeleteColumnsHovered("default", index) }}
                            >
                            </button>
                          </div>
                      </td>
                    ))}
                </tr>
              </thead>
              )}
              <tbody>
                {/* Level Header Row */}
                <tr className="addrubric--level-row">
                  <td className="addrubric--label">Criterion</td>
                  {rubricData["levels"] && rubricData["levels"].map((level, index) => (
                    <td className="addrubric--label" key={index}>
                      <div className="addrubric--level-inputs">
                        <input
                          className="addrubric--level-name"
                          onChange={(e) => handleLevelNameChange(index, e.target.value)}
                          placeholder="Name"
                          required
                          type="text"
                          value={level["name"]}
                        />
                        <input
                          className="addrubric--points-number"
                          onChange={(e) => handleLevelPointsChange(index, e.target.value)}
                          placeholder={index}
                          required
                          type="number"
                          value={level["score"]}
                        />
                        pts
                      </div>
                    </td>
                  ))}
                  {rubricData["levels"] && rubricData["levels"].length < 5 ? (
                    <td className="addrubric--add-level-btn-container">
                      <button
                        onClick={() => handleAddColumn()}
                      >
                        + Add Level
                      </button>
                    </td>
                  ) : null}
                </tr>
                {/* Rows of Criterion Names & Responses */}
                {rubricData["topics"] &&
                  rubricData["topics"].map((_, criterionIndex) => (
                    <tr className="addrubric--criterion-row-disabled" key={criterionIndex}>
                      <td>
                        <input
                          className="addrubric--criterion-name"
                          disabled
                        />
                      </td>
                      {rubricData["levels"] && rubricData["levels"].map((_, levelIndex) => (
                        <td key={levelIndex}>
                          <input
                            className="addrubric--response-description"
                            disabled
                          />
                        </td>
                      ))}
                      {rubricData["levels"] && rubricData["levels"].length < 5 ? (
                        <td className="addrubric--add-column-input-container"></td>
                      ) : null}
                    </tr>
                  ))}
              </tbody>
            </table>
          </div>
          <div className="addrubric--only-next-btn-container">
            <button
              className="addrubric--next-btn"
              onClick={() => handleNextButton()}
            >
              Create Criteria
            </button>
          </div>
        </div>
      )
        // (Modal 2) Create Rubric Criteria
        : (showCreateCriteriaModal ? (
          <div className="addrubric--contents-container">
            <h2>Create Rubric Criteria</h2>
            {rubricData["topics"] && rubricData["topics"].length === 1 && (
              <div className="addrubric--min-max-notification">
                A minimum of 1 criterion is required
              </div>
            )}
            <div className="addrubric-table-overflow-container">
              <table className="addrubric--table">
                <tbody>
                  {/* Level Header Row */}
                  <tr className="addrubric--level-row">
                    <td className="addrubric--confirmed-label">Criterion</td>
                    {rubricData["levels"] && rubricData["levels"].map((level, index) => (
                      <td className="addrubric--confirmed-label" key={index}>
                        {`${level.name} (${level.score} pts)`}
                      </td>
                    ))}
                    {rubricData["topics"] && rubricData["topics"].length > 1 && (
                      <td className="addrubric--delete-row-btns-section"></td>
                    )}
                  </tr>
                  {/* Rows of Criterion Names & Responses */}
                  {rubricData["topics"] &&
                    rubricData["topics"].map((criterion, criterionIndex) => (
                      <tr className="addrubric--criterion-row" key={criterionIndex}>
                        <td className="addrubric--label">
                          <textarea
                            className="addrubric--criterion-description"
                            onChange={(e) => handleCriterionNameChange(criterionIndex, e.target.value)}
                            placeholder="Description of Trait"
                            required
                            value={criterion["question"]}
                          />
                        </td>
                        {rubricData["levels"] && rubricData["levels"].map((_, levelIndex) => (
                          <td key={levelIndex} className={levelIndex === rubricData["levels"].length - 1 ? "addrubric--last-criterion-response-container" : null}>
                            <textarea
                              className="addrubric--criterion-description"
                              onChange={(e) => handleCriterionResponseChange(criterionIndex, levelIndex, e.target.value)}
                              placeholder="Description of Level Achievement"
                              required
                              value={criterion["responses"][levelIndex] || ""}
                            />
                          </td>
                        ))}
                        {rubricData["topics"] && rubricData["topics"].length > 1 ? (
                          <div
                            className={
                              deleteRowHovered[criterionIndex]
                                ? "addrubric--delete-row-btn-container-hovered"
                                : "addrubric--delete-row-btn-container"
                            }
                          >
                            <button
                              onClick={() => handleDeleteRow(criterionIndex)}
                              onMouseEnter={() => { handleDeleteRowsHovered("hovered", criterionIndex) }}
                              onMouseLeave={() => { handleDeleteRowsHovered("default", criterionIndex) }}
                            >
                            </button>
                          </div>
                        ) : null}
                      </tr>
                    ))}
                    <tr className="addrubric--criterion-row">
                      <td className="addrubric--add-criterion-btn-container">
                        <button
                          onClick={() => handleAddRow()}
                        >
                          + Add Criterion
                        </button>
                      </td>
                      {rubricData["levels"] && rubricData["levels"].map((level, levelIndex) => (
                          <td key={levelIndex} className={levelIndex === rubricData["levels"].length - 1 ? "addrubric--last-criterion-response-container" : null}></td>
                      ))}
                    </tr>
                </tbody>
              </table>
            </div>
            <div className="addrubric--back-next-btns-container">
              <button
                  className="addrubric--back-btn"
                  onClick={() => handleBackButton()}
              >
                  Back
              </button>
              <button
                className="addrubric--next-btn"
                onClick={() => handleNextButton()}
              >
                Preview
              </button>
            </div>
          </div>
        ) :
        // (Modal 3) Preview Rubric
        <div className="addrubric--contents-container">
            <h2>Preview {rubricData.name}</h2>
            <div className="addrubric-table-overflow-container">
              <table className="addrubric--table">
                <tbody>
                  {/* Level Header Row */}
                  <tr className="addrubric--level-row">
                    <td className="addrubric--confirmed-label">Criterion</td>
                    {rubricData["levels"] && rubricData["levels"].map((level, index) => (
                      <td className="addrubric--confirmed-label" key={index}>
                        {`${level.name} (${level.score} pts)`}
                      </td>
                    ))}
                  </tr>
                  {/* Rows of Criterion Names & Responses */}
                  {rubricData["topics"] &&
                    rubricData["topics"].map((criterion, criterionIndex) => (
                      <tr className="addrubric--criterion-row" key={criterionIndex}>
                        <td className="addrubric--confirmed-label">
                          {criterion.question}
                        </td>
                        {rubricData["levels"] && rubricData["levels"].map((_, levelIndex) => (
                          <td key={levelIndex} className={levelIndex === rubricData["levels"].length - 1 ? "addrubric--last-criterion-response-container" : null}>
                            {criterion["responses"][levelIndex]}
                          </td>
                        ))}
                      </tr>
                    ))}
                </tbody>
              </table>
            </div>
            <div className="addrubric--back-next-btns-container">
              <button
                  className="addrubric--back-btn"
                  onClick={() => handleBackButton()}
              >
                  Back
              </button>
              <button
                className="addrubric--next-btn"
                onClick={() => handleNextButton()}
              >
                Save Rubric
              </button>
            </div>
          </div>
        )}
    </div>
  );

}

export default AddRubric;

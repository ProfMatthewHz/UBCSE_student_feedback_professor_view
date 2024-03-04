import React, { useState, useEffect } from "react";
import "../styles/addrubric.css";

const AddRubric = ({ getRubrics, handleAddRubricModal, duplicatedRubricData }) => {


  // IMPORTANT: rubricData contains all the data collected from each modal
  const [rubricData, setRubricData] = useState({});

  const [errorMessage, setErrorMessage] = useState({});

  // (Modal 1) Create Rubric Levels
  const [deleteColumnHovered, setDeleteColumnsHovered] = useState([]);
  const [showCreateLevelsModal, setShowCreateLevelsModal] = useState(true);

  // (Modal 2) Create Rubric Criteria
  const [deleteRowHovered, setDeleteRowsHovered] = useState([]);
  const [showCreateCriteriaModal, setShowCreateCriteriaModal] = useState(false);

  // (Modal 3) Preview Rubric
  const [showPreviewModal, setShowPreviewModal] = useState(false);

  // (Modal 1) Rubric Levels


  // Add a new column to the rubric
  const handleAddColumn = () => {
    const updatedData = {
      ...rubricData,
      levels: [...rubricData["levels"], { name: "", score: "" }]
    };
    setRubricData(updatedData);
    setDeleteColumnsHovered([...deleteColumnHovered, false]);
  }

  // Delete a column from the rubric
  const handleDeleteColumn = (indexToRemove) => {
    const newErrorMessage = { ...errorMessage };

    const currentErrorMessage = Object.entries(errorMessage)

    if (currentErrorMessage.length > 0 && !newErrorMessage["rubric-name"]) {
      const currentLevelNumber = parseInt(currentErrorMessage[0][0].split("level-")[1])
      const newLevelNumber = currentLevelNumber - 1

      if (indexToRemove <= currentLevelNumber) {
        setErrorMessage({ ["level-" + newLevelNumber.toString()]: currentErrorMessage[0][1] })

        if (currentLevelNumber === indexToRemove) {
          setErrorMessage({})
        }
      }
    }

    // Remove the column from the rubric
    const updatedData = {
      ...rubricData,
      levels: rubricData.levels.filter((_, index) => index !== indexToRemove)
    };

    // Remove the column from the error message
    const updatedTopics = rubricData.topics.map((topic) => ({
      ...topic,
      responses: topic.responses.filter((_, index) => index !== indexToRemove)
    }));
    updatedData["topics"] = updatedTopics

    setRubricData(updatedData);

    // Remove the column from the delete button states
    const updatedColumnDisplay = deleteColumnHovered.filter((_, index) => index !== indexToRemove);
    setDeleteColumnsHovered(updatedColumnDisplay);

  };

  // Set the delete button states for columns
  const handleDeleteColumnsHovered = (action, index) => {
    if (action === "hovered") {
      const updatedData = [...deleteColumnHovered];
      updatedData[index] = true;
      setDeleteColumnsHovered(updatedData);
    } else {
      const updatedData = [...deleteColumnHovered];
      updatedData[index] = false;
      setDeleteColumnsHovered(updatedData);
    }
  }
  /**
   * Handles the change of the name of a level in the rubric.
   * @param index
   * @param value
   * @returns {void}
   */
  const handleLevelNameChange = (index, value) => {
    if (errorMessage["level-" + index.toString()] && errorMessage["level-" + index.toString()]["name"]) {
      setErrorMessage({});
    }

    const updatedData = { ...rubricData };
    updatedData["levels"][index]["name"] = value;
    setRubricData(updatedData);
  };

  /**
   * Handles the change of the score of a level in the rubric.
   * @param {number} index The index of the level in the rubric.
   * @param {number} value The new score of the level.
   * @returns {void}
   */
  const handleLevelPointsChange = (index, value) => {
    if (errorMessage["level-" + index.toString()]
      && !errorMessage["level-" + index.toString()]["name"]
      && errorMessage["level-" + index.toString()]["level"]) {
      setErrorMessage({});
    }
    const updatedData = { ...rubricData };
    updatedData["levels"][index]["score"] = parseInt(value);
    setRubricData(updatedData);
  };

  /**
   * Handles the change of the name of the rubric.
   * @param value
   * @returns {void}
   */
  const handleRubricNameChange = (value) => {
    if (errorMessage["rubric-name"]) {
      setErrorMessage({});
    }

    const updatedData = { ...rubricData }
    updatedData["name"] = value;
    setRubricData(updatedData)
  };

  // (Modal 2) Rubric Criteria
  /**
   * Adds a new row to the rubric.
   */
  const handleAddRow = () => {

    const emptyResponses = Array.from({ length: rubricData.levels.length }, () => "");

    const updatedData = {
      ...rubricData,
      topics: [...rubricData["topics"], { question: "", responses: emptyResponses, type: "multiple_choice" }]
    };
    setRubricData(updatedData);
    setDeleteRowsHovered([...deleteRowHovered, false]);
  };

  /**
   * Handles the change of the name of a criterion in the rubric.
   * @param index
   * @param value
   */
  const handleCriterionNameChange = (index, value) => {

    if (errorMessage["criterion-" + index.toString()]
      && errorMessage["criterion-" + index.toString()]["name"]) {
      setErrorMessage({});
    }

    const updatedData = { ...rubricData };
    updatedData["topics"][index]["question"] = value;
    setRubricData(updatedData);
  };

  /**
   * Handles the change of the response of a criterion in the rubric.
   * @param criterionIndex
   * @param levelIndex
   * @param value
   */
  const handleCriterionResponseChange = (criterionIndex, levelIndex, value) => {

    if (errorMessage["criterion-" + criterionIndex.toString()]
      && !errorMessage["criterion-" + criterionIndex.toString()]["name"]
      && errorMessage["criterion-" + criterionIndex.toString()]["level-" + levelIndex.toString()]) {
      setErrorMessage({});
    }

    const updatedData = { ...rubricData };
    updatedData["topics"][criterionIndex]["responses"][levelIndex] = value;
    setRubricData(updatedData);
  };

  /**
   * Deletes a row from the rubric.
   * @param indexToRemove
   */
  const handleDeleteRow = (indexToRemove) => {

    const currentErrorMessage = Object.entries(errorMessage)

    if (currentErrorMessage.length > 0) {
      const currenCriterionNumber = parseInt(currentErrorMessage[0][0].split("criterion-")[1])
      const newCriterionNumber = currenCriterionNumber - 1

      if (indexToRemove <= currenCriterionNumber) {
        setErrorMessage({ ["criterion-" + newCriterionNumber.toString()]: currentErrorMessage[0][1] })

        if (currenCriterionNumber === indexToRemove) {
          setErrorMessage({})
        }
      }
    }

    const updatedData = {
      ...rubricData,
      topics: rubricData["topics"].filter((_, index) => index !== indexToRemove)
    };
    const updatedRowDisplay = deleteRowHovered.filter((_, index) => index !== indexToRemove);

    setRubricData(updatedData);
    setDeleteRowsHovered(updatedRowDisplay);
  };

  /**
   * Sets the delete button states for rows.
   * @param action
   * @param index
   */
  const handleDeleteRowsHovered = (action, index) => {

    if (action === "hovered") {
      const updatedData = [...deleteRowHovered];
      updatedData[index] = true;
      setDeleteRowsHovered(updatedData);
    } else {
      const updatedData = [...deleteRowHovered];
      updatedData[index] = false;
      setDeleteRowsHovered(updatedData);
    }
  };

  /**
   * Handles the click of the back button.
   */
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

  /**
   * Handles the click of the next button.
   */
  const handleNextButton = async () => {

    if (showCreateLevelsModal) {
      let errors = await fetchRubricErrors("rubricInitialize.php");

      if (errors.length === 0) {
        setShowCreateLevelsModal(false);
        setShowCreateCriteriaModal(true);
        setShowPreviewModal(false);
      }

    } else if (showCreateCriteriaModal) {
      let errors = await fetchRubricErrors("rubricSetCriterions.php");

      if (errors.length === 0) {
        setShowCreateLevelsModal(false);
        setShowCreateCriteriaModal(false);
        setShowPreviewModal(true);
      }
    } else {
      let errors = await fetchSaveRubric();
      setShowPreviewModal(false);
      getRubrics();
      handleAddRubricModal(false);

    }
  };


  // Fetches
  /**
   * Fetches the errors from the API.
   * @param filename
   */
  const fetchRubricErrors = async (filename) => {
    try {
      const response = await fetch(
        process.env.REACT_APP_API_URL + filename,
        {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify(rubricData)
        }
      );

      const result = await response.json();

      if (result["errors"] && Object.keys(result["errors"]).length > 0) {
        const [errorKey, errorValue] = Object.entries(result["errors"])[0];
        setErrorMessage({ [errorKey]: errorValue });
      }

      return result["errors"];
    } catch (error) {
      console.error(error);
    }
  };

  /**
   * Fetches the save rubric from the API.
   */
  const fetchSaveRubric = async () => {
    try {
      const response = await fetch(
        process.env.REACT_APP_API_URL + "rubricConfirm.php",
        {
          method: "POST",
          headers: {
            "Content-Type": "application/x-www-form-urlencoded",
          },
          body: new URLSearchParams({
            "save-rubric": 1
          }),
        }
      );

      return
    } catch (error) {
      console.error(error);
    }
  };

  useEffect(() => {

    if (duplicatedRubricData) { // + Duplicate Rubric
      setRubricData(duplicatedRubricData)
    } else { // + Add Rubric
      
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
        const emptyResponses = Array.from({ length: defaultLevelAmount }, () => "");
        defaultCriterions.push({
          question: "",
          responses: emptyResponses,
          type: "multiple_choice"
        });
        defaultDeleteRowsHovered.push(false);
      }
      setDeleteRowsHovered(defaultDeleteRowsHovered)

      // Set Rubric Data
      setRubricData({ name: "", levels: defaultLevels, topics: defaultCriterions })
    }

  }, [])

  /**
   * Handles the click of the close button.
   */
  return (
    <div className="addrubric--container">
      {showCreateLevelsModal ? (
        <div className="addrubric--contents-container">
          <h2>Create Rubric Levels</h2>
          <label className={errorMessage["rubric-name"] ? "addrubric--rubric-name-error" : "addrubric--rubric-name"}>
            Rubric Name
            <input
              placeholder="Enter rubric name"
              onChange={(e) => handleRubricNameChange(e.target.value)}
              required
              value={rubricData["name"]}
            />
            {errorMessage["rubric-name"] && (
              <label className="addrubric--red-error-notif">
                <div className="addrubric--level-red-warning-sign" />
                {errorMessage["rubric-name"]}
              </label>
            )}
          </label>
          {rubricData["levels"] && (rubricData["levels"].length === 2 || rubricData["levels"].length === 5) && (
            <div className="addrubric--min-max-notification">
              <div className="whitewarningsign" />
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
                          className={
                            errorMessage["level-" + index.toString()] && errorMessage["level-" + index.toString()]["name"]
                              ? "addrubric--level-name-error"
                              : "addrubric--level-name"
                          }
                          onChange={(e) => handleLevelNameChange(index, e.target.value)}
                          placeholder="Name"
                          required
                          type="text"
                          value={level["name"]}
                        />
                        <input
                          className={
                            errorMessage["level-" + index.toString()] && !errorMessage["level-" + index.toString()]["name"] && errorMessage["level-" + index.toString()]["level"]
                              ? "addrubric--points-number-error"
                              : "addrubric--points-number"
                          }
                          onChange={(e) => handleLevelPointsChange(index, e.target.value)}
                          placeholder={index}
                          required
                          type="number"
                          value={level["score"]}
                        />
                        pts
                      </div>
                      {errorMessage["level-" + index.toString()] && (
                        <div className="addrubric--red-error-notif">
                          <div className="addrubric--level-red-warning-sign" />
                          <div className="addrubric--level-red-warning-message">
                            {errorMessage["level-" + index.toString()]["name"] ? (
                              errorMessage["level-" + index.toString()]["name"]
                            ) :
                              errorMessage["level-" + index.toString()]["level"]
                            }
                          </div>
                        </div>
                      )}
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
                            className={errorMessage["criterion-" + criterionIndex.toString()] && errorMessage["criterion-" + criterionIndex.toString()]["name"] ? (
                              "addrubric--criterion-description-error"
                            ) : "addrubric--criterion-description"}
                            onChange={(e) => handleCriterionNameChange(criterionIndex, e.target.value)}
                            placeholder="Description of Trait"
                            required
                            value={criterion["question"]}
                          />
                          {errorMessage["criterion-" + criterionIndex.toString()] && errorMessage["criterion-" + criterionIndex.toString()]["name"] && (
                            <div className="addrubric--red-error-notif">
                              <div className="addrubric--criterion-red-warning-sign" />
                              <div className="addrubric--criterion-red-warning-message">
                                {errorMessage["criterion-" + criterionIndex.toString()]["name"]}
                              </div>
                            </div>
                          )}
                        </td>
                        {rubricData["levels"] && rubricData["levels"].map((_, levelIndex) => (
                          <td key={levelIndex} className={levelIndex === rubricData["levels"].length - 1 ? "addrubric--last-criterion-response-container" : null}>
                            <textarea
                              className={errorMessage["criterion-" + criterionIndex.toString()]
                                && !errorMessage["criterion-" + criterionIndex.toString()]["name"]
                                && !errorMessage["criterion-" + criterionIndex.toString()]["level-" + (levelIndex - 1).toString()]
                                && errorMessage["criterion-" + criterionIndex.toString()]["level-" + levelIndex.toString()]
                                ? "addrubric--criterion-description-error"
                                : "addrubric--criterion-description"}
                              onChange={(e) => handleCriterionResponseChange(criterionIndex, levelIndex, e.target.value)}
                              placeholder="Description of Level Achievement"
                              required
                              value={criterion["responses"][levelIndex] || ""}
                            />
                            {errorMessage["criterion-" + criterionIndex.toString()]
                              && !errorMessage["criterion-" + criterionIndex.toString()]["name"]
                              && !errorMessage["criterion-" + criterionIndex.toString()]["level-" + (levelIndex - 1).toString()]
                              && errorMessage["criterion-" + criterionIndex.toString()]["level-" + levelIndex.toString()]
                              && (
                                <div className="addrubric--red-error-notif">
                                  <div className="addrubric--criterion-red-warning-sign" />
                                  <div className="addrubric--criterion-red-warning-message">
                                    {errorMessage["criterion-" + criterionIndex.toString()]["level-" + levelIndex.toString()]}
                                  </div>
                                </div>
                              )}
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

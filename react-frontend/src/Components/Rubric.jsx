import React, { useEffect, useState } from "react";
import "../styles/rubric.css";

const Rubric = ({rubric_id}) => {

  const [criterions, setCriterions] = useState({})
  const [levels, setLevels] = useState({})
  const [rubricName, setRubricName] = useState("")

  const fetchRubricInfo = () => {
    fetch(
      process.env.REACT_APP_API_URL + "getInstructorRubrics.php",
      {
        method: "POST",
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
        console.log("Results: ", result)
        setRubricName(result.data.name)
        setLevels(Object.values(result.data.levels).reverse())
        setCriterions(result.data.topics)
      })
      .catch((err) => {
        console.log(err);
      });
  };

  useEffect(() => {
    fetchRubricInfo()
  }, []);

  return (
    <div id={rubricName} className="rubric--container">
      <div className="rubric--content">
        <div className="rubric--header">
          <h2>
            {rubricName}
          </h2>
          <div className="rubric--header-btns">
            <button className="btn duplicate-btn">
              + Duplicate Rubric
            </button>
          </div>
        </div>
        {Object.entries(levels).length > 0 ? (
          <div className="table-overflow--div">
            <table className="rubric--table">
              <thead>
                <tr>
                  <th>Criterion</th>
                  {Object.values(levels).map((level) => (
                    <th>{level.name + " (" + level.score + " pts)"}</th>
                  ))}
                </tr>
              </thead>
              <tbody>
                {Object.values(criterions).map((criterion) =>
                  <tr>
                    <td className="criterion--heading">{criterion.question}</td>
                    {Object.values(criterion.responses).map((response) =>
                      <td>{response}</td>
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
  )

}

export default Rubric;

import React, { useEffect, useState } from "react";
import { CSVLink } from "react-csv";
import BarChart from "./Barchart";
import "../styles/viewresults.css";

const ViewResults = ({handleViewResultsModalChange, viewingCurrentSurvey, course}) => {

    /* Viewing Types of Survey Results */

    const [showRawSurveyResults, setShowRawSurveyResults] = useState(null)
    const [showNormalizedSurveyResults, setShowNormalizedSurveyResults] = useState(null)
    const [currentCSVData, setCurrentCSVData] = useState(null)

    const handleSelectedSurveyResultsModalChange = (surveyid, surveytype) => {
        fetch(
          process.env.REACT_APP_API_URL + "resultsView.php",
          {
            method: "POST",
            headers: {
              "Content-Type": "application/x-www-form-urlencoded",
            },
            body: new URLSearchParams({
              survey: surveyid,
              type: surveytype
            }),
          }
        )
          .then((res) => res.json())
          .then((result) => {
            if (surveytype == "raw-full") {
              setShowNormalizedSurveyResults(null)
              setShowRawSurveyResults(result)
              setRawResultsNumOfPages(Math.ceil((result.length - 1) / rawResultsPerPage))
              if (result.length > 1) {
                setCurrentCSVData(result)
              } else {
                setCurrentCSVData(null)
              }
            } else { // else if surveytype == "average" (For Normalized Results)
              setShowRawSurveyResults(null)
    
              console.log("Normalized Results", result)
              if (result.length > 1) {
                const results_without_headers = result.slice(1);
                const maxValue = Math.max(...results_without_headers.map(result => result[1]));
    
    
                let labels = {};
                let startLabel = 0.0;
                let endLabel = 0.2;
                labels[`${startLabel.toFixed(1)}-${endLabel.toFixed(1)}`] = 0
    
                startLabel = 0.01
                while (endLabel < maxValue) {
                  startLabel += 0.2;
                  endLabel += 0.2;
                  labels[`${startLabel.toFixed(2)}-${endLabel.toFixed(1)}`] = 0;
                }
    
                for (let individual_data of results_without_headers) {
                  for (let key of Object.keys(labels)) {
                    const label_split = key.split("-");
                    const current_min = parseFloat(label_split[0]);
                    const current_max = parseFloat(label_split[1]);
                    const current_normalized_average = individual_data[1].toFixed(1)
    
                    if (current_normalized_average >= current_min && current_normalized_average <= current_max) {
                      labels[key] += 1;
                    }
                  }
                }
    
                labels = Object.entries(labels)
                labels.unshift(["Normalized Averages", "Number of Students"])
    
                console.log(labels)
                setCurrentCSVData(result)
                setShowNormalizedSurveyResults(labels)
              } else {
                setCurrentCSVData(null)
                setShowNormalizedSurveyResults(true)
              }
            }
          })
          .catch((err) => {
            console.log(err);
          });
    }

    useEffect(() => {
        if (viewingCurrentSurvey) {
            handleSelectedSurveyResultsModalChange(viewingCurrentSurvey.id, "raw-full")
        }
        setShowNormalizedSurveyResults(null);

    }, [viewingCurrentSurvey]);


    /* Pagination for Raw Results*/

    const [rawResultsCurrentPage, setRawResultsCurrentPage] = useState(1)
    const [rawResultsNumOfPages, setRawResultsNumOfPages] = useState(1)
    const [rawResultNumbers, setRawResultNumbers] = useState([...Array(rawResultsNumOfPages + 1).keys()].slice(1))
    const [rawResultsRecords, setRawResultsRecords] = useState([])
    const rawResultsPerPage = 5
    const rawResultsLastIndex = rawResultsCurrentPage * rawResultsPerPage
    const rawResultsFirstIndex = (rawResultsLastIndex - rawResultsPerPage)

    const changeRawResultsPage = (number) => {
        setRawResultsCurrentPage(number)
    }

    const rawResultsPrevPage = () => {
        if(rawResultsFirstIndex >= rawResultsCurrentPage) {
        setRawResultsCurrentPage((prevPage) => prevPage - 1);
        }
    }

    const rawResultsNextPage = () => {
        if(rawResultsCurrentPage < rawResultNumbers.length) {
        setRawResultsCurrentPage((prevPage) => prevPage + 1);
        }
    }

    const displayPageNumbers = () => {
        const totalPages = rawResultNumbers.length;
        const maxDisplayedPages = 4;

        if (totalPages <= maxDisplayedPages) {
        return rawResultNumbers;
        }

        const middleIndex = Math.floor(maxDisplayedPages / 2);
        const startIndex = Math.max(0, rawResultsCurrentPage - middleIndex);
        const endIndex = Math.min(totalPages, startIndex + maxDisplayedPages);

        const displayedNumbers = [
        1,
        ...(startIndex > 1 ? ['...'] : []),
        ...rawResultNumbers.slice(startIndex, endIndex),
        ...(endIndex < totalPages ? ['...'] : []),
        totalPages
        ];
        
        return Array.from(new Set(displayedNumbers));
    };

    useEffect(() => {
        setRawResultNumbers([...Array(rawResultsNumOfPages + 1).keys()].slice(1))
        if(showRawSurveyResults !== null){
        const showRawSurveyResultsWithoutFirstElement = showRawSurveyResults.slice(1); // Exclude the first element
        const rawResultsRecordsAtCurrentPage = showRawSurveyResultsWithoutFirstElement.slice(rawResultsFirstIndex, rawResultsLastIndex)
        setRawResultsRecords(rawResultsRecordsAtCurrentPage)
        }
    }, [showRawSurveyResults, rawResultsCurrentPage])

    return (
        <div className="viewresults-modal">
          <div className="viewresults-modal-content">
            <div className="CancelContainer">
              <button className="CancelButton" style={{top: "0px"}} onClick={() => handleViewResultsModalChange(null)}>×</button>
            </div>
            <h2 className="viewresults-modal--heading">
              Results for {course.code} Survey: {viewingCurrentSurvey.name}
            </h2>
            <div className="viewresults-modal--main-button-container">
              <button className={showRawSurveyResults? "survey-result--option-active" : "survey-result--option"} onClick={() => handleSelectedSurveyResultsModalChange(viewingCurrentSurvey.id, "raw-full")}>Raw Results</button>
              <button className={showNormalizedSurveyResults? "survey-result--option-active" : "survey-result--option"} onClick={() => handleSelectedSurveyResultsModalChange(viewingCurrentSurvey.id, "average")}>Normalized Results</button>
            </div>
            {!showRawSurveyResults && !showNormalizedSurveyResults ? 
              <div className="viewresults-modal--no-options-selected-text">Select Option to View Results</div>
            : null}
            {
              showRawSurveyResults && currentCSVData ? (
                <div>
                  <div className="viewresults-modal--other-button-container">
                    <CSVLink className="downloadbtn" filename={"survey-" + viewingCurrentSurvey.id + "-raw-results.csv"} data={currentCSVData}>
                      Download Results
                    </CSVLink>
                  </div>
                  <div className="rawresults--table-container">
                    <table className="rawresults--table">
                      <thead>
                        <tr>
                          {showRawSurveyResults[0].map((header, index) => (
                            <th key={index}>{header}</th>
                          ))}
                        </tr>
                      </thead>
                      <tbody>
                        {rawResultsRecords && rawResultsRecords.map((rowData, rowIndex) => (
                          <tr key={rowIndex}>
                            {rowData.map((cellData, cellIndex) => (
                              cellData ? <td key={cellIndex}>{cellData}</td> 
                              : <td key={cellIndex}>--</td>

                              ))}
                          </tr>
                        ))}
                      </tbody>
                    </table>
                  </div>

                  {/* Pagination */}
                  <div className="rawresults--pagination-container">
                    <ul className="pagination">
                      <li className="page-item">
                        <div className="page-link page-link-prev" onClick={rawResultsPrevPage}>Prev</div>
                      </li>
                      {displayPageNumbers().map((pageNumber, index) => (
                        <li className={`page-item ${rawResultsCurrentPage === pageNumber ? 'page-active' : ''}`} key={index}>
                          {pageNumber === '...' ? (
                            <div className="page-link">...</div>
                          ) : (
                            <div className="page-link" onClick={() => changeRawResultsPage(pageNumber)}>{pageNumber}</div>
                          )}
                        </li>
                      ))}
                      <li className="page-item">
                        <div className="page-link page-link-next" onClick={rawResultsNextPage}>Next</div>
                      </li>
                    </ul>
                  </div>
                </div>
              )
              : (showRawSurveyResults && !currentCSVData) ? (
                <div className="viewresults-modal--no-options-selected-text">No Results Found</div>
              )
              : null}
            {
              showNormalizedSurveyResults && currentCSVData ? (
                <div>
                  <div className="viewresults-modal--other-button-container">
                    <CSVLink className="downloadbtn" filename={"survey-" + viewingCurrentSurvey.id + "-normalized-averages.csv"} data={currentCSVData}>
                      Download Results
                    </CSVLink>
                  </div>
                  <div className="viewresults-modal--barchart-container">
                    <BarChart survey_data={showNormalizedSurveyResults}/>
                  </div>
                </div>
             )
            : (showNormalizedSurveyResults && !currentCSVData) ? (
              <div className="viewresults-modal--no-options-selected-text">No Results Found</div>
            ) 
            : null}
          </div>
        </div>
    )

}

export default ViewResults;

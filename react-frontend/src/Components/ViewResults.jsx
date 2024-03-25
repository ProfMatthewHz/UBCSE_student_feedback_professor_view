import React, {useEffect, useState} from "react";
import {CSVLink} from "react-csv";
import {DataTable} from "primereact/datatable";
import {Column} from "primereact/column";
import "primereact/resources/themes/lara-light-blue/theme.css";
import "primereact/resources/primereact.min.css";
import BarChart from "./Barchart";
import "../styles/viewresults.css";

const ViewResults = ({
                         handleViewResultsModalChange,
                         viewingCurrentSurvey,
                         course,
                     }) => {
    /* Viewing Types of Survey Results */

    const [showRawSurveyResults, setShowRawSurveyResults] = useState(null); // For Raw Results
    const [rawResultsHeaders, setRawResultsHeaders] = useState(null); // For Raw Results
    const [rawResults, setRawResults] = useState([]); // For Raw Results
    const [showNormalizedSurveyResults, setShowNormalizedSurveyResults] =
        useState(null); // For Normalized Results
    const [normalizedTableHeaders, setNormalizedTableHeaders] = useState(null); // For Normalized Results
    const [normalizedResults, setNormalizedResults] = useState([]); // For Normalized Results
    const [currentCSVData, setCurrentCSVData] = useState(null); // For CSV Download
    const [feedbackCountData, setFeedbackCountData] = useState([]); //For Feedback View Count
    /**
     * Maps headers to values
     * @param headers
     * @param values
     * @returns {*}
     */
    const mapHeadersToValues = (headers, values) => {
        // Capitalize headers
        const capitalizedHeaders = headers.map(
            (header) => header.charAt(0).toUpperCase() + header.slice(1).toLowerCase()
        );

        return values.map((row) => {
            return row.reduce((obj, value, index) => {
                obj[capitalizedHeaders[index]] = value;
                return obj;
            }, {});
        });
    };

    /**
     * Handles the change of the selected survey results modal
     * @param surveyid
     * @param surveytype
     */
    const handleSelectedSurveyResultsModalChange = (surveyid, surveytype) => {
        fetch(process.env.REACT_APP_API_URL + "resultsView.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded",
            },
            body: new URLSearchParams({
                survey: surveyid,
                type: surveytype,
            }),
        })
            .then((res) => res.json())
            .then((result) => {
                if (surveytype == "raw-full") {
                   
                    setShowNormalizedSurveyResults(null);
                    setShowRawSurveyResults(result.slice(1));
                    setRawResultsHeaders(result[0]);
                    const mappedResults = mapHeadersToValues(result[0], result.slice(1));
                    console.log(mappedResults);
                    setRawResults(mappedResults);
                    if (result.length > 1) {
                        setCurrentCSVData(result);
                    } else {
                        setCurrentCSVData(null);
                    }
                } else {
                    // else if surveytype == "average" (For Normalized Results)
                    setShowRawSurveyResults(null);
                    console.log("------RIGHT HERE----------");
                    console.log(result)

                    if (result.length > 1) {
                        const results_without_headers = result.slice(1);
                        const maxValue = Math.max(
                            ...results_without_headers.map((result) => result[1])
                        );

                        let labels = {};
                        let startLabel = 0.0;
                        let endLabel = 0.2;
                        labels[`${startLabel.toFixed(1)}-${endLabel.toFixed(1)}`] = 0;

                        startLabel = 0.01;
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
                                const current_normalized_average =
                                    individual_data[1].toFixed(1);

                                if (
                                    current_normalized_average >= current_min &&
                                    current_normalized_average <= current_max
                                ) {
                                    labels[key] += 1;
                                }
                            }
                        }

                        labels = Object.entries(labels);
                        labels.unshift(["Normalized Averages", "Number of Students"]);
                       
                        console.log(labels);
                        console.log(result);
                        const mappedNormalizedResults = mapHeadersToValues(
                            result[0],
                            result.slice(1)
                        );
                        setCurrentCSVData(result);
                        setShowNormalizedSurveyResults(labels);
                        setNormalizedResults(mappedNormalizedResults);
                        setNormalizedTableHeaders(result[0]);
                    } else {
                        setCurrentCSVData(null);
                        setShowNormalizedSurveyResults(true);
                    }
                }
            })
            .catch((err) => {
                console.log(err);
            });
    };

    useEffect(() => {
        if (viewingCurrentSurvey) {
            handleSelectedSurveyResultsModalChange(
                viewingCurrentSurvey.id,
                "raw-full"
            );
        }
        setShowNormalizedSurveyResults(null);
    }, [viewingCurrentSurvey]);

    useEffect(() => {
        console.log(showRawSurveyResults);
        // console.log(rawResultsRecords)
    }, []);


    //Get feedback view count data from database
    const fetchFeedbackCount = () => {
        // Adjust the URL to point to your surveys endpoint and include the survey type query parameter
        const url = `${process.env.REACT_APP_API_URL}studentSurveyCount.php?type=upcoming`;
  
        fetch(url, {
            method: "GET",
            // Note: Removed the 'Content-Type' header and 'body' since it's a GET request
        })
            .then((res) => res.json())
            .then((result) => {
                // Assuming you have a way to set the surveys in your state or UI, similar to how courses were set
                setFeedbackCountData(result); // Consider renaming this function to reflect that it now sets surveys, not courses
            })
            .catch((err) => {
                console.log(err);
            });
    };
  
    useEffect(() => {
        fetchFeedbackCount()
    }, []);
  
  
    //Send JSONIFY version of {"student_id":id, "survey_name":surveyName, "survey_id":surveyID} to api for feedback to be updated
    const postDataToApi = (postData) => {
      console.log("Feedback Count Updated");
      const url = `${process.env.REACT_APP_API_URL}studentSurveyCount.php?type=current`; 
  
      // POST request to send additional data
      fetch(url, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(postData),
      })
        .then((response) => response.json())
        .then((postDataResult) => {
          // Handle the response from the POST request if needed
          console.log('POST Request Result:', postDataResult);
        })
        .catch((postErr) => {
          console.error('Error in POST request:', postErr);
        });
    };
  
    console.log("Normalized results");
    console.log(normalizedResults);

    return (
        <div className="viewresults-modal">
            <div className="viewresults-modal-content">
                <div className="CancelContainer">
                    <button
                        className="CancelButton"
                        style={{top: "0px"}}
                        onClick={() => handleViewResultsModalChange(null)}
                    >
                        ×
                    </button>
                </div>
                <h2 className="viewresults-modal--heading">
                    Results for {course.code} Survey: {viewingCurrentSurvey.name}
                </h2>
                <div className="viewresults-modal--main-button-container">
                    <button
                        className={
                            showRawSurveyResults
                                ? "survey-result--option-active"
                                : "survey-result--option"
                        }
                        onClick={() =>
                            handleSelectedSurveyResultsModalChange(
                                viewingCurrentSurvey.id,
                                "raw-full"
                            )
                        }
                    >
                        Raw Results
                    </button>
                    <button
                        className={
                            showNormalizedSurveyResults
                                ? "survey-result--option-active"
                                : "survey-result--option"
                        }
                        onClick={() =>
                            handleSelectedSurveyResultsModalChange(
                                viewingCurrentSurvey.id,
                                "average"
                            )
                        }
                    >
                        Normalized Results
                    </button>
                </div>
                {!showRawSurveyResults && !showNormalizedSurveyResults ? (
                    <div className="viewresults-modal--no-options-selected-text">
                        Select Option to View Results
                    </div>
                ) : null}
                {showRawSurveyResults && currentCSVData ? (
                    <div>
                        <div className="viewresults-modal--other-button-container">
                            <CSVLink
                                className="downloadbtn"
                                filename={
                                    "survey-" + viewingCurrentSurvey.id + "-raw-results.csv"
                                }
                                data={currentCSVData}
                            >
                                Download Results
                            </CSVLink>
                        </div>
                        <div className="rawresults--table-container">
                            {/* Table for Raw Results */}
                            <DataTable
                                value={rawResults}
                                paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                                paginator
                                rows={5}
                                className="rawresults--table"
                                currentPageReportTemplate="Showing {first} to {last} of {totalRecords} entries"
                                emptyMessage="No results found"
                            >
                                {Object.keys(rawResults[0]).map((header) => {
                                    return header === "Reviewee name (email)" ||
                                    header === "Reviewer name (email)" ? (
                                        <Column
                                            field={header}
                                            header={header}
                                            sortable
                                            style={{width: `${100 / rawResultsHeaders.length}%`}}
                                            filter
                                            filterPlaceholder="Search by email"
                                            filterMatchMode="contains"
                                        ></Column>
                                    ) : (
                                        <Column
                                            field={header}
                                            header={header}
                                            sortable
                                            style={{width: `${100 / rawResultsHeaders.length}%`}}
                                        ></Column>
                                    );
                                })}
                            </DataTable>
                        </div>
                    </div>
                ) : showRawSurveyResults && !currentCSVData ? (
                    <div className="viewresults-modal--no-options-selected-text">
                        No Results Found
                    </div>
                ) : null}
                {showNormalizedSurveyResults && currentCSVData ? (
                    <div>
                        <div className="viewresults-modal--other-button-container">
                            <CSVLink
                                className="downloadbtn"
                                filename={
                                    "survey-" +
                                    viewingCurrentSurvey.id +
                                    "-normalized-averages.csv"
                                }
                                data={currentCSVData}
                            >
                                Download Results
                            </CSVLink>
                        </div>
                        <div className="viewresults-modal--barchart-container">
                            <BarChart survey_data={showNormalizedSurveyResults}/>
                            {/* Table for normalized averages*/}
                            <DataTable
                                value={normalizedResults}
                                paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                                paginator
                                rows={5}
                                currentPageReportTemplate="Showing {first} to {last} of {totalRecords} entries"
                                emptyMessage="No results found"
                            >
                                {Object.keys(normalizedResults[0]).map((header) => {
                                    return header === "Reviewee name (email)" ||
                                    header === "Reviewer name (email)" ? (
                                        <Column
                                            field={header}
                                            header={header}
                                            sortable
                                            style={{width: `${100 / normalizedTableHeaders.length}%`}}
                                            filter
                                            filterPlaceholder="Search by email"
                                            filterMatchMode="contains"
                                        ></Column>
                                    ) : (
                                        <Column
                                            field={header}
                                            header={header}
                                            sortable
                                            style={{width: `${100 / normalizedTableHeaders.length}%`}}
                                        ></Column> 
                                        // TODO: Add another column here for feedback count
                                        
                                    );
                                })}
                            </DataTable>
                        </div>
                    </div>
                ) : showNormalizedSurveyResults && !currentCSVData ? (
                    <div className="viewresults-modal--no-options-selected-text">
                        No Results Found
                    </div>
                ) : null}
            </div>
        </div>
    );
};

export default ViewResults;

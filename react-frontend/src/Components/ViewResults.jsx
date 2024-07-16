import React, {useEffect, useState} from "react";
import {CSVLink} from "react-csv";
import {DataTable} from "primereact/datatable";
import {Column} from "primereact/column";
import "primereact/resources/themes/lara-light-blue/theme.css";
import "primereact/resources/primereact.min.css";
import BarChart from "./Barchart";
import "../styles/viewresults.css";

const ViewResults = ({
                        closeViewResultsModal,
                        surveyToView,
                        course,
                     }) => {
    /* Viewing Types of Survey Results */
   

    const [showRawSurveyResults, setShowRawSurveyResults] = useState(null); // For Raw Results
    const [rawResultsHeaders, setRawResultsHeaders] = useState(null); // For Raw Results
    const [rawResults, setRawResults] = useState([]); // For Raw Results
    const [showNormalizedSurveyResults, setShowNormalizedSurveyResults] = useState(null); // For Normalized Results
    const [normalizedTableHeaders, setNormalizedTableHeaders] = useState([]); // For Normalized Results
    const [normalizedResults, setNormalizedResults] = useState([]); // For Normalized Results
    const [currentCSVData, setCurrentCSVData] = useState([]); // For CSV Download
    const [completionCSVData, setCompletionCSVData] = useState([]); // For CSV Download for Completion Results
    const [individualAveragesCSVData, setIndividualAveragesCSVData] = useState([]); // For CSV Download for Completion Results
    
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

    //Fetches the data that tells use who completed surveys
    const fetchCompleted = (surveyid) => {
            fetch(process.env.REACT_APP_API_URL + "resultsView.php", {
                method: "POST",
                credentials: "include",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded",
                },

                body: new URLSearchParams({
                    survey: surveyid,
                    type: "completion",
                }),
            })
                .then((res) => res.json())
                .then((result) => {
                    const completedCSVLines = [["Name", "Email", "Completion Status"]];
                    for (let dict of result) {
                        const name = dict["name"];
                        const email = dict["email"];
                        const completed = dict["completed"];
                        const row = [name, email, completed];
                        completedCSVLines.push(row);
                    }
                    setCompletionCSVData(completedCSVLines);
                })
                .catch((err) => {
                    console.error('There was a problem with your fetch operation:', err);
                });
    };

    //Fetches the data with the individual averages for students
    const fetchIndividualAverages = (surveyid) => {
        fetch(process.env.REACT_APP_API_URL + "resultsView.php", {
            method: "POST",
            credentials: "include",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded",
            },

            body: new URLSearchParams({
                survey: surveyid,
                type: "individual",
            }),
        })
            .then((res) => res.json())
            .then((result) => {
                const csvLines = [];
                csvLines.push(...result);
                setIndividualAveragesCSVData(csvLines);
            })
            .catch((err) => {
                console.error('There was a problem with your fetch operation:', err);
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
            credentials: "include",
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
                if (surveytype === "raw-full") {
                    setShowNormalizedSurveyResults(null);
                    setShowRawSurveyResults(result.slice(1));
                    setRawResultsHeaders(result[0]);
                    const mappedResults = mapHeadersToValues(result[0], result.slice(1));
                    setRawResults(mappedResults);
                    if (result.length > 1) {
                        setCurrentCSVData(result);
                        fetchCompleted(surveyid);
                    } else {
                        setCurrentCSVData(null);
                    }
                    
                } else {
                    setShowRawSurveyResults(null);
                    if (result.length > 1) {
                        fetchIndividualAverages(surveyid);
                        const results_without_headers = result.slice(1);
                        console.log(results_without_headers)
                        const maxValue = Math.max(
                            ...results_without_headers.map((result) => isNaN(result[2])? 0 : result[2])
                        );
                        let labels = {'0.0-0.2' : 0};
                        let startLabel = 0.01;
                        let endLabel = 0.2;
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
                                const current_normalized_average = individual_data[2];

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
                        
                        const mappedNormalizedResults = mapHeadersToValues(
                            result[0],
                            results_without_headers
                        );
                        setCurrentCSVData(result);
                        setShowNormalizedSurveyResults(labels);
                    
                        setNormalizedResults(mappedNormalizedResults);
                        setNormalizedTableHeaders(result[0]);  
                    } else {
                        setShowNormalizedSurveyResults(true);
                    }
                }
            })
            .catch((err) => {
                console.log(err);
            });
    };

useEffect(() => {
        if (surveyToView) {
            handleSelectedSurveyResultsModalChange(
                surveyToView.id,
                "raw-full"
            );
        }
        setShowNormalizedSurveyResults(null);
    }, [surveyToView]);
    
    return (
        <div className="viewresults-modal">
            <div className="viewresults-modal-content">
                <div className="CancelContainer">
                    <button
                        className="CancelButton"
                        style={{top: "0px"}}
                        onClick={() => closeViewResultsModal(null)}
                    >
                        ×
                    </button>
                </div>
                <h2 className="viewresults-modal--heading">
                    Results for {course.code} Survey: {surveyToView.name}
                </h2>
                <div className="viewresults-modal--main-button-container">
                    <button
                        className={
                            showRawSurveyResults
                                ? "survey-result--option-active"
                                : "survey-result--option"
                        }
                        onClick={() =>{
                            handleSelectedSurveyResultsModalChange(
                                surveyToView.id,
                                "raw-full"
                            );
                            console.log("Raw Results Clicked")

                        }
                        }
                    >
                        Raw Surveys
                    </button>
                   
                    <button
                        className={
                            showNormalizedSurveyResults
                                ? "survey-result--option-active"
                                : "survey-result--option"
                        }
                        onClick={() =>
                           { handleSelectedSurveyResultsModalChange(surveyToView.id, "average");  
                           console.log("VIEW FEEDBACK CLICKED!!") ;
                        } 
                        }
                    >
                        Individual Results
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
                        <div className="viewresults-modal--download-button">
                            <CSVLink
                                className="downloadbtn"
                                filename={
                                    "survey-" + surveyToView.id + "-raw-results.csv"
                                }
                                data={currentCSVData}
                            >
                                Download Surveys
                            </CSVLink>
                        </div>
                        
                        {/* Button to view who completed the survey */}
                        <div className="viewresults-modal--download-button">
                            <CSVLink
                                className="downloadbtn"
                                filename={
                                    "survey-" + surveyToView.id + "-completion-results.csv"
                                }
                                data={completionCSVData}
                            >
                                Download Completion Results
                            </CSVLink>
                        </div>
                    </div>

                        <div className="rawresults--table-container">
                            {/* Table for Raw Surveys */}
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
                                            filterPlaceholder="Search by name or email"
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
                            <div className="viewresults-modal--download-button">
                                <CSVLink
                                    className="downloadbtn"
                                    filename={
                                        "survey-" + 
                                        surveyToView.id + 
                                        "-normalized-averages.csv"
                                    }
                                    data={currentCSVData}
                                >
                                    Download Normalized Scores
                                </CSVLink>
                            </div>
                            
                            {/* Button to view who completed the survey */}
                            <div className="viewresults-modal--download-button">
                                <CSVLink
                                    className="downloadbtn"
                                    filename={
                                        "survey-" + 
                                        surveyToView.id + 
                                        "-individual-averages.csv"
                                    }
                                    data={individualAveragesCSVData}
                                >
                                    Download Criterion Scores
                                </CSVLink>
                            </div>
                        </div>
                        <div className="viewresults-modal--barchart-container">
                        {/* {updateNormalizeFlag === 0 && callFetchFeedbackCount(surveyToView.id)}
                         */}
                            <BarChart survey_data={showNormalizedSurveyResults}/>
                           
                            <DataTable
                                value={normalizedResults}
                                paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                                paginator
                                rows={5}
                                currentPageReportTemplate="Showing {first} to {last} of {totalRecords} entries"
                                emptyMessage="No results found"
                            >
                                {Object.keys(normalizedResults[0]).map((header) => {
                                    return header === "Name" || header === "Email" ? (
                                        <Column
                                            field={header}
                                            header={header}
                                            sortable
                                            style={{width: `${100 / normalizedTableHeaders.length}%`}}
                                            filter
                                            filterPlaceholder="Search by name"
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
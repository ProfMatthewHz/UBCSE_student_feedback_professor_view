import React, {useCallback, useEffect, useState} from "react";
import {CSVLink} from "react-csv";
import {DataTable} from "primereact/datatable";
import {Column} from "primereact/column";
import "primereact/resources/themes/lara-light-blue/theme.css";
import "primereact/resources/primereact.min.css";
import BarChart from "./Barchart";
import "../styles/viewresults.css";

const SurveyResultsView = ({closeViewResultsModal, surveyToView, course}) => {
    /* Viewing Types of Survey Results */
    const [rawSurveysHeaders, setRawSurveysHeaders] = useState(null); // For Raw Results
    const [rawSurveys, setRawSurveys] = useState(null); // For Raw Results
    const [showNormalizedSurveyResults, setShowNormalizedSurveyResults] = useState(null); // For Normalized Results
    const [rawSurveyCSVData, setRawSurveyCSVData] = useState(); // For CSV download of the raw survey data
    const [normalizedCSVData, setNormalizedCSVData] = useState(); // For CSV download of the raw survey data
    const [normalizedTableHeaders, setNormalizedTableHeaders] = useState(null); // For Normalized Results
    const [normalizedResults, setNormalizedResults] = useState(null); // For Normalized Results
    const [completionCSVData, setCompletionCSVData] = useState([]); // For CSV Download for Completion Results
    const [individualAveragesCSVData, setIndividualAveragesCSVData] = useState([]); // For CSV Download for Completion Results
    const [surveyType, setSurveyType] = useState(""); // For Survey Type

    /**
     * Maps headers to values
     * @param headers
     * @param values
     * @returns {*}
     */
    const mapHeadersToValues = (headers, values) => {
          return values.map((row) => {
            return row.reduce((obj, value, index) => {
                obj[headers[index]] = value;
                return obj;
            }, {});
        });
    };

    //Fetches the data that tells use who completed surveys
    const fetchCompleted = useCallback((surveyid) => {
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
                    const completedCSVLines = result.map((dict) => {return [dict["name"], dict["email"], dict["completed"]]});
                    completedCSVLines.unshift(["Name", "Email", "Completion Status"]);
                    setCompletionCSVData(completedCSVLines);
                })
                .catch((err) => {
                    console.error('There was a problem with your fetch operation:', err);
                });
    },[]);

    //Fetches the data with the individual averages for students
    const fetchIndividualAverages = useCallback((surveyid) => {
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
    },[]);

    //Fetches the data with the individual averages for students
    const fetchRawSurveys = useCallback((surveyid) => {
        fetch(process.env.REACT_APP_API_URL + "resultsView.php", {
            method: "POST",
            credentials: "include",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded",
            },

            body: new URLSearchParams({
                survey: surveyid,
                type: "raw-full",
            }),
        })
            .then((res) => res.json())
            .then((result) => {
                setRawSurveysHeaders(result[0]);
                const mappedResults = mapHeadersToValues(result[0], result.slice(1));
                for (let result of mappedResults) {
                    result["Norm. Avg."] = isNaN(result["Norm. Avg."]) ? result["Norm. Avg."] : parseFloat(result["Norm. Avg."]).toFixed(4);
                }
                setRawSurveys(mappedResults);
                setRawSurveyCSVData(result);
            })
            .catch((err) => {
                console.error('There was a problem with your fetch operation:', err);
            });
    }, []);

    //Fetches the data with the individual averages for students
    const fetchNormalizedResults = useCallback((surveyid) => {
        fetch(process.env.REACT_APP_API_URL + "resultsView.php", {
            method: "POST",
            credentials: "include",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded",
            },

            body: new URLSearchParams({
                survey: surveyid,
                type: "average",
            }),
        })
            .then((res) => res.json())
            .then((result) => {
                const tableHeaders = result[0];
                setNormalizedTableHeaders(tableHeaders)
                if (result.length > 1) {
                    const results_without_headers = result.slice(1);
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
                        tableHeaders,
                        results_without_headers
                    );
                    setNormalizedCSVData(result);
                    setShowNormalizedSurveyResults(labels);
                    for (let result of mappedNormalizedResults) {
                        result["Norm. Avg."] = isNaN(result["Norm. Avg."]) ? result["Norm. Avg."] : parseFloat(result["Norm. Avg."]).toFixed(4);
                    }
                    setNormalizedResults(mappedNormalizedResults);
                } else {
                    setShowNormalizedSurveyResults([]);
                }
            })
            .catch((err) => {
                console.error('There was a problem with your fetch operation:', err);
            });
    }, []);


useEffect(() => {
        if (surveyToView) {
            fetchRawSurveys(surveyToView.id);
            fetchCompleted(surveyToView.id);
            fetchNormalizedResults(surveyToView.id);
            fetchIndividualAverages(surveyToView.id);
            setSurveyType("raw-full");
        }
    }, [surveyToView, fetchRawSurveys, fetchNormalizedResults, fetchCompleted, fetchIndividualAverages]);
    
    return (
        <div className="modal">
            <div style={{ width: "1200px", maxWidth: "90vw" }} className="modal-content modal-phone">
                <div className="CancelContainer">
                    <button
                        className="CancelButton"
                        onClick={() => closeViewResultsModal(null)}
                    >
                        ×
                    </button>
                </div>
            <h2 className="modal--main-title">
                Results for {course.code} Survey: {surveyToView.name}
            </h2>
            <div className="viewresults-modal--main-button-container">
                <button
                    className={
                        surveyType === "raw-full"
                            ? "survey-result--option-active"
                            : "survey-result--option"
                    }
                    onClick={() =>{
                        setSurveyType("raw-full");
                    }
                    }
                >
                    Raw Surveys
                </button>
                
                <button
                    className={
                        surveyType === "average"
                            ? "survey-result--option-active"
                            : "survey-result--option"
                        }
                        onClick={() => {
                            setSurveyType("average")
                        } 
                    }
                >
                    Individual Results
                </button>
            </div>
            {surveyType === "raw-full" && rawSurveys && rawSurveysHeaders ? (
                <div>
                    <div className="viewresults-modal--other-button-container">
                        <div className="viewresults-modal--download-button">
                            <CSVLink
                                className="downloadbtn"
                                filename={
                                    "survey-" + surveyToView.id + "-all-surveys.csv"
                                }
                                data={rawSurveyCSVData}
                            >
                                Download All Surveys
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
                            value={rawSurveys}
                            paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                            paginator
                            rows={10}
                            rowsPerPageOptions={[5, 10, 25, 50]}
                            className="rawresults--table"
                            currentPageReportTemplate="{first} to {last} of {totalRecords}" 
                            emptyMessage="No results found"
                        >
                            {rawSurveysHeaders.map((header) => {
                                return header === "Reviewee" || header === "Reviewer" ? (
                                        <Column
                                            key={header}
                                            field={header}
                                            header={header}
                                            sortable
                                            filter
                                            filterPlaceholder="Search by name or email"
                                            filterMatchMode="contains"
                                        ></Column>
                                    ) : (
                                        <Column
                                            key={header}
                                            field={header}
                                            header={header}
                                            sortable
                                        ></Column>
                                    );
                            })}
                        </DataTable>
                    </div>
                </div>
                ) : surveyType === "raw-full" && !rawSurveys && (
                    <div className="viewresults-modal--no-options-selected-text">
                        No Results Found
                    </div>)}
            {surveyType === "average" && normalizedResults ? (
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
                                    data={normalizedCSVData}
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
                                rowsPerPageOptions={[5, 10, 25, 50]}
                                className="rawresults--table"
                                currentPageReportTemplate="{first} to {last} of {totalRecords}" 
                                emptyMessage="No results found"
                            >
                                {normalizedTableHeaders.map((header) => {
                                    return header === "Name" || header === "Email" ? (
                                        <Column
                                            key={header}
                                            field={header}
                                            header={header}
                                            sortable
                                            filter
                                            filterPlaceholder="Search by name"
                                            filterMatchMode="contains"
                                        ></Column>
                                    ) : (
                                        <Column
                                            key={header}
                                            field={header}
                                            header={header}
                                            sortable
                                        ></Column>
                                    );
                                })}
                            </DataTable>
                        </div>
                    </div>
                ) : surveyType === "average" && !normalizedResults && (
                    <div className="viewresults-modal--no-options-selected-text">
                        No Results Found
                    </div>)}
            </div>
        </div>
    );
};

export default SurveyResultsView;
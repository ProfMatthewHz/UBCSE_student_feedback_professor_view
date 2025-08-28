import React, {useCallback, useEffect, useState} from "react";
import {CSVLink} from "react-csv";
import {DataTable} from "primereact/datatable";
import {Column} from "primereact/column";
import "primereact/resources/themes/lara-light-blue/theme.css";
import "primereact/resources/primereact.min.css";
import BarChart from "./Barchart";
import "../styles/viewresults.css";

const SurveyResultsModal = ({closeViewResultsModal, surveyToView, course}) => {
    /* Viewing Types of Survey Results */
    const [rawSurveys, setRawSurveys] = useState([]); // For Raw Results
    const [rawSurveyCSVData, setRawSurveyCSVData] = useState([]); // For CSV download of the raw survey data
    const [normalizedBarChartData, setNormalizedBarChartData] = useState([]); // For Normalized Results
    const [normalizedResultsCSVData, setNormalizedResultsCSVData] = useState([]); // For CSV download of the raw survey data
    const [normalizedResults, setNormalizedResults] = useState([]); // For display of normalized Results
    const [completionCSVData, setCompletionCSVData] = useState([]); // For CSV Download for Completion Results
    const [individualAveragesCSVData, setIndividualAveragesCSVData] = useState([]); // For CSV Download for Completion Results
    const [surveyType, setSurveyType] = useState("raw-full"); // For Survey Type

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
                    setCompletionCSVData(result);
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
                setIndividualAveragesCSVData(result);
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
                setRawSurveyCSVData(result);
            })
            .catch((err) => {
                console.error('There was a problem with your fetch operation:', err);
            });
    }, []);

    useEffect(() => {
        if (rawSurveyCSVData.length > 1) {
            const mappedResults = mapHeadersToValues(rawSurveyCSVData[0], rawSurveyCSVData.slice(1));
            const finalResult = mappedResults.map((result) => {
                return {...result, "Norm. Avg.": isNaN(result["Norm. Avg."]) ? result["Norm. Avg."] : parseFloat(result["Norm. Avg."]).toFixed(4)};
            });
            setRawSurveys(finalResult);
        }
    }, [rawSurveyCSVData]);

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
                if (result.length > 1) {
                    setNormalizedResultsCSVData(result);
                }
            })
            .catch((err) => {
                console.error('There was a problem with your fetch operation:', err);
            });
    }, []);

    useEffect(() => {
        if (normalizedResultsCSVData.length > 1) {
            const mappedNormalizedResults = mapHeadersToValues(normalizedResultsCSVData[0], normalizedResultsCSVData.slice(1));
            const finalResults = mappedNormalizedResults.map((result) => ({
                ...result,
                "Norm. Avg.": isNaN(result["Norm. Avg."]) ? result["Norm. Avg."] : parseFloat(result["Norm. Avg."]).toFixed(4)
            }));
            setNormalizedResults(finalResults);
        }
    }, [normalizedResultsCSVData]);

    useEffect(() => {
        if (normalizedResultsCSVData.length > 1) {
            const results = normalizedResultsCSVData.slice(1);
            const maxValue = Math.max(
                ...results.map((result) => isNaN(result.at(-2)) ? 0 : result.at(-2))
            );
            const lastIndex = Math.max(0, Math.floor((maxValue + 0.19999999) / 0.2) - 1);
            let labels = [ ['0.0-0.2', 0 ] ];
            while (labels.length <= lastIndex) {
                let startLabel = 0.01 + 0.2 * labels.length;
                let endLabel = startLabel + 0.19;
                labels.push([`${startLabel.toFixed(2)} - ${endLabel.toFixed(1)}`, 0]);
            }
            for (let individual_data of results) {
                const avg = individual_data.at(-2);
                if (!isNaN(avg)) {
                    let index = Math.max(0, Math.floor((avg + 0.19999999) / 0.2) - 1);
                    labels[index][1] += 1;
                }
            }
            labels.unshift(["Normalized Averages", "Number of Students"]);
            setNormalizedBarChartData(labels);
        }
    }, [normalizedResultsCSVData]);

    useEffect(() => {
        if (surveyToView) {
            fetchRawSurveys(surveyToView.id);
            fetchCompleted(surveyToView.id);
            fetchNormalizedResults(surveyToView.id);
            fetchIndividualAverages(surveyToView.id);
        }
    }, [surveyToView, fetchRawSurveys, fetchNormalizedResults, fetchCompleted, fetchIndividualAverages]);
    
    return (
        <div className="modal">
            <div style={{ minWidth: "75vw", maxWidth: "90vw" }} className="modal-content modal-phone">
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
            {surveyType === "raw-full" && rawSurveyCSVData.length > 0 ? (
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
                            {rawSurveyCSVData[0].map((header) => {
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
                ) : surveyType === "raw-full" && (
                    <div className="viewresults-modal--no-options-selected-text">
                        No Results Found
                    </div>)}
            {surveyType === "average" && normalizedResults.length > 0 ? (
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
                                    data={normalizedResultsCSVData}
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
                            <BarChart survey_data={normalizedBarChartData}/>
                           
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
                                {normalizedResultsCSVData[0].map((header) => {
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
                ) : surveyType === "average" && (
                    <div className="viewresults-modal--no-options-selected-text">
                        No Results Found
                    </div>)}
            </div>
        </div>
    );
};

export default SurveyResultsModal;
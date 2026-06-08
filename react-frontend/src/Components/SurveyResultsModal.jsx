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
    const [barChartData, setBarChartData] = useState([]); // For Normalized Results
    const [normalizedCSVData, setNormalizedCSVData] = useState([]); // For CSV download of the raw survey data
    const [normalizedDisplayData, setNormalizedDisplayData] = useState([]); // For display of normalized Results
    const [normalizedResults, setNormalizedResults] = useState([]); // For Normalized Results
    const [normalizedIndex, setNormalizedIndex] = useState(-1); // The index to display (or -1 for all)
    const [completionCSVData, setCompletionCSVData] = useState([]); // For CSV Download for Completion Results
    const [individualAveragesCSVData, setIndividualAveragesCSVData] = useState([]); // For CSV Download for Completion Results
    const [surveyType, setSurveyType] = useState("raw-full"); // For Survey Type

    // Zips together the parallel arrays into a single object
    const zipKeysAndValues = (keys, values) =>
        // Initializes an empty object and then adds each key to the value which appears at the same index in the values array
        keys.reduce((obj, key, index) => {
            obj[key] = values[index];
            return obj;
        }, {});

    // Given an table of data where the first row are the headers, create an array of objects with that data
    const convertTableToArrayOfObjects = (table) => {
        const headers = table[0];
        const values = table.slice(1);
        const retVal = values.map((row) => zipKeysAndValues(headers, row));
        return retVal;
    };

    //Fetches the data that tells use who completed surveys
    const fetchCompleted = useCallback((surveyid) => {
            const formData = new FormData();
            formData.append("survey", surveyid);
            formData.append("type", "completion");
            fetch(process.env.REACT_APP_API_URL + "resultsView.php", {
                method: "POST",
                credentials: "include",
                body: formData
            })
                .then((res) => {
                    if (res.ok) {
                        return res.json();
                    } else if (res.status === 511) {
                        // If we get a 511 error, we have been logged out of shibboleth and need to redirect to the starting page
                        window.location.href = `${process.env.REACT_APP_API_START}`;
                    }
                })
                .then((result) => {
                    setCompletionCSVData(result);
                })
                .catch((err) => {
                    console.error('There was a problem with your fetch operation:', err);
                });
    },[]);

    //Fetches the data with the individual averages for students
    const fetchIndividualAverages = useCallback((surveyid) => {
        const formData = new FormData();
        formData.append("survey", surveyid);
        formData.append("type", "individual");
        fetch(process.env.REACT_APP_API_URL + "resultsView.php", {
            method: "POST",
            credentials: "include",
            body: formData
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
        const formData = new FormData();
        formData.append("survey", surveyid);
        formData.append("type", "raw-full");
        fetch(process.env.REACT_APP_API_URL + "resultsView.php", {
            method: "POST",
            credentials: "include",
            body: formData
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
            const mappedResults = convertTableToArrayOfObjects(rawSurveyCSVData);
            const finalResult = mappedResults.map((result) => {
                return {...result, "Norm. Avg.": isNaN(result["Norm. Avg."]) ? result["Norm. Avg."] : parseFloat(result["Norm. Avg."]).toFixed(4)};
            });
            setRawSurveys(finalResult);
        }
    }, [rawSurveyCSVData]);

    //Fetches the data with the individual averages for students
    const fetchNormalizedResults = useCallback((surveyid) => {
        const formData = new FormData();
        formData.append("survey", surveyid);
        formData.append("type", "average");
        fetch(process.env.REACT_APP_API_URL + "resultsView.php", {
            method: "POST",
            credentials: "include",
            body: formData
        })
            .then((res) => res.json())
            .then((result) => {
                if (result.length > 1) {
                    setNormalizedCSVData(result);
                }
            })
            .catch((err) => {
                console.error('There was a problem with your fetch operation:', err);
            });
    }, []);

    useEffect(() => {
        if (normalizedCSVData.length > 1) {
            const mappedNormalizedResults = convertTableToArrayOfObjects(normalizedCSVData);

            const maxValue = Math.max(...mappedNormalizedResults.map((data) => isNaN(data["Norm. Avg."])? 0 : data["Norm. Avg."]));
            const lastIndex = Math.max(0, Math.floor((maxValue + 0.19999999) / 0.2) - 1);

            // Create the array of arrays that will hold the normalized data 
            const normData = [];
            for (let i = 0; i <= lastIndex + 1; i++) {
                normData.push([]);
            }
            for (let result of mappedNormalizedResults) {
                if (isNaN(result["Norm. Avg."])) {
                    normData[lastIndex].push(result);
                } else {
                    const index = Math.max(0, Math.floor((result["Norm. Avg."] + 0.19999999) / 0.2) - 1);
                    result["Norm. Avg."] = parseFloat(result["Norm. Avg."]).toFixed(4);
                    normData[index].push(result);
                }
            }
            setNormalizedResults(normData);
        }
    }, [normalizedCSVData]);

    useEffect(() => {
        if (normalizedResults.length > 0) {
            const labels = ['0.00 - 0.20'];
            for (let i = 1; i < normalizedResults.length - 1; i++) {
                const startLabel = (0.2 * i) + 0.01;
                const endLabel = 0.2 * (i+1);
                labels.push(`${startLabel.toFixed(2)} - ${endLabel.toFixed(2)}`);
            }

            let data = [];
            for (let i = 0; i < normalizedResults.length - 1; i++) {
                data.push(normalizedResults[i].length);
            }
            const axes = ["Normalized Averages", "Number of Students"];
            setBarChartData([axes, labels, data]);
        }
    }, [normalizedResults]);

    useEffect(() => {
        if (normalizedResults.length > 1) {
            if (normalizedIndex === -1) {
                const displayResult = normalizedResults.flat();
                setNormalizedDisplayData(displayResult);
            } else {
                setNormalizedDisplayData(normalizedResults[normalizedIndex]);
            }
        }
    }, [normalizedResults, normalizedIndex]);

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
            <div style={{ minWidth: "75%", maxWidth: "90%" }} className="modal-content modal-phone">
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
                                if (header === "Reviewee" || header === "Reviewer") {
                                    return (
                                        <Column
                                            key={header}
                                            field={header}
                                            header={header}
                                            sortable
                                            filter
                                            filterPlaceholder="Search by name or email"
                                            filterMatchMode="contains"
                                        ></Column>
                                    );
                                } else if (header === "Team") {
                                    return (
                                        <Column
                                            key={header}
                                            field={header}
                                            header={header}
                                            sortable
                                            filter
                                            filterPlaceholder="Search by team name"
                                            filterMatchMode="contains"
                                        ></Column>
                                    );
                                } else {
                                    return (
                                        <Column
                                            key={header}
                                            field={header}
                                            header={header}
                                            sortable
                                        ></Column>
                                    );
                                }
                            })}
                        </DataTable>
                    </div>
                </div>
                ) : surveyType === "raw-full" && (
                    <div className="viewresults-modal--no-options-selected-text">
                        No Results Found
                    </div>)}
            {surveyType === "average" && normalizedDisplayData.length > 0 ? (
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
                            <BarChart axes={barChartData[0]} labels={barChartData[1]} values={barChartData[2]} filterView={setNormalizedIndex} />
                           
                            <DataTable
                                value={normalizedDisplayData}
                                paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                                paginator
                                rows={5}
                                rowsPerPageOptions={[5, 10, 25, 50]}
                                className="rawresults--table"
                                currentPageReportTemplate="{first} to {last} of {totalRecords}" 
                                emptyMessage="No results found"
                            >
                                {normalizedCSVData[0].map((header) => {
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
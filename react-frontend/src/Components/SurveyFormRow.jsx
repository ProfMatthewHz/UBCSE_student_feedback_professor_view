import React, { useEffect, useState, useCallback } from 'react';
import "../styles/surveyForm.css";

const SurveyFormRow = ({rubricData, surveyResults, setSurveyResults, survey_id, key}) => {
    const [answered, setAnswered] = useState(0);
    const [topicQuestionElements, setTopicQuestionElements] = useState([]);
    const [topicQuestionWidth, setTopicQuestionWidth] = useState(30);
    const [clickedButtons, setClickedButtons] = useState({});

    useEffect(() => {
        if (surveyResults != null && setSurveyResults != null) {
            setSurveyResults(clickedButtons);
        }
    }, [answered, clickedButtons, setSurveyResults, surveyResults]);

    useEffect(() => {
        // Apply width of 250px to each element
        topicQuestionElements.forEach(element => {
          element.style.width = topicQuestionWidth + 'ex';
        });
    }, [topicQuestionElements, topicQuestionWidth]);

    const clickHandler = (response, topic) => {
        const rowID = topic.topic_id !== undefined ? topic.topic_id : topic.question;
        // Set the clicked state for the clicked button in the corresponding row
        setAnswered(answered+1);
        if (clickedButtons[rowID] === response) {
            setClickedButtons(prevState => {
                const newState = { ...prevState };
                delete newState[rowID];
                return newState;
            });
        } else {
        setClickedButtons(prevState => ({
          ...prevState,
          [rowID]: response
        }))
    }};
    
    const buttonClass = (response, topic) => {
        // Determine the class name based on whether the button is clicked or not in the corresponding row
        const rowID = topic.topic_id !== undefined ? topic.topic_id: topic.question;
        return clickedButtons[rowID] === response ? 'response clicked' : 'response unclicked';
    };

    const verticalLineClass = (topic) => {
        // Determine the class name based on whether the button is clicked or not in the corresponding row
        const rowID = topic.topic_id !== undefined ? topic.topic_id: topic.question;
        return clickedButtons[rowID] != null ? 'vertical-line green-vertical-line' : 'vertical-line red-vertical-line';
    }

    const fetchData = useCallback(() =>  {
        fetch(
            process.env.REACT_APP_API_URL_STUDENT + 'getEvalResults.php?reviewed=' + survey_id,
             {
                method: 'GET',
                credentials: 'include'
            })
                .then((res) => res.json())
                .then((result) => {
                    setClickedButtons(result);
                    setSurveyResults(result);
                })
                .catch((error) => {
                    console.error('Error:', error);
                });
    }, [setSurveyResults, survey_id]);

    useEffect(() => {
        if (survey_id != null) {
            fetchData();
        }
    }, [fetchData, survey_id]);

    const topics = rubricData.topics.map(topic => {
        const length = Object.keys(topic.responses).length;
        if (topic.question.length > 60 && topicQuestionWidth !== 30 ) {
            const elements = document.getElementsByClassName('row-topic-question-container');
            setTopicQuestionElements(Array.from(elements));
            setTopicQuestionWidth(60);
        }
        return (
            <div className='row-container' id={topic.question}>
                <div className={verticalLineClass(topic)}>
                    <div className='row-topic-question-container'>    
                        <span className='topic-question'>{topic.question}</span>
                    </div>
                    <div className="response-container">
                        {Object.values(topic.responses).map((response, index) => {
                            return (
                                <div className='table-data-container' style={{flex: 100 / length +'%', 'fontSize': 100 - (length / 5) +'%'}}>
                                    <button onClick={() => clickHandler(response, topic) } className={buttonClass(response, topic)}>{response}</button>
                                </div>    
                            )})}
                    </div>
                </div>      
            </div>
    )});

    return (
        <div className='survey-table-container'>
            {topics}
        </div>
    )
}

export default SurveyFormRow
import React, { useEffect, useState } from 'react';
import "../styles/surveyForm.css";

const SurveyFormRow = ({x, surveyResults, setSurveyResults, survey_id, key}) => {
    const [results, setResults] = useState([]);
    const [answered, setAnswered] = useState(0);
    const [topicQuestionElements, setTopicQuestionElements] = useState([]);
    const [topicQuestionWidth, setTopicQuestionWidth] = useState(150);
    const [clickedButtons, setClickedButtons] = useState({});
    const [oldReviewSelections, setOldReviewSelections] = useState(null);
    
    useEffect(() => {
        if (oldReviewSelections != null) {
            setClickedButtons(oldReviewSelections);
            setSurveyResults(oldReviewSelections);
        }
    }, [oldReviewSelections])

    useEffect(() => {
        if (surveyResults != null && setSurveyResults != null) {
            setSurveyResults(clickedButtons);
        }
    }, [answered]);

    useEffect(() => {
        // Apply width of 250px to each element
        topicQuestionElements.forEach(element => {
          element.style.width = '250px';
        });
    }, [topicQuestionElements]);

    const clickHandler = (response, rowID) => {
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
    
    const buttonClass = (response, rowID) => {
        // Determine the class name based on whether the button is clicked or not in the corresponding row
        return clickedButtons[rowID] === response ? 'clicked' : 'response-button';
    };

    const verticalLineClass = (rowID) => {
        // Determine the class name based on whether the button is clicked or not in the corresponding row
        return clickedButtons[rowID] != null ? 'green-vertical-line' : 'red-vertical-line';
    }

    useEffect(() => {
        const fetchData = async () => {
            try {
                const response = await fetch(process.env.REACT_APP_API_URL_STUDENT + 'getEvalResults.php?reviewed=' +survey_id, {
                    method: 'GET',
                    credentials: 'include'
                });
    
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
    
                const jsonData = await response.json(); 
                setOldReviewSelections(jsonData);
                
            } catch (error) {
                console.error('Error:', error);
            }
        };
    
        fetchData();
    }, []);

    const topics = x.topics.map(topic => {
        let count = -1;
        let width = 150;  
        const length = Object.keys(topic.responses).length;
        if (topic.question.length > 60 && topicQuestionWidth != 250 ) {
            const elements = document.getElementsByClassName('row-topic-question-container');
            setTopicQuestionElements(Array.from(elements));
            setTopicQuestionWidth(250);

        } 
        return (
            <div className='row-container' id={topic.question}>
                <div className={verticalLineClass(topic.topic_id)}>
                    <div className='row-topic-question-container' style={{'min-width': topicQuestionWidth +'px'}}>    
                        <span className='question' >{topic.question}</span>
                    </div>
                    {Object.values(topic.responses).map((response, index) => {
                        return (
                            <div className='table-data-container' style={{width: 100 / length +'%'}}>
                                <button onClick={() => clickHandler(response, topic.topic_id) } className={buttonClass(response, topic.topic_id)} style={{'font-size': 100 - (length / 5) +'%'}}>{response}</button>
                            </div>    
                        )})}
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
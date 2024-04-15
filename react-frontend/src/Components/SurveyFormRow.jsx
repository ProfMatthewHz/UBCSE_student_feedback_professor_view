import React, { useEffect, useState } from 'react';
import "../styles/surveyForm.css";

const SurveyFormRow = ({x, surveyResults, setSurveyResults, student, key}) => {
    const [results, setResults] = useState([]);
    const [answered, setAnswered] = useState(0);
    const [topicQuestionElements, setTopicQuestionElements] = useState([]);
    const [topicQuestionWidth, setTopicQuestionWidth] = useState(150);
    const [clickedButtons, setClickedButtons] = useState({});

    useEffect(() => {
        if (surveyResults != null && setSurveyResults != null && answered === x.topics.length) {
            setSurveyResults(clickedButtons);
            console.log(results);
        }
    }, [answered]);

    useEffect(() => {
        // Apply width of 250px to each element
        topicQuestionElements.forEach(element => {
          element.style.width = '250px';
        });
      }, [topicQuestionElements]);

    const click = (response) => {
        const btns = document.getElementsByClassName("response-button");
        console.log(btns);
        
        setAnswered(answered+1);
        setResults([...results,{response}]);

    }

    const clickHandler = (response, rowID) => {
        // Set the clicked state for the clicked button in the corresponding row
        setClickedButtons(prevState => ({
          ...prevState,
          [rowID]: response
        }))
        setAnswered(answered+1);
    };
    
    const buttonClass = (response, rowID) => {
        // Determine the class name based on whether the button is clicked or not in the corresponding row
        return clickedButtons[rowID] === response ? 'clicked' : 'response-button';
    };

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
                <div className='vertical-line'>
                    <div className='row-topic-question-container' style={{'min-width': topicQuestionWidth +'px'}}>    
                        <span className='question' >{topic.question}</span>
                    </div>
                    {console.log(Object.keys(topic.responses).length)}
                    {Object.values(topic.responses).map((response, index) => {
                        return (
                            <div className='table-data-container' style={{width: 100 / length +'%'}}>
                                <button onClick={() => clickHandler(response, topic.question) } className={buttonClass(response, topic.question)} style={{'font-size': 100 - (response.length / 5) +'%'}}>{response}</button>
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
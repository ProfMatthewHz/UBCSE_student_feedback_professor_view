import { useEffect, useState, useCallback } from 'react';
import "../styles/surveyForm.css";

const SurveyFormRows = ({topicData, setSurveyResults, survey_id}) => {
    const [topicQuestionElements, setTopicQuestionElements] = useState([]);
    const [topicQuestionWidth, setTopicQuestionWidth] = useState(30);
    const [clickedButtons, setClickedButtons] = useState({});

    useEffect(() => {
        if (setSurveyResults != null) {
            setSurveyResults(clickedButtons);
        }
    }, [clickedButtons, setSurveyResults]);

    useEffect(() => {
        // Apply width of 250px to each element
        topicQuestionElements.forEach(element => {
          element.style.width = topicQuestionWidth + 'ex';
        });
    }, [topicQuestionElements, topicQuestionWidth]);

    const clickHandler = (key, topic) => {
        const rowID = topic.topic_id !== undefined ? topic.topic_id : topic.question;
        // Set the clicked state for the clicked button in the corresponding row
        if (clickedButtons[rowID] === key) {
            delete clickedButtons[rowID];
            setClickedButtons({...clickedButtons});
        } else {
            clickedButtons[rowID] = key;
            setClickedButtons({...clickedButtons});
        }
    };
    
    const buttonClass = (key, topic) => {
        // Determine the class name based on whether the button is clicked or not in the corresponding row
        const rowID = topic.topic_id !== undefined ? topic.topic_id: topic.question;
        return clickedButtons[rowID] === key ? 'response clicked' : 'response unclicked';
    };

    const verticalLineClass = (topic) => {
        // Determine the class name based on whether the button is clicked or not in the corresponding row
        const rowID = topic.topic_id !== undefined ? topic.topic_id: topic.question;
        return clickedButtons[rowID] != null ? 'vertical-line green-vertical-line' : 'vertical-line red-vertical-line';
    }

    const fetchData = useCallback((survey_id) =>  {
        let body = new FormData();
        body.append('reviewed', survey_id);
        fetch(
            process.env.REACT_APP_API_URL_STUDENT + 'getEvalResults.php',
             {
                method: 'POST',
                credentials: 'include',
                body: body
            })
                .then((res) => res.json())
                .then((result) => {
                    setClickedButtons(result);
                })
                .catch((error) => {
                    console.error('Error:', error);
                });
    }, []);

    useEffect(() => {
        // Fetch data when the component mounts or when survey_id changesfimction         
        if (survey_id != null) {
            fetchData(survey_id);
        }
    }, [fetchData, survey_id]);

    const topics = topicData.map(topic => {
        const length = Object.keys(topic.responses).length;
        if (topic.question.length > 60 && topicQuestionWidth !== 30 ) {
            const elements = document.getElementsByClassName('row-topic-question-container');
            setTopicQuestionElements(Array.from(elements));
            setTopicQuestionWidth(60);
        }
        return (
            <div className='row-container' id={topic.question} key={topic.question}>
                <div className={verticalLineClass(topic)} id={topic.question+"line"}>
                    <div className='row-topic-question-container'>    
                        <span className='topic-question'>{topic.question}</span>
                    </div>
                    <div className="response-container">
                        {Object.keys(topic.responses).map(key => {
                            key = parseInt(key);
                            return (
                                <div key={topic.responses[key]} className='table-data-container' style={{flex: 100 / length +'%', 'fontSize': 100 - (length / 5) +'%'}}>
                                    <button onClick={() => clickHandler(key, topic) } className={buttonClass(key, topic)}>{topic.responses[key]}</button>
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

export default SurveyFormRows;
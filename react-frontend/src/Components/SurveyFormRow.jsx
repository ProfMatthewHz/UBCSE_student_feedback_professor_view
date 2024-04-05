import React, { useEffect, useState } from 'react';
import "../styles/surveyForm.css";

const SurveyFormRow = ({x}) => {

    const topics = x.topics.map(topic => {
        let count = -1;  
        return (
            <div className='row-container'>
                {/* <div className='vertical-line'></div>
                <h3 className='row-topic-question'>{topic.question}</h3>     */}
                    <div className='vertical-line'>
                        <div className='row-topic-question-container'>
                            {topic.question}
                        </div>
                            {Object.values(topic.responses).map((response, index) => {
                                return (
                                    <div className='table-data-container'>
                                        <button className='response-button'>{response}</button>
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
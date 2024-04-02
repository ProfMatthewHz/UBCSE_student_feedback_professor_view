import React, { useEffect, useState } from 'react';

const SurveyFormRow = ({x}) => {

    const topics = x.topics.map(topic => {
        let count = -1;  
        return (
            <div>
                <h3 className='row-header'>{topic.question}</h3>
                    <tr>
                        {Object.values(topic.responses).map((response, index) => {
                            return (
                                <td>
                                    <div className='table-data-container'>
                                        <div className='table-data-content'>{response}</div>
                                    </div>
                                </td>    
                            )})}
                    </tr>
            </div>
    )});

    return (
        <div className='row-container'>
            <table>
                {topics}
            </table>
        </div>
    )
}

export default SurveyFormRow
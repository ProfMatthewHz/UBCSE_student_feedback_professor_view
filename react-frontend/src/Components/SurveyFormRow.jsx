import React, { useEffect, useState } from 'react';


const SurveyFormRow = ({x}) => {
    const topics = x.topics.map(topic => 
        <div>
            <h3 className='row-header'>{topic.question}</h3>
                <tr>
                    
                        {/* <div className='table-data-container'>
                            <div className='table-data-points'>0pts</div>
                            <div className='table-data-content'>{topic.responses[1]}</div> */}
                            {Object.values(topic.responses).map((response, index) => 
                                // console.log(response)
                                <td>
                                    <div className='table-data-container'>
                                        <div className='table-data-points'>0pts</div>
                                        <div className='table-data-content'>{response}</div>
                                    </div>
                                    </td>
                            )}
                        {/* </div>     */}
                    {/* </td> */}
                </tr>
        </div>
    );
    return (

        <div className='row-container'>
            <table>
                {topics}
            </table>
        </div>
        /* //     <h3 className='row-header'>{x.topics[0].question}</h3>
        //     <table>
        //         <tr>
        //             <td>
        //                 <div className='table-data-container'>
        //                     <div className='table-data-points'>0pts</div>
        //                     <div className='table-data-content'>{x.topics[0].responses[1]}</div>
        //                 </div>
        //             </td>
        //             <td>
        //                 <div className='table-data-container'>
        //                     <div className='table-data-points'>1pts</div>
        //                     <div className='table-data-content'>{x.topics[0].responses[2]}</div>
        //                 </div>
        //             </td>
        //             <td>
        //                 <div className='table-data-container'>
        //                     <div className='table-data-points'>2pts</div>
        //                     <div className='table-data-content'>{x.topics[0].responses[3]}</div>
        //                 </div>
        //             </td>
        //             <td>
        //                 <div className='table-data-container'>
        //                     <div className='table-data-points'>3pts</div>
        //                     <div className='table-data-content'>{x.topics[0].responses[4]}</div>
        //                 </div>
        //             </td>
        //         </tr>
        //     </table>    
        // </div> */

    )
}

export default SurveyFormRow
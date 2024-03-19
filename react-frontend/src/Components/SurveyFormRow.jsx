import React, { useEffect, useState } from 'react';

const SurveyFormRow = ({x}) => {
    return (
        <div className='row-container'>
            <h3 className='row-header'>{x}</h3>
            <table>
                <tr>
                    <td>
                        <div className='table-data-container'>
                            <div className='table-data-points'>0pts</div>
                            <div className='table-data-content'>Does not willingly assume team roles, rarely completes assigned work</div>
                        </div>
                    </td>
                    <td>
                        <div className='table-data-container'>
                            <div className='table-data-points'>1pts</div>
                            <div className='table-data-content'>Usually accepts assigned team roles, occasionally completes assigned work</div>
                        </div>
                    </td>
                    <td>
                        <div className='table-data-container'>
                            <div className='table-data-points'>2pts</div>
                            <div className='table-data-content'>Accepts assigned team roles, mostly completes assigned work</div>
                        </div>
                    </td>
                    <td>
                        <div className='table-data-container'>
                            <div className='table-data-points'>3pts</div>
                            <div className='table-data-content'>Accepts all assigned team roles, always completes assigned work</div>
                        </div>
                    </td>
                </tr>
            </table>    
        </div>
    )
}

export default SurveyFormRow
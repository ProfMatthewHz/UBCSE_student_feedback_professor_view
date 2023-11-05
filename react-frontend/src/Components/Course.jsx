import React, { useEffect, useState } from "react";
import "../styles/course.css";
import "../styles/modal.css";
import Modal from "./Modal";

const Course = ({ course, page }) => {
  const [surveys, setSurveys] = useState([]);

  // MODAL CODE 
  const [modalIsOpen, setModalIsOpen] = useState(false);
  const [modalIsOpenError, setModalIsOpenError] = useState(false);
  const [errorsList, setErrorsList] = useState([]);
  const [rubricNames, setNames] = useState([]);
  const [rubricIDandDescriptions, setIDandDescriptions] = useState([]);
  const [pairingModesFull, setPairingModesFull] = useState([]);
  const [pairingModesNames, setPairingModesNames] = useState([]);

  const fetchRubrics =() => {
    fetch(
      "http://localhost/StudentSurvey/backend/instructor/rubricsGet.php",
      {
        method: "GET",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
      }
    )
      .then((res) => res.json())
      .then((result) => {
        
        //this is an array of objects of example elements {rubricId: 1, rubricDesc: 'exampleDescription'}
        let rubricIDandDescriptions= result.rubrics.map(element => element )
        setIDandDescriptions(rubricIDandDescriptions)
        //An array of just the rubricDesc
        let rubricNames = result.rubrics.map(element => element.rubricDesc)
        setNames(rubricNames)
        //setIDandDescriptions(rubricIDandDescriptions)
      })
      .catch(err => {
        console.log(err)
      })
  };
  const fetchPairingModes =() => {
    fetch(
      "http://localhost/StudentSurvey/backend/instructor/surveyTypesGet.php",
      {
        method: "GET",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
      }
    )
      .then((res) => res.json())
      .then((result) => {
        
        
        let allPairingModeArray = (result.survey_types.mult).concat(result.survey_types.no_mult)
        
        let pairingModeNames = allPairingModeArray.map(element=>element.description)
        let pairingModeFull1 = result.survey_types
        setPairingModesFull(pairingModeFull1)
        setPairingModesNames(pairingModeNames)
        

      })
      .catch(err => {
        console.log(err)
      })
  };
  
  const openModal = () => {
    setModalIsOpen(true);
    fetchRubrics();
    fetchPairingModes();
    

  };

  const closeModal = () => {
    setModalIsOpen(false);
    
  };
  const closeModalError = () => {
    setModalIsOpenError(false);
    
  };

  const getInitialStateRubric = () => {
    const value = "Select Rubric";
    return value;
  };
  const getInitialStatePairing = () => {
    const value = 'Each Team Member Reviewed By Entire Team';
    return value;
  };

  const [valueRubric, setValueRubric] = useState(getInitialStateRubric);

  const [valuePairing, setValuePairing] = useState(getInitialStatePairing);

  const [multiplierNumber, setMultiplierNumber] = useState('one');

  const [validPairingModeForMultiplier, setMultiplier] = useState(false);

  const handleChangeRubric = (e) => {
    setValueRubric(e.target.value);
  };
  const handleChangeMultiplierNumber = (e) => {
    setMultiplierNumber(e.target.value);
  };
  const handleChangePairing = (e) => {
    var boolean = false;

    let multiplierCheckArray = (pairingModesFull.mult).map(element=> element.description);
    if(multiplierCheckArray.includes(e.target.value)){
      boolean = true;
    }
    
    setValuePairing(e.target.value);
    setMultiplier(boolean);
    
  };
  //?course=" + course.id
  ///?course="+course.id

   async function getAddSurveyResponse(formData){
    console.log('this is before the addsurveyResponse function fetch call');
    
    let fetchHTTP = "http://localhost/StudentSurvey/backend/instructor/addSurveyToCourse.php?course="+ course.id
    //let response = await fetch(fetchHTTP,{method: "POST", body: formData});
    try {
      const response = await fetch(fetchHTTP, {
        method: "POST",
        body: formData
      });
      const result = await response.json();
      
      return result; // Return the result directly
    } catch (err) {
      console.error(err);
      throw err; // Re-throw to be handled by the caller
    }
    
    
  };

  

  

  async function verifySurvey(){
    

    var surveyName = (document.getElementById('survey-name').value);
    var startTime = (document.getElementById('start-time').value);
    var endTime = (document.getElementById('end-time').value);
    var startDate = (document.getElementById('start-date').value);
    var endDate = (document.getElementById('end-date').value);
    var csvFile = (document.getElementById('csv-file').value);
    var rubric = (document.getElementById('rubric-type').value);
    
    var dictNameToInputValue = {
      "Survey name": surveyName,
      "Start time": startTime,
      'End time' : endTime,
      'Start date' : startDate,
      'End date' : endDate,
      'Csv file' : csvFile
    };

    for(let k in dictNameToInputValue){
      if(dictNameToInputValue[k] === ""){
          alert(k + ' cannot be empty. Please fill in.');
          return;
      }
    }
    

    //date and time keyboard typing bound checks.
    
    let minDateObject = new Date("2023-08-31T00:00:00");  //first day of class
    let maxDateObject = new Date("2023-12-09T00:00:00"); //last day of class
    let startDateObject = new Date(startDate+'T00:00:00');   //inputted start date.
    let endDateObject = new Date(endDate+'T00:00:00');   //inputted end date.
    if(startDateObject < minDateObject){
      alert('Start Date is too early. Must start atleast at August 31');
      return;
    }
    if(startDateObject > maxDateObject){
      alert('Start Date is too late. Must be at or before December 9');
      return;
    }
    if(endDateObject < minDateObject){
      alert('End Date is too early. Must start atleast at August 31');
      return;
    }
    if(endDateObject > maxDateObject){
      alert('End Date is too late. Must be at or before December 9');
      return;
    }
    //END:date and time keyboard typing bound checks.

    //special startdate case. Startdate cannot be before the current day.
    let timestamp = new Date(Date.now());
    
    timestamp.setHours(0,0,0,0);  //set hours/minutes/seconds/etc to be 0. Just want to deal with the calendar date
    if(startDateObject < timestamp){
      alert('Survey start date cannot be before the current day.')
      return;
    }
    //END:special startdate case. Startdate cannot be before the current day.

    //Start date cannot be greater than End date.
    if(startDateObject>endDateObject){
      alert("Start date cannot be greater than the end date");
      return;
    }
    //END:Start date cannot be greater than End date.

    //If on the same day, start time must be before end time
    if(startDate===endDate){
        if(startTime===endTime){
          alert('If start and end days are the same, Start and End times must differ');
          return;
         }
        let startHour = parseInt(startTime.split(':')[0]);
        let endHour = parseInt(endTime.split(':')[0]);
        if(startHour===endHour){
          alert('If start and end days are the same, Start and End time hours must differ');
          return;
        }
        if(startHour>endHour){
          alert('If start and end days are the same, Start time cannot be after End time');
          return;
        }
      }
        //Start time must be after current time if start date is the current day.
        
        
        console.log('if conditional line 243');
        if(startDateObject.getDay(startDateObject) === timestamp.getDay(timestamp)){
          let timestampWithHour = new Date(Date.now());
          let currentHour = timestampWithHour.getHours(timestampWithHour);
          let currentMinutes = timestampWithHour.getMinutes(timestampWithHour);;
          let startHourNew = parseInt(startTime.split(':')[0]);
          let startMinutes = parseInt(startTime.split(':')[1]);

          if(startHourNew<currentHour){
            alert('Start time hour cannot be before the current hour');
            return;
          }
          if(startHourNew===currentHour){
            if(startMinutes<currentMinutes){
              alert('Start time minutes cannot be before current minutes');
              return;
            }
          }
        //End:Start time must be after current time
        }
    
      

  //Now it's time to send data to the backend

    let formData = new FormData();
    let rubricId;
    let pairingId;
    let multiplier;

    for (const element of rubricIDandDescriptions) {
      if(element.rubricDesc===rubric){
        rubricId = element.rubricId;
      }
    }

    for(const element in pairingModesFull.no_mult){
      
      if(  (pairingModesFull.no_mult[element].description) === document.getElementById('pairing-mode').value  ){
       pairingId = pairingModesFull.no_mult[element].id;
       multiplier = 1;
   }
 }
   for(const element in pairingModesFull.mult){
     
     if(  (pairingModesFull.mult[element].description) === document.getElementById('pairing-mode').value  ){
      pairingId = pairingModesFull.mult[element].id;
      multiplier = document.getElementById('multiplier-type').value;
     }
  } 
  
    let file = document.getElementById('csv-file').files[0];
    
    formData.append("survey-name", surveyName);
    formData.append("course-id", course.id );
    formData.append("rubric-id", rubricId);
    formData.append("pairing-mode", pairingId); 
    formData.append("start-date", startDate);
    formData.append("start-time", startTime);
    formData.append("end-date", endDate);
    formData.append("end-time", endTime);
    formData.append("pm-mult", multiplier);
    formData.append("pairing-file", file);
  
    //form data is set. Call the post request
    let awaitedResponse = await getAddSurveyResponse(formData);
    console.log(awaitedResponse);
    
    //let errorsObject = errorOrSuccessResponse.errors;
    let errorsObject = awaitedResponse.errors;
    let dataObject = awaitedResponse.data;

    
    

    if(errorsObject.length===0){
      //succesful survey. Alert user
      alert("Survey has no errors");
      return;
    }

    if(dataObject.length===0){
      let errorString = errorsObject['pairing-file'];
      setErrorsList(errorString.split("<br>"))
      closeModal();
      setModalIsOpenError(true);

      return;
    }
  

    return;

}



//MODAL CODE

  useEffect(() => {

    fetch(
      "http://localhost/StudentSurvey/backend/instructor/courseSurveysQueries.php",
      {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: new URLSearchParams({
          "course-id": course.id,
        }),
      }
    )
      .then((res) => res.json())
      .then((result) => {
        setSurveys([...result.active, ...result.expired]);
      })
      .catch(err => {
        console.log(err)
      })

  }, []);

  return (
    <div id={course.code} className="courseContainer">
      <Modal open = {modalIsOpenError}
        onRequestClose = {closeModalError}>

          <div style={{ display: 'flex', flexDirection: 'row', flexWrap: 'wrap', borderBottom:'thin solid #225cb5' }} >

            <div style={{ display: 'flex', width:'1250px', marginTop:"2px",paddingBottom: "2px",justifyContent: 'center', gap: '4px', borderBottom:'thin solid #225cb5' }}>
              <h2 style = {{color:'#225cb5'}}>Survey Errors</h2>
              </div>
              {errorsList.map((string, index) => (
                    <div key={index} className="string-list-item">
                      {string}
                    </div>
                  ))}
  
                  </div>

            
           <button className = "Cancel" style= {{borderRadius: '5px',fontSize: '18px', fontWeight:'700', padding:'5px 12px' }} onClick={closeModalError}>Close</button>
      </Modal>
      <Modal open = {modalIsOpen} 
        onRequestClose={closeModal}
        style={{
          content: {
            top: '50%',
            left: '50%',
            right: 'auto',
            bottom: 'auto',
            transform: 'translate(-50%, -50%)',
            backgroundColor: 'white',
            borderRadius: '10px',
            padding: '20px',
            width: '80%',
            maxWidth: '600px'
          },
          overlay: {
            backgroundColor: 'rgba(0, 0, 0, 0.5)'
          }
        }}>
           

           <div style={{ display: 'flex', flexDirection: 'row', flexWrap: 'wrap', borderBottom:'thin solid #225cb5' }} >
            <div style={{ display: 'flex', width:'1250px', marginTop:"2px",paddingBottom: "2px",justifyContent: 'center', gap: '4px', borderBottom:'thin solid #225cb5' }}>
              <h2 style = {{color:'#225cb5'}}>Add A New Survey To The Following Course: {course.code}</h2>
              </div>

              <div marginLeft= '10px' class="input-wrapper">
                  <label style= {{color:'#225cb5'}} for="subject-line">Survey Name</label>
                  <input id="survey-name" class="styled-input" type="text" placeholder="Survey Name"></input>
              </div>

              <div  class="input-wrapper">
                  <label style= {{color:'#225cb5'}} for="subject-line">Rubrics</label>
                  <select style = {{color:'black'}} value = {valueRubric} onChange = {handleChangeRubric} id="rubric-type" class="styled-input"  placeholder="Select a rubric">
                  {rubricNames.map((rubric) => (
                    <option value ={rubric}>{rubric}</option> ))}
                  </select>
              </div>
              
              <div  class="input-wrapper1">
                  <label style= {{color:'#225cb5'}} for="subject-line">Start Time</label>
                  <input id="start-time" class="styled-input1" type="time" placeholder="Enter Start Time"></input>
              </div>
              
              
              <div  class="input-wrapper1">
                  <label style= {{color:'#225cb5'}} for="subject-line">End Time</label>
                  <input id="end-time" class="styled-input1" type="time" placeholder="Enter End Time"></input>
              </div>
              

              
              <div  class="input-wrapper1">
                  <label style= {{color:'#225cb5'}} for="subject-line">Start Date</label>
                  <input id="start-date" class="styled-input1" type="date" min="2023-08-31" max="2023-12-09" placeholder="Enter Start Date"></input>
              </div>

              <div   class="input-wrapper1">
                  <label style={{color:'#225cb5'}} for="subject-line">End Date</label>
                  <input id="end-date" class="styled-input1" type="date" min="2023-08-31" max="2023-12-09" placeholder="Enter End Date"></input>
              </div>

              
              
              <div  class="input-wrapper">
                  <label style= {{color:'#225cb5'}} for="subject-line">Pairing Modes</label>
                  <select style = {{color:'black'}} value = {valuePairing}  onChange = {handleChangePairing} id="pairing-mode" class="styled-input" >
                  {pairingModesNames.map((pairing) => (
                    <option value ={pairing}>{pairing}</option> ))}
                  </select>
              </div>
              

              <div  class="input-wrapper">
                  <label style= {{color:'#225cb5'}} for="subject-line">CSV File Upload</label>
                  <input id="csv-file" class="styled-input" type="file" placeholder="Upload The File"></input>
              </div>


              {validPairingModeForMultiplier ? <div  class="input-wrapper">
                  <label style= {{color:'#225cb5'}} for="subject-line">Multiplier</label>
                  <select style= {{color:'black'}} value = {multiplierNumber} onChange = {handleChangeMultiplierNumber} id="multiplier-type" class="styled-input" >
                  <option value="one">1</option>
                  <option value="two">2</option>
                  <option value="three">3</option>
                  <option value="four">4</option>
                  </select>
              </div> : ''}

           </div>


             <div style={{ display: 'flex', justifyContent: 'center', marginTop: '20px', gap:'50px', marginBottom: '30px' }}>
              <button className = "Cancel" style= {{borderRadius: '5px',fontSize: '18px', fontWeight:'700', padding:'5px 12px' }} onClick={closeModal}>Cancel</button>
              <button className = "CompleteSurvey"style= {{borderRadius: '5px',fontSize: '18px', fontWeight:'700', padding:'5px 12px' }} onClick={verifySurvey} >Verify Survey</button>
             </div>

         </Modal>
      <div className="courseContent">
        <div className="courseHeader">
          <h2>
            {course.code}: {course.name}
          </h2>
          {page === "home" ?
          <div className="courseHeader-btns">
            <button className="btn add-btn" onClick = {openModal}>+ Add Survey</button>
            {/*<button className="btn update-btn">Update Roster</button>*/}
          </div>
            : null
          }
        </div>
        
        {surveys.length > 0 ? (
          <table className="surveyTable">
            <thead>
              <tr>
                <th>Survey Name</th>
                <th>Dates Available</th>
                <th>Completion Rate</th>
              </tr>
            </thead>
            <tbody>
              {surveys.map((survey) => (
                <tr key={survey.id}>
                  <td>{survey.name}</td>
                  <td>
                    Begins: {survey.start_date}
                    <br />
                    Ends: {survey.end_date}
                  </td>
                  <td>{survey.completion}</td>
                  {/*<td><button>Actions</button></td>*/}
                </tr>
              ))}
            </tbody>
          </table>
        ) : (
          <div className="no-surveys">
            {page === "home"
              ? `No Surveys Yet`
              : `No Surveys Created`}
          </div>
        )}
      </div>
    </div>
  );
};

export default Course;

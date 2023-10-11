
//how to create a component

//pascal casing. Capitalize first letter
// for every function component

import logo from './ublogoJPG.jpg'
import background from './webBackground.png'






function Message() {

  

    function handleClick(name:string){
        if(name=="homeButton"){
            
            let element = document.getElementById('homeButton'); 
            element.setAttribute("style", "background-color:#005bbb; color: white ;");

            let otherelement1 = document.getElementById('historyButton'); 
            let otherelement2 = document.getElementById('libraryButton'); 

            otherelement1.setAttribute("style", "background-color:#ffffff; color: #005bbb ;");
            otherelement2.setAttribute("style", "background-color:#ffffff; color: #005bbb ;");
            
        }
        if(name=="historyButton"){
            
            let element = document.getElementById('historyButton'); 
            element.setAttribute("style", "background-color:#005bbb; color: white ;");

            let otherelement1 = document.getElementById('homeButton'); 
            let otherelement2 = document.getElementById('libraryButton'); 

            otherelement1.setAttribute("style", "background-color:#ffffff; color: #005bbb ;");
            otherelement2.setAttribute("style", "background-color:#ffffff; color: #005bbb ;");
            
        }
        if(name=="libraryButton"){
            
            let element = document.getElementById('libraryButton'); 
            element.setAttribute("style", "background-color:#005bbb; color: white ;");

            let otherelement1 = document.getElementById('homeButton'); 
            let otherelement2 = document.getElementById('historyButton'); 

            otherelement1.setAttribute("style", "background-color:#ffffff; color: #005bbb ;");
            otherelement2.setAttribute("style", "background-color:#ffffff; color: #005bbb ;");
            
        }
    }

    
    return (<div className = "titleBar">
            <div className = "bluebarTop">
                text will be colored out
            </div>

            <div className = "whiteTop">
            
                <div>
                    <img src = {logo} alt = "" className = "image"/>
                </div>

                </div>

                
                <div> 
                <button type="button"  id = "homeButton" onClick = {() => handleClick("homeButton")}>Home</button>
                </div>
                
                    
               
                
                <div>   
                <button type="button"   id = "historyButton" onClick = {() => handleClick("historyButton")} >History</button>
                </div>

                
                <div>
                <button type="button"  id = "libraryButton"onClick = {() => handleClick("libraryButton")} >Library</button>
                </div>

                <div className = "leftbar">
                text will be colored out
                </div>

                <div>
                <img src = {background} alt = "" className = "backgroundimage"/>
                </div>

            

            </div>

            


                

        
    )
        
    
}

export default Message;
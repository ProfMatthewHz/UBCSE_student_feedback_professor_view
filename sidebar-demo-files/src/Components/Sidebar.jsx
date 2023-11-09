import '../styles/sidebar.css';
import React, { useEffect, useState } from "react";


function SideBar(props){
  
  const [activeButton, setActiveButton] = useState(false);
  const sidebar_items = Object.values(props.content_dictionary).flatMap((item_list) => (
    item_list.map((item) => item)
  ))


  useEffect(() => {
    const handleScroll = () => {
      const scrollPosition = window.scrollY;
      const sidebar_items_positions = sidebar_items.map((item) =>
        document.getElementById(item).offsetTop - 366
      );

      for (let i = sidebar_items.length - 1; i >= 0; i-- ){
        if (scrollPosition >= sidebar_items_positions[i]) {
          setActiveButton(sidebar_items[i] + "-Option");
          break;
        }
      }
    };

    window.addEventListener('scroll', handleScroll);
    return () => {
      window.removeEventListener('scroll', handleScroll);
    };
  }, [sidebar_items]);



  return (
    <div className="sidebar">
      {Object.entries(props.content_dictionary).map(([title, contents]) => {
        return(
          <div className="sidebar-content" style={Object.keys(props.content_dictionary).length === 1 ? {minHeight: "90%"} : null}>
            <h1>{title}</h1>
            <div className='sidebar-list'>
              {contents.length > 0 ? (
                contents.map(item => {
                  return (
                    <a href={"#" + item}><div onClick={() => setActiveButton(item + "-Option")} id={item + "-Option"} className={activeButton === item + "-Option" ? 'active': item + "-Option"}>{item}</div></a>
                  ) 
                })
              ) : (
                <div className="no-content">No {title}</div>
              )
              }
            </div>
            {props.route === "/" ? <button>+ Add Course</button> : null}
          </div>
        )
      })}
    </div>
  )

};

export default SideBar;

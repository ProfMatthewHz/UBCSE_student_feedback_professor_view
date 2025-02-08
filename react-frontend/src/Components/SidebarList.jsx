import { HashLink as Link } from "react-router-hash-link";
import { useState, useEffect } from "react";
import "../styles/sidebar.css";

const SidebarList = ({ list, emptyMessage }) => {
  const [activeButton, setActiveButton] = useState(false);

  useEffect(() => {
    const handleScroll = () => {
      if (list.length > 0) {
        let name = undefined;
        let closest_to_top = 0;
        for (let item_name of list) {
          const connected_course = document.getElementById(item_name);
          const course_top = connected_course.getBoundingClientRect().y;
          if (connected_course && (!name || closest_to_top < 0 || course_top <= closest_to_top)) {
            name = item_name;
            closest_to_top = course_top;
          }
        }
        const connector = document.getElementById(name + "-Option");
        if (connector) {
          connector.scrollIntoView({behavior: "smooth", block: "center"});
          setActiveButton(name + "-Option");
        }
      }
    };
    window.addEventListener("scroll", handleScroll);
    return () => {
      window.removeEventListener("scroll", handleScroll);
    };
  }, [list]);

  // Close the dropdown menu when the user clicks outside the container
  return (
    <div className="sidebar-list">
      {list.length > 0 ? list.map((item) => {
        return (
          <Link key={item} to={{ hash: "#" + item }}>
            <div
              id={item + "-Option"}
              className={
                list.length !== 1 && activeButton === item + "-Option"
                  ? "active"
                  : item + "-Option"
              }
            >
              {item}
            </div>
          </Link>
        )
      }
      ) : (
        <div className="no-content">{emptyMessage}</div>
      )}
    </div>
  );
}

export default SidebarList;

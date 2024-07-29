import { HashLink as Link } from "react-router-hash-link";
import { useState, useEffect } from "react";

const SidebarList = ({ list, emptyMessage }) => {
  const [activeButton, setActiveButton] = useState(false);

  useEffect(() => {
    const handleScroll = () => {
      if (list.length > 0) {
        const scrollPosition = window.scrollY;
        let name = list[0];
        for (let item_name of list) {
          const connected_course = document.getElementById(item_name);
          if (connected_course && connected_course.offsetTop < scrollPosition) {
            name = item_name;
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

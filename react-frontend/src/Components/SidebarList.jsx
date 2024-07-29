import { HashLink as Link } from "react-router-hash-link";

const SidebarList = ({ list, getActiveButton, emptyMessage }) => {

  // Close the dropdown menu when the user clicks outside the container
  return (
    <div className="sidebar-list">
      {list.length > 0 ? list.map((item) => {
        return (
          <Link key={item} to={{ hash: "#" + item }}>
            <div
              id={item + "-Option"}
              className={
                list.length !== 1 && getActiveButton() === item + "-Option"
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

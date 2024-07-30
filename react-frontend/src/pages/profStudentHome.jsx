import SideBar from "../Components/Sidebar";
import "../styles/home.css";
import "../styles/rubricCourse.css";
import "../styles/sidebar.css";
import SurveyListing from "../Components/SurveyListing";


/**
 * This will be rendered for profs when they click on Student View in side bar
 */
const StudentHome = () => {
  /**
   * The Home component renders a SideBar component and a list of Course components.
   */
  return (
    <>
    <SideBar route="/profStudentHome" content_dictionary={{}}/>
    <SurveyListing return_to="/student" />
    </>
  );
};

export default StudentHome;
